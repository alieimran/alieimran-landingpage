<?php

return [
    // Fallback when no zone has been chosen in the admin dashboard.
    'prayer_zone' => env('JAKIM_PRAYER_ZONE', 'WLY01'),

    // JAKIM e-Solat zones, grouped by state: code => areas covered.
    'prayer_zones' => [
        'Johor' => [
            'JHR01' => 'Pulau Aur, Pulau Pemanggil',
            'JHR02' => 'Johor Bahru, Kota Tinggi, Mersing, Kulai',
            'JHR03' => 'Kluang, Pontian',
            'JHR04' => 'Batu Pahat, Muar, Segamat, Gemas, Tangkak',
        ],
        'Kedah' => [
            'KDH01' => 'Kota Setar, Kubang Pasu, Pokok Sena',
            'KDH02' => 'Kuala Muda, Yan, Pendang',
            'KDH03' => 'Padang Terap, Sik',
            'KDH04' => 'Baling',
            'KDH05' => 'Bandar Baharu, Kulim',
            'KDH06' => 'Langkawi',
            'KDH07' => 'Gunung Jerai',
        ],
        'Kelantan' => [
            'KTN01' => 'Kota Bharu, Bachok, Machang, Pasir Mas, Pasir Puteh, Tanah Merah, Tumpat, Kuala Krai',
            'KTN03' => 'Gua Musang, Jeli, Lojing',
        ],
        'Melaka' => [
            'MLK01' => 'Seluruh negeri Melaka',
        ],
        'Negeri Sembilan' => [
            'NGS01' => 'Tampin, Jempol',
            'NGS02' => 'Seremban, Port Dickson, Jelebu, Kuala Pilah, Rembau',
        ],
        'Pahang' => [
            'PHG01' => 'Pulau Tioman',
            'PHG02' => 'Kuantan, Pekan, Muadzam Shah, Rompin',
            'PHG03' => 'Jerantut, Temerloh, Maran, Bera, Chenor, Jengka',
            'PHG04' => 'Bentong, Lipis, Raub',
            'PHG05' => 'Genting Sempah, Janda Baik, Bukit Tinggi',
            'PHG06' => 'Cameron Highlands, Genting Highlands, Bukit Fraser',
        ],
        'Perak' => [
            'PRK01' => 'Tapah, Slim River, Tanjung Malim',
            'PRK02' => 'Ipoh, Kuala Kangsar, Batu Gajah, Kampar, Sg. Siput',
            'PRK03' => 'Lenggong, Pengkalan Hulu, Grik',
            'PRK04' => 'Temengor, Belum',
            'PRK05' => 'Teluk Intan, Bagan Datuk, Sitiawan, Lumut, Pangkor, Beruas',
            'PRK06' => 'Taiping, Selama, Bagan Serai, Parit Buntar',
            'PRK07' => 'Bukit Larut',
        ],
        'Perlis' => [
            'PLS01' => 'Kangar, Padang Besar, Arau',
        ],
        'Pulau Pinang' => [
            'PNG01' => 'Seluruh negeri Pulau Pinang',
        ],
        'Sabah' => [
            'SBH01' => 'Sandakan (timur), Bukit Garam, Semawang, Temanggong, Tambisan',
            'SBH02' => 'Beluran, Telupid, Pinangah, Terusan, Kuamut, Sandakan (barat)',
            'SBH03' => 'Lahad Datu, Silabukan, Kunak, Sahabat, Semporna, Tungku',
            'SBH04' => 'Tawau, Balong, Merotai, Kalabakan',
            'SBH05' => 'Kudat, Kota Marudu, Pitas, Pulau Banggi',
            'SBH06' => 'Gunung Kinabalu',
            'SBH07' => 'Kota Kinabalu, Ranau, Kota Belud, Tuaran, Penampang, Papar, Putatan',
            'SBH08' => 'Pensiangan, Keningau, Tambunan, Nabawan',
            'SBH09' => 'Beaufort, Kuala Penyu, Sipitang, Tenom, Membakut, Weston',
        ],
        'Sarawak' => [
            'SWK01' => 'Limbang, Lawas, Sundar, Trusan',
            'SWK02' => 'Miri, Niah, Bekenu, Sibuti, Marudi',
            'SWK03' => 'Bintulu, Pandan, Belaga, Suai, Tatau, Sebauh',
            'SWK04' => 'Sibu, Mukah, Dalat, Song, Igan, Oya, Balingian, Kanowit, Kapit',
            'SWK05' => 'Sarikei, Matu, Julau, Rajang, Daro, Bintangor, Belawai',
            'SWK06' => 'Sri Aman, Lubok Antu, Betong, Saratok, Roban, Debak, Kabong',
            'SWK07' => 'Serian, Simunjan, Samarahan, Sebuyau, Meludam',
            'SWK08' => 'Kuching, Bau, Lundu, Sematan',
            'SWK09' => 'Zon khas (Kampung Patarikan)',
        ],
        'Selangor' => [
            'SGR01' => 'Shah Alam, Petaling, Gombak, Sepang, Hulu Langat, Hulu Selangor',
            'SGR02' => 'Kuala Selangor, Sabak Bernam',
            'SGR03' => 'Klang, Kuala Langat',
        ],
        'Terengganu' => [
            'TRG01' => 'Kuala Terengganu, Marang, Kuala Nerus',
            'TRG02' => 'Besut, Setiu',
            'TRG03' => 'Hulu Terengganu',
            'TRG04' => 'Dungun, Kemaman',
        ],
        'Wilayah Persekutuan' => [
            'WLY01' => 'Kuala Lumpur, Putrajaya',
            'WLY02' => 'Labuan',
        ],
    ],
];
