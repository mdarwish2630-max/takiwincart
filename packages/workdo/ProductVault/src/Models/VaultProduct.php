<?php

namespace Workdo\ProductVault\Models;

use Illuminate\Database\Eloquent\Model;

class VaultProduct extends Model
{
    protected $table = 'vault_products';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'category',
        'image',
        'file_path',
        'status',
        'features',
        'created_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    public function purchases()
    {
        return $this->hasMany('Workdo\ProductVault\Models\VaultPurchase', 'vault_product_id');
    }
}