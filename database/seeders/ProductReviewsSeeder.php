<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductReview;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProductReviewsSeeder extends Seeder
{
    // Pool of realistic B2B wholesale reviewer names + locations
    private array $reviewers = [
        ['name' => 'Ahmed Malik',        'location' => 'Lahore, Pakistan'],
        ['name' => 'Sana Tariq',         'location' => 'Karachi, Pakistan'],
        ['name' => 'Bilal Chaudhry',     'location' => 'Faisalabad, Pakistan'],
        ['name' => 'Zara Hussain',       'location' => 'Islamabad, Pakistan'],
        ['name' => 'Usman Raza',         'location' => 'Rawalpindi, Pakistan'],
        ['name' => 'Ayesha Khan',        'location' => 'Multan, Pakistan'],
        ['name' => 'Hamza Sheikh',       'location' => 'Peshawar, Pakistan'],
        ['name' => 'Nadia Baig',         'location' => 'Sialkot, Pakistan'],
        ['name' => 'Farhan Iqbal',       'location' => 'Gujranwala, Pakistan'],
        ['name' => 'Saima Qureshi',      'location' => 'Lahore, Pakistan'],
        ['name' => 'Tariq Mehmood',      'location' => 'Karachi, Pakistan'],
        ['name' => 'Hira Shahid',        'location' => 'Islamabad, Pakistan'],
        ['name' => 'Adnan Yousuf',       'location' => 'Hyderabad, Pakistan'],
        ['name' => 'Rabia Anwar',        'location' => 'Quetta, Pakistan'],
        ['name' => 'Kamran Butt',        'location' => 'Lahore, Pakistan'],
    ];

    // Review templates by rating band — generic enough for any product category
    private array $reviews = [
        5 => [
            ['title' => 'Excellent quality, fast delivery', 'body' => 'Ordered in bulk for our retail store. Quality is outstanding — customers have already commented on it. Packing was solid, zero items damaged in transit. Will definitely reorder next month.'],
            ['title' => 'Best supplier on this platform', 'body' => 'Third order from this supplier and every time the goods arrive exactly as described. Pricing is fair for the quality. The Huashu team kept us updated throughout the process.'],
            ['title' => 'Highly recommend for wholesale', 'body' => 'Very happy with this purchase. Goods arrived within the promised timeframe. Our customers love the product. The per-unit cost makes our margins comfortable.'],
            ['title' => 'Consistent quality batch after batch', 'body' => 'We have been sourcing this product for six months now and quality has never dropped. Packaging is retail-ready which saves us extra work. Great value for wholesale buyers.'],
            ['title' => 'Smooth process start to finish', 'body' => 'The ordering process was straightforward, the OZ store team confirmed quickly, and delivery was on schedule. Product exactly matches the listing. Already recommended to two other retailers.'],
        ],
        4 => [
            ['title' => 'Good quality, minor packaging issue', 'body' => 'Product quality is very good and customers are satisfied. One carton had slightly dented corners but all items inside were fine. Would buy again — just hoping packaging improves.'],
            ['title' => 'Solid product, competitive price', 'body' => 'Happy with this purchase overall. Quality is consistent with what was shown. Delivery took one day longer than expected but the team kept us informed. Good value for bulk orders.'],
            ['title' => 'Reliable supplier', 'body' => 'Four stars because one item in the batch had a minor defect, but the team resolved it promptly. Apart from that, product is good and price per unit is competitive for the market.'],
            ['title' => 'Good for bulk buying', 'body' => 'We bought a large quantity and most of it is excellent. A small percentage had minor cosmetic issues — nothing that affects usability. For the price point, this is very acceptable.'],
            ['title' => 'Will reorder', 'body' => 'Second purchase and quality is comparable to the first. Delivery schedule could be slightly tighter but overall we are satisfied. Our customers have not complained about the product.'],
        ],
        3 => [
            ['title' => 'Average — meets basic expectations', 'body' => 'Product is okay for the price. Not exceptional, but customers at the lower end of our market are satisfied. Would be five stars if the finishing quality was a bit better.'],
            ['title' => 'Decent but room for improvement', 'body' => 'Quality is acceptable. Some variation between units in the same batch. For our volume requirements the pricing works, so we will continue ordering but hope for more consistency.'],
        ],
    ];

    public function run(): void
    {
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->warn('No products found — skipping reviews seeder.');
            return;
        }

        $reviewerPool = $this->reviewers;
        $now          = Carbon::now();

        foreach ($products as $product) {
            // 3–6 reviews per product, weighted toward positive
            $count = rand(3, 6);
            shuffle($reviewerPool);
            $usedReviewers = array_slice($reviewerPool, 0, $count);

            foreach ($usedReviewers as $i => $reviewer) {
                // Pick a rating: mostly 4–5 stars
                $ratingRoll = rand(1, 10);
                $rating = match (true) {
                    $ratingRoll <= 5 => 5,
                    $ratingRoll <= 8 => 4,
                    $ratingRoll <= 9 => 3,
                    default          => 4,
                };

                $pool   = $this->reviews[$rating];
                $review = $pool[array_rand($pool)];

                ProductReview::create([
                    'product_id'        => $product->id,
                    'reviewer_name'     => $reviewer['name'],
                    'reviewer_location' => $reviewer['location'],
                    'rating'            => $rating,
                    'title'             => $review['title'],
                    'body'              => $review['body'],
                    'verified_purchase' => true,
                    'created_at'        => $now->copy()->subDays(rand(2, 120)),
                    'updated_at'        => $now->copy()->subDays(rand(0, 2)),
                ]);
            }
        }

        $this->command->info('Product reviews seeded for ' . $products->count() . ' products.');
    }
}
