@extends('front_end.layouts.app')

@section('page-title')
{{ __('Checkout Page') }}
@endsection

@section('content')
<main>
    @if ($themeSettings['checkout_banner_status'] && $themeSettings['checkout_banner_status'] == '1')
    <!-- Common Banner Section -->
    <section class="banner-section relative lg:py-16 py-10 bg-cover bg-center"
        style="background-image: url('{{ get_file($themeSettings['checkout_banner_image'] ?? '') }}');">
        <div class="md:container w-full mx-auto px-4">
            <div class="text-center relative z-[2]">
                <h2 class="text-[26px] sm:text-4xl lg:text-5xl font-bold mb-4 capitalize">
                    {{ $themeSettings['checkout_banner_title'] ?? __('Checkout Page') }}</h2>
            </div>
        </div>
    </section>
    @endif

    @if ($themeSettings['checkout_status'] && $themeSettings['checkout_status'] == '1')
   <section class="py-10 lg:py-20">
        <div class="md:container w-full mx-auto px-4">
            {!! Form::open([
                'route' => ['payment.process', $store->slug],
                'method' => 'post',
                'enctype' => 'multipart/form-data',
                'id' => 'formdata',
            ]) !!}
            @csrf
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Left Side - Checkout Forms -->
                    <div class="lg:w-2/3">
                        <div class="md:space-y-8 space-y-6">
                            <div>
                                <h2 class="font-semibold text-xl md:mb-4 mb-3">1. {{ __('Customer Information') }}</h2>
                                <div class="bg-gray-50 border md:p-6 p-4 rounded-lg shadow-sm">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- First Name -->
                                        <div>
                                            <label for="first-name" class="block mb-2 font-medium md:text-base text-sm">{{ __('First Name') }} <span
                                                class="text-red-500">*</span></label>
                                            {!! Form::text('billing_info[firstname]', !empty(Auth::guard('customers')->user()) ? Auth::guard('customers')->user()->first_name : '', [
                                                'class' => 'form-input',
                                                'placeholder' => 'John',
                                                'id' => 'first-name',
                                                'required' => true
                                            ]) !!}
                                        </div>

                                        <!-- Last Name -->
                                        <div>
                                            <label for="last-name" class="block mb-2 font-medium md:text-base text-sm">{{ __('Last Name') }} <span
                                                class="text-red-500">*</span></label>
                                            {!! Form::text('billing_info[lastname]', !empty(Auth::guard('customers')->user()) ? Auth::guard('customers')->user()->last_name : '', [
                                                'class' => 'form-input',
                                                'placeholder' => 'Doe',
                                                'id' => 'last-name',
                                                'required' => true
                                            ]) !!}
                                        </div>

                                        <!-- Email Field -->
                                        <div>
                                            <label for="email" class="block mb-2 font-medium md:text-base text-sm">{{ __('Email Address') }} <span
                                                class="text-red-500">*</span></label>
                                            {!! Form::email('billing_info[email]', !empty(Auth::guard('customers')->user()) ? Auth::guard('customers')->user()->email : '', [
                                                'class' => 'form-input',
                                                'placeholder' => 'shop@company.com',
                                                'id' => 'email',
                                                'required' => true
                                            ]) !!}
                                        </div>

                                        <!-- Phone Number -->
                                        <div>
                                            <label for="phone" class="block mb-2 font-medium md:text-base text-sm">{{ __('Phone Number') }} <span
                                                class="text-red-500">*</span></label>
                                            {!! Form::number('billing_info[billing_user_telephone]', !empty(Auth::guard('customers')->user()) ? Auth::guard('customers')->user()->mobile : '', [
                                                'class' => 'form-input',
                                                'placeholder' => '1234567890',
                                                'id' => 'phone',
                                                'required' => true
                                            ]) !!}
                                        </div>
                                    </div>

                                    @if (\App\Models\Utility::CustomerAuthCheck($store->slug) != true)
                                        <!-- Account Section -->
                                        <div class="mb-4 mt-4">
                                            <div class="checkbox flex items-center mb-2 gap-2">
                                                <input type="checkbox" id="register" name="register" value="{{ old('register') }}"
                                                    class="rounded border-gray-300 text-primary focus:ring-primary">
                                                <label for="create-account" class="flex-1 font-medium">{{ __('Create an account?') }}</label>
                                            </div>
                                            <p class="text-sm text-gray-500">{{ __('Create an account for faster checkout and to track your orders') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-4">
                                <h2 class="font-semibold text-xl md:mb-4 mb-3">2. {{ __('Billing Information') }}</h2>
                                <div class="bg-gray-50 border md:p-6 p-4 rounded-lg shadow-sm">

                                    <div class="selling_address_form col-12">
                                    </div>
                                </div>
                            </div>

                            {{-- Shipping Address section removed - digital products do not require shipping --}}

                            <div class="mt-4">
                                <h2 class="font-semibold text-xl md:mb-4 mb-3">3. {{ __('Payment Method') }}</h2>
                                <div class="bg-gray-50 border rounded-lg shadow-sm">
                                    <!-- Payment Options -->
                                    <div class="space-y-3 md:p-6 p-4 max-h-[484px] overflow-y-auto">
                                        <input type="hidden" name="payment_type" class="payment_types" value="cod">
                                        @foreach ($payments as $key => $payment)
                                            @if ($payment['status'] == 'on')
                                                <div class="radio-btn flex p-3 border rounded-md gap-3 flex-col">
                                                <div class="flex ">
                                                    <input type="radio" id="{{ $payment['name'] }}" name="payment_setting_id" {{ $key == 0 ? 'checked' : '' }} value="{{ $payment['name'] }}" onchange="document.getElementsByName('payment_type')[0].value = '{{ $payment['name'] }}'" class="text-primary focus:ring-primary mr-2 payment_change">
                                                    <label for="{{ $payment['name'] }}" class="flex items-center w-full flex-wrap">
                                                        <span class="font-medium mr-3">{{ $payment['name_string'] }}</span>
                                                        <div class="center-descrp text-sm"> {{ $payment['detail'] }} </div>
                                                    </label>
                                                </div>

                                                    @if($payment['name'] == 'whatsapp')
                                                        <form method="POST" action="{{ route('user.whatsapp',$slug) }}" class="payment-method-form">
                                                            @csrf
                                                            <div class="form-group w-100">
                                                                <input name="wts_number" class="form-input max-w-md phone-number" value="{{ old('wts_number') }}"  id="wts_number" type="text" placeholder="Enter Your Phone Number">
                                                            </div>

                                                        </form>
                                                    @endif
                                                    @if($payment['name'] == 'bank_transfer')
                                                        <div class="form-group w-100">
                                                            <input type="file" name="payment_receipt" class="form-input max-w-md mb-3 bank_transfer_receipt">
                                                        </div>
                                                    @endif
                                                    @if($payment['name'] == 'Paiementpro')
                                                    <div class="flex gap-3 sm:flex-row flex-col">
                                                        <div class="form-group" id="mobile_div">
                                                            <input type="text" name="mobile_number" value="{{ old('mobile_number') }}"  class="form-input max-w-md font-style mobile_number" id="mobile_number" placeholder="Enter Your Phone Number">
                                                        </div>
                                                        <div class="form-group flex flex-col gap-2" id="channel_div">
                                                            <input type="text" name="channel" value="{{ old('channel') }}"  class="form-input max-w-md font-style channel" id="channel" placeholder="Enter Your channel number">
                                                            <small class="text-red-500">Example : OMCIV2,MOMO,CARD,FLOOZ ,PAYPAL</small>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <h2 class="font-semibold text-xl md:mb-4 mb-3">4. {{ __('Order Notes') }}</h2>
                                <div class="bg-gray-50 border md:p-6 p-4 rounded-lg shadow-sm">
                                    <label class="block mb-2 font-medium md:text-base text-sm">{{ __('Add Comments About Your Order') }}</label>
                                    <textarea class="form-input" name="payment_comment" placeholder="Description" rows="3"></textarea>
                                </div>
                            </div>

                            @stack('CustomFieldView')
                            @stack('addCheckoutAttachment')

                            @if (isset($settings['additional_notes']) && $settings['additional_notes'] == 'on')
                                <div class="mt-4">
                                    <h2 class="font-semibold text-xl md:mb-4 mb-3">5. {{ __('Additional Notes') }}</h2>
                                    <div class="bg-gray-50 border md:p-6 p-4 rounded-lg shadow-sm">
                                        <div class="acnav-list additional_notes">

                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    @include('front_end.hooks.checkout_form')

                    {!! Form::hidden('coupon_code', null, ['id' => 'coupon_code']) !!}
                    {!! Form::hidden('sub_total', null, ['id' => 'sub_total_checkout_page']) !!}
                    <!-- Right Side - Order Summary -->
                    <div class="lg:w-1/3">
                        <div class="checkout_page_cart col-lg-3 col-12 ">
                            <div class="checkout-page-right">

                            </div>
                        </div>
                    </div>
                </div>
            {!! Form::close() !!}
        </div>
    </section>
    @endif
</main>
@endsection

@php
    $payfast_mode = \App\Models\Utility::GetValueByName('payfast_mode', $store->id);
    $pfHost = $payfast_mode == 'sandbox' ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
@endphp

@push('page-script')

<script>
    'use strict';
    $(document).ready(function() {
        loadAddressForm("billing", ".selling_address_form");
        loadAdditionalNote();
    });

    function loadAddressForm(type, targetElement) {
        $.ajax({
            url: "{{ route('order.address.form',$slug) }}",
            method: "POST",
            data: {
                type: type
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function(response) {
                $(targetElement).html(response.data.html);
            },
            error: function(xhr, status, error) {
                try {
                    var res = JSON.parse(xhr.responseText);
                    show_toastr('Error', res.message || 'Something went wrong', 'error');
                } catch (e) {
                    show_toastr('Error', 'Something went wrong', 'error');
                }
            },
        });
    }

    function loadAdditionalNote(type, targetElement) {
        $.ajax({
            url: additionalnote,
            method: 'GET',
            context: this,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $('.paymentlist_data_tab').removeClass('is-open');
                $('.paymentlist_data').hide();
                $('.additional_notes').html(response.html_data);
                $('.additional_notes_tab').addClass('is-open');
            },
            error: function(xhr, status, error) {
                try {
                    var res = JSON.parse(xhr.responseText);
                    show_toastr('Error', res.message || 'Something went wrong', 'error');
                } catch (e) {
                    show_toastr('Error', 'Something went wrong', 'error');
                }
            }
        });
    }
</script>

    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-steps/1.1.0/jquery.steps.min.js"></script>
    <script>
        $(document).ready(function() {
            setTimeout(() => {
                $("#formdata").append("<div id='get-payfast-input-data'></div>");
            }, 100);
        });
        $(document).on("click", ".payfast_form", function(event) {

            var payment_type = $('.payment_types').val();
            console.log(payment_type);

            if (payment_type == 'payfast') {
                get_payfast_status();
            }else{
                $('#formdata').submit();
            }
        });
        @if (\Auth::guard('customers')->user())
            function get_payfast_status(amount,coupon){
                var formdata = $('#formdata').serializeArray();
                var slug = '{{ $store->slug }}';
                $.ajax({
                    url: payfast_payment,
                    method: 'POST',
                    data: formdata,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data) {
                        if (data.success == true) {
                            $('#get-payfast-input-data').append(data.inputs);
                            $('#formdata').submit();
                        }else{
                            show_toastr('Error', data.inputs, 'error')
                        }
                    }
                });
            }
        @else
            function get_payfast_status(amount,coupon){
                var formdata = $('#formdata').serializeArray();
                var slug = '{{ $store->slug }}';
                $.ajax({
                    url: payfast_payment_guest,
                    method: 'POST',
                    data: formdata,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data) {
                        if (data.success == true) {
                            $('#get-payfast-input-data').append(data.inputs);
                            $('#formdata').submit();
                        }else{
                            show_toastr('Error', data.inputs, 'error')
                        }
                    }
                });
            }
        @endif
        $(".payfast_form").on("click", function(e) {
            var payment_type = $('#payment_type').val();

            if (payment_type == 'payfast') {
                $('#formdata').attr('action', "https://{{ $pfHost }}/eng/process");
                e.preventDefault();
            }
        });
    </script>
@endpush
