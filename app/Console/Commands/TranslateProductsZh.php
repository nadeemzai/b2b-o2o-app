<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TranslateProductsZh extends Command
{
    protected $signature = 'products:translate-zh
                            {--force : Re-translate products that already have name_zh}
                            {--dry-run : Print translations without saving}';

    protected $description = 'Auto-translate product name_en → name_zh using free Google Translate';

    // Free Google Translate endpoint (no API key needed)
    private const GT_URL = 'https://translate.googleapis.com/translate_a/single';

    public function handle(): int
    {
        $query = Product::query()->whereNull('deleted_at');
        if (! $this->option('force')) {
            $query->whereNull('name_zh');
        }

        $products = $query->select('id', 'name_en', 'name_zh')->get();
        $total    = $products->count();

        if ($total === 0) {
            $this->info('All products already have Chinese names. Use --force to re-translate.');
            return 0;
        }

        $this->info("Translating {$total} products via Google Translate (free, no key needed)...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $updated = 0;
        $failed  = 0;

        foreach ($products as $product) {
            $translated = $this->translate($product->name_en);

            if ($translated === null) {
                $this->newLine();
                $this->warn("  Failed: {$product->name_en}");
                $failed++;
                $bar->advance();
                continue;
            }

            if ($this->option('dry-run')) {
                $this->newLine();
                $this->line("  [{$product->id}] {$product->name_en} → {$translated}");
            } else {
                Product::where('id', $product->id)->update(['name_zh' => $translated]);
                $updated++;
            }

            $bar->advance();

            // Polite delay to avoid rate-limiting
            usleep(300000); // 300ms between requests
        }

        $bar->finish();
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->info('Dry run complete — nothing was saved.');
        } else {
            $this->info("Done! {$updated} translated, {$failed} failed.");
            if ($failed > 0) {
                $this->warn("Re-run with --force to retry failed ones.");
            }
        }

        return 0;
    }

    private function translate(string $text): ?string
    {
        try {
            $response = Http::timeout(10)->get(self::GT_URL, [
                'client' => 'gtx',
                'sl'     => 'en',
                'tl'     => 'zh-CN',
                'dt'     => 't',
                'q'      => $text,
            ]);

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();

            // Response structure: [[[translated, original, ...],...], ...]
            if (! isset($data[0])) {
                return null;
            }

            $translated = collect($data[0])
                ->pluck(0)
                ->filter()
                ->implode('');

            return trim($translated) ?: null;

        } catch (\Throwable $e) {
            return null;
        }
    }
}
