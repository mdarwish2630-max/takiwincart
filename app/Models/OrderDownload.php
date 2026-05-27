<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDownload extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'customer_id',
        'store_id',
        'download_token',
        'download_count',
        'max_downloads',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // التحقق إن التحميل لسه صالح
    public function isValid()
    {
        // تحقق من عدد التحميلات
        if ($this->download_count >= $this->max_downloads) {
            return false;
        }

        // تحقق من تاريخ الانتهاء
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    // توليد توكن تحميل جديد
    public static function generateToken()
    {
        return bin2hex(random_bytes(32));
    }
}
