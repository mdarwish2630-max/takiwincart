@extends('front_end.layouts.app')

@section('page-title')
{{ __('My Downloads') }}
@endsection

@section('content')
<main>
    @if (!empty($store))
    <section class="banner-section relative lg:py-16 py-10 bg-cover bg-center bg-gray-100">
        <div class="md:container w-full mx-auto px-4">
            <div class="text-center relative z-[2]">
                <h2 class="text-[26px] sm:text-4xl lg:text-5xl font-bold mb-4 capitalize">{{ __('My Downloads') }}</h2>
            </div>
        </div>
    </section>
    @endif

    <section class="py-10 lg:py-20">
        <div class="md:container w-full mx-auto px-4">
            @if ($downloads->isEmpty())
                <div class="text-center py-16">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-download text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">{{ __('No downloads yet') }}</h3>
                    <p class="text-gray-500 mb-6">{{ __('Your purchased digital products will appear here after order delivery.') }}</p>
                    <a href="{{ route('page.product-list', $store->slug ?? '') }}" class="btn-primary inline-flex items-center gap-2">
                        <i class="fas fa-shopping-bag"></i> {{ __('Browse Products') }}
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($downloads as $download)
                        @php
                            $product = $download->product;
                            $isValid = $download->isValid();
                            $hasFile = ($download->variant_id)
                                ? !empty(\App\Models\ProductVariant::find($download->variant_id)->downloadable_product)
                                : !empty($product->downloadable_product);
                            $hasKey = !empty($product->digital_key);
                            $variant = $download->variant_id ? \App\Models\ProductVariant::find($download->variant_id) : null;
                        @endphp
                        <div class="bg-white rounded-lg border shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                            {{-- صورة المنتج --}}
                            @if ($product->cover_image_path)
                            <div class="h-40 bg-gray-100">
                                <img src="{{ get_file($product->cover_image_path) }}" alt="{{ $product->name }}"
                                     class="w-full h-full object-cover">
                            </div>
                            @else
                            <div class="h-40 bg-gray-100 flex items-center justify-center">
                                <i class="fas fa-file-alt text-gray-300 text-4xl"></i>
                            </div>
                            @endif

                            <div class="p-4">
                                {{-- اسم المنتج --}}
                                <h3 class="font-semibold text-gray-800 mb-1">{{ $product->name }}</h3>
                                @if ($variant)
                                    <p class="text-sm text-gray-500 mb-2">{{ $variant->variant }}</p>
                                @endif

                                {{-- معلومات الطلب --}}
                                <p class="text-xs text-gray-400 mb-3">
                                    {{ __('Order') }}: #{{ $download->order->product_order_id ?? $download->order_id }}
                                    &bull; {{ $download->created_at->format('M d, Y') }}
                                </p>

                                {{-- حالة التحميل --}}
                                @if (!$isValid)
                                    <div class="bg-red-50 text-red-600 rounded-md p-2 text-xs text-center mb-3">
                                        @if ($download->download_count >= $download->max_downloads)
                                            <i class="fas fa-exclamation-circle"></i> {{ __('Download limit reached') }}
                                        @else
                                            <i class="fas fa-clock"></i> {{ __('Download expired') }}
                                        @endif
                                    </div>
                                @endif

                                {{-- أزرار التحميل --}}
                                <div class="flex flex-col gap-2">
                                    @if ($hasFile && $isValid)
                                        <a href="{{ route('digital.download', $download->download_token) }}"
                                           class="w-full inline-flex items-center justify-center gap-2 bg-primary text-white px-4 py-2.5 rounded-md text-sm font-medium hover:bg-primary-dark transition-colors">
                                            <i class="fas fa-download"></i>
                                            {{ __('Download File') }}
                                        </a>
                                        <span class="text-xs text-gray-400 text-center">
                                            {{ __('Remaining:') }} {{ $download->max_downloads - $download->download_count }} / {{ $download->max_downloads }}
                                        </span>
                                    @endif

                                    @if ($hasKey)
                                        <div class="bg-gray-50 rounded-md border border-dashed border-gray-200 p-3">
                                            <span class="text-xs text-gray-500 block mb-1">{{ __('Your Code / PIN:') }}</span>
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono font-bold text-gray-800 flex-1">{{ $product->digital_key }}</span>
                                                <button onclick="copyKey(this, '{{ $product->digital_key }}')"
                                                    class="bg-yellow-400 hover:bg-yellow-500 text-gray-800 px-2 py-1.5 rounded text-xs font-medium transition-colors">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</main>
@endsection

@push('page-script')
<script>
function copyKey(button, key) {
    navigator.clipboard.writeText(key).then(function() {
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i>';
        button.classList.add('bg-green-400');
        button.classList.remove('bg-yellow-400');
        setTimeout(function() {
            button.innerHTML = originalHTML;
            button.classList.remove('bg-green-400');
            button.classList.add('bg-yellow-400');
        }, 1500);
    });
}
</script>
@endpush
