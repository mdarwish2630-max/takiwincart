-- =====================================================
-- تعديلات HomeController لعرض المنتجات الرقمية فقط
-- الملف: app/Http/Controllers/HomeController.php
-- =====================================================

/*
=== في كل دالة تستعلم عن المنتجات، استبدل ===

ابحث عن كل occurrence:
    Product::where('product_type', null)

واستبدله بـ:
    Product::where('product_type', 'digital')

=== الدوال المتأثرة ===
- landing_page()
- product_page()
- product_page_filter()
- product_detail()
- search_products()
- storeSlug()

=== مثال تعديل في product_page() ===

قبل:
    $products = Product::where('product_type', null)
        ->where('store_id', $store->id)
        ->where('status', 1)
        ->...

بعد:
    $products = Product::where('product_type', 'digital')
        ->where('store_id', $store->id)
        ->where('status', 1)
        ->...
*/
