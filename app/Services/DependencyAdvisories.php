<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class DependencyAdvisories
{
    public function report(): array
    {
        $composer = file_get_contents(base_path('composer.lock'));
        $npm = file_get_contents(base_path('package-lock.json'));

        return Cache::remember('advisories:'.hash('sha256', $composer.$npm), 21600, function () use ($composer, $npm) {
            $queries = [];
            foreach (json_decode($composer, true)['packages'] ?? [] as $package) {
                $queries[] = ['package' => ['ecosystem' => 'Packagist', 'name' => $package['name']], 'version' => ltrim($package['version'], 'v')];
            }
            foreach (json_decode($npm, true)['packages'] ?? [] as $path => $package) {
                if (str_contains($path, 'node_modules/') && isset($package['version'])) {
                    $name = substr($path, strrpos($path, 'node_modules/') + 13);
                    $queries[] = ['package' => ['ecosystem' => 'npm', 'name' => $name], 'version' => $package['version']];
                }
            }
            $result = ['checked_at' => now('Asia/Kuala_Lumpur')->format('d M Y H:i'), 'complete' => false, 'findings' => [], 'count' => count($queries)];
            try {
                $response = Http::connectTimeout(2)->timeout(6)->post('https://api.osv.dev/v1/querybatch', ['queries' => $queries]);
                $rows = $response->json('results');
                if (! $response->successful() || ! is_array($rows) || count($rows) !== count($queries)) {
                    return $result;
                }
                $result['complete'] = true;
                foreach ($rows as $index => $row) {
                    if (! is_array($row) || ! empty($row['next_page_token']) || isset($row['error'])) {
                        $result['complete'] = false;
                    }
                    foreach ($row['vulns'] ?? [] as $vuln) {
                        if (is_string($vuln['id'] ?? null)) {
                            $result['findings'][] = ['package' => $queries[$index]['package']['name'], 'version' => $queries[$index]['version'], 'id' => $vuln['id']];
                        }
                    }
                }
            } catch (Throwable) {
                $result['complete'] = false;
            }

            return $result;
        });
    }
}
