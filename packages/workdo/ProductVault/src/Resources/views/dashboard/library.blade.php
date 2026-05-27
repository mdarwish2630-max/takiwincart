@extends('layouts.app')

@section('page-title', __('My Library'))
@section('breadcrumb')
@endsection

@section('content')
@if($purchases->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <h3 class="text-muted">&#128218;</h3>
            <p class="text-muted">{{ __("You haven't purchased any products yet.") }}</p>
            <a href="{{ route('vault-marketplace.index') }}" class="btn btn-primary">{{ __('Browse Marketplace') }}</a>
        </div>
    </div>
@else
    <div class="row">
        @foreach($purchases as $purchase)
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <div class="card h-100">
                    @if($purchase->product && $purchase->product->preview_image)
                        <img src="{{ asset($purchase->product->preview_image) }}" class="card-img-top" style="height:150px;object-fit:cover;">
                    @else
                        <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height:150px;"><span style="font-size:36px;color:#94a3b8">&#128196;</span></div>
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $purchase->product ? $purchase->product->name : __('Product Removed') }}</h5>
                        @if($purchase->price_paid > 0)
                            <h6 class="text-success">${{ number_format($purchase->price_paid, 2) }}</h6>
                        @else
                            <h6 class="text-primary">{{ __('Free') }}</h6>
                        @endif
                        @php
                            $st = $purchase->payment_status ?? 'pending';
                            $cls = 'bg-warning';
                            if($st === 'approved') $cls = 'bg-success';
                            elseif($st === 'rejected') $cls = 'bg-danger';
                        @endphp
                        <span class="badge {{ $cls }}">{{ ucfirst($st) }}</span>
                        <p class="text-muted small mt-1 mb-2">{{ $purchase->purchased_at ? \Carbon\Carbon::parse($purchase->purchased_at)->format('M d, Y') : 'N/A' }}</p>
                        @if($purchase->rejection_reason)
                            <div class="alert alert-danger small py-1 px-2 mb-2">{{ $purchase->rejection_reason }}</div>
                        @endif
                        @if($purchase->admin_notes)
                            <div class="alert alert-info small py-1 px-2 mb-2">{{ $purchase->admin_notes }}</div>
                        @endif
                                               <div class="d-flex gap-2 mt-2">
                            @if($purchase->payment_status === 'approved' && $purchase->product && $purchase->product->file_path)
                                <a href="{{ asset($purchase->product->file_path) }}" download class="btn btn-sm btn-success" target="_blank">{{ __('Download') }}</a>
                            @endif
                            @if($purchase->receipt)
                                <a href="{{ asset($purchase->receipt) }}" target="_blank" class="btn btn-sm btn-outline-primary">{{ __('Receipt') }}</a>
                            @endif
                            @if($purchase->payment_status === 'approved')
                                @if($purchase->imported)
                                    <a href="{{ route('vault-library.edit-import', $purchase->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> {{ __('Edit') }}
                                    </a>
                                    <span class="badge bg-success align-self-center small"><i class="fas fa-check"></i> {{ __('Imported') }}</span>
                                @else
                                    <a href="{{ route('vault-library.import-form', $purchase->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-file-import"></i> {{ __('Import') }}
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection