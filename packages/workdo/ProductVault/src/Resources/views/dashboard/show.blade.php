@extends('layouts.app')

@section('page-title', $product->name)
@section('breadcrumb')
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            @if($product->preview_image)
                <div class="col-md-3">
                    <img src="{{ asset($product->preview_image) }}" class="img-fluid rounded" style="border-radius:10px;">
                </div>
            @endif
            <div class="col-md-9">
                <h2 class="mb-2">{{ $product->name }}</h2>
                @if($product->price > 0)
                    <h3 class="text-success">${{ number_format($product->price, 2) }}</h3>
                @else
                    <span class="badge bg-success" style="font-size:14px;">FREE</span>
                @endif
                <div class="mt-2">
                    @if($product->category)
                        <span class="badge bg-info me-1">{{ $product->category }}</span>
                    @endif
                    @if($product->status)
                        <span class="badge bg-success">{{ ucfirst($product->status) }}</span>
                    @endif
                </div>
                @if($product->short_description)
                    <p class="text-muted mt-2">{{ $product->short_description }}</p>
                @endif
                <div class="d-flex gap-2 mt-3 pt-3 border-top">
                    <a href="{{ route('vault-library.checkout', $product->id) }}" class="btn btn-primary">{{ __('Buy Now') }} &rarr;</a>
                    @if($product->demo_url)
                        <a href="{{ $product->demo_url }}" target="_blank" class="btn btn-outline-secondary">{{ __('Live Demo') }} &rarr;</a>
                    @endif
                </div>
            </div>
        </div>

        @if($product->description)
            <div class="mt-4 pt-3 border-top">
                <h5>{{ __('Description') }}</h5>
                <div class="text-muted">{!! $product->description !!}</div>
            </div>
        @endif
    </div>
</div>
@endsection