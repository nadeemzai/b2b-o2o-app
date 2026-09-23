<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

/**
 * Seeds 3 placeholder images per product using picsum.photos with SKU-based
 * seeds so images are consistent across re-seeds. Images are grouped by
 * category seed-prefix so each category gets visually similar image clusters.
 *
 * Replace any image_url row with a real uploaded storage path later —
 * ProductImage::getDisplayUrlAttribute() handles both https:// and storage paths.
 */
class ProductImagesSeeder extends Seeder
{
    /**
     * Map each category name to a picsum "base seed" prefix.
     * Picsum seed strings produce deterministic images, so same SKU → same image.
     *
     * The prefix differentiates categories visually (different random streams).
     */
    private array $categorySeeds = [
        'Household Cleaning'      => 'clean',
        'Dairy & Beverages'       => 'dairy',
        'Soft Drinks & Juices'    => 'drink',
        'Tea & Coffee'            => 'coffee',
        'Snacks & Noodles'        => 'snack',
        'Biscuits & Confectionery'=> 'biscuit',
        'Cooking Oils & Ghee'     => 'oil',
        'Spices & Condiments'     => 'spice',
        'Staples & Grains'        => 'grain',
        'Frozen & Chilled'        => 'frozen',
        'Personal Care'           => 'personal',
        'Hair Care'               => 'hair',
        'Oral Care'               => 'oral',
        'Home Care'               => 'homecare',
        'Baby Products'           => 'baby',
    ];

    public function run(): void
    {
        // Clear existing seeded placeholder images (those with picsum URLs)
        ProductImage::where('image_url', 'like', 'https://picsum.photos/%')->delete();

        $products = Product::with('category')->get();

        $rows = [];

        foreach ($products as $product) {
            $categoryName = $product->category?->name ?? 'general';
            $prefix       = $this->categorySeeds[$categoryName] ?? 'product';
            $sku          = $product->sku ?: 'p' . $product->id;

            for ($i = 1; $i <= 3; $i++) {
                // Seed format: {category_prefix}_{sku}_{variant}
                // 600×600 is large enough to look good on the product card.
                $seed = urlencode("{$prefix}_{$sku}_{$i}");
                $url  = "https://picsum.photos/seed/{$seed}/600/600";

                $rows[] = [
                    'product_id'  => $product->id,
                    'image_url'   => $url,
                    'sort_order'  => $i,
                    'is_primary'  => $i === 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }

            // Flush in batches of 150 to avoid memory spikes
            if (count($rows) >= 150) {
                ProductImage::insert($rows);
                $rows = [];
            }
        }

        if (!empty($rows)) {
            ProductImage::insert($rows);
        }

        $total = ProductImage::count();
        $this->command->info("✓ {$total} product images seeded ({$products->count()} products × 3).");
    }
}
