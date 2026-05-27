<?php

namespace App\Http\Controllers;

use App\Models\OrderDownload;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class DigitalDownloadController extends Controller
{
    /**
     * تحميل ملف رقمي محمي - يتحقق من الشراء قبل التحميل
     */
    public function download(Request $request, $token)
    {
        // البحث عن سجل التحميل بالتوكن
        $orderDownload = OrderDownload::where('download_token', $token)->first();

        if (!$orderDownload) {
            return redirect()->back()->with('error', __('Download link is invalid.'));
        }

        // التحقق من صلاحية التحميل
        if (!$orderDownload->isValid()) {
            $reason = $orderDownload->download_count >= $orderDownload->max_downloads
                ? __('Download limit exceeded.')
                : __('Download link has expired.');
            return redirect()->back()->with('error', $reason);
        }

        // تحديد الملف المراد تحميله
        $product = Product::find($orderDownload->product_id);
        $filePath = null;

        if ($orderDownload->variant_id) {
            $variant = ProductVariant::find($orderDownload->variant_id);
            if ($variant) {
                $filePath = $variant->downloadable_product;
            }
        }

        if (!$filePath && $product) {
            $filePath = $product->downloadable_product;
        }

        if (!$filePath || !file_exists(public_path($filePath))) {
            return redirect()->back()->with('error', __('File not found.'));
        }

        // زيادة عداد التحميلات
        $orderDownload->increment('download_count');

        // تحميل الملف
        return response()->download(public_path($filePath));
    }

    /**
     * عرض صفحة التحميلات الخاصة بالعميل
     */
    public function myDownloads(Request $request, $storeSlug = null)
    {
        $customer = Auth::guard('customers')->user();
        $store = \App\Models\Store::where('slug', $storeSlug)->first();

        if (!$customer) {
            return redirect()->route('customer.login', $storeSlug);
        }

        $downloads = OrderDownload::with(['product', 'order'])
            ->where('customer_id', $customer->id)
            ->where('store_id', $store->id ?? 0)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('front_end.pages.my-downloads', compact('downloads', 'store'));
    }

    /**
     * الحصول على الكود/الرقم الرقمي لمنتج معين في طلب معين
     */
    public function getDigitalKey(Request $request, $order_id, $product_id)
    {
        $order = Order::find($order_id);
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // التحقق إن المستخدم مالك الطلب
        $customer = Auth::guard('customers')->user();
        if ($order->customer_id != $customer->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $product = Product::find($product_id);
        $key = $product->digital_key ?? null;

        if (!$key) {
            return response()->json(['error' => 'No digital key available'], 404);
        }

        return response()->json(['key' => $key]);
    }
}
