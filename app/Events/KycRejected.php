<?php

namespace App\Events;

use App\Models\Retailer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KycRejected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Retailer $retailer,
        public readonly string   $reason,
    ) {}
}
