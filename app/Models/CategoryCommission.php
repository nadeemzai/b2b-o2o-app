<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryCommission extends Model
{
    protected $fillable = [
        'category_id',
        'commission_rate',
        'effective_from',
        'created_by',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:4',
        'effective_from'  => 'date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('effective_from', '<=', now()->toDateString());
    }
}
