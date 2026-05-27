@extends('layouts.main')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="h3 mb-1">My Purchases</h2>
            <p class="text-muted">Your purchased products and download history</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($purchases->isEmpty())
        <div class="text-center py-5">
            <div style="font-size: 4rem; opacity: 0.3;">&#128230;</div>
            <h4 class="mt-3 text-muted">No purchases yet</h4>
            <p class="text-muted mb-3">Browse the marketplace to find products</p>
            <a href="{{ route('vault-marketplace.index') }}" class="btn btn-primary">Browse Marketplace</a>
        </div>
    @else
        <div class="row">
            @foreach($purchases as $purchase)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        @if($purchase->product && $purchase->product->preview_image)
                            <img src="{{ Storage::url($purchase->product->preview_image) }}" class="card-img-top" alt="{{ $purchase->product->name }}" style="height: 180px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                                <span style="font-size: 3rem; opacity: 0.2;">&#128196;</span>
                            </div>
                        @endif
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">{{ $purchase->product ? $purchase->product->name : 'Deleted Product' }}</h5>
                            <p class="card-text text-muted small">
                                Ref: #{{ $purchase->id }}<br>
                                @if($purchase->purchased_at)
                                    Date: {{ $purchase->purchased_at->format('M d, Y') }}<br>
                                @else
                                    Date: {{ $purchase->created_at->format('M d, Y') }}<br>
                                @endif
                                Method: {{ strtoupper(str_replace('_', ' ', $purchase->payment_type)) }}<br>
                                Amount: ${{ number_format($purchase->price_paid, 2) }}
                            </p>
                            <div class="mt-auto">
                                @if($purchase->payment_status == 'approved')
                                    <span class="badge bg-success mb-2">Approved</span>
                                    @if($purchase->product && $purchase->product->file_path)
                                        <a href="{{ route('vault-library.import', $purchase->id) }}" class="btn btn-primary btn-sm w-100">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    @else
                                        <button class="btn btn-secondary btn-sm w-100" disabled>File Not Available</button>
                                    @endif
                                @elseif($purchase->payment_status == 'pending')
                                    <span class="badge bg-warning text-dark mb-2">Pending Review</span>
                                    <button class="btn btn-secondary btn-sm w-100" disabled>
                                        <i class="fas fa-clock"></i> Awaiting Approval
                                    </button>
                                @elseif($purchase->payment_status == 'rejected')
                                    <span class="badge bg-danger mb-2">Rejected</span>
                                    @if($purchase->rejection_reason)
                                        <p class="text-danger small mb-0">{{ $purchase->rejection_reason }}</p>
                                    @else
                                        <p class="text-danger small mb-0">Payment was not approved</p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection