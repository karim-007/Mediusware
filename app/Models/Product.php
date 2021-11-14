<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];

    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants()
    {
        return $this->belongsToMany(Variant::class,'product_variants')->withPivot('variant')->withTimestamps();
    }

    public function productVariant()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function productVariantPrices()
    {
        return $this->hasMany(ProductVariantPrice::class);
    }

}
