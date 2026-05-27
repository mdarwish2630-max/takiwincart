@extends('layouts.app')

@section('page-title', __('Import Product'))
@section('breadcrumb')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Import to My Store') }}</h5>
                <small class="text-muted">{{ __('Customize before importing') }}</small>
            </div>
            <div class="card-body">
                @if($vaultProduct->preview_image)
                    <div class="text-center mb-3">
                        <img src="{{ asset($vaultProduct->preview_image) }}" style="max-height:150px;border-radius:8px;object-fit:cover;">
                    </div>
                @endif

                <form method="POST" action="{{ route('vault-library.import', $purchase->id) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">{{ __('Product Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ $vaultProduct->name }}" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Price') }} <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" value="{{ $vaultProduct->price }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Sale Price') }} <span class="text-muted small">(optional)</span></label>
                            <input type="number" name="sale_price" class="form-control" step="0.01" min="0" value="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Category') }}</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- {{ __('Select Category') }} --</option>
                            @foreach($categories as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @if($categories->isEmpty())
                            <small class="text-warning">{{ __('No categories found. You can create them from Settings.') }}</small>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Short Description') }}</label>
                        <textarea name="description" class="form-control" rows="3">{{ $vaultProduct->short_description }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Full Description / Detail') }}</label>
                        <textarea name="detail" class="form-control" rows="5">{{ $vaultProduct->description }}</textarea>
                    </div>

                    <div class="alert alert-info small">
                        <strong>{{ __('Note') }}:</strong>
                        {{ __('The product image and downloadable file from the vault will be linked automatically.') }}
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('vault-library.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary">{{ __('Import to Store') }} &rarr;</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection