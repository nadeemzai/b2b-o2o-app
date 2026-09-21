<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly int    $productId,
        public readonly string $productName,
        public readonly int    $requested,
        public readonly int    $available,
    ) {
        parent::__construct(
            "Insufficient stock for \"{$productName}\": requested {$requested}, available {$available}."
        );
    }

    public function toArray(): array
    {
        return [
            'product_id'   => $this->productId,
            'product_name' => $this->productName,
            'requested'    => $this->requested,
            'available'    => $this->available,
        ];
    }
}
