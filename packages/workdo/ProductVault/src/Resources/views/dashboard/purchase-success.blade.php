@extends('layouts.main')

@section('content')
<div class="container" style="max-width:600px; margin-top:30px;">
    <div class="card">
        <div class="card-body text-center">
            <div class="mb-3">
                <i class="fas fa-check-circle fa-4x text-success"></i>
            </div>
            <h4 class="text-success">Purchase Successful!</h4>

            @if(isset($product))
                <p class="text-muted">
                    You have purchased: <strong>{{ $product->name }}</strong>
                </p>
            @endif

            <p>Your product is now available in your library.</p>

            <div class="d-flex justify-content-center gap-2 mt-4">
                <a href="{{ route('vault-library.index') }}" class="btn btn-primary">
                    <i class="fas fa-book"></i> Go to Library
                </a>
                <a href="{{ route('vault-marketplace.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-store"></i> Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
