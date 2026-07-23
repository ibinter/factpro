<?php
namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $baseUrl = 'https://factpro.ibigsoft.com';
        $today   = now()->toDateString();

        $urls = [
            ['loc' => $baseUrl, 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => $baseUrl.'/pricing', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => $baseUrl.'/a-propos', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => $baseUrl.'/contact', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => $baseUrl.'/status', 'priority' => '0.6', 'changefreq' => 'hourly'],
            ['loc' => $baseUrl.'/help', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => $baseUrl.'/nouveautes', 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => $baseUrl.'/legal/cgu', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['loc' => $baseUrl.'/legal/confidentialite', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['loc' => $baseUrl.'/legal/cookies', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['loc' => $baseUrl.'/legal/mentions', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['loc' => $baseUrl.'/legal/sla', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['loc' => $baseUrl.'/legal/securite', 'priority' => '0.4', 'changefreq' => 'yearly'],
            ['loc' => $baseUrl.'/legal/rgpd-details', 'priority' => '0.3', 'changefreq' => 'yearly'],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$url['loc']}</loc>\n";
            $xml .= "    <lastmod>{$today}</lastmod>\n";
            $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$url['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
