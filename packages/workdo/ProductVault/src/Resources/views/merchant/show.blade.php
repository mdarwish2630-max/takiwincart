@extends("layouts.app")

@section("content")
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <a href="{{ route("vault-marketplace.index") }}" class="text-muted">
                        <i class="ti ti-arrow-left"></i> {{ __("Back to Marketplace") }}
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body text-center p-4">
                        @if($product->preview_image)
                            <img src="{{ asset("storage/" . $product->preview_image) }}" class="img-fluid rounded" style="max-height:350px;object-fit:contain;">
                        @else
                            <i class="ti ti-package" style="font-size:120px;color:#ccc;"></i>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-body">
                        <h3>{{ $product->name }}</h3>
                        @if($product->category)
                            <span class="badge bg-secondary mb-2">{{ $product->category }}</span>
                        @endif
                        @if($product->is_featured)
                            <span class="badge bg-warning text-dark mb-2"><i class="ti ti-star"></i> {{ __("Featured") }}</span>
                        @endif

                        <h3 class="text-primary mt-3">{{ number_format($product->price, 2) }}</h3>

                        @if($product->short_description)
                            <p class="mt-3">{{ $product->short_description }}</p>
                        @endif

                        @if($product->description)
                            <div class="mt-3">
                                <h6>{{ __("Description") }}</h6>
                                <p class="text-muted">{{ $product->description }}</p>
                            </div>
                        @endif

                        @if($product->demo_url)
                            <div class="mt-3">
                                <a href="{{ $product->demo_url }}" target="_blank" class="btn btn-outline-info btn-sm">
                                    <i class="ti ti-external-link"></i> {{ __("Live Preview") }}
                                </a>
                            </div>
                        @endif

                        <div class="mt-4">
                            @if($alreadyPurchased)
                                <a href="{{ route("vault-library.index") }}" class="btn btn-success btn-lg">
                                    <i class="ti ti-check"></i> {{ __("Already Purchased - Go to Library") }}
                                </a>
                            @else
                                <a href="{{ route("vault-marketplace.checkout", $product->id) }}" class="btn btn-primary btn-lg">
                                    <i class="ti ti-credit-card"></i> {{ __("Purchase Now") }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($related->count() > 0)
        <div class="mt-4">
            <h5>{{ __("Related Products") }}</h5>
            <div class="row mt-3">
                @foreach($related as $rp)
                <div class="col-md-3 col-6 mb-3">
                    <div class="card h-100">
                        @if($rp->preview_image)
                            <img src="{{ asset("storage/" . $rp->preview_image) }}" class="card-img-top" style="height:120px;object-fit:contain;background:#f8f9fa;">
                        @else
                            <div class="text-center p-3" style="background:#f8f9fa;height:120px;">
                                <i class="ti ti-package" style="font-size:32px;color:#ccc;"></i>
                            </div>
                        @endif
                        <div class="card-body">
                            <h6 class="text-truncate small">{{ $rp->name }}</h6>
                            <span class="text-primary small">{{ number_format($rp->price, 2) }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection