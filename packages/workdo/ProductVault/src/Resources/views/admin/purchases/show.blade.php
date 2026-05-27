@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Purchase #{{ $purchase->id }}</h5>
            <a href="{{ route('product-vault.purchases.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>User Info</h6>
                    <table class="table table-sm">
                        <tr><td width="120"><strong>Name:</strong></td><td>{{ $purchase->user ? $purchase->user->name : 'N/A' }}</td></tr>
                        <tr><td><strong>Email:</strong></td><td>{{ $purchase->user ? $purchase->user->email : 'N/A' }}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Product Info</h6>
                    <table class="table table-sm">
                        <tr><td width="120"><strong>Product:</strong></td><td>{{ $purchase->product ? $purchase->product->name : 'N/A' }}</td></tr>
                        <tr><td><strong>Amount:</strong></td><td>{{ number_format($purchase->price_paid, 2) }}</td></tr>
                        <tr><td><strong>Type:</strong></td><td>{{ ucfirst($purchase->payment_type) }}</td></tr>
                        <tr><td><strong>Status:</strong></td>
                            <td>
                                <span class="badge bg-{{ $purchase->payment_status === 'approved' ? 'success' : ($purchase->payment_status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($purchase->payment_status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Receipt</h6>
                    @if($purchase->receipt)
                        <a href="{{ asset('storage/' . $purchase->receipt) }}" target="_blank">
                            <img src="{{ asset('storage/' . $purchase->receipt) }}" alt="Receipt" style="max-width:100%;max-height:300px;border-radius:8px;border:1px solid #ddd;">
                        </a>
                    @else
                        <p class="text-muted">No receipt uploaded.</p>
                    @endif
                </div>
                <div class="col-md-6">
                    <h6>User Notes</h6>
                    <p>{{ $purchase->notes or 'No notes provided.' }}</p>

                    @if($purchase->approved_at)
                        <h6>Approved At</h6>
                        <p>{{ $purchase->approved_at->format('M d, Y H:i') }}</p>
                    @endif

                    @if($purchase->rejected_at)
                        <h6>Rejected At</h6>
                        <p>{{ $purchase->rejected_at->format('M d, Y H:i') }}</p>
                        <h6>Rejection Reason</h6>
                        <p class="text-danger">{{ $purchase->rejection_reason or 'N/A' }}</p>
                    @endif

                    @if($purchase->admin_notes)
                        <h6>Admin Notes</h6>
                        <p>{{ $purchase->admin_notes }}</p>
                    @endif
                </div>
            </div>

            @if($purchase->payment_status === 'pending')
            <div class="card mt-3">
                <div class="card-header bg-warning text-dark">
                    <strong>Pending Action Required</strong>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <form method="POST" action="{{ route('product-vault.purchases.approve', $purchase->id) }}">
                                @csrf
                                <h6><i class="fas fa-check-circle text-success"></i> Approve Purchase</h6>
                                <div class="mb-2">
                                    <label class="form-label">Admin Notes (optional)</label>
                                    <textarea name="admin_notes" class="form-control" rows="2"></textarea>
                                </div>
                                <button type="submit" class="btn btn-success" onclick="return confirm('Approve this purchase?')">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form method="POST" action="{{ route('product-vault.purchases.reject', $purchase->id) }}">
                                @csrf
                                <h6><i class="fas fa-times-circle text-danger"></i> Reject Purchase</h6>
                                <div class="mb-2">
                                    <label class="form-label">Rejection Reason *</label>
                                    <textarea name="rejection_reason" class="form-control" rows="2" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Reject this purchase?')">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="mt-3">
                <small class="text-muted">Created: {{ $purchase->created_at->format('M d, Y H:i:s') }} | Updated: {{ $purchase->updated_at->format('M d, Y H:i:s') }}</small>
            </div>
        </div>
    </div>
</div>
@endsection
