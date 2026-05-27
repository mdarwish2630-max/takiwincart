@if (isset($settings['service_status']) && $settings['service_status'] == 1)
<section class="lg:py-20 py-10 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 text-center">
            <div class="card lg:p-6 p-4">
                <div class="bg-primary/10 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-bolt text-primary text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium mb-2">{{ __($settings['service_title'] ?? 'Instant Delivery') }}</h3>
                <p class="text-gray-600">{{ __($settings['service_content'] ?? 'Get your digital products immediately after purchase') }}</p>
            </div>
        </div>
    </div>
</section>
@endif
