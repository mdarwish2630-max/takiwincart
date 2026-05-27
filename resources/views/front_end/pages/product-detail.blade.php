@extends('front_end.layouts.app')
@section('page-title')
    {{ __('Products') }}
@endsection

@section('content')

    @if ($themeSettings['product_page_status'] && $themeSettings['product_page_status'] == '1')

    <section class="lg:pt-20 pt-10 bg-gray-50">
        <div class="md:container w-full mx-auto px-4">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-4 md:p-8">
                    <!-- Product Images -->
                     <div>
                        <!-- Main Image -->
                        <div class="swiper main-image-slider mb-4">
                            <div class="swiper-wrapper">
                                @if (!empty($product->Sub_image($product->id)['data']))
                                    @foreach ($product->Sub_image($product->id)['data'] as $item)
                                    <div class="swiper-slide">
                                        <div class="relative pt-[65%] border bg-gray-100 rounded-lg overflow-hidden">
                                        <img src="{{ get_file($item->image_path) }}" alt="{{ $product->name }}"
                                            class="w-full h-full absolute top-0 left-0 object-contain" />
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="swiper-slide">
                                        <div class="relative pt-[65%] border bg-gray-100 rounded-lg overflow-hidden">
                                        <img src="{{ get_file($product->cover_image_path) }}" alt="{{ $product->name }}"
                                            class="w-full h-full absolute top-0 left-0 object-contain" />
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <!-- Add Navigation -->
                            <div class="arrow-wrapper">
                            <div class="swiper-button-next pdp-arrow">
                            </div>
                            <div class="swiper-button-prev pdp-arrow">
                            </div>
                            </div>
                        </div>
                        <!-- Thumbnail Images -->
                        <div class="swiper thumbnail-slider">
                            <div class="swiper-wrapper">
                                @if (!empty($product->Sub_image($product->id)['data']))
                                    @foreach ($product->Sub_image($product->id)['data'] as $item)
                                    <div class="swiper-slide">
                                        <div
                                        class="relative pt-[90%] thumb-image rounded-lg overflow-hidden border bg-gray-100 cursor-pointer">
                                        <img src="{{ get_file($item->image_path) }}" alt="{{ $product->name }}"
                                            class="absolute top-0 left-0 w-full h-full object-contain" />
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                <div class="swiper-slide">
                                    <div class="relative pt-[90%] thumb-image rounded-lg overflow-hidden border bg-gray-100 cursor-pointer">
                                    <img src="{{ get_file($product->cover_image_path) }}" alt="{{ $product->name }}"
                                        class="absolute top-0 left-0 w-full h-full object-contain" />
                                    </div>
                                </div>
                                @endif


                            </div>
                        </div>
                    </div>
                    <!-- Product Info -->
                    <div>
                        {{-- Digital Product Badge --}}
                        <div class="flex items-center mb-3 gap-2">
                            <span class="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1.5 rounded-full">
                                <i class="fas fa-cloud-download-alt mr-1"></i> {{ __('Digital Product') }}
                            </span>
                            @if(isset($product->label) && !empty($product->label))
                                <span class="bg-primary/10 text-primary text-xs font-bold px-2 py-1 rounded-md mr-2">
                                    {{ ucfirst(optional($product->label)->name ?? '') }}
                                </span>
                            @endif
                        </div>
                        <h2 class="font-bold text-2xl md:text-3xl mb-2">{{ $product->name }}</h2>

                        {{-- Price Section --}}
                        <div class="md:mb-6 mb-4">
                            <div class="flex items-baseline md:mb-4 mb-2">
                                <span class="font-bold sm:text-3xl text-xl text-primary-dark mr-3 product-price-amount">
                                    {!! \App\Models\Product::getProductPrice($product, $store) !!}
                                </span>
                            </div>
                            {{-- Instant Delivery Badge --}}
                            <div class="flex items-center gap-2 mb-3">
                                <div class="flex items-center gap-1 text-green-600 text-sm font-medium bg-green-50 px-3 py-1.5 rounded-full">
                                    <i class="fas fa-bolt"></i>
                                    <span>{{ __('Instant Delivery') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-b md:py-6 py-4 md:mb-6 mb-4">
                            {!! $product->description !!}
                        </div>

                        <div class="md:mb-6 mb-4">
                            {{-- Quantity Selector Hidden for Digital Products --}}
                            <div class="mb-5 quantity-wrp quantity-select flex gap-3 items-center" style="display:none;">
                                <h3 class="font-semibold">{{ __('quantity:') }}</h3>
                                <div class="flex items-center">
                                    <button type="button"
                                        class="quantity-decrement change_price w-10 h-10 bg-gray-100 flex items-center justify-center ltr:rounded-l-md rtl:rounded-r-md border ltr:border-r-0 rtl:border-l-0 border-gray-300 hover:bg-gray-200" data-product="{{ $product->id}}">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="text" class="quantity w-14 h-10 border border-gray-300 text-center product-quantity" data-cke-saved-name="quantity"
                                        name="quantity" value="1" min="1" max="10" data-product="{{ $product->id}}">
                                    <button type="button"
                                        class="quantity-increment change_price w-10 h-10 bg-gray-100 flex items-center justify-center ltr:rounded-r-md rtl:rounded-l-md border ltr:border-l-0 rtl:border-r-0 border-gray-300 hover:bg-gray-200" data-product="{{ $product->id}}">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                                @include('front_end.common.product.variant')
                            </form>
                            <button class="btn-primary max-w-sm w-full addtocart-btn btn addcart-btn addcart-btn-globaly price-wise-btn product_var_option" product_id="{{ $product->id }}" variant_id="{{ $product->default_variant_id }}" qty="1">
                                    <i class="fas fa-shopping-cart"></i>
                                   {{ __('Buy Now') }}
                            </button>

                                {!! \App\Models\Product::ProductcardButton($slug, $product) !!}
                        </div>
                        <div class="product-hook">
                            @include('front_end.hooks.product_detail_info_button')
                        </div>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 text-sm text-gray-600">
                            @include('front_end.common.product.sale_counter')
                        </div>
                    </div>
                </div>

                <!-- Product Tabs -->
                @include('front_end.theme_common_table')

            </div>
        </div>
    </section>

    @endif
     @include('front_end.hooks.product_detail_slider')
    {{-- tab section. --}}
    @include('front_end.pages.bestseller')
@endsection
