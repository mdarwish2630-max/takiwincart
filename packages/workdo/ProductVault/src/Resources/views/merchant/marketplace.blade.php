@extends('layouts.main')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-12">
            <h2 class="h3 mb-1">Product Marketplace</h2>
            <p class="text-muted mb-0">Browse our digital products library</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Categories Filter --}}
    @if($categories && $categories->isNotEmpty())
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('vault-marketplace.index') }}" class="btn btn-outline-primary btn-sm {{ !request('category') ? 'active' : '' }}">All</a>
                    @foreach($categories as $cat)
                        <a href="{{ route('vault-marketplace.index', ['category' => $cat]) }}" class="btn btn-outline-secondary btn-sm {{ request('category') == $cat ? 'active' : '' }}">{{ $cat }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Products Grid --}}
    <div class="row">
        @forelse($products as $product)
            <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                <div class="card h-100 shadow-sm">
                    @if($product->preview_image)
                        <a href="{{ route('vault-marketplace.show', $product->id) }}">
                            <img src="{{ Storage::url($product->preview_image) }}" class="card-img-top" alt="{{ $product->name }}" style="height:200px;object-fit:cover;">
                        </a>
                    @else
                        <a href="{{ route('vault-marketplace.show', $product->id) }}">
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:200px;">
                                <span style="font-size:3rem;opacity:0.2;">&#128196;</span>
                            </div>
                        </a>
                    @endif
                    <div class="card-body d-flex flex-column">
                        @if($product->category)
                            <span class="badge bg-light text-dark mb-2">{{ $product->category }}</span>
                        @endif
                        <h5 class="card-title">
                            <a href="{{ route('vault-marketplace.show', $product->id) }}" class="text-dark text-decoration-none">{{ $product->name }}</a>
                        </h5>
                        <p class="card-text text-muted small">
                            {{ Str::limit(strip_tags($product->description), 100) }}
                        </p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <div>
                                @if($product->price > 0)
                                    <span class="h5 text-primary mb-0">${{ number_format($product->price, 2) }}</span>
                                @else
                                    <span class="h5 text-success mb-0">FREE</span>
                                @endif
                            </div>
                            @if(in_array($product->id, $purchasedIds))
                                <a href="{{ route('vault-library.index') }}" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-check me-1"></i>Owned
                                </a>
                            @else
                                <a href="{{ route('vault-marketplace.checkout', $product->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-shopping-cart me-1"></i>Buy
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div style="font-size:4rem;opacity:0.3;">&#128218;</div>
                <h4 class="text-muted mt-3">No products available</h4>
                <p class="text-muted">Check back later for new products</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="row mt-4">
        <div class="col-12 d-flex justify-content-center">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection