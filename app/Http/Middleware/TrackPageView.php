<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use App\Support\UserAgentParser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageView
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $userAgent = (string) $request->userAgent();

        if ($request->isMethod('GET')
            && $response->isSuccessful()
            && $userAgent !== ''
            && ! UserAgentParser::isBot($userAgent)
        ) {
            PageView::create([
                'path' => '/'.ltrim($request->path(), '/'),
                'referrer' => $request->header('referer'),
                'device_type' => UserAgentParser::deviceType($userAgent),
                'browser' => UserAgentParser::browser($userAgent),
                'os' => UserAgentParser::os($userAgent),
                'visitor_hash' => hash('sha256', $request->ip().$userAgent.now()->format('Y-m-d').config('app.key')),
            ]);
        }

        return $response;
    }
}
