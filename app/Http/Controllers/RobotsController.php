<?php

namespace App\Http\Controllers;

use App\Services\SettingService;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(SettingService $settings): Response
    {
        $content = $settings->public()['robots_content'] ?? "User-agent: *\nAllow: /\nSitemap: ".route('sitemap');
        $content = str_replace('Sitemap: /sitemap.xml', 'Sitemap: '.route('sitemap'), (string) $content);

        return response($content."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
