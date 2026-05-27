@extends('layouts.main')

@section('content')
<div class="container-fluid py-4">
    <a href="{{ route('vault-marketplace.index') }}" class="btn btn-link mb-3">
        <i class="fas fa-arrow-left"></i> Back to Marketplace
    </a>
    <div class="row">
        <div class="col-md-6 mb-4">
            @if($product->preview_image)
                <img src="{{ Storage::url($product->preview_image) }}" class="img-fluid rounded shadow" alt="{{ $product->name }}">
            @else
                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 400px;">
                    <span style="font-size: 5rem; opacity: 0.2;">&#128196;</span>
                </div>
            @endif
        </div>
        <div class="col-md-6">
            <h2>{{ $product->name }}</h2>
            @if($product->category)
                <span class="badge bg-secondary mb-3">{{ $product->category }}</span>
            @endif
            <h3 class="text-primary mb-3">
                @if($product->price > 0)
                    ${{ number_format($product->price, 2) }}
                @else
                    FREE
                @endif
            </h3>
            <div class="mb-4">
                <h5>Description</h5>
                <p>{!! nl2br(e($product->description)) !!}</p>
            </div>
            @if($product->short_description)
                <div class="mb-4">
                    <h5>Short Description</h5>
                    <p>{{ $product->short_description }}</p>
                </div>
            @endif
            @if($alreadyPurchased)
                <div class="alert alert-info">
                    <i class="fas fa-check-circle me-2"></i> You already own this product.
                    <a href="{{ route('vault-library.index') }}" class="btn btn-primary btn-sm ms-2">Go to My Purchases</a>
                </div>
            @else
                <a href="{{ route('vault-marketplace.checkout', $product->id) }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-cart me-2"></i>
                    @if($product->price > 0)
                        Purchase - ${{ number_format($product->price, 2) }}
                    @else
                        Get for Free
                    @endif
                </a>
            @endif
        </div>
    </div>
</div>
@endsection