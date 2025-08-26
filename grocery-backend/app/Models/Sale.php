<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'total_amount_mga',
        'total_cost_mga',
        'profit_mga',
        'sold_at',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
