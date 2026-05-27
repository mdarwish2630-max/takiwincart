@extends('layouts.main')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <a href="{{ route('vault-marketplace.index') }}" class="btn btn-link mb-3">
                <i class="fas fa-arrow-left"></i> Back to Marketplace
            </a>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="mb-0">Checkout</h4>
                </div>
                <div class="card-body">

                    {{-- Product Info --}}
                    <div class="d-flex mb-4 p-3 bg-light rounded align-items-center">
                        @if($product->preview_image)
                            <img src="{{ Storage::url($product->preview_image) }}" class="rounded me-3" style="width:80px;height:80px;object-fit:cover;">
                        @else
                            <div class="bg-secondary text-white rounded me-3 d-flex align-items-center justify-content-center" style="width:80px;height:80px;">
                                <i class="fas fa-file"></i>
                            </div>
                        @endif
                        <div>
                            <h5 class="mb-1">{{ $product->name }}</h5>
                            @if($product->category)
                                <small class="text-muted">{{ $product->category }}</small>
                            @endif
                            <div class="mt-1">
                                @if($product->price > 0)
                                    <span class="h5 text-primary">{{ $currency }}{{ number_format($product->price, 2) }}</span>
                                @else
                                    <span class="h5 text-success">FREE</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($product->price <= 0)
                        {{-- FREE PRODUCT --}}
                        <div class="text-center py-3 mb-4">
                            <i class="fas fa-gift fa-3x text-success"></i>
                            <h5 class="text-success mt-2">This product is free!</h5>
                        </div>
                        <form method="POST" action="{{ route('vault-marketplace.process-checkout', $product->id) }}">
                            @csrf
                            <input type="hidden" name="payment_method" value="free">
                            <button type="submit" class="btn btn-success btn-lg w-100"><i class="fas fa-download me-2"></i>Get for Free</button>
                        </form>
                    @else
                        {{-- PAID PRODUCT --}}
                        <div class="text-center mb-4">
                            <div class="mb-3">
                                <i class="fas fa-credit-card fa-2x text-primary"></i>
                            </div>
                            <h5>Complete your payment</h5>
                            <p class="text-muted">Click the button below to proceed to payment</p>

                            @if($product->payment_link)
                                <a href="{{ $product->payment_link }}" target="_blank" class="btn btn-primary btn-lg w-100 mb-3">
                                    <i class="fas fa-external-link-alt me-2"></i>Pay Now - {{ $currency }}{{ number_format($product->price, 2) }}
                                </a>
                                <p class="text-muted small"><i class="fas fa-info-circle me-1"></i>You will be redirected to the payment page. After payment, upload your receipt below.</p>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Payment link is not available for this product. Please contact support.
                                </div>
                            @endif
                        </div>

                        {{-- Receipt Upload --}}
                        <hr>
                        <h6 class="mb-3"><i class="fas fa-receipt me-2"></i>Upload Payment Receipt</h6>
                        <form method="POST" action="{{ route('vault-marketplace.process-checkout', $product->id) }}" enctype="multipart/form-data" id="receiptForm">
                            @csrf
                            <input type="hidden" name="payment_method" value="external">

                            <div class="mb-3">
                                <label class="form-label">Payment Receipt *</label>
                                <input type="file" name="receipt" class="form-control" accept="image/*,.pdf" required>
                                <small class="text-muted">Upload screenshot or PDF of your payment confirmation</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes (Optional)</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Transaction ID, additional info..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-success btn-lg w-100" id="submitReceipt">
                                <i class="fas fa-check-circle me-2"></i>Submit Receipt
                            </button>
                            <p class="text-center text-muted small mt-2">Your purchase will be reviewed after submitting the receipt</p>
                        </form>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection