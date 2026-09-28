<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    protected static array $sampleProducts = [
        ['name' => 'Surf Excel 1kg',      'unit' => 'piece'],
        ['name' => 'Tarang Milk Powder 1kg', 'unit' => 'piece'],
        ['name' => 'Lays Masala 34g',     'unit' => 'piece'],
        ['name' => 'Sunsilk Shampoo 185ml', 'unit' => 'piece'],
        ['name' => 'Lifebuoy Soap 90g',   'unit' => 'piece'],
        ['name' => 'Nestle Pure Life 1.5L', 'unit' => 'piece'],
        ['name' => 'Pakola Mango 250ml',  'unit' => 'piece'],
        ['name' => 'Knorr Noodles 66g',   'unit' => 'piece'],
        ['name' => 'Olpers Cream 200ml',  'unit' => 'piece'],
        ['name' => 'National Rice 5kg',   'unit' => 'piece'],
    ];

    private static int $idx = 0;

    public function definition(): array
    {
        $product = self::$sampleProducts[self::$idx % count(self::$sampleProducts)];
        self::$idx++;

        $name = $product['name'];

        return [
            'sku'            => strtoupper(Str::slug($name, '-')) . '-' . $this->faker->numerify('###'),
            'name_en'        => $name,
            'description_en' => null,
            'category_id'    => Category::factory(),
            'unit'           => $product['unit'],
            'image_path'     => null,
            'is_active'      => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
