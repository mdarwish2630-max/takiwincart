<div class="bg-gray-50 border md:p-6 p-4 rounded-lg shadow-sm sticky top-[20px]">
    {{-- Instant Delivery Notice --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-6 flex items-center gap-3">
        <div class="bg-blue-100 rounded-full h-10 w-10 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-bolt text-blue-600 text-lg"></i>
        </div>
        <div>
            <p class="font-semibold text-blue-800 text-sm">{{ __('Instant Digital Delivery') }}</p>
            <p class="text-blue-600 text-xs">{{ __('Your digital product will be delivered immediately after payment confirmation.') }}</p>
        </div>
    </div>

    <h2 class="font-bold text-xl mb-6">{{ __('Order Summary') }}: <span class="checkout-cartcount">[{{ $response->data->cart_total_product }}]</span></h2>

    <!-- Order Items -->
    <div class="space-y-4 mb-6">
        @if (!empty($response->data->cart_total_product))
            @foreach ($response->data->product_list as $product)
                <div class="flex items-start gap-3 pb-3 border-b">
                    <input type="hidden" id="product_id" value="{{ $product->product_id }}" >
                    <input type="hidden" id="product_qty" value="{{ $product->qty }}">
                    <div class="h-20 w-20 flex-shrink-0 border bg-gray-100 rounded-lg">
                        <a href="{{ route('page.product', [$slug, getProductSlug($product->product_id)]) }}">
                            <img src="{{ get_file($product->image) }}" alt="cart-image" class="h-full w-full object-contain rounded-lg">
                        </a>
                    </div>
                    <div class="flex-grow">
                        <h3 class="font-medium mb-1"><a href="{{ route('page.product', [$slug, getProductSlug($product->product_id)]) }}">{{ $product->name }}</a></h3>
                        <p class="text-sm text-gray-500">
                            @if ($product->variant_id != 0)
                            {!! \App\Models\ProductVariant::variantlist($product->variant_id) !!}
                            @endif
                        </p>
                        <p class="text-sm text-gray-500">{{ $product->qty . ' x ' . currency_format_with_sym($product->final_price / $product->qty, $store->id) }}</p>
                    </div>
                    <div class="price">
                        {!! \App\Models\Product::ManageCheckoutPrice($product, $store) !!}
                    </div>
                </div>
            @endforeach
        @else
            <div class="flex items-start gap-3 pb-3 border-b">
                {{ __('You have no items in your shopping cart.') }}
            </div>
        @endif
    </div>

    {{-- Shipping section removed - digital products do not require shipping --}}

    <!-- Coupon Code -->
    <div class="pb-4 mb-4 border-b">
        <div class="flex">
            <input class="p-2 form-input ltr:rounded-tr-none rtl:rounded-tl-none  ltr:rounded-br-none rtl:rounded-bl-none coupon_code" placeholder="{{ __('Enter coupon code') }}" name="coupon" type="text" value="">
            <a class="bg-primary hover:bg-primary-dark text-white px-4 py-2 ltr:rounded-r-md rtl:rounded-l-md transition checkout-btn apply_coupon">
                {{ __('Apply') }}
            </a>
        </div>
    </div>

    <!-- Order Totals -->
    <div class="space-y-2 mb-6">
        <div class="flex justify-between">
            <span>{{ __('Subtotal') }}</span>
            <input type="hidden" value="{{  $response->data->final_price ?? ($response->data->sub_total ?? 0) }}" id="subtotal">
            <span class="font-semibold subtotal">{{  currency_format_with_sym(($response->data->final_price ?? ($response->data->sub_total ?? 0)), $store->id) ?? SetNumberFormat($response->data->final_price ?? ($response->data->sub_total ?? 0)) }}</span>
        </div>
        {{-- Shipping cost removed - not applicable for digital products --}}
        @stack('clubPointPriceShow')
        <input type="hidden" value="{{ $response->data->total_coupon_price ?? 0 }}" id="coupon_amount">
        <div class="flex justify-between">
            <span>{{ __('Coupon') }}</span>
            <span class="font-semibold discount_amount_currency"> - {{ currency_format_with_sym(($response->data->total_coupon_price ?? 0), $store->id) ?? SetNumberFormat($response->data->total_coupon_price ?? 0) }} </span>
        </div>
        <div class="flex justify-between">
            <span>{{ __('Tax') }}</span>
            <span class="font-semibold final_tax_price"> {{ currency_format_with_sym(($response->data->tax_price ?? 0), $store->id) ?? SetNumberFormat($response->data->tax_price) }} </span>
        </div>
        <div class="flex justify-between text-lg font-bold pt-2 border-t">
            <span>{{ __('Total') }}</span>
            <span class="text-primary-dark final_amount_currency shipping_total_price" final_total="{{ $response->data->total_sub_price  }}">{{ currency_format_with_sym(($response->data->total_sub_price ?? 0), $store->id) ?? SetNumberFormat($response->data->total_sub_price) }}</span>
        </div>
    </div>

    @include('front_end.hooks.checkout_list')

    <!-- Checkout Button -->
    <input type="hidden" class="method_id" id="method_id" name="method_id" value="{{ old('method_id') }}">
    <button class="btn-primary w-full continue-btn place_order_submit payfast_form" id="payfast_form" type="submit">
        <i class="fas fa-lock mr-2"></i> {{ __('Complete Purchase') }}
    </button>
</div>
