<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStorePrice;
use App\Models\TownshipStore;
use Illuminate\Database\Seeder;

class CatalogueSeeder extends Seeder
{
    /**
     * 10 real FMCG products, categorised, with prices for the Lahore store.
     */
    private array $catalogue = [
        [
            'category' => 'Household Cleaning',
            'products' => [
                ['sku' => 'SURF-EXCEL-1KG', 'name' => 'Surf Excel 1kg',           'unit' => 'piece', 'price' => 420.00],
                ['sku' => 'LIFEBUOY-90G',   'name' => 'Lifebuoy Soap 90g',        'unit' => 'piece', 'price' => 65.00],
            ],
        ],
        [
            'category' => 'Dairy & Beverages',
            'products' => [
                ['sku' => 'TARANG-MP-1KG',  'name' => 'Tarang Milk Powder 1kg',   'unit' => 'piece', 'price' => 1150.00],
                ['sku' => 'OLPERS-CR-200ML', 'name' => 'Olpers Cream 200ml',      'unit' => 'piece', 'price' => 145.00],
                ['sku' => 'NESTLE-PL-15L',  'name' => 'Nestle Pure Life 1.5L',    'unit' => 'piece', 'price' => 80.00],
                ['sku' => 'PAKOLA-MG-250ML', 'name' => 'Pakola Mango 250ml',      'unit' => 'piece', 'price' => 60.00],
            ],
        ],
        [
            'category' => 'Snacks & Noodles',
            'products' => [
                ['sku' => 'LAYS-MASALA-34G', 'name' => 'Lays Masala 34g',         'unit' => 'piece', 'price' => 30.00],
                ['sku' => 'KNORR-ND-66G',    'name' => 'Knorr Noodles 66g',       'unit' => 'piece', 'price' => 50.00],
            ],
        ],
        [
            'category' => 'Personal Care',
            'products' => [
                ['sku' => 'SUNSILK-SH-185ML', 'name' => 'Sunsilk Shampoo 185ml', 'unit' => 'piece', 'price' => 195.00],
            ],
        ],
        [
            'category' => 'Staples',
            'products' => [
                ['sku' => 'NATIONAL-RICE-5KG', 'name' => 'National Rice 5kg',     'unit' => 'piece', 'price' => 1050.00],
            ],
        ],
    ];

    public function run(): void
    {
        $store = TownshipStore::where('code', 'TS-LHR-01')->firstOrFail();

        foreach ($this->catalogue as $cat) {
            $category = Category::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($cat['category'])],
                ['name' => $cat['category'], 'is_active' => true]
            );

            foreach ($cat['products'] as $pd) {
                $product = Product::firstOrCreate(
                    ['sku' => $pd['sku']],
                    [
                        'name_en'     => $pd['name'],
                        'category_id' => $category->id,
                        'unit'        => $pd['unit'],
                        'is_active'   => true,
                    ]
                );

                // Attach category
                $product->categories()->syncWithoutDetaching([$category->id]);

                // Set price for Lahore store
                ProductStorePrice::firstOrCreate(
                    ['product_id' => $product->id, 'store_id' => $store->id],
                    ['price_pkr' => $pd['price'], 'is_active' => true]
                );
            }
        }

        $this->command->info('Catalogue seeded: 5 categories, 10 products with Lahore store prices.');
    }
}
