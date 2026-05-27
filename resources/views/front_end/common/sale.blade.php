@if (isset($settings['more_offer_status']) && $settings['more_offer_status'] == 1)
<section class="lg:py-20 py-10 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="bg-cover bg-center lg:py-20 lg:px-10 sm:p-6 px-4 py-8 rounded-2xl sm:bg-center bg-left" style="background-image:url('{{ get_file( $settings['more_offer_image'] ?? 'themes/' . $currentTheme . '/assets/images/offer-banner.png')}}');">
            <div class="lg:max-w-lg md:max-w-md max-w-sm">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold md:mb-4 mb-2">{{ __($settings['more_offer_big_text'] ?? 'Special Digital Offer!') }}</h2>
                <p class="lg:text-lg xl:text-xl md:mb-6 mb-3">
                    {{ __($settings['more_offer_content'] ?? 'Get up to 40% off on selected digital products. Instant delivery after purchase!') }}
                </p>
                <button class="btn-primary">
                    {{ __($settings['more_offer_button'] ?? 'Shop Now') }}
                </button>
            </div>
        </div>
    </div>
</section>
@endif
