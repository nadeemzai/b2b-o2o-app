<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TranslateProductsZh extends Command
{
    protected $signature = 'products:translate-zh
                            {--force : Re-translate products that already have name_zh}
                            {--dry-run : Print translations without saving}
                            {--debug : Show errors per product}';

    protected $description = 'Auto-translate product name_en → name_zh using free Google Translate';

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

        $this->info("Translating {$total} products via Google Translate (free)...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $updated = 0;
        $failed  = 0;

        foreach ($products as $product) {
            [$translated, $error] = $this->translate($product->name_en);

            if ($translated === null) {
                if ($this->option('debug')) {
                    $this->newLine();
                    $this->warn("  FAIL [{$product->id}] {$product->name_en}: {$error}");
                }
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
            usleep(300000); // 300ms polite delay
        }

        $bar->finish();
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->info('Dry run complete — nothing was saved.');
        } else {
            $this->info("Done! {$updated} translated, {$failed} failed.");
            if ($failed > 0) {
                $this->warn("Re-run with --force --debug to see errors.");
            }
        }

        return 0;
    }

    private function translate(string $text): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Referer'    => 'https://translate.google.com/',
                    'Accept'     => 'application/json, text/plain, */*',
                ])
                ->get(self::GT_URL, [
                    'client' => 'gtx',
                    'sl'     => 'en',
                    'tl'     => 'zh-CN',
                    'dt'     => 't',
                    'q'      => $text,
                ]);

            if (! $response->successful()) {
                return [null, "HTTP {$response->status()}"];
            }

            $data = $response->json();

            if (! isset($data[0])) {
                return [null, 'Unexpected response: ' . $response->body()];
            }

            $translated = collect($data[0])
                ->pluck(0)
                ->filter()
                ->implode('');

            $translated = trim($translated);

            return $translated !== '' ? [$translated, null] : [null, 'Empty result'];

        } catch (\Throwable $e) {
            return [null, $e->getMessage()];
        }
    }
}
