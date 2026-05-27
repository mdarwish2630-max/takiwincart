@extends("layouts.app")

@section("content")
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __("Purchase Requests") }}</h3>
                </div>
                <div class="col-auto">
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            @if(request("status")) {{ __("Status: ") }} {{ request("status") }} @else {{ __("All Status") }} @endif
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item {{ !request("status") ? "active" : "" }}" href="{{ route("product-vault.purchases") }}">{{ __("All") }} ({{ $stats["total"] }})</a></li>
                            <li><a class="dropdown-item {{ request("status") == "pending" ? "active" : "" }}" href="{{ route("product-vault.purchases", ["status" => "pending"]) }}">{{ __("Pending") }} ({{ $stats["pending"] }})</a></li>
                            <li><a class="dropdown-item {{ request("status") == "approved" ? "active" : "" }}" href="{{ route("product-vault.purchases", ["status" => "approved"]) }}">{{ __("Approved") }} ({{ $stats["approved"] }})</a></li>
                            <li><a class="dropdown-item {{ request("status") == "rejected" ? "active" : "" }}" href="{{ route("product-vault.purchases", ["status" => "rejected"]) }}">{{ __("Rejected") }} ({{ $stats["rejected"] }})</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        @if(session("success"))
            <div class="alert alert-success">{{ session("success") }}</div>
        @endif

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">{{ __("Total") }}</p>
                                <h4>{{ $stats["total"] }}</h4>
                            </div>
                            <div class="avatar-sm bg-primary-subtle rounded">
                                <i class="ti ti-shopping-cart avatar-title text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">{{ __("Pending") }}</p>
                                <h4 class="text-warning">{{ $stats["pending"] }}</h4>
                            </div>
                            <div class="avatar-sm bg-warning-subtle rounded">
                                <i class="ti ti-clock avatar-title text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">{{ __("Approved") }}</p>
                                <h4 class="text-success">{{ $stats["approved"] }}</h4>
                            </div>
                            <div class="avatar-sm bg-success-subtle rounded">
                                <i class="ti ti-check avatar-title text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">{{ __("Revenue") }}</p>
                                <h4 class="text-primary">{{ number_format($stats["revenue"], 2) }}</h4>
                            </div>
                            <div class="avatar-sm bg-info-subtle rounded">
                                <i class="ti ti-currency-dollar avatar-title text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchases Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __("Date") }}</th>
                                <th>{{ __("Product") }}</th>
                                <th>{{ __("Merchant") }}</th>
                                <th>{{ __("Amount") }}</th>
                                <th>{{ __("Payment") }}</th>
                                <th>{{ __("Status") }}</th>
                                <th>{{ __("Actions") }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchases as $purchase)
                            <tr>
                                <td>{{ $purchase->id }}</td>
                                <td>{{ $purchase->purchased_at ? $purchase->purchased_at->format("Y-m-d H:i") : "-" }}</td>
                                <td>
                                    @if($purchase->product)
                                        <strong>{{ $purchase->product->name }}</strong>
                                    @else
                                        <span class="text-muted">{{ __("Deleted") }}</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $purchase->payer_name ?? $purchase->user->name ?? "-" }}<br>
                                    <small class="text-muted">{{ $purchase->payer_email ?? $purchase->user->email ?? "" }}</small>
                                </td>
                                <td><strong>{{ number_format($purchase->price_paid, 2) }}</strong></td>
                                <td>
                                    <span class="badge bg-secondary">{{ $purchase->payment_type }}</span>
                                    @if($purchase->receipt)
                                        <br><a href="{{ asset("storage/" . $purchase->receipt) }}" target="_blank" class="small text-info">
                                            <i class="ti ti-photo"></i> {{ __("View Receipt") }}
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $purchase->status_badge }}">{{ $purchase->status_label }}</span>
                                    @if($purchase->approved_at)
                                        <br><small class="text-muted">{{ $purchase->approved_at->format("Y-m-d H:i") }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if(in_array($purchase->payment_status, ["pending", "bank_pending"]))
                                        <form method="POST" action="{{ route("product-vault.purchases.approve", $purchase->id) }}" style="display:inline;">
                                            @csrf
                                            <button class="btn btn-sm btn-success" title="{{ __("Approve") }}">
                                                <i class="ti ti-check"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $purchase->id }}" title="{{ __("Reject") }}">
                                            <i class="ti ti-x"></i>
                                        </button>

                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal{{ $purchase->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">{{ __("Reject Purchase") }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" action="{{ route("product-vault.purchases.reject", $purchase->id) }}">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">{{ __("Rejection Reason") }} <span class="text-danger">*</span></label>
                                                                <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="{{ __("Why are you rejecting this purchase?") }}"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __("Cancel") }}</button>
                                                            <button type="submit" class="btn btn-danger">{{ __("Reject") }}</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    {{ __("No purchases found.") }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $purchases->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection