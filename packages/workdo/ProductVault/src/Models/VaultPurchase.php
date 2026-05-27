<?php

namespace Workdo\ProductVault\Models;

use Illuminate\Database\Eloquent\Model;

class VaultPurchase extends Model
{
    protected $table = 'vault_purchases';

    protected $fillable = [
        'vault_product_id',
        'buyer_id',
        'buyer_name',
        'buyer_email',
        'payment_method',
        'amount',
        'status',
        'receipt',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo('Workdo\ProductVault\Models\VaultProduct', 'vault_product_id');
    }

    public function buyer()
    {
        return $this->belongsTo('App\Models\User', 'buyer_id');
    }

    public function approver()
    {
        return $this->belongsTo('App\Models\User', 'approved_by');
    }
}