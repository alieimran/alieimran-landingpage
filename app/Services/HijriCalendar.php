<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use IntlCalendar;

/**
 * Upcoming Islamic occasions. Gregorian dates are computed from the Hijri calendar, then shifted
 * to match JAKIM's official Hijri date for today, so they track Malaysia's moon-sighting calendar
 * as closely as a calculation can. Real dates are announced by the Keeper of the Rulers' Seal and
 * can differ by a day, so every date is an estimate.
 */
class HijriCalendar
{
    public const MONTHS = [1 => 'Muharram', 'Safar', 'Rabiulawal', 'Rabiulakhir', 'Jamadilawal', 'Jamadilakhir', 'Rejab', 'Syaaban', 'Ramadan', 'Syawal', 'Zulkaedah', 'Zulhijjah'];

    /** [month, day, name] */
    private const OCCASIONS = [
        [1, 1, 'Awal Muharram (Maal Hijrah)'],
        [3, 12, 'Maulidur Rasul'],
        [7, 27, 'Israk dan Mikraj'],
        [8, 15, 'Nisfu Syaaban'],
        [9, 1, 'Awal Ramadan'],
        [9, 17, 'Nuzul Al-Quran'],
        [10, 1, 'Hari Raya Aidilfitri'],
        [12, 9, 'Hari Arafah'],
        [12, 10, 'Hari Raya Aidiladha'],
    ];

    /** @param string|null $jakimToday JAKIM hijri date for today, "YYYY-MM-DD" */
    public function __construct(private ?string $jakimToday = null) {}

    public function today(): ?array
    {
        if (! $this->jakimToday || ! preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $this->jakimToday, $m)) {
            return null;
        }

        return ['year' => (int) $m[1], 'month' => (int) $m[2], 'day' => (int) $m[3], 'month_name' => self::MONTHS[(int) $m[2]] ?? '', 'source' => 'JAKIM'];
    }

    /** The occasion dates need PHP's intl extension (IntlCalendar). Without it the card shows a notice instead of failing. */
    public static function supported(): bool
    {
        return class_exists(IntlCalendar::class);
    }

    /** @return list<array{name:string,date:CarbonImmutable,hijri:string,days:int}> */
    public function upcoming(int $limit = 4): array
    {
        if (! self::supported()) {
            return [];
        }

        $today = CarbonImmutable::now('Asia/Kuala_Lumpur')->startOfDay();
        $hijri = $this->today();
        $offset = 0;
        if ($hijri) {
            $anchor = $this->toGregorian($hijri['year'], $hijri['month'], $hijri['day']);
            $offset = (int) $anchor->diffInDays($today, false); // shift calculated dates onto JAKIM's calendar
        }
        $year = $hijri['year'] ?? $this->currentCalculatedYear();

        $items = [];
        foreach ([$year, $year + 1] as $y) {
            foreach (self::OCCASIONS as [$month, $day, $name]) {
                $date = $this->toGregorian($y, $month, $day)->addDays($offset);
                if ($date->gte($today)) {
                    $items[] = ['name' => $name, 'date' => $date, 'hijri' => $day.' '.self::MONTHS[$month].' '.$y, 'days' => (int) $today->diffInDays($date)];
                }
            }
        }
        usort($items, fn ($a, $b) => $a['date'] <=> $b['date']);

        return array_slice($items, 0, $limit);
    }

    private function toGregorian(int $year, int $month, int $day): CarbonImmutable
    {
        $cal = IntlCalendar::createInstance('Asia/Kuala_Lumpur', 'en@calendar=islamic-umalqura');
        $cal->clear();
        $cal->setDateTime($year, $month - 1, $day, 12, 0, 0);

        return CarbonImmutable::instance($cal->toDateTime())->setTimezone('Asia/Kuala_Lumpur')->startOfDay();
    }

    private function currentCalculatedYear(): int
    {
        $cal = IntlCalendar::createInstance('Asia/Kuala_Lumpur', 'en@calendar=islamic-umalqura');

        return (int) $cal->get(IntlCalendar::FIELD_YEAR);
    }
}
