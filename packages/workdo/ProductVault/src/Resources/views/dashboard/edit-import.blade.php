@extends('layouts.app')

@section('page-title', __('Edit Imported Product'))
@section('breadcrumb')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Edit Imported Product') }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('vault-library.update-import', $purchase->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">{{ __('Product Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Price') }} <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" value="{{ $product->price }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Sale Price') }}</label>
                            <input type="number" name="sale_price" class="form-control" step="0.01" min="0" value="{{ $product->sale_price }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Category') }}</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- {{ __('No Category') }} --</option>
                            @foreach($categories as $id => $name)
                                <option value="{{ $id }}" {{ $product->category_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Description') }}</label>
                        <textarea name="description" class="form-control" rows="3">{{ $product->description }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Detail') }}</label>
                        <textarea name="detail" class="form-control" rows="5">{{ $product->detail }}</textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="status" class="form-check-input" id="statusToggle" {{ $product->status ? 'checked' : '' }}>
                            <label class="form-check-label" for="statusToggle">{{ __('Active') }}</label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('vault-library.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection