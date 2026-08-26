<?php

namespace App\Console\Commands;

use App\Http\Controllers\SitemapController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateSitemapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate {--path= : Custom file path to write the sitemap XML}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and write the XML sitemap to public/sitemap.xml for SEO';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generating sitemap XML...');

        $sitemapController = new SitemapController();
        $xml = $sitemapController->buildSitemapXml();

        $destinationPath = $this->option('path') ?: public_path('sitemap.xml');

        File::put($destinationPath, $xml);

        $this->info("Sitemap successfully generated at: {$destinationPath}");

        return self::SUCCESS;
    }
}
