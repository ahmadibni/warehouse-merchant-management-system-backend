<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MerchantProduct extends Model
{
    use SoftDeletes;
    protected $fillable = ['product_id', 'merchant_id', 'stock', 'warehouse_id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
