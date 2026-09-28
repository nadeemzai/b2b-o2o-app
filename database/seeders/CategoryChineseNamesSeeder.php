<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Populates name_zh for categories seeded by BigCatalogueSeeder / CatalogueSeeder.
 * Run after the add_name_zh_to_categories_table migration:
 *   php artisan db:seed --class=CategoryChineseNamesSeeder
 */
class CategoryChineseNamesSeeder extends Seeder
{
    private array $translations = [
        'Household Cleaning'     => '家用清洁',
        'Dairy & Beverages'      => '乳制品与饮料',
        'Soft Drinks & Juices'   => '软饮料与果汁',
        'Tea & Coffee'           => '茶与咖啡',
        'Snacks & Noodles'       => '零食与面条',
        'Biscuits & Confectionery' => '饼干糖果',
        'Cooking Oils & Ghee'    => '食用油与酥油',
        'Spices & Condiments'    => '香料与调味品',
        'Staples & Grains'       => '主食与谷物',
        'Personal Care'          => '个人护理',
        'Hair Care'              => '护发产品',
        'Oral Care'              => '口腔护理',
        'Home Care'              => '家居护理',
        'Baby Products'          => '婴儿产品',
        'Frozen & Chilled'       => '冷冻与冷藏',
        // Fallback names from CatalogueSeeder
        'Staples'                => '主食',
    ];

    public function run(): void
    {
        foreach ($this->translations as $english => $chinese) {
            Category::where('name', $english)->update(['name_zh' => $chinese]);
        }

        $this->command->info('Chinese category names seeded: ' . count($this->translations) . ' categories updated.');
    }
}
