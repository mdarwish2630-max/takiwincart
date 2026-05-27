@extends('layouts.app')

@section('page-title', __('Checkout'))
@section('breadcrumb')
@endsection

@section('content')
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <div style="max-width:700px;margin:0 auto;">
            {{-- Product Info --}}
            <div class="row mb-4 p-3 rounded" style="background:#f8fafc;">
                @if($product->preview_image)
                    <div class="col-auto">
                        <img src="{{ asset($product->preview_image) }}" style="width:100px;height:100px;border-radius:8px;object-fit:cover;">
                    </div>
                @endif
                <div>
                    <h5 class="mb-1">{{ $product->name }}</h5>
                    @if($product->price > 0)
                        <h4 class="text-success mb-1">${{ number_format($product->price, 2) }}</h4>
                    @else
                        <span class="badge bg-success">FREE</span>
                    @endif
                    @if($product->short_description)
                        <p class="text-muted mb-0 small">{{ $product->short_description }}</p>
                    @endif
                </div>
            </div>

            {{-- FREE --}}
            @if($product->price == 0 || $product->price === '0.00' || $product->price === '0')
                <div class="alert alert-info">
                    <strong>{{ __('This product is free!') }}</strong>
                    <form method="POST" action="{{ route('vault-library.process-checkout', $product->id) }}" class="mt-3">
                        @csrf
                        <input type="hidden" name="payment_type" value="free">
                        <button type="submit" class="btn btn-success">{{ __('Download Now') }} &rarr;</button>
                    </form>
                </div>

            {{-- Has Payment Link --}}
            @elseif(!empty($product->payment_link))
                <div class="alert alert-info">
                    <h5>{{ __('Complete Payment') }}</h5>
                    <p class="text-muted mb-2">{{ __('Click below to pay, then upload your receipt.') }}</p>
                    <a href="{{ $product->payment_link }}" target="_blank" class="btn btn-primary">{{ __('Pay Now') }} &rarr;</a>
                </div>
                <h5 class="mt-4">{{ __('Upload Payment Receipt') }}</h5>
                <form method="POST" action="{{ route('vault-library.process-checkout', $product->id) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Payer Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="payer_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Payer Email') }} <span class="text-danger">*</span></label>
                            <input type="email" name="payer_email" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Receipt / Screenshot') }} <span class="text-danger">*</span></label>
                        <input type="file" name="receipt" class="form-control" accept="image/*,.pdf" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Notes (optional)') }}</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                    <input type="hidden" name="payment_type" value="external">
                    <button type="submit" class="btn btn-success">{{ __('Submit Receipt') }} &rarr;</button>
                </form>

            {{-- No Payment Link --}}
            @else
                <h5>{{ __('Upload Payment Receipt') }}</h5>
                <p class="text-muted mb-3">{{ __('Upload your receipt. We will review and activate your access.') }}</p>
                <form method="POST" action="{{ route('vault-library.process-checkout', $product->id) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Payer Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="payer_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Payer Email') }} <span class="text-danger">*</span></label>
                            <input type="email" name="payer_email" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Receipt / Screenshot') }} <span class="text-danger">*</span></label>
                        <input type="file" name="receipt" class="form-control" accept="image/*,.pdf" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Notes (optional)') }}</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                    <input type="hidden" name="payment_type" value="manual">
                    <button type="submit" class="btn btn-success">{{ __('Submit Receipt') }} &rarr;</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection