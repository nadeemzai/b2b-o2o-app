<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TranslateProductsZh extends Command
{
    protected $signature = 'products:translate-zh
                            {--force : Re-translate products that already have name_zh}
                            {--chunk=20 : How many products to send per API call}
                            {--dry-run : Print translations without saving}';

    protected $description = 'Auto-translate product name_en → name_zh (Chinese) using Claude AI';

    public function handle(): int
    {
        $apiKey = config('services.anthropic.key');
        if (! $apiKey) {
            $this->error('ANTHROPIC_API_KEY not set in .env');
            return 1;
        }

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

        $this->info("Translating {$total} products in chunks of {$this->option('chunk')}...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $chunkSize = (int) $this->option('chunk');
        $updated   = 0;

        foreach ($products->chunk($chunkSize) as $chunk) {
            // Build a numbered list for the prompt
            $list = $chunk->values()->map(fn ($p, $i) => ($i + 1) . '. ' . $p->name_en)->implode("\n");

            $prompt = <<<PROMPT
You are a product catalog translator for a wholesale B2B marketplace.
Translate each of the following English product names into Simplified Chinese (简体中文).
Rules:
- Keep the same numbering.
- Output ONLY the numbered list of Chinese translations, nothing else.
- Use natural, commercially appropriate Chinese product names.
- Do not add extra explanation.

{$list}
PROMPT;

            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model'      => 'claude-haiku-4-5',
                'max_tokens' => 1024,
                'messages'   => [['role' => 'user', 'content' => $prompt]],
            ]);

            if (! $response->successful()) {
                $this->newLine();
                $this->error('API error: ' . $response->body());
                return 1;
            }

            $text  = $response->json('content.0.text', '');
            $lines = collect(explode("\n", trim($text)))
                ->filter(fn ($l) => preg_match('/^\d+\.\s+/', $l))
                ->values();

            foreach ($chunk->values() as $i => $product) {
                $translated = preg_replace('/^\d+\.\s+/', '', $lines->get($i, ''));
                $translated = trim($translated);

                if ($translated === '') {
                    $this->newLine();
                    $this->warn("  No translation returned for: {$product->name_en}");
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
            }

            // Small pause to avoid rate-limiting
            if (! $this->option('dry-run')) {
                usleep(200000); // 200ms
            }
        }

        $bar->finish();
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->info('Dry run complete — nothing was saved.');
        } else {
            $this->info("Done! {$updated} / {$total} products updated with Chinese names.");
        }

        return 0;
    }
}
