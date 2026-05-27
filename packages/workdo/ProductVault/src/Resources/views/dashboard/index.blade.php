@extends('layouts.app')

@section('page-title', __('Product Marketplace'))
@section('breadcrumb')
@endsection

@section('action-button')
<a href="{{ route('vault-library.index') }}" class="btn btn-outline-primary btn-sm">&#128218; {{ __('My Library') }}</a>
@endsection

@section('content')
@if($products->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <h3 class="text-muted">&#128722;</h3>
            <p class="text-muted">{{ __('No products available yet.') }}</p>
        </div>
    </div>
@else
    <div class="row">
        @foreach($products as $product)
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <a href="{{ route('vault-marketplace.show', $product->id) }}" style="text-decoration:none;color:inherit;">
                    <div class="card h-100">
                        @if($product->preview_image)
                            <img src="{{ asset($product->preview_image) }}" class="card-img-top" style="height:170px;object-fit:cover;">
                        @else
                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height:170px;"><span style="font-size:36px;color:#94a3b8">&#128196;</span></div>
                        @endif
                        <div class="card-body">
                            @if($product->category)
                                <small class="text-uppercase text-primary fw-bold">{{ $product->category }}</small><br>
                            @endif
                            <h5 class="card-title mb-2">{{ $product->name }}</h5>
                            @if($product->short_description)
                                <p class="card-text text-muted small mb-2">{{ Str::limit($product->short_description, 80) }}</p>
                            @endif
                            <div class="d-flex justify-content-between align-items-center">
                                @if($product->price > 0)
                                    <span class="text-success fw-bold fs-5">${{ number_format($product->price, 2) }}</span>
                                @else
                                    <span class="text-primary fw-bold fs-5">Free</span>
                                @endif
                                <span class="btn btn-primary btn-sm">{{ __('View') }} &rarr;</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endif
@endsection