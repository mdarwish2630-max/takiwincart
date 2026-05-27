@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Edit Product: :name', ['name' => $product->name]) }}</h5>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)
                        <p class="mb-0">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('product-vault.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Product Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Category *</label>
                        <input type="text" name="category" class="form-control" value="{{ old('category', $product->category) }}" required list="categories">
                        <datalist id="categories">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Price *</label>
                        <input type="number" name="price" class="form-control" value="{{ old('price', $product->price) }}" step="0.01" min="0" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Payment Link (External)</label>
                        <input type="url" name="payment_link" class="form-control" value="{{ old('payment_link', $product->payment_link) }}" placeholder="https://pay.example.com/...">
                        <small class="text-muted">Users will be redirected to this link to pay. Leave empty for manual payment.</small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Status *</label>
                        <select name="status" class="form-select">
                            <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Product Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="" style="width:80px;height:80px;object-fit:cover;margin-top:8px;border-radius:4px;">
                        @endif
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Product File (Downloadable)</label>
                        <input type="file" name="file_path" class="form-control">
                        @if($product->file_path)
                            <small class="text-muted d-block mt-1">Current file: {{ basename($product->file_path) }}</small>
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="5">{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Product
                    </button>
                    <a href="{{ route('product-vault.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
