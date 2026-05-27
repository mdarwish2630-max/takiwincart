<?php
/**
 * PV_FixLayoutV3.php - Smart path detection + layout fix
 * Works whether you put it in public/ OR project root
 * 
 * Run: http://localhost/takwincart/PV_FixLayoutV3.php
 */

echo "<h2>ProductVault - Layout Fix V3</h2>";
echo "<pre>";

// ── Smart base path detection ──
$base = str_replace('\\', '/', dirname(__FILE__));
// If we're in public/, go up one level
if (basename($base) === 'public') {
    $base = dirname($base);
}
// Verify artisan exists
if (!file_exists($base . '/artisan')) {
    echo "ERROR: Cannot find project root. Artisan not at: $base/artisan\n";
    echo "Please place this file in: C:\\xampp\\htdocs\\takwincart\\public\\\n";
    echo "</pre>";
    exit;
}
echo "Project root: $base\n\n";

$pkg = $base . '/packages/workdo/ProductVault/src/Resources/views/dashboard';
$pkgCtrl = $base . '/packages/workdo/ProductVault/src/Http/Controllers';

// ─────────────────────────────────────
// STEP 1: checkout.blade.php
// ─────────────────────────────────────
echo "=== Step 1: checkout.blade.php ===\n";

$code = <<<'BLADE'
@extends('layouts.main')

@section('content')
<style>
.vault-checkout-wrap { max-width:800px; margin:0 auto; padding:20px; background:#fff; border-radius:10px; box-shadow:0 1px 8px rgba(0,0,0,.06); }
.vault-ch-title { font-size:20px; font-weight:700; margin-bottom:18px; color:#1e293b; border-bottom:2px solid #e2e8f0; padding-bottom:10px; }
.vault-ch-prod { display:flex; gap:18px; align-items:flex-start; padding:16px; background:#f8fafc; border-radius:8px; margin-bottom:18px; }
.vault-ch-img { width:100px; height:100px; border-radius:8px; object-fit:cover; border:1px solid #e2e8f0; flex-shrink:0; }
.vault-ch-prod h4 { font-size:17px; font-weight:600; margin:0 0 6px 0; color:#1e293b; }
.vault-ch-price { font-size:19px; font-weight:700; color:#16a34a; margin-bottom:6px; }
.vault-ch-desc { color:#64748b; font-size:13px; line-height:1.5; margin:0; }
.vault-ch-section { margin-top:18px; padding:18px; border:1px solid #e2e8f0; border-radius:8px; }
.vault-ch-section h5 { font-size:16px; font-weight:600; margin:0 0 12px 0; color:#1e293b; }
.vault-ch-section p { color:#64748b; font-size:13px; margin-bottom:10px; }
.vault-ch-btn { display:inline-block; padding:10px 24px; background:#2563eb; color:#fff!important; border:none; border-radius:6px; font-size:14px; font-weight:600; text-decoration:none; }
.vault-ch-btn:hover { background:#1d4ed8; color:#fff!important; }
.vault-ch-btn-green { background:#16a34a; }
.vault-ch-btn-green:hover { background:#15803d; }
.vault-ch-info { background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; padding:10px 14px; border-radius:6px; margin-top:10px; font-size:12px; }
.vault-ch-fg { margin-bottom:14px; }
.vault-ch-fg label { display:block; font-weight:600; margin-bottom:5px; color:#374151; font-size:13px; }
.vault-ch-fc { width:100%; padding:9px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; box-sizing:border-box; }
.vault-ch-fc:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 2px rgba(37,99,235,.1); }
textarea.vault-ch-fc { min-height:70px; resize:vertical; }
input[type="file"].vault-ch-fc { padding:8px; border:1px dashed #cbd5e1; background:#f9fafb; }
.vault-ch-free { display:inline-block; padding:4px 12px; background:#dcfce7; color:#16a34a; border-radius:16px; font-weight:600; font-size:13px; }
.vault-ch-alert { padding:10px 14px; border-radius:6px; margin-bottom:14px; font-size:13px; }
.vault-ch-alert-ok { background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a; }
.vault-ch-alert-err { background:#fef2f2; border:1px solid #fecaca; color:#dc2626; }
</style>

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center justify-content-between">
            <div class="col-auto">
                <div class="page-header-title">
                    <h4 class="m-b-10">{{ __('Checkout') }}</h4>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('vault-marketplace.index') }}">{{ __('Marketplace') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('vault-marketplace.show', $product->id) }}">{{ $product->name }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Checkout') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="vault-checkout-wrap">
        @if(session('error'))
            <div class="vault-ch-alert vault-ch-alert-err">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="vault-ch-alert vault-ch-alert-ok">{{ session('success') }}</div>
        @endif

        <div class="vault-ch-prod">
            @if($product->preview_image)
                <img src="{{ asset($product->preview_image) }}" alt="{{ $product->name }}" class="vault-ch-img">
            @else
                <div style="width:100px;height:100px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;flex-shrink:0"><span style="font-size:32px;color:#94a3b8">&#128196;</span></div>
            @endif
            <div>
                <h4>{{ $product->name }}</h4>
                @if($product->price > 0)
                    <div class="vault-ch-price">${{ number_format($product->price, 2) }}</div>
                @else
                    <span class="vault-ch-free">{{ __('FREE') }}</span>
                @endif
                @if($product->short_description)
                    <p class="vault-ch-desc">{{ $product->short_description }}</p>
                @endif
            </div>
        </div>

        @if($product->price == 0 || $product->price === '0.00' || $product->price === '0')
            <div class="vault-ch-section">
                <h5>{{ __('This product is free!') }}</h5>
                <form method="POST" action="{{ route('vault-library.process-checkout', $product->id) }}">
                    @csrf
                    <input type="hidden" name="payment_type" value="free">
                    <button type="submit" class="vault-ch-btn vault-ch-btn-green">{{ __('Download Now') }} &rarr;</button>
                </form>
            </div>
        @elseif(!empty($product->payment_link))
            <div class="vault-ch-section">
                <h5>{{ __('Complete Payment') }}</h5>
                <p>{{ __('Click below to pay. After paying, come back and upload your receipt.') }}</p>
                <a href="{{ $product->payment_link }}" target="_blank" class="vault-ch-btn">{{ __('Pay Now') }} &rarr;</a>
                <div class="vault-ch-info"><strong>{{ __('Important:') }}</strong> {{ __('After payment, upload your receipt below so we can verify.') }}</div>

                <div style="margin-top:18px">
                    <h5>{{ __('Upload Payment Receipt') }}</h5>
                    <form method="POST" action="{{ route('vault-library.process-checkout', $product->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="vault-ch-fg">
                            <label>{{ __('Payer Name') }} <span style="color:#ef4444">*</span></label>
                            <input type="text" name="payer_name" class="vault-ch-fc" placeholder="{{ __('Name used in payment') }}" required>
                        </div>
                        <div class="vault-ch-fg">
                            <label>{{ __('Payer Email') }} <span style="color:#ef4444">*</span></label>
                            <input type="email" name="payer_email" class="vault-ch-fc" placeholder="{{ __('Your payment email') }}" required>
                        </div>
                        <div class="vault-ch-fg">
                            <label>{{ __('Receipt / Screenshot') }} <span style="color:#ef4444">*</span></label>
                            <input type="file" name="receipt" accept="image/*,.pdf" class="vault-ch-fc" required>
                        </div>
                        <div class="vault-ch-fg">
                            <label>{{ __('Notes (optional)') }}</label>
                            <textarea name="notes" class="vault-ch-fc" placeholder="{{ __('Any additional info...') }}"></textarea>
                        </div>
                        <input type="hidden" name="payment_type" value="external">
                        <button type="submit" class="vault-ch-btn vault-ch-btn-green">{{ __('Submit Receipt') }} &rarr;</button>
                    </form>
                </div>
            </div>
        @else
            <div class="vault-ch-section">
                <h5>{{ __('Upload Payment Receipt') }}</h5>
                <p>{{ __('Upload your payment receipt. We will review and activate your access.') }}</p>
                <form method="POST" action="{{ route('vault-library.process-checkout', $product->id) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="vault-ch-fg">
                        <label>{{ __('Payer Name') }} <span style="color:#ef4444">*</span></label>
                        <input type="text" name="payer_name" class="vault-ch-fc" placeholder="{{ __('Name used in payment') }}" required>
                    </div>
                    <div class="vault-ch-fg">
                        <label>{{ __('Payer Email') }} <span style="color:#ef4444">*</span></label>
                        <input type="email" name="payer_email" class="vault-ch-fc" placeholder="{{ __('Your payment email') }}" required>
                    </div>
                    <div class="vault-ch-fg">
                        <label>{{ __('Receipt / Screenshot') }} <span style="color:#ef4444">*</span></label>
                        <input type="file" name="receipt" accept="image/*,.pdf" class="vault-ch-fc" required>
                    </div>
                    <div class="vault-ch-fg">
                        <label>{{ __('Notes (optional)') }}</label>
                        <textarea name="notes" class="vault-ch-fc" placeholder="{{ __('Any additional info...') }}"></textarea>
                    </div>
                    <input type="hidden" name="payment_type" value="manual">
                    <button type="submit" class="vault-ch-btn vault-ch-btn-green">{{ __('Submit Receipt') }} &rarr;</button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
BLADE;

$r = file_put_contents($pkg . '/checkout.blade.php', $code);
echo ($r !== false ? "OK (" . strlen($code) . " bytes)\n" : "FAILED!\n");

// ─────────────────────────────────────
// STEP 2: show.blade.php
// ─────────────────────────────────────
echo "=== Step 2: show.blade.php ===\n";

$code2 = <<<'BLADE'
@extends('layouts.main')

@section('content')
<style>
.vault-show-wrap { max-width:900px; margin:0 auto; padding:20px; background:#fff; border-radius:10px; box-shadow:0 1px 8px rgba(0,0,0,.06); }
.vault-show-header { display:flex; gap:20px; align-items:flex-start; margin-bottom:20px; }
.vault-show-img { width:180px; height:180px; border-radius:10px; object-fit:cover; border:1px solid #e2e8f0; flex-shrink:0; }
.vault-show-name { font-size:22px; font-weight:700; margin:0 0 8px 0; color:#1e293b; }
.vault-show-price { font-size:24px; font-weight:700; color:#16a34a; margin-bottom:8px; }
.vault-show-meta { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:10px; }
.vault-show-badge { display:inline-block; padding:3px 10px; border-radius:16px; font-size:12px; font-weight:500; }
.badge-cat { background:#eff6ff; color:#2563eb; }
.badge-status { background:#dcfce7; color:#16a34a; }
.vault-show-sdesc { color:#64748b; font-size:13px; line-height:1.6; margin:8px 0 0 0; }
.vault-show-actions { margin-top:18px; padding-top:14px; border-top:1px solid #e2e8f0; display:flex; gap:10px; flex-wrap:wrap; }
.vault-show-buy { display:inline-block; padding:11px 28px; background:#2563eb; color:#fff!important; border-radius:6px; font-size:14px; font-weight:600; text-decoration:none; }
.vault-show-buy:hover { background:#1d4ed8; color:#fff!important; }
.vault-show-demo { display:inline-block; padding:11px 28px; background:#f1f5f9; color:#475569!important; border:1px solid #e2e8f0; border-radius:6px; font-size:14px; font-weight:600; text-decoration:none; }
.vault-show-demo:hover { background:#e2e8f0; }
.vault-show-free { display:inline-block; padding:4px 12px; background:#dcfce7; color:#16a34a; border-radius:16px; font-weight:600; font-size:13px; }
.vault-show-body { margin-top:18px; padding-top:14px; border-top:1px solid #e2e8f0; }
.vault-show-body h5 { font-size:16px; font-weight:600; color:#1e293b; margin:0 0 10px 0; }
.vault-show-body p { color:#64748b; font-size:13px; line-height:1.7; }
</style>

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center justify-content-between">
            <div class="col-auto">
                <div class="page-header-title">
                    <h4 class="m-b-10">{{ $product->name }}</h4>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('vault-marketplace.index') }}">{{ __('Marketplace') }}</a></li>
                    <li class="breadcrumb-item active">{{ $product->name }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="vault-show-wrap">
        <div class="vault-show-header">
            @if($product->preview_image)
                <img src="{{ asset($product->preview_image) }}" alt="{{ $product->name }}" class="vault-show-img">
            @else
                <div style="width:180px;height:180px;border-radius:10px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;flex-shrink:0"><span style="font-size:44px;color:#94a3b8">&#128196;</span></div>
            @endif
            <div>
                <h2 class="vault-show-name">{{ $product->name }}</h2>
                @if($product->price > 0)
                    <div class="vault-show-price">${{ number_format($product->price, 2) }}</div>
                @else
                    <span class="vault-show-free">{{ __('FREE') }}</span>
                @endif
                <div class="vault-show-meta">
                    @if($product->category)
                        <span class="vault-show-badge badge-cat">{{ $product->category }}</span>
                    @endif
                    @if($product->status)
                        <span class="vault-show-badge badge-status">{{ ucfirst($product->status) }}</span>
                    @endif
                </div>
                @if($product->short_description)
                    <p class="vault-show-sdesc">{{ $product->short_description }}</p>
                @endif
            </div>
        </div>

        <div class="vault-show-actions">
            <a href="{{ route('vault-library.checkout', $product->id) }}" class="vault-show-buy">{{ __('Buy Now') }} &rarr;</a>
            @if($product->demo_url)
                <a href="{{ $product->demo_url }}" target="_blank" class="vault-show-demo">{{ __('Live Demo') }} &rarr;</a>
            @endif
        </div>

        @if($product->description)
            <div class="vault-show-body">
                <h5>{{ __('Description') }}</h5>
                <p>{!! $product->description !!}</p>
            </div>
        @endif
    </div>
</div>
@endsection
BLADE;

$r2 = file_put_contents($pkg . '/show.blade.php', $code2);
echo ($r2 !== false ? "OK (" . strlen($code2) . " bytes)\n" : "FAILED!\n");

// ─────────────────────────────────────
// STEP 3: library.blade.php
// ─────────────────────────────────────
echo "=== Step 3: library.blade.php ===\n";

$code3 = <<<'BLADE'
@extends('layouts.main')

@section('content')
<style>
.vault-lib-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:18px; }
.vault-lib-empty { text-align:center; padding:50px 20px; color:#64748b; }
.vault-lib-empty a { display:inline-block; margin-top:14px; padding:10px 22px; background:#2563eb; color:#fff!important; border-radius:6px; text-decoration:none; font-weight:600; font-size:14px; }
.vault-lib-card { background:#fff; border-radius:8px; box-shadow:0 1px 6px rgba(0,0,0,.05); overflow:hidden; border:1px solid #e2e8f0; }
.vault-lib-card-img { width:100%; height:150px; object-fit:cover; }
.vault-lib-card-body { padding:14px; }
.vault-lib-card-title { font-size:15px; font-weight:600; margin:0 0 5px 0; color:#1e293b; }
.vault-lib-card-price { font-size:14px; font-weight:700; color:#16a34a; margin-bottom:6px; }
.vault-lib-st { display:inline-block; padding:2px 9px; border-radius:14px; font-size:11px; font-weight:600; }
.st-pending { background:#fef3c7; color:#d97706; }
.st-approved { background:#dcfce7; color:#16a34a; }
.st-rejected { background:#fef2f2; color:#dc2626; }
.vault-lib-date { font-size:12px; color:#94a3b8; margin-bottom:6px; }
.vault-lib-note { font-size:12px; color:#64748b; margin-top:6px; padding:7px; background:#f8fafc; border-radius:5px; }
.vault-lib-acts { margin-top:10px; display:flex; gap:6px; }
.vault-lib-btn { display:inline-block; padding:5px 12px; border-radius:5px; font-size:12px; font-weight:600; text-decoration:none; }
.btn-dl { background:#16a34a; color:#fff!important; }
.btn-dl:hover { background:#15803d; color:#fff!important; }
.btn-rc { background:#eff6ff; color:#2563eb!important; }
.btn-rc:hover { background:#dbeafe; }
</style>

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center justify-content-between">
            <div class="col-auto">
                <div class="page-header-title">
                    <h4 class="m-b-10">{{ __('My Library') }}</h4>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('vault-marketplace.index') }}">{{ __('Marketplace') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('My Library') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    @if($purchases->isEmpty())
        <div class="vault-lib-empty">
            <p style="font-size:36px;margin-bottom:8px">&#128218;</p>
            <p>{{ __("You haven't purchased any products yet.") }}</p>
            <a href="{{ route('vault-marketplace.index') }}">{{ __('Browse Marketplace') }} &rarr;</a>
        </div>
    @else
        <div class="vault-lib-grid">
            @foreach($purchases as $purchase)
                <div class="vault-lib-card">
                    @if($purchase->product && $purchase->product->preview_image)
                        <img src="{{ asset($purchase->product->preview_image) }}" alt="{{ $purchase->product->name }}" class="vault-lib-card-img">
                    @else
                        <div style="width:100%;height:150px;background:#f1f5f9;display:flex;align-items:center;justify-content:center"><span style="font-size:36px;color:#94a3b8">&#128196;</span></div>
                    @endif
                    <div class="vault-lib-card-body">
                        <h5 class="vault-lib-card-title">{{ $purchase->product ? $purchase->product->name : __('Product Removed') }}</h5>
                        @if($purchase->price_paid > 0)
                            <div class="vault-lib-card-price">${{ number_format($purchase->price_paid, 2) }}</div>
                        @else
                            <div class="vault-lib-card-price">{{ __('Free') }}</div>
                        @endif
                        @php
                            $st = $purchase->payment_status ?? 'pending';
                            $cls = 'st-pending';
                            if($st === 'approved') $cls = 'st-approved';
                            elseif($st === 'rejected') $cls = 'st-rejected';
                        @endphp
                        <span class="vault-lib-st {{ $cls }}">{{ ucfirst($st) }}</span>
                        <div class="vault-lib-date">{{ __('Purchased') }}: {{ $purchase->purchased_at ? \Carbon\Carbon::parse($purchase->purchased_at)->format('M d, Y') : 'N/A' }}</div>
                        @if($purchase->rejection_reason)
                            <div class="vault-lib-note" style="background:#fef2f2;color:#dc2626"><strong>{{ __('Reason') }}:</strong> {{ $purchase->rejection_reason }}</div>
                        @endif
                        @if($purchase->admin_notes)
                            <div class="vault-lib-note"><strong>{{ __('Note') }}:</strong> {{ $purchase->admin_notes }}</div>
                        @endif
                        <div class="vault-lib-acts">
                            @if($purchase->payment_status === 'approved' && $purchase->product && $purchase->product->file_path)
                                <a href="{{ asset($purchase->product->file_path) }}" download class="vault-lib-btn btn-dl" target="_blank">{{ __('Download') }}</a>
                            @endif
                            @if($purchase->receipt)
                                <a href="{{ asset($purchase->receipt) }}" target="_blank" class="vault-lib-btn btn-rc">{{ __('View Receipt') }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
BLADE;

$r3 = file_put_contents($pkg . '/library.blade.php', $code3);
echo ($r3 !== false ? "OK (" . strlen($code3) . " bytes)\n" : "FAILED!\n");

// ─────────────────────────────────────
// STEP 4: index.blade.php
// ─────────────────────────────────────
echo "=== Step 4: index.blade.php ===\n";

$code4 = <<<'BLADE'
@extends('layouts.main')

@section('content')
<style>
.vault-idx-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:18px; }
.vault-idx-empty { text-align:center; padding:50px 20px; color:#64748b; }
.vault-idx-card { background:#fff; border-radius:8px; box-shadow:0 1px 6px rgba(0,0,0,.05); overflow:hidden; border:1px solid #e2e8f0; transition:box-shadow .2s; text-decoration:none!important; color:inherit!important; display:block; }
.vault-idx-card:hover { box-shadow:0 3px 12px rgba(0,0,0,.1); }
.vault-idx-img-wrap { position:relative; overflow:hidden; height:170px; }
.vault-idx-img { width:100%; height:100%; object-fit:cover; }
.vault-idx-featured { position:absolute; top:8px; left:8px; padding:2px 8px; background:#f59e0b; color:#fff; border-radius:14px; font-size:10px; font-weight:700; }
.vault-idx-body { padding:14px; }
.vault-idx-cat { font-size:11px; font-weight:600; color:#2563eb; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px; }
.vault-idx-name { font-size:15px; font-weight:600; color:#1e293b; margin:0 0 5px 0; }
.vault-idx-desc { font-size:12px; color:#64748b; line-height:1.4; margin-bottom:10px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.vault-idx-footer { display:flex; justify-content:space-between; align-items:center; }
.vault-idx-price { font-size:17px; font-weight:700; color:#16a34a; }
.vault-idx-btn { padding:5px 14px; background:#2563eb; color:#fff!important; border-radius:5px; font-size:12px; font-weight:600; text-decoration:none; }
.vault-idx-btn:hover { background:#1d4ed8; color:#fff!important; }
</style>

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center justify-content-between">
            <div class="col-auto">
                <div class="page-header-title">
                    <h4 class="m-b-10">{{ __('Product Marketplace') }}</h4>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item active">{{ __('Marketplace') }}</li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="{{ route('vault-library.index') }}" class="btn btn-outline-primary btn-sm">&#128218; {{ __('My Library') }}</a>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    @if($products->isEmpty())
        <div class="vault-idx-empty">
            <p style="font-size:36px;margin-bottom:8px">&#128722;</p>
            <p>{{ __('No products available yet.') }}</p>
        </div>
    @else
        <div class="vault-idx-grid">
            @foreach($products as $product)
                <a href="{{ route('vault-marketplace.show', $product->id) }}" class="vault-idx-card">
                    <div class="vault-idx-img-wrap">
                        @if($product->preview_image)
                            <img src="{{ asset($product->preview_image) }}" alt="{{ $product->name }}" class="vault-idx-img">
                        @else
                            <div style="width:100%;height:100%;background:#f1f5f9;display:flex;align-items:center;justify-content:center"><span style="font-size:36px;color:#94a3b8">&#128196;</span></div>
                        @endif
                        @if($product->is_featured)
                            <span class="vault-idx-featured">&#11088; Featured</span>
                        @endif
                    </div>
                    <div class="vault-idx-body">
                        @if($product->category)
                            <div class="vault-idx-cat">{{ $product->category }}</div>
                        @endif
                        <h5 class="vault-idx-name">{{ $product->name }}</h5>
                        @if($product->short_description)
                            <p class="vault-idx-desc">{{ $product->short_description }}</p>
                        @endif
                        <div class="vault-idx-footer">
                            @if($product->price > 0)
                                <span class="vault-idx-price">${{ number_format($product->price, 2) }}</span>
                            @else
                                <span class="vault-idx-price" style="color:#2563eb">Free</span>
                            @endif
                            <span class="vault-idx-btn">{{ __('View') }} &rarr;</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
BLADE;

$r4 = file_put_contents($pkg . '/index.blade.php', $code4);
echo ($r4 !== false ? "OK (" . strlen($code4) . " bytes)\n" : "FAILED!\n");

// ─────────────────────────────────────
// STEP 5: Verify files
// ─────────────────────────────────────
echo "\n=== Step 5: Verify ===\n";
$checkFiles = ['checkout.blade.php', 'show.blade.php', 'library.blade.php', 'index.blade.php'];
$allOk = true;
foreach ($checkFiles as $f) {
    $path = $pkg . '/' . $f;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        $hasPH = str_contains($content, 'page-header');
        $hasCF = str_contains($content, 'container-fluid');
        $ok = $hasPH && $hasCF;
        echo "  $f: " . ($ok ? "OK (page-header + container-fluid)" : "MISSING page-header/container-fluid!") . "\n";
        if (!$ok) $allOk = false;
    } else {
        echo "  $f: FILE NOT FOUND!\n";
        $allOk = false;
    }
}

// ─────────────────────────────────────
// STEP 6: Clear caches (Windows-safe)
// ─────────────────────────────────────
echo "\n=== Step 6: Clear Caches ===\n";
$phpExe = 'C:\\xampp\\php\\php.exe';
$artisan = $base . '\\artisan';
$cmds = ['view:clear', 'cache:clear', 'config:clear', 'route:clear'];
foreach ($cmds as $c) {
    $cmd = "\"$phpExe\" \"$artisan\" $c 2>&1";
    $out = shell_exec($cmd);
    echo "  artisan $c => " . (trim($out) ?: "OK") . "\n";
}

echo "\n=== ";
echo $allOk ? "ALL FIXED SUCCESSFULLY!" : "SOME FILES HAD ISSUES!";
echo " ===\n";
echo "</pre>";
?>
