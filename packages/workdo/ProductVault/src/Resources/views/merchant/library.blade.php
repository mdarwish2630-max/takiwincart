@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h4 class="mb-4">My Product Library</h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    @if(isset($pendingPurchases) && $pendingPurchases->count() > 0)
        <div class="card mb-4 border-warning">
            <div class="card-header bg-warning text-dark"><h6 class="mb-0"><i class="fas fa-clock me-2"></i>Pending ({{ $pendingPurchases->count() }})</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Product</th><th>Payment</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                        @foreach($pendingPurchases as $p)
                            <tr>
                                <td>{{ $p->product->name ?? 'N/A' }}</td>
                                <td>{{ $p->payment_type }}</td>
                                <td><span class="badge bg-warning text-dark">{{ ucfirst(str_replace('_', ' ', $p->payment_status)) }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($p->purchased_at)->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header"><h6 class="mb-0"><i class="fas fa-box me-2"></i>My Products</h6></div>
        <div class="card-body p-0">
            @if(isset($purchases) && $purchases->count() > 0)
                <table class="table mb-0">
                    <thead><tr><th>Product</th><th>Price</th><th>Status</th><th>Approved</th><th>Imported</th><th>Action</th></tr></thead>
                    <tbody>
                        @foreach($purchases as $p)
                            <tr>
                                <td><strong>{{ $p->product->name ?? 'N/A' }}</strong></td>
                                <td>${{ number_format($p->price_paid, 2) }}</td>
                                <td>
                                    @php $c = match($p->payment_status){'approved'=>'success','rejected'=>'danger',default=>'secondary'}; @endphp
                                    <span class="badge bg-{{ $c }}">{{ ucfirst($p->payment_status) }}</span>
                                    @if($p->rejection_reason)<br><small class="text-danger">{{ $p->rejection_reason }}</small>@endif
                                </td>
                                <td>{{ $p->approved_at ? \Carbon\Carbon::parse($p->approved_at)->format('M d, Y') : '-' }}</td>
                                <td>{{ $p->imported ? '<span class="badge bg-info">Yes</span>' : '<span class="text-muted">No</span>' }}</td>
                                <td>
                                    @if($p->payment_status === 'approved' && !$p->imported)
                                        <form method="POST" action="{{ route('vault-library.import', $p->id) }}" style="display:inline">@csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Import to your store?')"><i class="fas fa-file-import me-1"></i>Import</button>
                                        </form>
                                    @elseif($p->imported)
                                        <span class="text-success"><i class="fas fa-check"></i> Done</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-box-open fa-3x mb-3"></i>
                    <p>No products yet. <a href="{{ route('vault-marketplace.index') }}">Browse marketplace</a></p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection