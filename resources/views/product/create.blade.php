@extends('layouts.app')

@section('page-title')
  {{ __('Product') }}
@endsection

@php
  $logo = asset(Storage::url('uploads/profile/'));
@endphp

@section('breadcrumb')
  <li class="breadcrumb-item" aria-current="page"><a href="{{ route('product.index') }}">{{ __('Product') }}</a></li>
  <li class="breadcrumb-item" aria-current="page">{{ __('Create') }}</li>
@endsection
@section('action-button')
  <div class=" text-end d-flex all-button-box justify-content-md-end justify-content-center">
    <a href="#" class="btn btn-badge btn-primary" id="submit-all" data-title="{{ __('Create Product') }}" data-toggle="tooltip"
      title="{{ __('Create Product') }}">
      <i class="ti ti-plus drp-icon"></i> <span class="ms-2 me-2">{{ __('Save') }}</span> </a>
  </div>
@endsection
@php
  $plan = \App\Models\Plan::find(\Auth::user()->plan_id);
  $stock_management = \App\Models\Utility::GetValueByName('stock_management');
  $low_stock_threshold = \App\Models\Utility::GetValueByName('low_stock_threshold');
@endphp

@section('content')
  {{-- Digital Product Mode Banner --}}
  <div class="alert alert-info d-flex align-items-center mb-3" role="alert">
    <i class="ti ti-cloud-download me-2" style="font-size:1.4rem;"></i>
    <div>
      <strong>{{ __('Digital Product Mode') }}</strong> &mdash;
      {{ __('Shipping, Weight & Stock fields are hidden. Select digital product type below.') }}
    </div>
  </div>

  {{ Form::open(['route' => 'product.store', 'method' => 'post', 'id' => 'choice_form', 'enctype' => 'multipart/form-data']) }}
  <div class="row product-page-info">
    <div class="col-md-12">
      <div class="row">
        <div class="col-lg-7 col-md-12 col-12">
          <div class="border rounded">
            <h5 class="mb-0 p-3 border-bottom">{{__('Main Informations')}}</h5>
            <div class="card-body">
              <div class="product-info-top p-3 border-bottom">
                <div class="row row-gap">
                  <div class="col-12">
                    {!! Form::label('', __('Name'), ['class' => 'form-label']) !!}<span class="text-danger">*</span>
                    {!! Form::text('name', null, ['class' => 'form-control name']) !!}
                  </div>
                  <div class="col-12 parmalink " style =  "display: none; ">
                    {!! Form::label('', __('parmalink'), ['class' => 'form-label col-md-3']) !!}
                    <div class="d-flex flex-wrap gap-3">
                      <input class="input-group-text col-12"  readonly id="basic-addon2" value="{{ $link }}">
                      {!! Form::text('slug', null, ['class' => 'form-control slug col-12', 'data-bs-toggle'=>'tooltip', 'title'=> __('Sku or Slug')]) !!}
                    </div>
                  </div>
                  <div class="col-sm-6 col-12">
                    <label class="form-label">{{ __('Category') }}</label><span class="text-danger">*</span>
                    <select name="category_id" class="form-control" data-role="tagsinput" id="category_id">
                        <option value="">{{ __('Select Category') }}</option>
                        @foreach ($categoryTree as $category)
                            <option value="{{ $category['id'] }}">{!! $category['name'] !!}</option>
                        @endforeach
                    </select>
                  </div>
                  <div class="col-md-6 col-12 switch-width">
                    {{ Form::label('tax_id', __('Taxs'), ['class' => ' form-label']) }}
                    <select name="tax_id[]" data-role="tagsinput" id="tax_id" multiple>
                      @foreach ($Tax as $Key => $tax)
                        <option value={{ $Key }}>
                          {{ $tax }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-6 col-12">
                    {!! Form::label('', __('Tax Status'), ['class' => 'form-label']) !!}
                    {!! Form::select('tax_status', $Tax_status, null, [
                        'class' => 'form-control',
                        'data-role' => 'tagsinput',
                        'id' => 'tax_id',
                    ]) !!}
                  </div>
                  <div class=" col-sm-6 col-12" data_val='0'>
                    {!! Form::label('', __('Brand'), ['class' => 'form-label']) !!}
                    <span>
                      {!! Form::select('brand_id', $brands, null, [
                          'class' => 'form-control',
                          'data-role' => 'tagsinput',
                          'id' => 'brand-dropdown',
                      ]) !!}
                    </span>
                  </div>
                  <div class=" col-sm-6 col-12" data_val='0'>
                    {!! Form::label('', __('Label'), ['class' => 'form-label']) !!}
                    <span>
                      {!! Form::select('label_id', $labels, null, [
                          'class' => 'form-control',
                          'data-role' => 'tagsinput',
                          'id' => 'label-dropdown',
                      ]) !!}
                    </span>
                  </div>
                  <div class="col-sm-6 col-12">
                    {!! Form::label('', __('Tags'), ['class' => 'form-label']) !!}
                    <select name ="tag_id[]" class="select2 form-control" id="tag_id" multiple>
                      @foreach ($tag as $key => $t)
                        <option value="{{ $key }}">{{ $t }}</option>
                      @endforeach
                    </select>
                  </div>

                  {{-- HIDDEN: Shipping field --}}
                  <div class="col-sm-6 col-12" style="display:none !important;">
                    {!! Form::label('', __('Shipping'), ['class' => 'form-label']) !!}
                    {!! Form::select('shipping_id', $Shipping, null, [
                        'class' => 'form-control',
                        'data-role' => 'tagsinput',
                        'id' => 'shipping_id',
                    ]) !!}
                  </div>
                  {{-- HIDDEN: Weight field --}}
                  <div class="col-sm-6 col-12 product-weight" style="display:none !important;">
                    {!! Form::label('', __('Weight(Kg)'), ['class' => 'form-label ']) !!}
                    {!! Form::number('product_weight', null, ['class' => 'form-control', 'min' => '0', 'step' => '0.01']) !!}
                  </div>
                  <div class="col-md-6 col-12 product_price">
                    {!! Form::label('', __('Price'), ['class' => 'form-label']) !!}<span class="text-danger">*</span>
                    {!! Form::number('price', null, ['class' => 'form-control', 'min' => '0', 'step' => '0.01']) !!}
                  </div>
                  <div class="col-md-6 col-12">
                    {!! Form::label('', __('Sale Price'), ['class' => 'form-label']) !!}
                    {!! Form::number('sale_price', null, ['class' => 'form-control', 'min' => '0', 'step' => '0.01']) !!}
                  </div>
                </div>
              </div>

              {{-- ========== DIGITAL PRODUCT SETTINGS (NEW) ========== --}}
              <div class="digital-settings-section p-3 border-bottom" style="background:#f8f9ff;">
                <h5 class="mb-3" style="color:#5066f0;">
                  <i class="ti ti-file-digital me-1"></i> {{ __('Digital Product Settings') }}
                </h5>
                <div class="row row-gap">
                  <div class="col-md-6 col-12">
                    <label class="form-label">{{ __('Digital Product Type') }}<span class="text-danger">*</span></label>
                    <select name="digital_type" id="digital_type" class="form-control">
                      <option value="file">{{ __('Downloadable File (PDF, ZIP, etc.)') }}</option>
                      <option value="code">{{ __('PIN Code / Activation Code') }}</option>
                    </select>
                    <small class="text-muted">{{ __('Select "File" for downloadable products or "Code" for PIN/license keys') }}</small>
                  </div>

                  {{-- Show when type = file --}}
                  <div class="col-md-6 col-12" id="digital-file-field">
                    <label class="form-label">{{ __('Digital File') }}</label>
                    <input type="file" class="form-control" name="digital_file" id="digital_file" accept=".pdf,.zip,.rar,.7z,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.psd,.ai,.svg,.mp3,.mp4,.avi,.mov,.epub">
                    <small class="text-muted">{{ __('Upload the digital file customers will download') }}</small>
                  </div>

                  {{-- Show when type = code --}}
                  <div class="col-md-6 col-12 d-none" id="digital-code-field">
                    <label class="form-label">{{ __('PIN / Activation Code') }}</label>
                    <textarea name="digital_key" id="digital_key" class="form-control" rows="3" placeholder="{{ __('Enter PIN codes, one per line') }}"></textarea>
                    <small class="text-muted">{{ __('Enter one code per line. Each line = one code. Quantity auto-set from codes count.') }}</small>
                  </div>

                  <div class="col-md-6 col-12">
                    <label class="form-label">{{ __('Max Downloads') }}</label>
                    <input type="number" name="max_downloads" id="max_downloads" class="form-control" value="5" min="1" max="999">
                    <small class="text-muted">{{ __('How many times customer can download (default: 5)') }}</small>
                  </div>
                  <div class="col-md-6 col-12">
                    <label class="form-label">{{ __('Download Expires After (Days)') }}</label>
                    <input type="number" name="download_expiry_days" id="download_expiry_days" class="form-control" value="30" min="1" max="3650">
                    <small class="text-muted">{{ __('Download link validity in days (default: 30)') }}</small>
                  </div>
                </div>
              </div>
              {{-- ========== END DIGITAL PRODUCT SETTINGS ========== --}}

              {{-- HIDDEN: Stock management section --}}
              <div class="product-stock-div p-3 pb-0" style="display:none !important;">
                  <h4>{{ __('Product Stock') }}</h4>
                  <div class="row form-group row-gap">
                    @if ($stock_management == 'on')
                      <div class=" col-md-6 col-12">
                        <div class="product-stock-top d-flex ">
                        <div class="form-check form-switch">
                          <input type="hidden" name="track_stock" value="0">
                          <input type="checkbox" class="form-check-input enable_product_stock" name="track_stock"
                            id="enable_product_stock" value="1">
                          <label class="form-check-label" for="enable_product_stock"></label>
                        </div>
                        {!! Form::label('', __('Stock Management'), ['class' => 'form-label']) !!}
                      </div>
                    </div>
                    @else
                      <div class="col-md-6 col-12 product_stock">
                        {!! Form::label('', __('Stock Management'), ['class' => 'form-label']) !!}<br>
                        <label name="trending" value=""><small>Disabled in <a
                              href="{{ route('setting.index') . '#Brand_Setting ' }}"> store
                              setting</a></small></label>
                      </div>
                    @endif
                  </div>
                  <div class="row row-gap">
                      <div class="col-12 stock_stats">
                        {!! Form::label('', __('Stock Status:'), ['class' => 'form-label f-w-800']) !!}
                        <div class="col-mb-9 d-flex flex-wrap row-gap">
                          <div class="form-check form-check-inline">
                            <input class="form-check-input code" type="radio" id="in_stock" value="in_stock"
                              name="stock_status" checked="checked">
                            <label class="form-check-label" for="   ">
                              {{ __('In Stock') }}
                            </label>
                          </div>
                          <div class="form-check form-check-inline">
                            <input class="form-check-input code" type="radio" id="out_of_stock" value="out_of_stock"
                              name="stock_status">
                            <label class="form-check-label" for="out_of_stock">
                              {{ __('Out of stock') }}
                            </label>
                          </div>
                          <div class="form-check form-check-inline">
                            <input class="form-check-input code" type="radio" id="on_backorder" value="on_backorder"
                              name="stock_status">
                            <label class="form-check-label" for="on_backorder">
                              {{ __('On Backorder') }}
                            </label>
                          </div>
                        </div>
                      </div>
                    </div>
                   </div>
                   <div class="product-stock-top-inner px-3" style="display:none !important;">
                  @if ($stock_management == 'on')
                    <div class="row row-gap" id="options">
                      <div class="col-md-6 col-12 product_stock">
                        {!! Form::label('', __('Stock'), ['class' => 'form-label']) !!}
                        {!! Form::number('product_stock', null, ['class' => 'form-control productStock']) !!}
                      </div>
                      <div class="col-md-6 col-12">
                        {!! Form::label('', __('Low stock threshold'), ['class' => 'form-label']) !!}
                        {!! Form::number('low_stock_threshold', $low_stock_threshold, ['class' => 'form-control', 'min' => '0']) !!}
                      </div>
                      <div class="col-12 mb-3">
                        {!! Form::label('', __('Allow BackOrders:'), ['class' => 'form-label']) !!}
                        <div class="form-check m-1">
                          <input type="radio" id="not_allow" value="not_allow" name="stock_order_status"
                            class="form-check-input code" checked="checked">
                          <label class="form-check-label" for="not_allow">{{ __('Do Not Allow') }}</label>
                        </div>
                        <div class="form-check m-1">
                          <input type="radio" id="notify_customer" value="notify_customer" name="stock_order_status"
                            class="form-check-input code">
                          <label class="form-check-label"
                            for="notify_customer">{{ __('Allow, But notify customer') }}</label>
                        </div>
                        <div class="form-check m-1">
                          <input type="radio" id="allow" value="allow" name="stock_order_status"
                            class="form-check-input code">
                          <label class="form-check-label" for="allow">{{ __('Allow') }}</label>
                        </div>
                      </div>
                    </div>
                  @endif
                  @stack('addCartQuantityControlFilds')
                    </div>
                    <div class="product-stock-bottom p-3 pb-0 border-top">
                    <div class="col-12">
                    <h5 class="mb-3">{{ __('Product Options') }}</h5>
                    <div class="card">
                      <div class="card-body ms-2">
                        <div class="row row-gap align-items-center">
                          <div class="col-xl-4 col-lg-6 col-sm-6 col-12" style="display:none;">
                            <div class="stock-main-opts align-items-center d-flex">
                            <div class="form-check form-switch">
                              <input type="hidden" name="variant_product" value="0">
                              <input type="checkbox" class="form-check-input enable_product_variant" name="variant_product"
                                id="enable_product_variant" value="1">
                              <label class="form-check-label" for="enable_product_variant"></label>
                            </div>
                            {!! Form::label('enable_product_variant', __('Display Variants'), ['class' => 'form-label']) !!}
                            </div>
                          </div>
                          <div class="col-xl-3 col-lg-6 col-sm-6 col-12">
                          <div class="stock-main-opts align-items-center d-flex">
                            <div class="form-check form-switch">
                              <input type="hidden" name="trending" value="0">
                              <input type="checkbox" class="form-check-input" name="trending" id="trending_product"
                                value="1">
                              <label class="form-check-label" for="trending_product"></label>
                            </div>
                            {!! Form::label('trending_product', __('Trending'), ['class' => 'form-label']) !!}
                          </div>
                              </div>
                          <div class="col-xl-3 col-lg-6 col-sm-6 col-12">
                          <div class="stock-main-opts align-items-center d-flex">
                            <div class="form-check form-switch">
                              <input type="hidden" name="status" value="0">
                              <input type="checkbox" class="form-check-input" name="status" id="status" value="1" checked>
                              <label class="form-check-label" for="status"></label>
                            </div>
                            {!! Form::label('status', __('Display Product'), ['class' => 'form-label']) !!}
                              </div>
                          </div>
                        </div>

                      </div>
                    </div>
                    </div>

                  </div>
                  
                </div>
              </div>
          
            <div class="product-image-sec border rounded mb-4 mt-4">
            <h5 class="mb-3 p-3 border-bottom">{{ __('Product Image') }}</h5>
            <div class="card p-3 pt-0">
              <div class="card-body">
                <div class="row row-gap">
                  <div class="col-12">
                      {{ Form::label('sub_images', __('Upload Product Images (Optional)'), ['class' => 'form-label f-w-800']) }}
                        <div class="dropzone dropzone-multiple" data-toggle="dropzone1" data-dropzone-url="http://"
                            data-dropzone-multiple>
                            <div class="dz-message d-flex flex-column mb-2">
                                <img src="{{ asset('assets/images/notification/upload_icon.png') }}" alt="Upload Icon" style="width: 50px; height: 50px; margin:0 auto 10px;">
                                <span>Drop files here to upload</span>
                            </div>

                            <div class="fallback">
                                <div class="custom-file">
                                    <input type="file" name="file" id="dropzone-1" class="custom-file-input"
                                          onchange="document.getElementById('dropzone').src = window.URL.createObjectURL(this.files[0])"
                                          multiple>
                                    <img id="dropzone" src="" width="20%" class="mt-2" />
                                    <label class="custom-file-label" for="customFileUpload">{{ __('Choose file') }}</label>
                                </div>
                            </div>

                            <ul class="dz-preview dz-preview-multiple list-group list-group-lg list-group-flush">
                                <li class="list-group-item px-0">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <div class="avatar">
                                                <img class="rounded" src="" alt="Image placeholder" data-dz-thumbnail>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <h6 class="text-sm mb-1" data-dz-name>...</h6>
                                            <p class="small text-muted mb-0" data-dz-size></p>
                                        </div>
                                        <div class="col-auto">
                                            <a href="#" class="dropdown-item btn-badge" data-dz-remove>
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-12">
                      <label for="cover_image" class="form-label">{{ __('Upload Cover Image') }}</label><span class="text-danger">*</span>
                      <input type="file" name="cover_image" id="cover_image" class="form-control custom-input-file"
                        onchange="document.getElementById('upcoverImg').src = window.URL.createObjectURL(this.files[0]);"
                        multiple>
                      <img id="upcoverImg" src="" width="20%" class="mt-2" />
                    </div>

                  <div class="col-12" id="downloadable-product-div" style="display:none;">
                      <div class="choose-file">
                        <label for="downloadable_product" class="form-label">{{ __('Downloadable Product') }}</label>
                        <input type="file" class="form-control" name="downloadable_product" id="downloadable_product"
                          onchange="document.getElementById('downloadable_product').src = window.URL.createObjectURL(this.files[0]);"
                          multiple>
                        <div class="invalid-feedback">{{ __('invalid form file') }}</div>
                      </div>
                  </div>
                  <div class="col-md-6 col-12" id="preview_type">
                    {{ Form::label('preview_type', __('Preview Type'), ['class' => 'form-label']) }}
                    {{ Form::select('preview_type', $preview_type, null, ['class' => 'form-control font-style', 'id' => 'preview_type']) }}
                  </div>
                  <div class=" col-md-6 col-12" id="preview-video-div">
                      <div class="choose-file">
                        <label for="preview_video" class="form-label">{{ __('Preview Video') }}</label>
                        <input type="file" class="form-control" name="preview_video" id="preview_video"
                          onchange="document.getElementById('preview_video').src = window.URL.createObjectURL(this.files[0]);"
                          multiple>
                        <div class="invalid-feedback">{{ __('invalid form file') }}</div>
                      </div>
                  </div>

                  <div class=" col-md-6 col-12 ml-auto d-none" id="preview-iframe-div">
                    {{ Form::label('preview_iframe', __('Preview iFrame'), ['class' => 'form-label']) }}
                    {{ Form::textarea('preview_iframe', null, ['class' => 'form-control font-style', 'rows' => 2]) }}
                  </div>

                  <div class=" col-md-6 col-12" id="video_url_div">
                    {{ Form::label('video_url', __('Video URL'), ['class' => 'form-label']) }}
                    {{ Form::text('video_url', null, ['class' => 'form-control font-style']) }}
                  </div>
                </div>
              </div>

          
            </div>
          </div> 
          <div class="card border">
                <div class="card-body p-3 pb-0 ">
                  <div class="row row-gap">
                    <div class="col-md-12">
                      {!! Form::label('', __('Product Attribute'), ['class' => 'form-label']) !!}
                      {!! Form::select('attribute_id[]', $ProductAttribute, null, [
                          'class' => 'form-control attribute_option attribute_option_data',
                          'multiple' => 'multiple',
                          'data-role' => 'tagsinput',
                          'id' => 'attribute_id',
                      ]) !!}
                      <small>{{ __('Choose Existing Attribute') }}</small>
                    </div>
                    <div class="attribute_options" id="attribute_options">
                    </div>
                    <div class="attribute_combination" id="attribute_combination">
                    </div>
                  </div>
                </div>
              </div> 
             
        </div>
        
        <div class="col-lg-5 col-12">
          <div class="product-info-right border mb-4 rounded">
            <h5 class="mb-0 p-3 border-bottom">{{ __('About product') }}</h5>
            <div class="card p-3 pb-0">
              <div class="card-body">
                <div class="form-group">
                  {{ Form::label('description', __('Product Description'), ['class' => 'form-label']) }}
                  {{ Form::textarea('description', null, ['class' => 'form-control  summernote-simple-product', 'rows' => 1, 'placeholder' => __('Product Description'), 'id' => 'description']) }}
                </div>
                <div class="form-group">
                  {{ Form::label('specification', __('Product Specification'), ['class' => 'form-label']) }}
                  {{ Form::textarea('specification', null, ['class' => 'form-control  summernote-simple-product', 'rows' => 1, 'placeholder' => __('Product Specification'), 'id' => 'specification']) }}
                </div>
                <div class="form-group">
                  {{ Form::label('detail', __('Product Details'), ['class' => 'form-label']) }}
                  {{ Form::textarea('detail', null, ['class' => 'form-control  summernote-simple-product', 'rows' => 1, 'placeholder' => __('Product Details'), 'id' => 'detail']) }}
                </div>
              </div>
            </div>
          </div>
          <div class="product-price-info border rounded mb-4" style="display: none" id="custom_value">
            <div class="price-info-title p-3 border-bottom d-flex align-items-center  justify-content-between">
                <h5 class="mb-0">{{__('Custom Field')}}</h5>
                <a href="javascript:;" data-repeater-create class="custom_field_repeater btn-badge btn-sm btn btn-light-primary">
                        <i class="ti ti-plus"></i>
                      </a>
            </div>
            <div class="card-body p-3">
                  <div id="custom_field_repeater_basic">
                    <div data-repeater-list="custom_field_repeater_basic">
                        <div data-repeater-item>
                          <div class="row">
                            <div class="col-md-6">
                              {!! Form::label('', __('Field'), ['class' => 'form-label']) !!}
                              {!! Form::text('custom_field', null, ['class' => 'form-control', 'placeholder' => __('Enter Custom Field')]) !!}
                            </div>
                            <div class="col-md-5">
                              {!! Form::label('', __('Value'), ['class' => 'form-label']) !!}
                              {!! Form::text('custom_value', null, ['id' => 'answer', 'rows' => 2, 'class' => 'form-control','placeholder' => __('Enter Custom Value')]) !!}
                            </div>
                            <div class="col-md-1">
                              <label></label>
                              <a href="javascript:;" data-repeater-delete class="btn field-trash-btn btn-sm btn-light-danger mt-3 mt-md-8 btn-badge"  data-bs-toggle="tooltip" title="{{__('Delete')}}">
                                  <i class="ti ti-trash"></i>
                              </a>
                            </div>
                          </div>
                        </div>
                      </div>
                      @stack('CustomFieldsView')
                  </div>
            </div>
          </div>
          @stack('addsizeguidefields')
          @stack('addwholesalefields')
          @stack('ProductPageSetting')
        </div>
        
      </div>
    </div>
  </div>


  {!! Form::close() !!}
@endsection

@push('custom-script')
  <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
  <script src="{{ asset('js/repeater.js') }}"></script>

  <script>
    $(document).ready(function() {

      // ===== DIGITAL PRODUCT TYPE TOGGLE =====
      $('#digital_type').on('change', function() {
        var val = $(this).val();
        if (val === 'file') {
          $('#digital-file-field').removeClass('d-none');
          $('#digital-code-field').addClass('d-none');
        } else {
          $('#digital-file-field').addClass('d-none');
          $('#digital-code-field').removeClass('d-none');
        }
      });

      // ===== Auto-fill stock from PIN codes count =====
      $('#digital_key').on('input', function() {
        var lines = $(this).val().split('\n').filter(function(line) { return line.trim() !== ''; });
        // We store the count to send as product_stock
        $('#pin_count').val(lines.length);
      });

      // tag
      $('.select2').select2({
        tags: true,
        createTag: function(params) {
          var term = $.trim(params.term);
          if (term === '') {
            return null;
          }
          return {
            id: term,
            text: term,
            newTag: true
          };
        }
      });

      var link = $('.slug').val();
      var focusOutCalled = false;

      // permalink
      $('.name').on('focusout', function() {
        var nameval = $(this).val();
        if (!focusOutCalled) {
          $.ajax({
            url: "{{ route('get.slug') }}",
            type: 'POST',
            data: {
              'value': nameval
            },
            dataType: 'json',
            success: function(response) {
              $('#loader').fadeOut();
              $('.slug').val(response.result);
              $('.parmalink').show();
              focusOutCalled = true;
            },
            error: function(error) {
              $('#loader').fadeOut();
            }
          });
        }
      });

      // stock
      $('#options').hide();
      $('.stock_stats').show();
      $(document).on("change", "#enable_product_stock", function() {
        $('#options').prop('checked', false);
        if ($(this).prop('checked')) {
          $('.stock_stats').hide();
          $('#options').show();
        } else {
          $('.stock_stats').show();
          $('#options').hide();
        }
      });

      // preview video
      $("#preview_type").change(function() {
        $(this).find("option:selected").each(function() {
          var optionValue = $(this).attr("value");
          if (optionValue == 'Video Url') {
            $('#video_url_div').removeClass('d-none');
            $('#video_url_div').addClass('d-block');
            $('#preview-iframe-div').addClass('d-none');
            $('#preview-iframe-div').removeClass('d-block');
            $('#preview-video-div').addClass('d-none');
            $('#preview-video-div').removeClass('d-block');
          } else if (optionValue == 'iFrame') {
            $('#video_url_div').addClass('d-none');
            $('#video_url_div').removeClass('d-block');
            $('#preview-iframe-div').removeClass('d-none');
            $('#preview-iframe-div').addClass('d-block');
            $('#preview-video-div').addClass('d-none');
            $('#preview-video-div').removeClass('d-block');
          } else if (optionValue == 'Video File') {
            $('#video_url_div').addClass('d-none');
            $('#video_url_div').removeClass('d-block');
            $('#preview-iframe-div').addClass('d-none');
            $('#preview-iframe-div').removeClass('d-block');
            $('#preview-video-div').removeClass('d-none');
            $('#preview-video-div').addClass('d-block');
          }
        });
      }).change();
    });
    $(document).on("change", "#enable_custom_field", function() {
      $('#custom_value').hide();
      $('.custom_field').hide();
      if ($(this).prop('checked') == true) {
        $('#custom_value').show();
        $('.custom_field').show();
      }
    });
    
    $('#custom_field_repeater_basic').repeater({
                initEmpty: false,
                defaultValues: {
                    'text-input': 'foo'
                },
                show: function () {
                    $(this).slideDown();
                },
                hide: function (deleteElement) {
                    if ($('#custom_field_repeater_basic [data-repeater-item]').length > 1) {
                        $(this).slideUp(deleteElement);
                    } else {
                        show_toastr('Error', 'At least one field is required.', 'error');
                    }
                }
        });

        $('.custom_field_repeater').on('click', function (e) {
                e.preventDefault();
                var $repeaterList = $('#custom_field_repeater_basic [data-repeater-list]');
                var $items = $repeaterList.children();
                var index = $items.length;
                var $newItem = $items.last().clone();
                $newItem.find('input, select, textarea').each(function () {
                    var $input = $(this);
                    $input.val('');
                    var name = $input.attr('name');
                    if (name) {
                        var updatedName = name.replace(/\[\d+\]/, '[' + index + ']');
                        $input.attr('name', updatedName);
                    }
                });
                $repeaterList.append($newItem);
            });
        $('.deleteRecord').on('click', function() {
            var id = $(this).data("id");
            $.ajax({
                url: '{{ route('products.file.detele', '__product_id') }}'.replace('__product_id', id),
                type: 'DELETE',
                data: {
                    id: id,
                },
                success: function(data) {
                    $('#loader').fadeOut();
                    $('.delete_img_' + id).hide();
                    if (data.success) {
                        show_toastr('success', data.success, 'success');
                    }
                }
            });
        });
  </script>

  <script>
    // display variant hide show
    $(document).on("change", "#enable_product_variant", function() {
      $('.product-price-div').show();
      $('.product-stock-div').show();
      $('.product-weight').show();
      $('#use_for_variation').addClass("d-none");
      $('.product_price input').prop('readOnly', false);
      $('.product_discount_amount input').prop('readOnly', false);
      $('.product_discount_type input').prop('readOnly', false);
      $('.attribute_options_datas').hide();

      if ($(this).prop('checked') == true) {
        $('.product-price-div').hide();
        $('.product-stock-div').hide();
        $('.product-weight').hide();
        $("#use_for_variation").removeClass("d-none");
        $('.product_price input').prop('readOnly', true);
        $('.product_discount_amount input').prop('readOnly', true);
        $('.product_discount_type input').prop('readOnly', true);
        $('.attribute_options_datas').show();
      }
    });

    $(document).on('change', '#attribute_id', function() {
      $('#attribute_options').html("<h3 class='d-none'>Variation</h3>");
      var selectedOptions = $("#attribute_id option:selected");
      selectedOptions.each(function() {
        var optionValue = $(this).val();
        var optionText = $(this).text();
        add_more_choice_option(optionValue, optionText);
        var attribute_id = optionValue;
        $.ajax({
          url: '{{ route('products.attribute_option') }}',
          type: 'POST',
          data: {
            "attribute_id": attribute_id,
            "_token": "{{ csrf_token() }}",
          },
          success: function(data) {
            $('#loader').fadeOut();
            $('.attribute').empty();
            $.each(data, function(key, value) {
              $('.attribute_options_datas').empty();
              $(".attribute").append(
                '<option class="option-item" value="' + key + '">' +
                value + '</option>');
            });
            var multipleCancelButton = new Choices('#attribute' + attribute_id, {
              removeItemButton: true,
            });
          }
        });
      });
    });

    function update_attribute() {
      var variant_val = $('.attribute option:selected')
        .toArray().map(item => item.text).join();
      if (variant_val == '') {
        return;
      }
      $.ajax({
        type: "POST",
        url: '{{ route('products.attribute_combination') }}',
        data: $('#choice_form').serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content'),
        success: function(data) {
          $('#loader').fadeOut();
          $('#attribute_combination').html(data);
          if (data.length > 1) {
            $('#quantity').hide();
          } else {
            $('#quantity').show();
          }
        }
      });
    }
    $(document).on("change", ".attribute_option_data", function() {
      var inputValue = $('.attribute_option_data').val();
      if (inputValue != []) {
        var b = $('.attribute_option_data').closest('.parent-clase').find('.input-options');
        var dataid = b.attr('data-id');
        $('.enable_variation_' + dataid).on('change', function() {
          if ($('.enable_variation_' + dataid).prop('checked') == true) {
            $('.attribute_combination').show();
            update_attribute();
          } else {
            $('.attribute_options_datas').empty();
          }
        });
        if ($('.enable_variation_' + dataid).prop('checked') != true) {
          $('.attribute_options_datas').empty();
        }
      }
    });

    $(document).on("change", "#enable_product_variant", function() {
      if ($(this).prop('checked') == true) {
        $(document).on('change', '.attribute', function() {
          var b = $(this).closest('.parent-clase').find('.input-options');
          var dataid = b.attr('data-id');
          if ($('.enable_variation_' + dataid).prop('checked') == true) {
            update_attribute();
          }
        });
        var b = $(this).closest('.parent-clase').find('.input-options');
        var dataid = b.attr('data-id');
        if ($('.enable_variation_' + dataid).prop('checked') == true) {
          update_attribute();
        }
      }
    });

    $(document).on('change', '#attribute_id', function() {
      $('#attribute_options').html("<h3 class='d-none'>Variation</h3>");
      $.each($("#attribute_id option:selected"), function() {
        add_more_choice_option($(this).val(), $(this).text());
      });
    });
  </script>

  {{-- Dropzones --}}
  <script>
    var Dropzones = function() {
      var e = $('[data-toggle="dropzone1"]'),
        t = $(".dz-preview");

      var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
      e.length && (Dropzone.autoDiscover = !1, e.each(function() {
        var e, a, n, o, i;
        e = $(this), a = void 0 !== e.data("dropzone-multiple"), n = e.find(t), o = void 0, i = {
          url: "{{ route('product.store') }}",
          headers: {
            'x-csrf-token': CSRF_TOKEN,
          },
          thumbnailWidth: null,
          thumbnailHeight: null,
          previewsContainer: n.get(0),
          previewTemplate: n.html(),
          maxFiles: 10,
          parallelUploads: 10,
          autoProcessQueue: false,
          uploadMultiple: true,
          acceptedFiles: a ? null : "image/*",
          success: function(file, response) {
            if (response.flag == "success") {
              show_toastr('success', response.msg, 'success');
              window.location.href = "{{ route('product.create') }}";
            } else {
              show_toastr('Error', response.msg, 'error');
            }
          },
          error: function(file, response) {
            if (response.error) {
              show_toastr('Error', response.error, 'error');
            } else {
              show_toastr('Error', response, 'error');
            }
          },
          init: function() {
            var myDropzone = this;
            this.on("addedfile", function(e) {
              !a && o && this.removeFile(o), o = e
            })
          }
        }, n.html(""), e.dropzone(i)
      }))
    }()

    // Hidden field for PIN count
    $('<input>').attr({type:'hidden', id:'pin_count', name:'pin_count', value:'0'}).appendTo('#choice_form');

    $('#submit-all').on('click', function() {
      $('#submit-all').attr('disabled', true);

      // Validation: Check required fields
      var name = $('input[name="name"]').val();
      var category = $('select[name="category_id"]').val();
      var coverFile = document.getElementById('cover_image').files[0];
      var price = $('input[name="price"]').val();

      if (!name || !name.trim()) {
        show_toastr('Error', 'Product name is required', 'error');
        $('#submit-all').attr('disabled', false);
        return false;
      }
      if (!category) {
        show_toastr('Error', 'Please select a category', 'error');
        $('#submit-all').attr('disabled', false);
        return false;
      }
      if (!coverFile) {
        show_toastr('Error', 'Cover image is required', 'error');
        $('#submit-all').attr('disabled', false);
        return false;
      }
      if (!price || parseFloat(price) < 0) {
        show_toastr('Error', 'Price is required', 'error');
        $('#submit-all').attr('disabled', false);
        return false;
      }

      var digitalType = $('#digital_type').val();
      var digitalFile = document.getElementById('digital_file').files[0];
      var digitalKey = $('#digital_key').val().trim();

      if (digitalType === 'file' && !digitalFile) {
        show_toastr('Error', 'Please upload a digital file or change type to PIN Code', 'error');
        $('#submit-all').attr('disabled', false);
        return false;
      }
      if (digitalType === 'code' && !digitalKey) {
        show_toastr('Error', 'Please enter at least one PIN code or change type to File', 'error');
        $('#submit-all').attr('disabled', false);
        return false;
      }

      var fd = new FormData();

      var file = document.getElementById('cover_image').files[0];
      var preview_video = document.getElementById('preview_video').files[0];
      var downloadable_product = document.getElementById('downloadable_product').files[0];
      var inputs = $(".downloadable_product_variant");
      for (var i = 0; i < inputs.length; i++) {
        var files = $(inputs[i]).prop('files');
        var dataValue = $(inputs[i]).data('value');
        if (files && files.length > 0) {
          for (var j = 0; j < files.length; j++) {
            fd.append(dataValue, files[j]);
          }
        }
      }

      // Append Summernote content
      fd.append('description', $('#description').summernote('code'));
      fd.append('specification', $('#specification').summernote('code'));
      fd.append('detail', $('#detail').summernote('code'));

      if (file) {
        fd.append('cover_image', file);
      }
      if (preview_video) {
        fd.append('preview_video', preview_video);
      }
      if (downloadable_product) {
        fd.append('downloadable_product', downloadable_product);
      }

      // Append digital file if type is 'file'
      if (digitalType === 'file' && digitalFile) {
        fd.append('digital_file', digitalFile);
      }

      // Append dropzone images (optional)
      var dz = $('[data-toggle="dropzone1"]').get(0).dropzone;
      if (dz) {
        var files = dz.getAcceptedFiles();
        $.each(files, function(key, file) {
          fd.append('product_image[' + key + ']', file);
        });
      }

      var other_data = $('#choice_form').serializeArray();
      $.each(other_data, function(key, input) {
        fd.append(input.name, input.value);
      });

      // Set digital fields
      fd.append('digital_type', digitalType);
      fd.append('digital_key', digitalKey);

      // Set PIN count as stock if type is code
      if (digitalType === 'code') {
        var lines = digitalKey.split('\n').filter(function(line) { return line.trim() !== ''; });
        fd.append('pin_count', lines.length);
        fd.append('product_stock', lines.length);
        fd.append('stock_status', 'in_stock');
      }

      // Ensure stock fields have defaults
      if (digitalType === 'file') {
        fd.append('stock_status', 'in_stock');
        fd.append('product_stock', '999');
      }

      $.ajax({
        url: "{{ route('product.store') }}",
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: fd,
        contentType: false,
        processData: false,
        type: 'POST',
        success: function(data) {
          $('#loader').fadeOut();
          if (data.flag == "success") {
            $('#submit-all').attr('disabled', true);
            window.location.href = "{{ route('product.index') }}" + '?id=1';
          } else {
            show_toastr('Error', data.msg, 'error');
            $('#submit-all').attr('disabled', false);
          }
        },
        error: function(data) {
          $('#loader').fadeOut();
          $('#submit-all').attr('disabled', false);
          if (data.responseJSON && data.responseJSON.msg) {
            show_toastr('Error', data.responseJSON.msg, 'error');
          } else if (data.error) {
            show_toastr('Error', data.error, 'error');
          } else {
            show_toastr('Error', 'An error occurred. Please try again.', 'error');
          }
        },
      });

    });
  </script>
@endpush
