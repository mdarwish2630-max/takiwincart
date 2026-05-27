@extends("layouts.app")

@section("content")
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __("Product Details") }}</h3>
                </div>
                <div class="col-auto">
                    <a href="{{ route("product-vault.index") }}" class="btn btn-secondary">
                        <i class="ti ti-arrow-left"></i> {{ __("Back") }}
                    </a>
                    <a href="{{ route("product-vault.edit", $product->id) }}" class="btn btn-warning">
                        <i class="ti ti-edit"></i> {{ __("Edit") }}
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        @if($product->preview_image)
                            <div class="mb-4 text-center">
                                <img src="{{ asset("storage/" . $product->preview_image) }}" style="max-width:300px;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                            </div>
                        @endif

                        <h4>{{ $product->name }}</h4>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p><strong>{{ __("Category:") }}</strong> {{ $product->category ?? "-" }}</p>
                                <p><strong>{{ __("Price:") }}</strong> {{ number_format($product->price, 2) }}</p>
                                <p><strong>{{ __("Status:") }}</strong>
                                    <span class="badge bg-{{ $product->status === "active" ? "success" : "danger" }}">{{ $product->status }}</span>
                                </p>
                                <p><strong>{{ __("Featured:") }}</strong> {{ $product->is_featured ? __("Yes") : __("No") }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>{{ __("Slug:") }}</strong> {{ $product->slug }}</p>
                                @if($product->demo_url)
                                <p><strong>{{ __("Demo URL:") }}</strong> <a href="{{ $product->demo_url }}" target="_blank">{{ $product->demo_url }}</a></p>
                                @endif
                                @if($product->file_path)
                                <p><strong>{{ __("File:") }}</strong> {{ basename($product->file_path) }}</p>
                                @endif
                            </div>
                        </div>

                        @if($product->short_description)
                            <div class="mt-3">
                                <h6>{{ __("Short Description") }}</h6>
                                <p>{{ $product->short_description }}</p>
                            </div>
                        @endif

                        @if($product->description)
                            <div class="mt-3">
                                <h6>{{ __("Description") }}</h6>
                                <p>{{ $product->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection