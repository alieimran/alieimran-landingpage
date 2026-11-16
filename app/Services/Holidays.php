<?php

namespace App\Services;

use App\Models\SiteSetting;
use App\Support\PrayerZones;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Next national and state public holidays, including replacement holidays, for the admin dashboard.
 * See ADMIN_DASHBOARD.md for the data sources and the rules applied.
 */
class Holidays
{
    private const TZ = 'Asia/Kuala_Lumpur';

    public function stateCode(): string
    {
        $zone = PrayerZones::current();

        return config('holidays.zone_override.'.$zone)
            ?? config('holidays.state_by_zone_prefix.'.substr($zone, 0, 3), 'MY-14');
    }

    public function stateName(): string
    {
        return config('holidays.state_names.'.$this->stateCode(), 'your state');
    }

    /** Optional second state chosen in the dashboard (never the same as the zone's state). */
    public function extraStateCode(): ?string
    {
        try {
            $code = (string) SiteSetting::current()->holiday_extra_state;
        } catch (Throwable) {
            return null;
        }

        return $code !== '' && $code !== $this->stateCode() && isset(config('holidays.state_names')[$code]) ? $code : null;
    }

    /** @return list<string> state codes shown on the dashboard: the zone's state, then the optional second one */
    public function stateCodes(): array
    {
        return array_values(array_filter([$this->stateCode(), $this->extraStateCode()]));
    }

    /** @return list<string> display names of the selected states */
    public function stateNames(): array
    {
        return array_map(fn ($c) => config('holidays.state_names.'.$c), $this->stateCodes());
    }

    /**
     * National holidays (plus replacement days, using the weekend rules of the prayer-zone state).
     *
     * @return array{items: list<array{date:CarbonImmutable,name:string,tentative:bool,replacement:bool,days:int}>, available: bool}
     */
    public function national(int $limit = 3): array
    {
        $feed = $this->feed();
        $items = $this->nationalItems($feed['items']);
        $all = [...$items, ...$this->replacements($items, $this->stateCode())];

        return ['items' => $this->upcoming($all, $limit), 'available' => $feed['available']];
    }

    /**
     * Holidays of the selected state(s): fixed-date state holidays + regional feed holidays that apply to the state,
     * plus replacement days. The same holiday in two states is merged into one row listing both.
     *
     * @return list<array{date:CarbonImmutable,name:string,states:list<string>,tentative:bool,replacement:bool,days:int}>
     */
    public function state(int $limit = 4): array
    {
        $today = CarbonImmutable::now(self::TZ)->startOfDay();
        $feed = $this->feed()['items'];
        $nationalDates = array_map(fn ($i) => $i['date']->toDateString(), $this->nationalItems($feed));
        $merged = [];

        foreach ($this->stateCodes() as $code) {
            $stateName = config('holidays.state_names.'.$code);
            $items = [];
            foreach ([$today->year, $today->year + 1] as $year) {
                foreach (config('holidays.state.'.$code, []) as [$month, $day, $name]) {
                    $items[] = ['date' => CarbonImmutable::create($year, $month, $day, 0, 0, 0, self::TZ), 'name' => $name, 'tentative' => false, 'replacement' => false];
                }
            }
            foreach ($feed as $f) {
                if (($f['states'] ?? null) && in_array($code, $f['states'], true)) {
                    $items[] = ['date' => CarbonImmutable::parse($f['date'], self::TZ), 'name' => $f['name'], 'tentative' => $f['tentative'], 'replacement' => false];
                }
            }

            foreach ([...$items, ...$this->replacements($items, $code, $nationalDates)] as $item) {
                $key = $item['date']->toDateString().'|'.$item['name'];
                $merged[$key] ??= [...$item, 'states' => []];
                $merged[$key]['states'][] = $stateName;
            }
        }

        return $this->upcoming(array_values($merged), $limit);
    }

    /** @param list<array{date:string,name:string,tentative:bool,states:?list<string>}> $feedItems */
    private function nationalItems(array $feedItems): array
    {
        return collect($feedItems)->filter(fn ($i) => empty($i['states']))
            ->map(fn ($i) => ['date' => CarbonImmutable::parse($i['date'], self::TZ), 'name' => $i['name'], 'tentative' => $i['tentative'], 'replacement' => false])
            ->values()->all();
    }

    /**
     * Replacement ("cuti ganti") holidays. Under the Holidays Act 1951 a public holiday that falls on the
     * weekly rest day (Sunday; Friday in Kedah, Kelantan and Terengganu) is replaced by the next working day
     * that is not itself a weekend day or another holiday. Dates are estimates until gazetted.
     *
     * @param  list<array>  $items  holidays of one state (or the national set)
     * @param  list<string>  $occupied  other holiday dates (Y-m-d) the replacement must skip
     */
    private function replacements(array $items, string $stateCode, array $occupied = []): array
    {
        $weekend = config('holidays.weekend_days.'.$stateCode, config('holidays.weekend_days.default'));
        $trigger = in_array(5, $weekend, true) ? 5 : 0; // Carbon: 0 = Sunday, 5 = Friday
        $taken = array_flip([...$occupied, ...array_map(fn ($i) => $i['date']->toDateString(), $items)]);

        usort($items, fn ($a, $b) => $a['date'] <=> $b['date']);
        $out = [];
        foreach ($items as $item) {
            if ($item['replacement'] || $item['date']->dayOfWeek !== $trigger) {
                continue;
            }
            $date = $item['date']->addDay();
            while (in_array($date->dayOfWeek, $weekend, true) || isset($taken[$date->toDateString()])) {
                $date = $date->addDay();
            }
            $taken[$date->toDateString()] = true;
            $out[] = ['date' => $date, 'name' => 'Cuti ganti '.$item['name'], 'tentative' => $item['tentative'], 'replacement' => true];
        }

        return $out;
    }

    private function upcoming(array $items, int $limit): array
    {
        $today = CarbonImmutable::now(self::TZ)->startOfDay();

        return collect($items)->filter(fn ($i) => $i['date']->gte($today))
            ->sort(fn ($a, $b) => [$a['date'], $a['replacement']] <=> [$b['date'], $b['replacement']])
            ->take($limit)
            ->map(fn ($i) => [...$i, 'days' => (int) $today->diffInDays($i['date'])])
            ->values()->all();
    }

    /** @return array{items: list<array{date:string,name:string,tentative:bool,states:?list<string>}>, available: bool} */
    private function feed(): array
    {
        return Cache::remember('holidays:feed:v2', 43200, function () {
            try {
                $response = Http::connectTimeout(3)->timeout(8)->get(config('holidays.feed'));
                if (! $response->successful() || ! str_contains($response->body(), 'BEGIN:VCALENDAR')) {
                    return $this->unavailable();
                }
                $items = $this->parse($response->body());

                return $items ? ['items' => $items, 'available' => true] : $this->unavailable();
            } catch (Throwable) {
                return $this->unavailable();
            }
        });
    }

    private function unavailable(): array
    {
        // Short-lived so a transient outage is retried soon instead of for 12 hours.
        Cache::put('holidays:feed:v2', ['items' => [], 'available' => false], 600);

        return ['items' => [], 'available' => false];
    }

    /**
     * Parse the ICS feed. Entries named in config('holidays.national') are national; entries named in
     * config('holidays.regional') apply only to the listed states. Everything else (observances such as
     * Valentine's Day, and the feed's own replacement entries) is ignored: replacements are computed here.
     */
    public function parse(string $ics): array
    {
        $ics = preg_replace("/\r?\n[ \t]/", '', $ics); // unfold wrapped lines
        $national = config('holidays.national');
        $regional = config('holidays.regional');
        $out = [];

        foreach (preg_split('/BEGIN:VEVENT/', $ics) as $block) {
            if (! preg_match('/DTSTART[^:]*:(\d{4})(\d{2})(\d{2})/', $block, $d) || ! preg_match('/^SUMMARY:(.+)$/m', $block, $s)) {
                continue;
            }
            $summary = trim($s[1]);
            $tentative = str_contains($summary, '(tentative)');
            $isRegional = str_contains($summary, '(regional holiday)');
            $clean = trim(str_replace(['(tentative)', '(regional holiday)'], '', $summary));
            $date = "{$d[1]}-{$d[2]}-{$d[3]}";

            // The feed tags Deepavali "regional" although it is a federal holiday.
            if (isset($national[$clean]) && (! $isRegional || $clean === 'Diwali')) {
                $out[] = ['date' => $date, 'name' => $national[$clean], 'tentative' => $tentative, 'states' => null];
            } elseif ($isRegional && isset($regional[$clean])) {
                $out[] = ['date' => $date, 'name' => $regional[$clean]['name'], 'tentative' => $tentative, 'states' => $regional[$clean]['states']];
            }
        }

        return $out;
    }
}
