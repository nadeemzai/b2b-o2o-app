<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStorePrice;
use App\Models\TownshipStore;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BigCatalogueSeeder extends Seeder
{
    private array $stores = [
        ['code' => 'TS-LHR-01', 'name' => 'Lahore Township Store',   'city' => 'Lahore',     'address' => 'Plot 12, Township Sector B, Lahore',   'phone' => '0300-4001234', 'email' => 'manager@lahore-ts.com'],
        ['code' => 'TS-KHI-01', 'name' => 'Karachi Central Store',   'city' => 'Karachi',    'address' => 'Block 7, Gulshan-e-Iqbal, Karachi',    'phone' => '0300-9001234', 'email' => 'manager@karachi-ts.com'],
        ['code' => 'TS-ISB-01', 'name' => 'Islamabad F-10 Store',    'city' => 'Islamabad',  'address' => 'F-10 Markaz, Islamabad',               'phone' => '0300-5101234', 'email' => 'manager@islamabad-ts.com'],
        ['code' => 'TS-FSD-01', 'name' => 'Faisalabad Madina Store', 'city' => 'Faisalabad', 'address' => 'D-Ground, Madina Town, Faisalabad',    'phone' => '0300-6101234', 'email' => 'manager@faisalabad-ts.com'],
        ['code' => 'TS-RWP-01', 'name' => 'Rawalpindi Saddar Store', 'city' => 'Rawalpindi', 'address' => 'Saddar Bazaar, Rawalpindi',             'phone' => '0300-5551234', 'email' => 'manager@rawalpindi-ts.com'],
    ];

    // [category, sku, name_en, unit, pieces_per_carton, base_price_pkr, description]
    private array $catalogue = [
        // ── Household Cleaning ──────────────────────────────────────────
        ['Household Cleaning','SURF-EXCEL-1KG',      'Surf Excel Detergent 1kg',              'piece',12,  420.00,'Premium stain-remover detergent powder 1kg'],
        ['Household Cleaning','SURF-EXCEL-500G',     'Surf Excel Detergent 500g',             'piece',24,  225.00,'Premium stain-remover detergent 500g'],
        ['Household Cleaning','LIFEBUOY-90G',        'Lifebuoy Soap 90g',                     'piece',48,   65.00,'Anti-bacterial protection bar soap'],
        ['Household Cleaning','ARIEL-1KG',           'Ariel Detergent Powder 1kg',            'piece',12,  450.00,'Auto-machine and hand wash detergent'],
        ['Household Cleaning','VIM-DISHBAR-200G',    'Vim Dish Bar 200g',                     'piece',48,   75.00,'Grease-cutting dish washing bar'],
        ['Household Cleaning','HARPIC-500ML',        'Harpic Toilet Cleaner 500ml',           'piece',24,  280.00,'Power plus toilet cleaner'],
        ['Household Cleaning','DOMEX-500ML',         'Domex Floor Cleaner 500ml',             'piece',24,  255.00,'Multi-surface disinfectant cleaner'],
        ['Household Cleaning','SAFEGUARD-130G',      'Safeguard Antibacterial Soap 130g',     'piece',48,   95.00,'Gold formula anti-bacterial bar'],
        ['Household Cleaning','DETTOL-SOAP-115G',    'Dettol Soap 115g',                      'piece',48,  110.00,'Original protection antiseptic soap'],
        ['Household Cleaning','COMFORT-450ML',       'Comfort Fabric Conditioner 450ml',      'piece',24,  340.00,'Sunrise fresh fabric softener'],
        // ── Dairy & Beverages ───────────────────────────────────────────
        ['Dairy & Beverages', 'TARANG-MP-1KG',      'Tarang Milk Powder 1kg',                'piece', 6, 1150.00,'Fortified tea whitener powder'],
        ['Dairy & Beverages', 'TARANG-MP-500G',     'Tarang Milk Powder 500g',               'piece',12,  595.00,'Fortified tea whitener 500g'],
        ['Dairy & Beverages', 'OLPERS-CR-200ML',    'Olpers Cream 200ml',                    'piece',24,  145.00,'Full-fat UHT cream'],
        ['Dairy & Beverages', 'OLPERS-FM-1L',       'Olpers Full Milk 1L',                   'piece',24,  190.00,'UHT full-cream milk'],
        ['Dairy & Beverages', 'NESTLE-PL-15L',      'Nestle Pure Life 1.5L',                 'piece',12,   80.00,'Natural mineral water 1.5L'],
        ['Dairy & Beverages', 'NESTLE-PL-500ML',    'Nestle Pure Life 500ml',                'piece',24,   45.00,'Natural mineral water 500ml'],
        ['Dairy & Beverages', 'PAKOLA-MG-250ML',    'Pakola Mango 250ml',                    'piece',24,   60.00,'Mango flavoured carbonated drink'],
        ['Dairy & Beverages', 'HALEEB-FM-1L',       'Haleeb Full Cream Milk 1L',             'piece',24,  185.00,'Fresh UHT full-cream milk'],
        ['Dairy & Beverages', 'ROOH-AFZA-800ML',    'Rooh Afza Sharbat 800ml',               'piece',12,  520.00,'Classic herbal rose sherbet'],
        ['Dairy & Beverages', 'DEWS-PINEAP-1L',     'Dews Pineapple Juice 1L',               'piece',12,  175.00,'100% natural pineapple juice'],
        // ── Soft Drinks & Juices ────────────────────────────────────────
        ['Soft Drinks & Juices','PEPSI-500ML',       'Pepsi 500ml PET',                       'piece',24,   75.00,'Cola flavoured carbonated drink'],
        ['Soft Drinks & Juices','COCA-COLA-500ML',   'Coca-Cola 500ml PET',                   'piece',24,   75.00,'Original taste cola drink'],
        ['Soft Drinks & Juices','7UP-500ML',         '7UP 500ml PET',                         'piece',24,   72.00,'Lemon-lime flavoured clear soda'],
        ['Soft Drinks & Juices','SPRITE-345ML',      'Sprite 345ml Can',                      'piece',24,   90.00,'Lemon-lime sparkling drink'],
        ['Soft Drinks & Juices','MOUNTAIN-DEW-500',  'Mountain Dew 500ml',                    'piece',24,   75.00,'Citrus flavoured carbonated drink'],
        ['Soft Drinks & Juices','FANTA-ORANGE-345',  'Fanta Orange 345ml Can',                'piece',24,   90.00,'Orange flavoured carbonated drink'],
        ['Soft Drinks & Juices','RED-BULL-250ML',    'Red Bull Energy Drink 250ml',           'piece',24,  195.00,'Caffeine energy drink with taurine'],
        ['Soft Drinks & Juices','STING-250ML',       'Sting Energy Drink 250ml',              'piece',24,   95.00,'Strawberry blast energy drink'],
        // ── Tea & Coffee ────────────────────────────────────────────────
        ['Tea & Coffee',       'LIPTON-YL-100TB',   'Lipton Yellow Label 100 Tea Bags',      'piece',12,  650.00,'Premium black tea bags 200g'],
        ['Tea & Coffee',       'LIPTON-YL-200G',    'Lipton Yellow Label Loose Tea 200g',    'piece',24,  320.00,'Classic black loose leaf tea'],
        ['Tea & Coffee',       'TAPAL-DF-200G',     'Tapal Danedar Loose Tea 200g',          'piece',24,  310.00,'Extra strong danedar grain tea'],
        ['Tea & Coffee',       'TAPAL-DF-500G',     'Tapal Danedar Loose Tea 500g',          'piece',12,  740.00,'Extra strong danedar 500g'],
        ['Tea & Coffee',       'NESCAFE-CL-200G',   'Nescafe Classic 200g',                  'piece',12, 1850.00,'Instant coffee rich aroma'],
        ['Tea & Coffee',       'NESCAFE-3IN1-10',   'Nescafe 3in1 Original 10 sachets',      'piece',24,  280.00,'Coffee sugar and creamer mix'],
        ['Tea & Coffee',       'BROOKE-BND-200G',   'Brooke Bond Supreme 200g',              'piece',24,  295.00,'Strong blended black tea'],
        // ── Snacks & Noodles ────────────────────────────────────────────
        ['Snacks & Noodles',   'LAYS-MASALA-34G',   'Lays Masala 34g',                       'piece',48,   30.00,'Masala flavoured potato crisps'],
        ['Snacks & Noodles',   'LAYS-SALT-34G',     'Lays Classic Salted 34g',               'piece',48,   30.00,'Classic salted potato crisps'],
        ['Snacks & Noodles',   'KNORR-ND-66G',      'Knorr Noodles Chicken 66g',             'piece',48,   50.00,'Chicken flavoured instant noodles'],
        ['Snacks & Noodles',   'KNORR-ND-VEGE-66',  'Knorr Noodles Vegetable 66g',           'piece',48,   50.00,'Vegetable flavoured instant noodles'],
        ['Snacks & Noodles',   'COCOMO-CHOCO-18G',  'Cocomo Chocolate Wafer 18g',            'piece',96,   20.00,'Chocolate-coated wafer biscuit'],
        ['Snacks & Noodles',   'PRINGLES-SOUR-107', 'Pringles Sour Cream 107g',              'piece',24,  450.00,'Sour cream potato crisps can'],
        ['Snacks & Noodles',   'CHEETOS-PUFFS-27G', 'Cheetos Puffs 27g',                     'piece',48,   40.00,'Cheese flavoured corn puffs'],
        ['Snacks & Noodles',   'INDOMIE-GORENG-80', 'Indomie Mi Goreng 80g',                 'piece',40,   75.00,'Fried noodle instant noodles'],
        // ── Biscuits & Confectionery ────────────────────────────────────
        ['Biscuits & Confectionery','OREO-137G',     'Oreo Chocolate Sandwich 137g',          'piece',24,  170.00,'Cream-filled chocolate sandwich cookie'],
        ['Biscuits & Confectionery','PEEK-FREANS-MR','Peek Freans Marie Biscuit 130g',        'piece',24,   80.00,'Light crispy marie biscuit'],
        ['Biscuits & Confectionery','SOOPER-180G',   'Sooper Biscuit 180g',                   'piece',24,   95.00,'Classic cream sandwich biscuit'],
        ['Biscuits & Confectionery','KITKAT-40G',    'KitKat Milk Chocolate 40g',             'piece',48,  130.00,'4-finger milk chocolate wafer bar'],
        ['Biscuits & Confectionery','DAIRY-MILK-38G','Cadbury Dairy Milk 38g',                'piece',48,  125.00,'Classic milk chocolate bar'],
        ['Biscuits & Confectionery','TOBLERONE-100G','Toblerone Swiss Milk 100g',             'piece',24,  650.00,'Honey almond nougat chocolate'],
        ['Biscuits & Confectionery','CANDY-JELLY-20G','Candy Land Jelly 20g',                'piece',96,   20.00,'Assorted fruit jelly candies'],
        // ── Cooking Oils & Ghee ──────────────────────────────────────────
        ['Cooking Oils & Ghee','DALDA-VEG-GH-1KG',  'Dalda Vegetable Ghee 1kg',              'piece', 6, 1350.00,'Vanaspati vegetable ghee 1kg tin'],
        ['Cooking Oils & Ghee','DALDA-VEG-GH-2KG',  'Dalda Vegetable Ghee 2kg',              'piece', 4, 2650.00,'Vanaspati vegetable ghee 2kg tin'],
        ['Cooking Oils & Ghee','HABIB-COOK-OIL-3L', 'Habib Cooking Oil 3L',                  'piece', 4,  980.00,'Refined cooking canola oil 3L'],
        ['Cooking Oils & Ghee','SOYA-SUPREME-5L',   'Soya Supreme Cooking Oil 5L',           'piece', 4, 1580.00,'Soybean cooking oil 5L can'],
        ['Cooking Oils & Ghee','GOLDEN-GH-1KG',     'Golden Deski Pure Ghee 1kg',            'piece', 6, 2100.00,'Pure desi cow ghee 1kg'],
        ['Cooking Oils & Ghee','CANOLA-GOLD-3L',    'Canola Gold Cooking Oil 3L',            'piece', 4,  950.00,'Heart-healthy canola oil 3L'],
        // ── Spices & Condiments ──────────────────────────────────────────
        ['Spices & Condiments','NATIONAL-CHILLI-100G','National Red Chilli Powder 100g',     'piece',48,  120.00,'Pure ground red chilli spice'],
        ['Spices & Condiments','SHAN-BIRYANI-50G',  'Shan Biryani Masala 50g',               'piece',48,   95.00,'Authentic biryani spice mix'],
        ['Spices & Condiments','SHAN-HALEEM-50G',   'Shan Haleem Masala 50g',                'piece',48,   90.00,'Ready haleem spice mix'],
        ['Spices & Condiments','NATIONAL-KETCHUP-800','National Tomato Ketchup 800g',        'piece',12,  380.00,'Classic tomato ketchup squeeze bottle'],
        ['Spices & Condiments','NATIONAL-CHAAT-50G','National Chaat Masala 50g',             'piece',48,   85.00,'Tangy chaat spice mix'],
        ['Spices & Condiments','KNORR-CHICKEN-CUBE','Knorr Chicken Stock Cube 24pcs',        'piece',24,  160.00,'Ready chicken flavour stock cubes'],
        // ── Staples & Grains ────────────────────────────────────────────
        ['Staples & Grains',   'NATIONAL-RICE-5KG', 'National Basmati Rice 5kg',             'piece', 4, 1050.00,'Long grain premium basmati rice'],
        ['Staples & Grains',   'NATIONAL-RICE-1KG', 'National Basmati Rice 1kg',             'piece',12,  235.00,'Long grain premium basmati rice 1kg'],
        ['Staples & Grains',   'SHAKARGANJ-SUGAR-1KG','Shakarganj Sugar 1kg',                'piece',24,  185.00,'Refined white granulated sugar'],
        ['Staples & Grains',   'SHAKARGANJ-SUGAR-5KG','Shakarganj Sugar 5kg',                'piece', 4,  890.00,'Refined white granulated sugar 5kg'],
        ['Staples & Grains',   'PILSBURY-FLOUR-5KG','Pilsbury Atta Flour 5kg',               'piece', 4,  760.00,'Whole wheat atta flour'],
        ['Staples & Grains',   'NATIONAL-IODINE-SALT','National Iodised Salt 1kg',           'piece',48,   65.00,'Free-flow iodised table salt'],
        ['Staples & Grains',   'MOONG-DAL-500G',    'Moong Dal Split 500g',                  'piece',24,  175.00,'Yellow split mung lentils'],
        ['Staples & Grains',   'MASOOR-DAL-500G',   'Masoor Dal 500g',                       'piece',24,  195.00,'Red split lentils'],
        // ── Personal Care ────────────────────────────────────────────────
        ['Personal Care',      'SUNSILK-SH-185ML',  'Sunsilk Shampoo 185ml',                 'piece',24,  195.00,'Egg protein hair smoothing shampoo'],
        ['Personal Care',      'SUNSILK-SH-350ML',  'Sunsilk Shampoo 350ml',                 'piece',12,  355.00,'Damage restore shampoo 350ml'],
        ['Personal Care',      'DOVE-SH-360ML',     'Dove Intense Repair Shampoo 360ml',     'piece',12,  480.00,'Keratin-fortify repair shampoo'],
        ['Personal Care',      'HEAD-SHLDS-360ML',  'Head and Shoulders Classic 360ml',      'piece',12,  520.00,'Anti-dandruff classic clean shampoo'],
        ['Personal Care',      'LIFEBUOY-BW-250ML', 'Lifebuoy Body Wash 250ml',              'piece',24,  250.00,'Total 10 protection body wash'],
        ['Personal Care',      'DOVE-BW-250ML',     'Dove Go Fresh Body Wash 250ml',         'piece',24,  380.00,'Refreshing cucumber green tea wash'],
        ['Personal Care',      'PONDS-CREAM-200G',  'Ponds Cold Cream 200g',                 'piece',24,  260.00,'Deep nourishing cold cream'],
        ['Personal Care',      'FAIR-LOVELY-75G',   'Glow and Lovely Cream 75g',             'piece',48,  185.00,'Advanced multi-vitamin cream'],
        ['Personal Care',      'NIVEA-CREAM-150ML', 'Nivea Creme Tin 150ml',                 'piece',24,  390.00,'Classic multi-purpose skin cream'],
        // ── Hair Care ────────────────────────────────────────────────────
        ['Hair Care',          'PANTENE-COND-360ML','Pantene Pro-V Conditioner 360ml',        'piece',12,  495.00,'Daily moisture renewal conditioner'],
        ['Hair Care',          'SUNSILK-COND-300ML','Sunsilk Conditioner 300ml',              'piece',12,  295.00,'Honey infused nourishing conditioner'],
        ['Hair Care',          'VATIKA-OIL-200ML',  'Vatika Coconut Hair Oil 200ml',          'piece',24,  290.00,'Coconut herbs enriched hair oil'],
        ['Hair Care',          'BIOAMLA-OIL-200ML', 'Bio Amla Hair Oil 200ml',                'piece',24,  195.00,'Amla-enriched strength hair oil'],
        // ── Oral Care ────────────────────────────────────────────────────
        ['Oral Care',          'COLGATE-PASTE-150G','Colgate Total Toothpaste 150g',          'piece',24,  280.00,'Complete antibacterial toothpaste'],
        ['Oral Care',          'COLGATE-PASTE-75G', 'Colgate Toothpaste 75g',                 'piece',48,  155.00,'Regular clean fluoride toothpaste'],
        ['Oral Care',          'SENSODYNE-75G',     'Sensodyne Whitening Toothpaste 75g',     'piece',48,  450.00,'Daily sensitivity relief toothpaste'],
        ['Oral Care',          'PEPSODENT-175G',    'Pepsodent Germicheck 175g',              'piece',24,  245.00,'Bacteria-fighting toothpaste'],
        ['Oral Care',          'LISTERINE-250ML',   'Listerine Cool Mint 250ml',              'piece',24,  550.00,'Antiseptic mouthwash cool mint'],
        ['Oral Care',          'COLGATE-TB-MEDIUM', 'Colgate Medium Toothbrush',              'piece',48,   85.00,'Spiral bristle medium toothbrush'],
        // ── Baby Products ────────────────────────────────────────────────
        ['Baby Products',      'PAMPERS-NB-42',     'Pampers New Born Diapers 42pcs',         'piece', 4, 1450.00,'Ultra-dry baby diapers NB size up to 5kg'],
        ['Baby Products',      'PAMPERS-S3-40',     'Pampers Baby Dry S3 40pcs',              'piece', 4, 1650.00,'Baby dry diapers size 3 (6-10kg)'],
        ['Baby Products',      'HUGGIES-S3-44',     'Huggies S3 Diaper Pants 44pcs',          'piece', 4, 1700.00,'Pull-up pants diapers size 3'],
        ['Baby Products',      'JOHNSON-SHAMP-200', 'Johnsons Baby Shampoo 200ml',            'piece',24,  390.00,'No-more-tears gentle baby shampoo'],
        ['Baby Products',      'JOHNSON-LOTION-200','Johnsons Baby Lotion 200ml',             'piece',24,  380.00,'Clinically mild baby moisturiser'],
        ['Baby Products',      'NESTLE-CERELAC-250','Nestle Cerelac Rice 250g',               'piece',12,  780.00,'Iron-fortified infant cereal stage 1'],
        // ── Home Care ────────────────────────────────────────────────────
        ['Home Care',          'ODONIL-CAKE-75G',   'Odonil Bathroom Freshener 75g',          'piece',48,  140.00,'Long-lasting bathroom block freshener'],
        ['Home Care',          'MORTEIN-SPRAY-600', 'Mortein Fast Knockdown 600ml',           'piece',12,  650.00,'Instant kill insecticide spray'],
        ['Home Care',          'MORTEIN-COIL-10',   'Mortein Mosquito Coil 10pcs',            'piece',24,  120.00,'8-hour mosquito repellent coils'],
        ['Home Care',          'SCOTCH-BRITE-3PK',  'Scotch-Brite Scrubber Pack of 3',        'piece',24,  165.00,'Heavy-duty scouring pad set'],
        ['Home Care',          'TISSUE-BOX-150SHTS','Saffron Tissue Box 150 sheets',          'piece',24,  175.00,'Soft 2-ply facial tissue box'],
        ['Home Care',          'KLEENEX-WIPES-80',  'Kleenex Wet Wipes 80 sheets',            'piece',12,  380.00,'Moisturising unscented wet wipes'],
        // ── Frozen & Chilled ────────────────────────────────────────────
        ['Frozen & Chilled',   'K&NS-BROAST-1KG',   'K&NS Broast Chicken Pieces 1kg',        'piece', 8,  890.00,'Ready-to-fry marinated broast chicken'],
        ['Frozen & Chilled',   'K&NS-NUGGETS-500G', 'K&NS Chicken Nuggets 500g',             'piece',12,  620.00,'Golden crumbed chicken nuggets'],
        ['Frozen & Chilled',   'K&NS-STRIPS-500G',  'K&NS Chicken Strips 500g',              'piece',12,  640.00,'Crispy seasoned chicken strips'],
        ['Frozen & Chilled',   'LUSSO-ICECREAM-1L', 'Omore Lusso Ice Cream 1L',              'piece', 8,  580.00,'Classic vanilla ice cream tub'],
        ['Frozen & Chilled',   'POLKA-CHOCO-IL',    'Polka Chocolate Ice Cream 1L',          'piece', 8,  540.00,'Rich chocolate ice cream tub'],
    ];


    /** Chinese category name translations */
    private array $categoryZh = [
        'Household Cleaning'       => '家用清洁',
        'Dairy & Beverages'        => '乳制品与饮料',
        'Soft Drinks & Juices'     => '软饮料与果汁',
        'Tea & Coffee'             => '茶与咖啡',
        'Snacks & Noodles'         => '零食与面条',
        'Biscuits & Confectionery' => '饼干糖果',
        'Cooking Oils & Ghee'      => '食用油与酥油',
        'Spices & Condiments'      => '香料与调味品',
        'Staples & Grains'         => '主食与谷物',
        'Personal Care'            => '个人护理',
        'Hair Care'                => '护发产品',
        'Oral Care'                => '口腔护理',
        'Home Care'                => '家居护理',
        'Baby Products'            => '婴儿产品',
        'Frozen & Chilled'         => '冷冻与冷藏',
    ];

    public function run(): void
    {
        // 1. Seed / fetch all stores
        $storeModels = [];
        foreach ($this->stores as $s) {
            $manager = User::firstOrCreate(
                ['email' => $s['email']],
                [
                    'name'              => $s['name'] . ' Manager',
                    'password'          => Hash::make('Manager@12345'),
                    'role'              => 'store_staff',
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ]
            );
            $storeModels[] = TownshipStore::firstOrCreate(
                ['code' => $s['code']],
                [
                    'name'            => $s['name'],
                    'city'            => $s['city'],
                    'address'         => $s['address'],
                    'phone'           => $s['phone'],
                    'manager_user_id' => $manager->id,
                    'is_active'       => true,
                ]
            );
        }
        $this->command->info('Stores ready: ' . count($storeModels));

        // 2. Seed categories, products, and per-store prices
        $productCount = 0;
        $priceCount   = 0;
        // Price multipliers per store index: LHR base, KHI +5%, ISB -3%, FSD +8%, RWP -5%
        $multipliers = [1.00, 1.05, 0.97, 1.08, 0.95];

        foreach ($this->catalogue as [$catName, $sku, $nameEn, $unit, $ppc, $basePrice, $desc]) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($catName)],
                ['name' => $catName, 'name_zh' => $this->categoryZh[$catName] ?? null, 'is_active' => true]
            );

            $product = Product::firstOrCreate(
                ['sku' => $sku],
                [
                    'name_en'           => $nameEn,
                    'description_en'    => $desc,
                    'category_id'       => $category->id,
                    'unit'              => $unit,
                    'pieces_per_carton' => $ppc,
                    'is_active'         => true,
                ]
            );
            $productCount++;

            foreach ($storeModels as $i => $store) {
                // Round to nearest 5 PKR, minimum 10 PKR
                $price = max((int)(round($basePrice * $multipliers[$i] / 5) * 5), 10);
                ProductStorePrice::firstOrCreate(
                    ['product_id' => $product->id, 'store_id' => $store->id],
                    ['price_pkr' => $price, 'is_active' => true]
                );
                $priceCount++;
            }
        }

        $this->command->info("Products seeded  : {$productCount}");
        $this->command->info("Price rows seeded: {$priceCount}");
        $this->command->info('BigCatalogueSeeder complete');
    }
}
