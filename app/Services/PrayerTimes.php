<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class PrayerTimes
{
    public function today(): array
    {
        $zone = strtoupper((string) config('dashboard.prayer_zone', 'WLY01'));
        $date = now('Asia/Kuala_Lumpur')->toDateString();
        if (! preg_match('/^[A-Z]{3}\d{2}$/', $zone)) {
            return ['zone' => $zone, 'date' => $date, 'times' => null];
        }

        return Cache::remember('jakim:'.$zone.':'.$date, 900, function () use ($zone, $date) {
            $result = ['zone' => $zone, 'date' => $date, 'times' => null];
            try {
                $response = Http::connectTimeout(2)->timeout(4)->get('https://www.e-solat.gov.my/index.php', [
                    'r' => 'esolatApi/takwimsolat', 'period' => 'today', 'zone' => $zone,
                ]);
                $row = $response->json('prayerTime.0');
                $serverDate = substr((string) $response->json('serverTime'), 0, 10);
                if ($response->successful() && $serverDate === $date && is_array($row)) {
                    $times = [];
                    foreach (['imsak' => 'Imsak', 'fajr' => 'Subuh', 'syuruk' => 'Syuruk', 'dhuha' => 'Dhuha', 'dhuhr' => 'Zohor', 'asr' => 'Asar', 'maghrib' => 'Maghrib', 'isha' => 'Isyak'] as $key => $label) {
                        if (! preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $row[$key] ?? '')) {
                            return $result;
                        }
                        $times[$label] = substr($row[$key], 0, 5);
                    }
                    $result['times'] = $times;
                    $result['checked_at'] = now('Asia/Kuala_Lumpur')->format('d M Y H:i');
                }
            } catch (Throwable) {
                // An unavailable provider must not break the admin dashboard.
            }

            return $result;
        });
    }
}
