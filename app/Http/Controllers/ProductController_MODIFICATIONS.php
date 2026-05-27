-- =====================================================
-- تعديلات على Product Model
-- الملف: app/Models/Product.php
-- =====================================================

/*
=== 1. أضف الحقول الجديدة في $fillable ===

ابحث عن:
    protected $fillable = [
        ...
        'product_type',
        ...
    ];

وأضف بعده:
        'digital_type',
        'digital_key',
        'max_downloads',
        'download_expiry_days',


=== 2. في الدالة store() في ProductController.php ===

ابحث عن (في الجزء الخاص بالمنتج بدون variants):
    $product->product_type = $request->product_type;

وأضف بعده:
    // تعديل رقمي: تعيين نوع المنتج كرقمي دائماً
    $product->product_type = 'digital';
    $product->digital_type = $request->digital_type ?? 'file';
    $product->digital_key = $request->digital_key;
    $product->max_downloads = $request->max_downloads ?? 5;
    $product->download_expiry_days = $request->download_expiry_days;
    // إزالة الشحن والوزن للمنتجات الرقمية
    $product->shipping_id = null;
    $product->product_weight = 0;


=== 3. في الدالة store() للمنتجات مع variants ===

بعد إنشاء كل variant، أضف:
    $variant->digital_key = $request->input("variant_digital_key.$combinationIndex") ?? $product->digital_key;


=== 4. في الدالة index() في ProductController.php ===

ابحث عن:
    Product::where('product_type', null)

واستبدله بـ:
    Product::where('product_type', 'digital')

لإظهار المنتجات الرقمية فقط.


=== 5. في واجهة إنشاء المنتج (create.blade.php) ===

أضف هذا القسم بعد حقل السعر:

{{-- === قسم المنتج الرقمي === --}}
<div class="mt-4">
    <h2 class="font-semibold text-xl md:mb-4 mb-3">{{ __('Digital Product Settings') }}</h2>
    <div class="bg-gray-50 border md:p-6 p-4 rounded-lg shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- نوع التوصيل --}}
            <div>
                <label class="block mb-2 font-medium md:text-base text-sm">
                    {{ __('Delivery Type') }} <span class="text-red-500">*</span>
                </label>
                <select name="digital_type" id="digital_type" class="form-input" required>
                    <option value="file" {{ old('digital_type', 'file') == 'file' ? 'selected' : '' }}>
                        {{ __('File Download (PDF, Video, etc.)') }}
                    </option>
                    <option value="code" {{ old('digital_type') == 'code' ? 'selected' : '' }}>
                        {{ __('Digital Code / PIN (Recharge, eSIM)') }}
                    </option>
                    <option value="both" {{ old('digital_type') == 'both' ? 'selected' : '' }}>
                        {{ __('Both File + Code') }}
                    </option>
                </select>
            </div>

            {{-- الكود الرقمي --}}
            <div id="digital_key_wrapper">
                <label class="block mb-2 font-medium md:text-base text-sm">
                    {{ __('Digital Code / PIN') }}
                </label>
                <textarea name="digital_key" id="digital_key" class="form-input" rows="2"
                    placeholder="{{ __('Enter PIN, recharge code, or eSIM activation code...') }}">{{ old('digital_key') }}</textarea>
                <small class="text-gray-500 text-xs mt-1 block">
                    {{ __('Leave empty if delivery type is file only. Each purchase will receive this code.') }}
                </small>
            </div>

            {{-- الحد الأقصى للتحميلات --}}
            <div>
                <label class="block mb-2 font-medium md:text-base text-sm">
                    {{ __('Max Downloads') }}
                </label>
                <input type="number" name="max_downloads" id="max_downloads" class="form-input"
                    value="{{ old('max_downloads', 5) }}" min="1" max="100">
            </div>

            {{-- مدة صلاحية التحميل --}}
            <div>
                <label class="block mb-2 font-medium md:text-base text-sm">
                    {{ __('Download Expiry (Days)') }}
                </label>
                <input type="number" name="download_expiry_days" id="download_expiry_days"
                    class="form-input" value="{{ old('download_expiry_days') }}" min="1" max="365"
                    placeholder="{{ __('Leave empty for unlimited') }}">
                <small class="text-gray-500 text-xs mt-1 block">
                    {{ __('Leave empty if downloads should never expire') }}
                </small>
            </div>
        </div>
    </div>
</div>

=== 6. أضف هذا JavaScript في نهاية الصفحة (create.blade.php) ===

<script>
$(document).ready(function() {
    function toggleDigitalFields() {
        var type = $('#digital_type').val();
        if (type === 'file') {
            $('#digital_key_wrapper').hide();
        } else {
            $('#digital_key_wrapper').show();
        }
    }

    $('#digital_type').on('change', toggleDigitalFields);
    toggleDigitalFields();

    // إخفاء حقول المنتج الفيزيائي
    // الوزن
    $('label:contains("Weight (Kg)")').closest('.col-12, .form-group, div').hide();
    // الشحن
    $('label:contains("Shipping")').closest('.col-12, .form-group, div').hide();
    // إدارة المخزون (اختياري)
});
</script>
*/
