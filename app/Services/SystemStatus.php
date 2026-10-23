<?php

namespace App\Services;

use Composer\InstalledVersions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

class SystemStatus
{
    public function components(): array
    {
        $build = json_decode(@file_get_contents(resource_path('build-info.json')) ?: '{}', true) ?: [];
        $db = null;
        $product = null;
        try {
            if (DB::connection()->getDriverName() !== 'sqlite') {
                $raw = DB::selectOne('SELECT VERSION() AS version')->version;
                $product = stripos($raw, 'MariaDB') !== false ? 'mariadb' : 'mysql';
                preg_match('/(\d+\.\d+\.\d+)/', $raw, $match);
                $db = $match[1] ?? null;
            }
        } catch (Throwable) {
        }

        $rows = [
            ['PHP', PHP_VERSION, 'php', 'Web runtime', 'https://www.php.net/security/'],
            [$product === 'mysql' ? 'MySQL' : 'MariaDB', $db, $product, 'Connected database', 'https://mariadb.com/kb/en/security/'],
            ['Laravel', app()->version(), 'laravel', 'Application framework', 'https://github.com/laravel/framework/security/advisories'],
            ['Node.js', $build['node'] ?? null, 'nodejs', 'Asset build machine; not the production PHP runtime', 'https://nodejs.org/en/blog/vulnerability'],
            ['npm', $build['npm'] ?? null, null, 'Asset build machine', 'https://github.com/npm/cli/security/advisories'],
            ['Vite', $build['vite'] ?? null, null, 'Asset build tool', 'https://github.com/vitejs/vite/security/advisories'],
            ['Composer', InstalledVersions::isInstalled('composer/composer') ? InstalledVersions::getPrettyVersion('composer/composer') : null, null, 'CLI version not exposed to web runtime', 'https://github.com/composer/composer/security/advisories'],
            ['OpenSSL', defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : null, null, 'PHP TLS library; host-managed patches may differ', 'https://openssl-library.org/news/vulnerabilities/'],
            ['Web server', request()->server('SERVER_SOFTWARE') ?: null, null, 'Reported by hosting server', 'https://www.litespeedtech.com/products/litespeed-web-server/release-log'],
        ];

        return array_map(fn ($row) => $this->describe(...$row), $rows);
    }

    public function describe(string $name, ?string $version, ?string $product, string $scope, string $advisories): array
    {
        $row = compact('name', 'version', 'scope', 'advisories') + ['status' => 'Unknown', 'tone' => 'gray', 'latest' => null, 'checked_at' => null, 'eol' => null];
        if (! $version || ! $product || ! preg_match('/^(\d+)\.(\d+)/', $version, $match)) {
            return $row;
        }
        $cycle = in_array($product, ['nodejs', 'laravel']) ? $match[1] : $match[1].'.'.$match[2];
        $data = Cache::remember('lifecycle:'.$product.':'.$cycle, 21600, function () use ($product, $cycle) {
            try {
                $response = Http::connectTimeout(2)->timeout(4)->get('https://endoflife.date/api/v1/products/'.$product.'/releases/'.$cycle);
                $data = $response->json('result');
                if ($response->successful() && is_array($data) && is_bool($data['isEol'] ?? null) && preg_match('/^\d+\.\d+(?:\.\d+)?$/', $data['latest']['name'] ?? '')) {
                    return ['data' => $data, 'checked_at' => now('Asia/Kuala_Lumpur')->format('d M Y H:i')];
                }
            } catch (Throwable) {
            }

            return [];
        });
        if (! isset($data['data'])) {
            return $row;
        }
        $release = $data['data'];
        $row['latest'] = $release['latest']['name'];
        $row['checked_at'] = $data['checked_at'];
        $row['eol'] = $release['eolFrom'] ?? null;
        if ($release['isEol']) {
            $row['status'] = 'End of support';
            $row['tone'] = 'red';
        } elseif (version_compare($version, $row['latest'], '<')) {
            $row['status'] = 'Update available';
            $row['tone'] = 'amber';
        } elseif (version_compare($version, $row['latest'], '==') && ($release['isMaintained'] ?? false) === true) {
            $row['status'] = 'Up to date';
            $row['tone'] = 'green';
        }

        return $row;
    }
}
