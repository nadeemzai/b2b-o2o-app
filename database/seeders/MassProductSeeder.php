<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStorePrice;
use App\Models\TownshipStore;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * ~200 additional FMCG products for pagination / search / filter testing.
 * Run: php artisan db:seed --class=MassProductSeeder
 * Format: [category, sku, name_en, unit, pieces_per_carton, base_price_pkr, description_en]
 */
class MassProductSeeder extends Seeder
{
    // Price multipliers: LHR base, KHI +5%, ISB -3%, FSD +8%, RWP -5%
    private array $multipliers = [1.00, 1.05, 0.97, 1.08, 0.95];

    private array $catalogue = [

        // ── Household Cleaning ───────────────────────────────────────────
        ['Household Cleaning','SURF-EXCEL-2KG',       'Surf Excel Detergent 2kg',                 'piece', 6,  820.00,'Premium stain-remover detergent 2kg bag'],
        ['Household Cleaning','RIN-POWDER-1KG',       'Rin Detergent Powder 1kg',                 'piece',12,  360.00,'Brightening blue detergent powder'],
        ['Household Cleaning','ARIEL-500G',            'Ariel Detergent Powder 500g',              'piece',24,  240.00,'Auto-machine wash detergent 500g'],
        ['Household Cleaning','BONUX-1KG',             'Bonux Detergent Powder 1kg',               'piece',12,  310.00,'3-in-1 wash, soften, and freshener'],
        ['Household Cleaning','DETTOL-ANTISEP-200ML', 'Dettol Antiseptic Liquid 200ml',           'piece',24,  290.00,'Original antiseptic and disinfectant'],
        ['Household Cleaning','SAVLON-LIQD-500ML',    'Savlon Antiseptic Liquid 500ml',           'piece',12,  480.00,'Hospital-grade skin antiseptic'],
        ['Household Cleaning','WHEEL-PASTE-200G',     'Wheel Washing Paste 200g',                 'piece',48,   55.00,'Multi-purpose laundry paste bar'],
        ['Household Cleaning','SUPERNET-300G',         'Supernet Detergent Powder 300g',           'piece',48,  110.00,'Economy powder for hand washing'],
        ['Household Cleaning','BRITE-WHITENER-100G',  'Brite Optical Whitener 100g',              'piece',48,   90.00,'Fabric optical brightener sachet'],
        ['Household Cleaning','HARPIC-FLUSHMATIC',    'Harpic Flushmatic In-Cistern 50g',         'piece',48,  195.00,'Continuous toilet bowl cleaner tablet'],
        ['Household Cleaning','PLEDGE-SPRAY-300ML',   'Pledge Furniture Polish 300ml',            'piece',24,  420.00,'Shine and protect wood and surfaces'],
        ['Household Cleaning','PRESTIGE-DISHWASH-1L', 'Prestige Dishwash Liquid 1L',              'piece',12,  320.00,'Lemon-cut grease dishwash liquid'],
        ['Household Cleaning','FAIRY-500ML',           'Fairy Original Dishwash 500ml',            'piece',24,  490.00,'Ultra-concentrated dish liquid'],

        // ── Dairy & Beverages ────────────────────────────────────────────
        ['Dairy & Beverages', 'OLPERS-SLIM-1L',       'Olpers Lite Low-Fat Milk 1L',              'piece',24,  175.00,'UHT low-fat milk 1L'],
        ['Dairy & Beverages', 'GOOD-MILK-1L',         'Good Milk UHT Full Cream 1L',              'piece',24,  195.00,'Fortified full-cream UHT milk'],
        ['Dairy & Beverages', 'NESTLE-YOGURT-400G',   'Nestle Fruit Yogurt 400g',                 'piece',24,  185.00,'Strawberry and mixed fruit yogurt'],
        ['Dairy & Beverages', 'GOURMET-DAHI-400G',    'Gourmet Dahi 400g',                        'piece',24,  130.00,'Fresh plain set yogurt'],
        ['Dairy & Beverages', 'COUNTRY-BUTTER-200G',  'Country Butter Unsalted 200g',             'piece',24,  380.00,'Pure unsalted table butter'],
        ['Dairy & Beverages', 'NURPUR-CHEESE-500G',   'Nurpur Processed Cheese 500g',             'piece',12,  780.00,'Mild processed cheddar block'],
        ['Dairy & Beverages', 'TARANG-MP-250G',       'Tarang Milk Powder 250g',                  'piece',24,  310.00,'Fortified tea whitener sachet pack'],
        ['Dairy & Beverages', 'NESTLE-EVERYDAY-400G', 'Nestle Everyday Dairy Whitener 400g',      'piece',12,  490.00,'Creamy tea whitener powder 400g'],
        ['Dairy & Beverages', 'MANGO-JUIC-250ML',     'Shezan Mango Juice 250ml',                 'piece',24,   55.00,'Natural mango pulp drink tetra'],
        ['Dairy & Beverages', 'SHEZAN-APPLE-250ML',   'Shezan Apple Juice 250ml',                 'piece',24,   55.00,'Natural apple juice tetra pack'],
        ['Dairy & Beverages', 'MAAZA-MANGO-1L',       'Maaza Mango Drink 1L',                     'piece',12,  130.00,'Mango pulp fruit drink 1L PET'],
        ['Dairy & Beverages', 'MINUTE-MAID-1L',       'Minute Maid Pulpy Orange 1L',              'piece',12,  145.00,'Pulpy orange juice drink 1L'],

        // ── Soft Drinks & Juices ─────────────────────────────────────────
        ['Soft Drinks & Juices','PEPSI-1.5L',          'Pepsi 1.5L PET',                           'piece',12,  130.00,'Cola flavoured carbonated drink 1.5L'],
        ['Soft Drinks & Juices','COCA-COLA-1.5L',      'Coca-Cola 1.5L PET',                       'piece',12,  130.00,'Original taste cola drink 1.5L'],
        ['Soft Drinks & Juices','7UP-1.5L',             '7UP 1.5L PET',                             'piece',12,  125.00,'Lemon-lime soda 1.5L'],
        ['Soft Drinks & Juices','SPRITE-1.5L',          'Sprite 1.5L PET',                          'piece',12,  125.00,'Lemon-lime sparkling 1.5L'],
        ['Soft Drinks & Juices','PEPSI-CAN-330ML',      'Pepsi Can 330ml',                          'piece',24,  105.00,'Chilled cola can 330ml'],
        ['Soft Drinks & Juices','COCA-CAN-330ML',       'Coca-Cola Can 330ml',                      'piece',24,  105.00,'Classic cola can 330ml'],
        ['Soft Drinks & Juices','MOUNTAIN-DEW-CAN',     'Mountain Dew Can 330ml',                   'piece',24,  105.00,'Citrus surge cola can 330ml'],
        ['Soft Drinks & Juices','MIRINDA-ORG-345ML',    'Mirinda Orange 345ml Can',                 'piece',24,   90.00,'Orange fizzy drink can 345ml'],
        ['Soft Drinks & Juices','PEPSI-250ML',           'Pepsi Mini Can 250ml',                    'piece',24,   80.00,'Mini cola can 250ml'],
        ['Soft Drinks & Juices','STING-250ML-RB',        'Sting Red Burst 250ml',                   'piece',24,   95.00,'Wild berry energy drink 250ml'],
        ['Soft Drinks & Juices','TROPICANA-ORG-1L',      'Tropicana Orange Juice 1L',               'piece',12,  320.00,'100% pure squeezed orange juice'],
        ['Soft Drinks & Juices','NESTLE-JUICE-250ML',    'Nestle Fruita Vitals 250ml',              'piece',24,   75.00,'Chilled fruit nectar tetra 250ml'],

        // ── Tea & Coffee ─────────────────────────────────────────────────
        ['Tea & Coffee',       'LIPTON-DP-100G',      'Lipton Dip Dust Leaf Tea 100g',            'piece',48,  180.00,'Fine-cut dust tea for quick brew'],
        ['Tea & Coffee',       'VITAL-TEA-500G',      'Vital Tea 500g Pouch',                     'piece',12,  680.00,'Economy blend black tea 500g'],
        ['Tea & Coffee',       'TAPAL-FK-200G',       'Tapal Family Mixture 200g',                'piece',24,  280.00,'Blend of leaf and dust teas'],
        ['Tea & Coffee',       'NESCAFE-GOLD-100G',   'Nescafe Gold Blend 100g',                  'piece',12, 1450.00,'Premium freeze-dried instant coffee'],
        ['Tea & Coffee',       'DAVIDOFF-ESPRESSO-90G','Davidoff Espresso 57 90g',               'piece',12, 1850.00,'Rich dark roast instant espresso'],
        ['Tea & Coffee',       'DILMAH-TEA-100TB',    'Dilmah Ceylon Tea 100 Bags',               'piece',12,  780.00,'Single-origin Ceylon black teabags'],
        ['Tea & Coffee',       'GREENS-OOLONG-25TB',  'Tetley Green Tea 25 Bags',                 'piece',48,  280.00,'Natural green tea antioxidant bags'],
        ['Tea & Coffee',       'AKBAR-GOLD-200G',     'Akbar Gold Premium Tea 200g',              'piece',24,  340.00,'High-grown Ceylon blend leaf tea'],

        // ── Snacks & Noodles ─────────────────────────────────────────────
        ['Snacks & Noodles',   'LAYS-BBQ-34G',        'Lays BBQ 34g',                             'piece',48,   30.00,'Smoky BBQ flavoured potato crisps'],
        ['Snacks & Noodles',   'KURKURE-MASALA-62G',  'Kurkure Masala Munch 62g',                 'piece',48,   50.00,'Crunchy corn puff masala snack'],
        ['Snacks & Noodles',   'PRINGLES-ORIG-107G',  'Pringles Original 107g',                   'piece',24,  450.00,'Original salted potato crisps can'],
        ['Snacks & Noodles',   'DORITOS-NACHO-73G',   'Doritos Nacho Cheese 73g',                 'piece',24,  220.00,'Nacho cheese tortilla chips'],
        ['Snacks & Noodles',   'KNORR-SHRIMP-66G',    'Knorr Noodles Shrimp 66g',                 'piece',48,   50.00,'Shrimp flavoured instant noodles'],
        ['Snacks & Noodles',   'MAGGI-ND-MASALA-70G', 'Maggi Noodles Masala 70g',                 'piece',48,   55.00,'Masala instant noodles 70g'],
        ['Snacks & Noodles',   'YIPPEE-MAGIC-MASALA', 'Sunfeast Yippee Noodles 70g',              'piece',48,   50.00,'Magic masala instant noodles'],
        ['Snacks & Noodles',   'CORNNETTO-50G',        'Cornnetto Roasted Corn 50g',               'piece',48,   40.00,'Crunchy roasted corn kernels'],
        ['Snacks & Noodles',   'BISCONNI-COCO-60G',   'Bisconni Cocomo Choco 60g',                'piece',48,   60.00,'Chocolate filled wafer snack'],
        ['Snacks & Noodles',   'SAFEWAY-RINGS-60G',   'Safeway Onion Rings 60g',                  'piece',48,   45.00,'Crispy fried onion ring snack'],

        // ── Biscuits & Confectionery ─────────────────────────────────────
        ['Biscuits & Confectionery','OREO-DOUBLE-150G',   'Oreo Double Stuf 150g',               'piece',24,  195.00,'Extra cream double stuffed cookies'],
        ['Biscuits & Confectionery','PEEK-HIDE-SEEK-100', 'Peek Freans Hide and Seek 100g',      'piece',24,   95.00,'Chocolate chip sandwich biscuit'],
        ['Biscuits & Confectionery','BISCONNI-SOOPER-130','Bisconni Sooper 130g',                 'piece',48,   80.00,'Cream-filled sandwich biscuit'],
        ['Biscuits & Confectionery','MALIBAN-MILK-180G',  'Maliban Milk Short Cake 180g',         'piece',24,  110.00,'Milk-enriched short cake biscuits'],
        ['Biscuits & Confectionery','MCVITIES-DIGES-200G','McVities Digestive 200g',              'piece',24,  320.00,'Classic whole-wheat digestive biscuit'],
        ['Biscuits & Confectionery','KITKAT-CHUNKY-40G',  'KitKat Chunky 40g',                    'piece',48,  145.00,'Thick chunky chocolate wafer bar'],
        ['Biscuits & Confectionery','SNICKERS-50G',        'Snickers 50g',                         'piece',48,  145.00,'Peanut caramel chocolate bar'],
        ['Biscuits & Confectionery','BOUNTY-57G',           'Bounty Milk 57g',                      'piece',48,  145.00,'Coconut milk chocolate bar'],
        ['Biscuits & Confectionery','TWIX-50G',             'Twix Caramel Biscuit 50g',             'piece',48,  145.00,'Caramel biscuit chocolate finger'],
        ['Biscuits & Confectionery','GALAXY-SMOOTH-42G',   'Galaxy Smooth Milk Chocolate 42g',    'piece',48,  120.00,'Silky smooth milk chocolate bar'],
        ['Biscuits & Confectionery','PATCHI-CHOCO-100G',   'Patchi Dark Chocolate 100g',          'piece',24,  680.00,'Artisan 70% dark chocolate slab'],
        ['Biscuits & Confectionery','HARIBO-BEARS-100G',   'Haribo Gold Bears 100g',              'piece',24,  380.00,'Classic fruit gummy bears candy'],
        ['Biscuits & Confectionery','MENTOS-FRUIT-37G',    'Mentos Fruit Chewy 37g',              'piece',96,   55.00,'Fruit-flavoured chewy mints roll'],

        // ── Cooking Oils & Ghee ──────────────────────────────────────────
        ['Cooking Oils & Ghee','DALDA-PALM-5L',       'Dalda Palm Oil 5L',                        'piece', 4, 1850.00,'Pure refined palm cooking oil 5L'],
        ['Cooking Oils & Ghee','SUFI-OIL-3L',         'Sufi Sunflower Oil 3L',                    'piece', 4,  940.00,'Light sunflower cooking oil 3L'],
        ['Cooking Oils & Ghee','SUFI-OIL-5L',         'Sufi Sunflower Oil 5L',                    'piece', 4, 1550.00,'Light sunflower cooking oil 5L'],
        ['Cooking Oils & Ghee','SHAN-OIL-3L',         'Shan Canola Oil 3L',                       'piece', 4,  920.00,'Cholesterol-free canola oil 3L'],
        ['Cooking Oils & Ghee','SEASONS-OLIVE-500ML', 'Seasons Pure Olive Oil 500ml',             'piece',12, 1480.00,'100% pure extra light olive oil'],
        ['Cooking Oils & Ghee','NUTIVA-COCONUT-500ML','Nutiva Coconut Oil 500ml',                  'piece',12, 1650.00,'Organic virgin coconut oil 500ml'],
        ['Cooking Oils & Ghee','ZULFI-GH-500G',       'Zulfi Desi Ghee 500g',                     'piece',12, 1100.00,'Pure buffalo milk desi ghee 500g'],
        ['Cooking Oils & Ghee','PHULKARI-GH-1KG',     'Phulkari Pure Ghee 1kg',                   'piece', 6, 2150.00,'Farm-fresh pure desi ghee 1kg'],

        // ── Spices & Condiments ──────────────────────────────────────────
        ['Spices & Condiments','SHAN-NIHARI-50G',      'Shan Nihari Masala 50g',                   'piece',48,   95.00,'Slow-cook nihari spice blend'],
        ['Spices & Condiments','SHAN-KARAHI-50G',      'Shan Karahi Gosht 50g',                    'piece',48,   90.00,'Restaurant-style karahi masala'],
        ['Spices & Condiments','SHAN-PULAO-50G',       'Shan Pulao Masala 50g',                    'piece',48,   85.00,'Aromatic rice pulao spice mix'],
        ['Spices & Condiments','NATIONAL-QORMA-50G',   'National Qorma Masala 50g',                'piece',48,   85.00,'Rich qorma curry spice blend'],
        ['Spices & Condiments','NATIONAL-BBQ-50G',     'National BBQ Masala 50g',                  'piece',48,   85.00,'Smoky BBQ spice rub'],
        ['Spices & Condiments','NATIONAL-KETCHUP-500G','National Tomato Ketchup 500g',             'piece',24,  260.00,'Classic tomato ketchup 500g squeeze'],
        ['Spices & Condiments','REMIA-MAYO-946ML',     'Remia Real Mayonnaise 946ml',              'piece',12,  650.00,'Creamy egg mayonnaise jar 946ml'],
        ['Spices & Condiments','RAFHAN-MUSTARD-200G',  'Rafhan Mustard Paste 200g',                'piece',48,  120.00,'Tangy yellow mustard paste tube'],
        ['Spices & Condiments','KNORR-SOYA-630ML',     'Knorr Soy Sauce 630ml',                    'piece',12,  420.00,'Dark soy sauce for stir-fry'],
        ['Spices & Condiments','VINEGAR-SHANAZ-500ML', 'Shanaz White Vinegar 500ml',               'piece',24,  110.00,'Distilled white vinegar 500ml'],
        ['Spices & Condiments','TAMARIND-PASTE-200G',  'Ahmed Foods Tamarind Paste 200g',          'piece',48,  130.00,'Concentrated imli tamarind paste'],
        ['Spices & Condiments','NATIONAL-MANGO-PICKLE','National Mango Pickle 1kg',                'piece',12,  380.00,'Spicy whole mango achar 1kg'],

        // ── Staples & Grains ─────────────────────────────────────────────
        ['Staples & Grains',   'GUARD-RICE-5KG',      'Guard Basmati Rice 5kg',                   'piece', 4, 1080.00,'Aged extra-long grain basmati 5kg'],
        ['Staples & Grains',   'GUARD-RICE-1KG',      'Guard Basmati Rice 1kg',                   'piece',12,  240.00,'Aged extra-long grain basmati 1kg'],
        ['Staples & Grains',   'SELLA-RICE-5KG',      'Sella Parboiled Rice 5kg',                 'piece', 4,  920.00,'Parboiled sella rice non-sticky 5kg'],
        ['Staples & Grains',   'ROSHAN-ATTA-10KG',    'Roshan Flour 10kg',                        'piece', 2, 1380.00,'Premium whole wheat atta 10kg bag'],
        ['Staples & Grains',   'SOOPER-MAIDA-1KG',    'Sooper Maida Refined Flour 1kg',           'piece',24,  155.00,'Fine all-purpose white flour 1kg'],
        ['Staples & Grains',   'IODINE-DIAMOND-1KG',  'Diamond Iodised Salt 1kg',                 'piece',48,   65.00,'Free-flow iodised table salt 1kg'],
        ['Staples & Grains',   'LENTIL-CHANA-500G',   'Chana Dal 500g',                           'piece',24,  165.00,'Split gram lentil (chana dal) 500g'],
        ['Staples & Grains',   'LENTIL-URID-500G',    'Urid Dal 500g',                            'piece',24,  190.00,'Black split gram lentil 500g'],
        ['Staples & Grains',   'OATS-QUAKER-500G',    'Quaker Oats 500g',                         'piece',24,  380.00,'100% wholegrain rolled oats 500g'],
        ['Staples & Grains',   'VERMICELLI-150G',     'Shahi Vermicelli 150g',                    'piece',48,   80.00,'Fine wheat vermicelli for kheer'],
        ['Staples & Grains',   'RAFHAN-SUJI-500G',    'Rafhan Semolina Suji 500g',                'piece',24,  125.00,'Fine-ground semolina for halwa'],

        // ── Personal Care ────────────────────────────────────────────────
        ['Personal Care',      'DOVE-BEAUTY-100G',    'Dove Beauty Cream Bar 100g',               'piece',48,  155.00,'Moisturising 1/4 cream beauty bar'],
        ['Personal Care',      'LUX-CREAM-150G',      'Lux Creamy Perfection 150g',               'piece',48,  140.00,'French cream luxury soap bar'],
        ['Personal Care',      'LIFEBUOY-TOTAL-125G', 'Lifebuoy Total 10 Soap 125g',              'piece',48,   95.00,'10-in-1 germ protection bar'],
        ['Personal Care',      'SAFEGUARD-FAMILY-175','Safeguard Family Shield 175g',             'piece',48,  115.00,'Family health soap antibacterial'],
        ['Personal Care',      'VASELINE-JELLY-50G',  'Vaseline Petroleum Jelly 50g',             'piece',48,  135.00,'Healing petroleum jelly 50g'],
        ['Personal Care',      'PONDS-BB-CREAM-18G',  'Ponds BB Cream SPF30 18g',                 'piece',48,  280.00,'Lightening BB cream SPF30 tube'],
        ['Personal Care',      'OLAY-TOTAL-50G',      'Olay Total Effects Cream 50g',             'piece',24,  880.00,'7-in-1 anti-ageing moisturiser'],
        ['Personal Care',      'GILLETTE-FOAM-200ML', 'Gillette Regular Shave Foam 200ml',        'piece',24,  380.00,'Classic protection shaving foam'],
        ['Personal Care',      'GILLETTE-MACH3-4PK',  'Gillette Mach3 Blade 4 Pack',              'piece',12,  950.00,'Triple-blade refill cartridges 4pk'],
        ['Personal Care',      'LADY-SPEED-DEOS-75ML','Lady Speed Stick Deodorant 75ml',          'piece',48,  380.00,'Fresh and gentle women deodorant'],
        ['Personal Care',      'AXE-DEOS-150ML',      'Axe Dark Temptation Deodorant 150ml',     'piece',24,  420.00,'Long-lasting men body spray'],
        ['Personal Care',      'LOREAL-SERUM-40ML',   'L\'Oreal Total Repair Serum 40ml',        'piece',24, 1200.00,'Hair damage repair serum 40ml'],

        // ── Hair Care ────────────────────────────────────────────────────
        ['Hair Care',          'PANTENE-SH-360ML',    'Pantene Pro-V Total Damage 360ml',         'piece',12,  490.00,'10-symptom damage repair shampoo'],
        ['Hair Care',          'DOVE-SH-INTENSE-360', 'Dove Intense Nourish Shampoo 360ml',       'piece',12,  475.00,'Intense moisture replenish shampoo'],
        ['Hair Care',          'LOREAL-SH-360ML',     'L\'Oreal Smooth Intense Shampoo 360ml',   'piece',12,  520.00,'Anti-frizz straightening shampoo'],
        ['Hair Care',          'CLEAR-SH-MENTHOL-360','Clear Ice Cool Menthol Shampoo 360ml',    'piece',12,  460.00,'Cool menthol anti-dandruff shampoo'],
        ['Hair Care',          'DOVE-COND-INT-300ML', 'Dove Intense Conditioner 300ml',          'piece',12,  390.00,'Moisture intensive repair conditioner'],
        ['Hair Care',          'PANTENE-COND-INT-360','Pantene Intensive Conditioner 360ml',     'piece',12,  480.00,'Miraculous recovery conditioner'],
        ['Hair Care',          'PARACHUTE-OIL-200ML', 'Parachute Coconut Hair Oil 200ml',        'piece',24,  220.00,'Pure coconut oil for hair'],
        ['Hair Care',          'JAMILA-HENNA-250G',   'Jamila Henna Powder 250g',                'piece',24,  180.00,'Natural mehndi hair colour powder'],
        ['Hair Care',          'GARNIER-COLOR-60ML',  'Garnier Olia Hair Colour 60ml',           'piece',24,  650.00,'Oil-powered permanent hair colour'],

        // ── Oral Care ────────────────────────────────────────────────────
        ['Oral Care',          'COLGATE-MAX-150G',    'Colgate MaxFresh Toothpaste 150g',         'piece',24,  295.00,'Cooling crystals fresh blast paste'],
        ['Oral Care',          'CLOSEUP-RED-150G',    'Closeup Red Hot Toothpaste 150g',          'piece',24,  260.00,'Fire-freeze fresh toothpaste'],
        ['Oral Care',          'SIGNAL-WHITENING-75G','Signal White Now 75g',                     'piece',48,  280.00,'Instant whitening toothpaste'],
        ['Oral Care',          'ORALB-CROSS-TB',      'Oral-B CrossAction Toothbrush',            'piece',24,  320.00,'Angled cross-action bristle brush'],
        ['Oral Care',          'COLGATE-TB-SOFT',     'Colgate Soft Zigzag Toothbrush',           'piece',48,   90.00,'Soft zigzag bristle toothbrush'],
        ['Oral Care',          'LISTERINE-500ML',     'Listerine Total Care 500ml',               'piece',12,  780.00,'6-in-1 antibacterial mouthwash'],
        ['Oral Care',          'FLOSS-ORAL-40M',      'Oral-B Dental Floss 40m',                  'piece',24,  185.00,'Shred-resistant waxed dental floss'],

        // ── Baby Products ─────────────────────────────────────────────────
        ['Baby Products',      'PAMPERS-S1-44',       'Pampers Active Baby S1 44pcs',             'piece', 4, 1250.00,'Soft baby diapers size 1 up to 5kg'],
        ['Baby Products',      'PAMPERS-S4-40',       'Pampers Baby Dry S4 40pcs',                'piece', 4, 1750.00,'Dry baby diapers size 4 (9-14kg)'],
        ['Baby Products',      'HUGGIES-S1-40',       'Huggies S1 Ultra Dry 40pcs',               'piece', 4, 1200.00,'Absorbent dry diaper newborn 40pcs'],
        ['Baby Products',      'JOHNSON-POWDER-200G', 'Johnsons Baby Powder 200g',                'piece',24,  320.00,'Gentle talcum-free baby powder'],
        ['Baby Products',      'JOHNSON-OIL-200ML',   'Johnsons Baby Oil 200ml',                  'piece',24,  360.00,'Mineral oil gentle baby skin oil'],
        ['Baby Products',      'NESTLE-CERELAC-WHT',  'Nestle Cerelac Wheat Honey 250g',          'piece',12,  810.00,'Wheat honey infant cereal stage 2'],
        ['Baby Products',      'NESTLE-NAN-400G',     'Nestle NAN PRO 1 Formula 400g',            'piece', 6, 2200.00,'Starter infant formula 0-6 months'],
        ['Baby Products',      'WET-WIPES-BABY-80',   'Mamy Poko Baby Wipes 80 sheets',           'piece',12,  390.00,'Extra soft fragrance-free wipes'],
        ['Baby Products',      'BABY-RASH-CR-100G',   'Bepanthen Nappy Rash Cream 100g',          'piece',24,  650.00,'Protective barrier nappy cream'],

        // ── Home Care ────────────────────────────────────────────────────
        ['Home Care',          'ODONIL-POTPOURRI-75G','Odonil Potpourri Freshener 75g',           'piece',48,  145.00,'Rose potpourri bathroom block'],
        ['Home Care',          'AIRWICK-SPRAY-300ML', 'Air Wick Spring Fresh 300ml',              'piece',12,  680.00,'Spring breeze room spray 300ml'],
        ['Home Care',          'MORTEIN-MAXGUARD-450','Mortein MaxGuard Spray 450ml',             'piece',12,  720.00,'Power shield insecticide spray'],
        ['Home Care',          'RAID-FLYING-450ML',   'Raid Flying Insect Killer 450ml',          'piece',12,  680.00,'Fast-kill flying insect spray'],
        ['Home Care',          'SCOTCH-BRITE-SPONGE', 'Scotch-Brite Heavy Duty Sponge',          'piece',48,   85.00,'2-sided scrubbing sponge'],
        ['Home Care',          'TISSUE-ROLL-12PK',    'Rose Petal Toilet Roll 12pk',              'piece',12,  420.00,'2-ply soft toilet tissue 12 rolls'],
        ['Home Care',          'KITCHEN-ROLL-2PK',    'Kleenex Kitchen Roll 2pk',                 'piece',24,  280.00,'Strong absorbent kitchen towel 2pk'],
        ['Home Care',          'PLASTIC-WRAP-100M',   'Magic Wrap Cling Film 100m',               'piece',24,  195.00,'Food-grade cling wrap roll 100m'],
        ['Home Care',          'FOIL-MOTI-50SQ-M',    'Moti Aluminium Foil 50sq m',               'piece',24,  220.00,'Heavy-duty cooking foil 50m roll'],
        ['Home Care',          'GARBAGE-BAG-30PCS',   'Saaf Bin Liner Bags 30pcs',                'piece',48,  110.00,'Black plastic garbage bags 30pcs'],

        // ── Frozen & Chilled ─────────────────────────────────────────────
        ['Frozen & Chilled',   'K&NS-MINCE-500G',     'K&NS Chicken Mince 500g',                  'piece',12,  480.00,'Fresh minced chicken 500g pack'],
        ['Frozen & Chilled',   'K&NS-SEEKH-500G',     'K&NS Seekh Kabab 500g',                    'piece',12,  680.00,'Spiced minced seekh kabab 500g'],
        ['Frozen & Chilled',   'K&NS-BURGER-PAT-6',   'K&NS Chicken Burger Patties 6pcs',         'piece',12,  550.00,'Seasoned chicken burger patties 6pk'],
        ['Frozen & Chilled',   'OMORE-VANILLA-750ML', 'Omore Vanilla Ice Cream 750ml',            'piece', 8,  480.00,'Classic vanilla ice cream 750ml'],
        ['Frozen & Chilled',   'WALLS-CORNETTO-1L',   'Walls Cornetto Royale 1L',                 'piece', 8,  520.00,'Royale cornetto tub 1L'],
        ['Frozen & Chilled',   'IGLOO-FISH-FILLET-1KG','Igloo Fish Fillet Battered 1kg',         'piece', 6,  980.00,'Crispy battered white fish fillet'],
        ['Frozen & Chilled',   'FROYO-MANGO-400ML',   'Yummy Froyo Mango Frozen Yogurt 400ml',   'piece',12,  380.00,'Mango frozen yogurt 400ml tub'],

    ];

    public function run(): void
    {
        $stores = TownshipStore::orderBy('id')->get();

        if ($stores->isEmpty()) {
            $this->command->warn('No TownshipStore records found — run TownshipStoreSeeder first.');
            return;
        }

        $productCount = 0;
        $priceCount   = 0;

        foreach ($this->catalogue as [$catName, $sku, $nameEn, $unit, $ppc, $basePrice, $desc]) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($catName)],
                ['name' => $catName, 'is_active' => true]
            );

            $product = Product::updateOrCreate(
                ['sku' => $sku],
                [
                    'name_en'               => $nameEn,
                    'description_en'        => $desc,
                    'category_id'           => $category->id,
                    'unit'                  => $unit,
                    'pieces_per_carton'     => $ppc,
                    'huashu_base_price_pkr' => $basePrice,
                    'is_active'             => true,
                ]
            );
            $productCount++;

            foreach ($stores as $i => $store) {
                $multiplier = $this->multipliers[$i] ?? 1.00;
                $price = max((int)(round($basePrice * $multiplier / 5) * 5), 10);
                ProductStorePrice::firstOrCreate(
                    ['product_id' => $product->id, 'store_id' => $store->id],
                    ['price_pkr' => $price, 'is_active' => true]
                );
                $priceCount++;
            }
        }

        $this->command->info("MassProductSeeder — Products: {$productCount}, Price rows: {$priceCount}");
    }
}
