-- =====================================================
-- تعديلات على ProductController في getProductStatus()
-- أضف هذا الكود بعد إنشاء الطلب مباشرة
-- في الملف: app/Http/Controllers/PaymentController.php
-- في الدالة: getProductStatus()
-- =====================================================

/*
=== في الدالة getProductStatus() بعد سطر إنشاء الطلب ===

ابحث عن هذا السطر (تقريباً بعد إنشاء $order):
    // Events
    event(new GetProductStatus($order));

وأضف قبلها هذا الكود:

// ========== تعديل رقمي: تسليم فوري + إنشاء سجلات التحميل ==========
use App\Models\OrderDownload;
use App\Models\Product;
use App\Models\ProductVariant;

// تعيين حالة الطلب = "تم التوصيل" تلقائياً للمنتجات الرقمية
$order->delivered_status = 1;
$order->delivery_date = now();
$order->save();

// إنشاء سجلات التحميل لكل منتج في الطلب
$productJson = json_decode($order->product_json, true);
$digitalProducts = [];

if (is_array($productJson)) {
    foreach ($productJson as $item) {
        $product = Product::find($item['product_id']);
        $variantId = $item['variant_id'] ?? 0;
        
        // تحديد الكود الرقمي
        $digitalKey = null;
        if ($product) {
            $digitalKey = $product->digital_key;
            if ($variantId) {
                $variant = ProductVariant::find($variantId);
                if ($variant && !empty($variant->digital_key)) {
                    $digitalKey = $variant->digital_key;
                }
            }
        }
        
        // تحديد الحد الأقصى للتحميل ومدة الصلاحية
        $maxDownloads = $product->max_downloads ?? 5;
        $expiryDays = $product->download_expiry_days;
        $expiresAt = $expiryDays ? now()->addDays($expiryDays) : null;
        
        // إنشاء سجل التحميل
        OrderDownload::create([
            'order_id'       => $order->id,
            'product_id'     => $item['product_id'],
            'variant_id'     => $variantId,
            'customer_id'    => $order->customer_id,
            'store_id'       => $order->store_id,
            'download_token' => OrderDownload::generateToken(),
            'download_count' => 0,
            'max_downloads'  => $maxDownloads,
            'expires_at'     => $expiresAt,
        ]);
        
        // تجهيز بيانات المنتجات الرقمية لصفحة إتمام الطلب
        $digitalProducts[] = [
            'name'           => $item['name'] ?? $product->name ?? '',
            'variant_name'   => $variantId ? (ProductVariant::find($variantId)->variant ?? '') : '',
            'digital_key'    => $digitalKey,
            'download_token' => OrderDownload::where('order_id', $order->id)
                                    ->where('product_id', $item['product_id'])
                                    ->value('download_token'),
            'max_downloads'  => $maxDownloads,
        ];
    }
}

// تمرير بيانات المنتجات الرقمية لصفحة إتمام الطلب
session(['digitalProducts' => $digitalProducts]);
// ========== نهاية التعديل الرقمي ==========
*/

// === في الدالة orderComplete() في OrderController.php ===
// أضف هذا قبل return view:
/*
$digitalProducts = session('digitalProducts', []);
session()->forget('digitalProducts');
// أضف $digitalProducts للـ compact أو pass كمتغير:
return view('front_end.pages.order-complete', compact('order', 'store', 'url', 'digitalProducts'));
*/
