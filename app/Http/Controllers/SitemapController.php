<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Guide;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class SitemapController extends Controller
{
    /**
     * Render dynamic XML sitemap.
     */
    public function index(): Response
    {
        $content = $this->buildSitemapXml();

        return response($content, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Build the sitemap XML string.
     */
    public function buildSitemapXml(): string
    {
        $urls = [];

        // 1. Static Core Pages
        $urls[] = [
            'loc' => url('/'),
            'lastmod' => Carbon::now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ];

        $urls[] = [
            'loc' => route('doctors.index'),
            'lastmod' => Carbon::now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.9',
        ];

        $urls[] = [
            'loc' => route('guides.index'),
            'lastmod' => Carbon::now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.9',
        ];

        $urls[] = [
            'loc' => route('doctors.apply'),
            'lastmod' => Carbon::now()->startOfMonth()->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.7',
        ];

        // 2. Published Cancer Guides
        $publishedGuides = Guide::published()->with('cancerType')->get();
        foreach ($publishedGuides as $guide) {
            if (! $guide->cancerType) {
                continue;
            }

            $lastmod = $guide->last_updated_at ?? $guide->published_at ?? $guide->updated_at;

            $urls[] = [
                'loc' => route('guides.show', $guide->cancerType->slug),
                'lastmod' => $lastmod ? Carbon::parse($lastmod)->toAtomString() : Carbon::now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ];
        }

        // 3. Published Doctors
        $publishedDoctors = Doctor::published()->get();
        foreach ($publishedDoctors as $doctor) {
            $lastmod = $doctor->last_verified_at ?? $doctor->updated_at;

            $urls[] = [
                'loc' => route('doctors.show', $doctor->slug),
                'lastmod' => $lastmod ? Carbon::parse($lastmod)->toAtomString() : Carbon::now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $item) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($item['loc'], ENT_XML1, 'UTF-8')."</loc>\n";
            $xml .= '    <lastmod>'.$item['lastmod']."</lastmod>\n";
            $xml .= '    <changefreq>'.$item['changefreq']."</changefreq>\n";
            $xml .= '    <priority>'.$item['priority']."</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }
}
