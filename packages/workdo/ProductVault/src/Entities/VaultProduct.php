<?php

namespace Workdo\ProductVault\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaultProduct extends Model
{
    use HasFactory;

    protected $table = 'vault_products';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'category',
        'price',
        'preview_image',
        'file_path',
        'file_type',
        'file_size',
        'demo_url',
        'payment_link',
        'status',
        'is_featured',
        'downloads_count',
        'created_by',
        'workspace_id',
        'store_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_featured' => 'boolean',
        'downloads_count' => 'integer',
    ];

    public function purchases()
    {
        return $this->hasMany(VaultPurchase::class, 'product_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}