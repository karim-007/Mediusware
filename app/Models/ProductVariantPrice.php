<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantPrice extends Model
{
    protected $fillable = [
        'product_variant_one', 'product_variant_two', 'product_variant_three', 'price', 'stock', 'product_id',
    ];

    public function product()
    {
        return $this->hasMany(Product::class);
    }

    public function productVariantOne()
    {
        return $this->belongsTo(ProductVariant::class,'product_variant_one','id');
    }
    public function productVariantTwo()
    {
        return $this->belongsTo(ProductVariant::class,'product_variant_two','id');
    }
    public function productVariantThree()
    {
        return $this->belongsTo(ProductVariant::class,'product_variant_three','id');
    }
}

