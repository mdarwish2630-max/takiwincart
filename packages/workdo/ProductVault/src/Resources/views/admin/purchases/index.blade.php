@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-receipt"></i> Purchase Orders</h5>
            <a href="{{ route('product-vault.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Products
            </a>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('product-vault.purchases.index') }}" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by user name..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                </div>
            </form>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Product</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Receipt</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->id }}</td>
                            <td>{{ $purchase->user ? $purchase->user->name : 'N/A' }}</td>
                            <td>{{ $purchase->product ? Str::limit($purchase->product->name, 30) : 'N/A' }}</td>
                            <td>{{ number_format($purchase->price_paid, 2) }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($purchase->payment_type) }}</span></td>
                            <td>
                                <span class="badge bg-{{ $purchase->payment_status === 'approved' ? 'success' : ($purchase->payment_status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($purchase->payment_status) }}
                                </span>
                            </td>
                            <td>
                                @if($purchase->receipt)
                                    <a href="{{ asset('storage/' . $purchase->receipt) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-image"></i> View
                                    </a>
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </td>
                            <td>{{ $purchase->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <a href="{{ route('product-vault.purchases.show', $purchase->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $purchases->links() }}
        </div>
    </div>
</div>
@endsection
