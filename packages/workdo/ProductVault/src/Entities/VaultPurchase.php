<?php

namespace Workdo\ProductVault\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaultPurchase extends Model
{
    use HasFactory;

    protected $table = 'vault_purchases';

    protected $fillable = [
        'user_id',
        'product_id',
        'store_id',
        'price_paid',
        'payment_type',
        'payment_status',
        'payer_name',
        'payer_email',
        'receipt',
        'notes',
        'rejection_reason',
        'admin_notes',
        'purchased_at',
        'approved_at',
        'rejected_at',
        'imported',
        'imported_product_id',
        'imported_at',
    ];

    protected $casts = [
        'price_paid' => 'decimal:2',
        'purchased_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'imported' => 'boolean',
        'imported_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(VaultProduct::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}