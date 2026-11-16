<?php

/*
 | Malaysian public-holiday configuration for the admin dashboard.
 |
 | National holidays come from a cached public calendar feed and are filtered
 | to the gazetted federal list below. State holidays are curated HERE because
 | no free feed exposes them per state. Only fixed-date holidays are listed;
 | edit freely, and confirm against the official gazette before relying on a date.
 */
return [
    'feed' => env('HOLIDAY_FEED_URL', 'https://calendar.google.com/calendar/ical/en.malaysia%23holiday%40group.v.calendar.google.com/public/basic.ics'),

    // JAKIM prayer-zone prefix => state code used by the lists below.
    // WLY01 (KL/Putrajaya) and WLY02 (Labuan) are Federal Territories, resolved separately.
    'state_by_zone_prefix' => [
        'JHR' => 'MY-01', 'KDH' => 'MY-02', 'KTN' => 'MY-03', 'MLK' => 'MY-04', 'NGS' => 'MY-05',
        'PHG' => 'MY-06', 'PNG' => 'MY-07', 'PRK' => 'MY-08', 'PLS' => 'MY-09', 'SGR' => 'MY-10',
        'TRG' => 'MY-11', 'SBH' => 'MY-12', 'SWK' => 'MY-13', 'WLY' => 'MY-14',
    ],
    'zone_override' => ['WLY02' => 'MY-15'],

    'state_names' => [
        'MY-01' => 'Johor', 'MY-02' => 'Kedah', 'MY-03' => 'Kelantan', 'MY-04' => 'Melaka', 'MY-05' => 'Negeri Sembilan',
        'MY-06' => 'Pahang', 'MY-07' => 'Pulau Pinang', 'MY-08' => 'Perak', 'MY-09' => 'Perlis', 'MY-10' => 'Selangor',
        'MY-11' => 'Terengganu', 'MY-12' => 'Sabah', 'MY-13' => 'Sarawak', 'MY-14' => 'Kuala Lumpur / Putrajaya', 'MY-15' => 'Labuan',
    ],

    // Feed summary (without "(tentative)") => display name. Anything not listed is not shown as national.
    'national' => [
        "Chinese New Year's Day" => 'Tahun Baru Cina',
        'Hari Raya Puasa' => 'Hari Raya Aidilfitri',
        'Hari Raya Puasa Holiday Day 2' => 'Hari Raya Aidilfitri (hari kedua)',
        'Labour Day' => 'Hari Pekerja',
        'Hari Raya Haji' => 'Hari Raya Aidiladha',
        'Wesak Day' => 'Hari Wesak',
        'Muharram' => 'Awal Muharram',
        "The Yang di-Pertuan Agong's Birthday" => 'Hari Keputeraan Yang di-Pertuan Agong',
        "The Prophet Muhammad's Birthday" => 'Maulidur Rasul',
        "Malaysia's National Day" => 'Hari Kebangsaan',
        'Malaysia Day' => 'Hari Malaysia',
        'Diwali' => 'Deepavali',
        'Christmas Day' => 'Hari Krismas',
    ],

    // Weekend days per state (Carbon: 0 = Sunday ... 6 = Saturday). Kedah, Kelantan and Terengganu rest on Friday/Saturday.
    // A holiday on the rest day (Sunday; Friday in those three states) gets a replacement ("cuti ganti") on the next working day.
    'weekend_days' => [
        'default' => [6, 0],
        'MY-02' => [5, 6],
        'MY-03' => [5, 6],
        'MY-11' => [5, 6],
    ],

    // Holidays the feed marks "(regional holiday)" without saying where. Each applies only to the states listed here
    // (KL/Putrajaya = MY-14, Labuan = MY-15). Entries are curated for KL and Selangor; extend the list for other
    // states after checking the official gazette. Feed name => display name + state codes.
    'regional' => [
        "New Year's Day" => ['name' => 'Tahun Baru', 'states' => ['MY-14', 'MY-15', 'MY-10']],
        'Thaipusam' => ['name' => 'Thaipusam', 'states' => ['MY-14', 'MY-15', 'MY-10']],
        'Second Day of Chinese New Year' => ['name' => 'Tahun Baru Cina (hari kedua)', 'states' => ['MY-14', 'MY-15', 'MY-10']],
        'Nuzul Al-Quran' => ['name' => 'Nuzul Al-Quran', 'states' => ['MY-14', 'MY-15', 'MY-10']],
        "Isra and Mi'raj" => ['name' => 'Israk dan Mikraj', 'states' => ['MY-10']],
    ],

    // Fixed-date state holidays: state code => [[month, day, name], ...]
    'state' => [
        'MY-01' => [[3, 23, 'Hari Keputeraan Sultan Johor']],
        'MY-04' => [[4, 15, 'Hari Pengisytiharan Melaka Bandar Bersejarah']],
        'MY-05' => [[1, 14, 'Hari Keputeraan Yang di-Pertuan Besar Negeri Sembilan']],
        'MY-07' => [[7, 7, 'Ulang Tahun Pengisytiharan George Town Tapak Warisan Dunia']],
        'MY-10' => [[12, 11, 'Hari Keputeraan Sultan Selangor']],
        'MY-12' => [[5, 30, 'Pesta Kaamatan (hari pertama)'], [5, 31, 'Pesta Kaamatan (hari kedua)']],
        'MY-13' => [[6, 1, 'Hari Gawai Dayak'], [6, 2, 'Hari Gawai Dayak (hari kedua)'], [7, 22, 'Hari Sarawak']],
        'MY-14' => [[2, 1, 'Hari Wilayah Persekutuan']],
        'MY-15' => [[2, 1, 'Hari Wilayah Persekutuan']],
    ],
];
