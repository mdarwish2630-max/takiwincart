
                <div class="flex-col flex h-full bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300">
                <div class="relative">
                <a href="{{ route('page.product', ['storeSlug' => $slug, 'product_slug' => $product->slug]) }}" class="block">
                    <img  src="{{ get_file($product->cover_image_path) ?? asset('assets/images/default-category.png') }}" width="300" height="300"
                        alt="{{ __('Product image') }}" loading="lazy" class="w-full h-80 object-cover object-top"/>
                </a>
                    <div class="absolute top-4 right-4">
                        <button class="wishlist-btn wishbtn-globaly relative flex bg-white rounded p-2 border {{ $product->in_whishlist ? 'active' : ''}}" 
                        product_id="{{ $product->id }}"
                        in_wishlist="{{ $product->in_whishlist ? 'remove' : 'add' }}"
                        aria-label="Add to wishlist">
                            <i class="far fa-heart text-gray-600"></i>
                        </button>
                        {!! \App\Models\Product::ProductcardButton($slug, $product) !!}
                    </div>
                </div>
                <div class="h-full flex flex-col">
                    <div class="p-4 pb-0 flex-1">
                    <h3 class="font-medium mb-1 text-lg">{{ $product->name ?? "" }}</h3>
                    <p class="text-gray-500 text-sm mb-2">     {{ Str::limit(strip_tags($product->description), 90) }}</p>
                    </div>  
                    <div class="p-4 pt-0 flex justify-between items-center">
                
                        @if ($product->variant_product == 0)
                        <span class="font-bold text-lg">
                        {!! \App\Models\Product::getProductPrice($product, $store) !!}
                        </span>
                        @else
                        <span class="font-bold text-lg">{{ __('In Variant') }}</span>
                        @endif
                       
                    <button class="addtocart-btn btn addcart-btn-globaly cart-btn bg-[var(--primary-color)] text-white px-3 py-1 rounded hover:bg-opacity-90 transition-colors duration-300" tabindex="0"
                        product_id="{{ $product->id }}" variant_id="0" qty="1">
                        <span> {{ __('Add to cart') }} </span>
                    </button> 
                    </div>
                </div>
                </div>
            </div>      
        @endforeach
    </div>
    <div class="arrow-wrapper">
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
    </div>
</div>