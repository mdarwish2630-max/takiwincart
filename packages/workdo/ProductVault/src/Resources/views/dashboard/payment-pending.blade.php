@extends('layouts.main')

@section('content')
<div class="container" style="max-width:600px; margin-top:30px;">
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-clock"></i> Payment Pending</h5>
        </div>
        <div class="card-body text-center">
            <div class="mb-3">
                <i class="fas fa-hourglass-half fa-3x text-warning"></i>
            </div>

            <h5>Complete Your Payment</h5>
            <p class="text-muted">
                You are purchasing: <strong>{{ $product->name }}</strong><br>
                Amount: <strong>{{ number_format($product->price, 2) }}</strong>
            </p>

            @if($product->payment_link)
                <div class="alert alert-info mb-3">
                    <p class="mb-1"><strong>Step 1:</strong> Complete payment using the link below:</p>
                    <a href="{{ $product->payment_link }}" target="_blank" class="btn btn-primary btn-lg w-100 mb-2">
                        <i class="fas fa-external-link-alt"></i> Pay {{ number_format($product->price, 2) }}
                    </a>
                </div>
            @endif

            <div class="alert alert-secondary">
                <p class="mb-1">
                    <strong>Step 2:</strong> Upload your payment receipt:
                </p>
                <form method="POST" action="{{ route('vault-library.upload-receipt', $purchase->id) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-2 text-start">
                        <label class="form-label">Receipt Image *</label>
                        <input type="file" name="receipt" class="form-control" accept="image/*" required>
                    </div>
                    <div class="mb-2 text-start">
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Transaction number or any notes..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-paper-plane"></i> Submit Receipt
                    </button>
                </form>
            </div>

            <p class="text-muted small mt-3">
                After submitting your receipt, an admin will review it and approve your purchase.
            </p>

            <a href="{{ route('vault-library.index') }}" class="btn btn-link">
                <i class="fas fa-arrow-left"></i> Back to Library
            </a>
        </div>
    </div>
</div>
@endsection
