@extends('layouts.app')
@section('page-title')
    {{ __('Extensions - إضافات') }}
@endsection
@section('page-breadcrumb')
    {{ __('Extensions - إضافات') }}
@endsection
@push('css')
    <style>
        .product-img {
            padding-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .system-version h5 {
            position: absolute;
            bottom: -44px;
            right: 27px;
        }

        .center-text {
            display: flex;
            flex-direction: column;
        }

        .center-text .text-primary {
            font-size: 14px;
            margin-top: 5px;
        }

        .theme-main {
            display: flex;
            align-items: center;
        }

        .theme-main .theme-avtar {
            margin-right: 15px;
        }

        .product-img .checkbox-custom .card-option {
            border: 0;
            outline: 0;
        }

        .product-img .checkbox-custom .card-option .btn.show {
            color: #000;
            border: 0;
        }

        .product-img .checkbox-custom .card-option .btn:focus {
            border: 0;
        }

        @media only screen and (max-width: 575px) {
            .system-version h5 {
                position: unset;
                margin-bottom: 0px;
            }

            .system-version {
                text-align: center;
                margin-bottom: -22px;
            }
        }
    </style>
@endpush
@section('page-action')
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="col-md-12">
            <div class="row mb-3">
                <div class="col-md-8">
                    <h4 class="mb-3"> {{ __('Installed Add-on') }}</h4>
                </div>
                <div class="col-md-4">
                    <input type="text" id="moduleSearch" class="form-control"
                        placeholder="{{ __('Search Add-ons...') }}">
                </div>
            </div>
            <!-- Installed Add-ons -->
            <div class="event-cards row px-0">
                @php
                    $module_array = [];
                @endphp
                @foreach ($modules as $module)
                    @php
                        $module_name = $module->name;
                        $id = strtolower(preg_replace('/\s+/', '_', $module_name));
                        $module_array[] = $module->alias;
                    @endphp
                    @if (
                        (!isset($module->display) || $module->display == true || $module_name == 'GoogleCaptcha') &&
                            $module_name != 'LandingPage')
                    <div class="col-xl-3 col-md-4 col-sm-6 product-card h-auto module-card"
                        data-name="{{ strtolower($module->alias) }}">
                        <div
                            class="card {{ $module->isEnabled() ? 'enable_module' : 'disable_module' }} mb-0 h-100 justify-content-between">
                            <div class="product-img">
                                <div class="theme-main">
                                    <div class="theme-avtar">
                                        <img src="{{ $module->image ?? '' }}"
                                            alt="{{ $module->name }}" class="img-user"
                                            style="max-width: 100%">
                                    </div>
                                    <div class="center-text">
                                        <small class="text-muted">
                                            @if ($module->isEnabled())
                                                <span
                                                    class="badge bg-success">{{ __('Enable') }}</span>
                                            @else
                                                <span
                                                    class="badge bg-danger">{{ __('Disable') }}</span>
                                            @endif
                                        </small>
                                        <small class="text-primary">{{ __('V') }}
                                            {{ sprintf('%.1f', $module->version ?? '1.0') }}</small>
                                    </div>
                                </div>
                                <div class="checkbox-custom">
                                    <div class="btn-group card-option">
                                        <button type="button" class="btn p-0" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end" style="">
                                            @if ($module->isEnabled())
                                                <a href="#!" class="dropdown-item module_change"
                                                    data-id="{{ $id }}">
                                                    <span>{{ __('Disable') }}</span>
                                                </a>
                                            @else
                                                <a href="#!" class="dropdown-item module_change"
                                                    data-id="{{ $id }}">
                                                    <span>{{ __('Enable') }}</span>
                                                </a>
                                            @endif
                                            <form action="{{ route('module.enable') }}"
                                                method="POST" id="form_{{ $id }}">
                                                @csrf
                                                <input type="hidden" name="name"
                                                    value="{{ $module->name }}">
                                            </form>
                                            <form action="{{ route('module.remove', $module->name) }}" method="POST" id="form_{{ $id }}">
                                                @csrf
                                                <button type="button" class="dropdown-item show_confirm"
                                                    data-confirm="{{__('Are You Sure?')}}"
                                                    data-text="{{__('This action can not be undone. Do you want to continue?')}}"
                                                    data-confirm-yes="delete-form-{{$id}}" data-text-yes="{{ __('Yes') }}" data-text-no="{{ __('No') }}">
                                                    <span class="text-danger">{{ __("Remove") }}</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="product-content">
                                <h4 class="text-capitalize"> {{ $module->alias }}</h4>
                                <p class="text-muted text-sm mb-0">
                                    {{ $module->description ?? '' }}
                                </p>
                                <a href="{{ route('software.details', $module->alias) }}"
                                    class="btn  btn-outline-primary w-100 mt-2">{{ __('View Details') }}</a>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
            </div>
            <!-- Installed Add-ons End -->

            <!-- Themes Section -->
            <hr class="my-4">
            <div class="row mb-3">
                <div class="col-md-8">
                    <h4 class="mb-3"> {{ __('Installed Theme') }}</h4>
                </div>
                <div class="col-md-4 text-end">
                    <input type="text" id="searchThemes" class="form-control"
                        placeholder="{{ __('Search Themes...') }}">
                </div>
            </div>
            <div class="event-cards row px-0">
                @php
                    $theme_array = [];
                @endphp
                @foreach ($addon_themes as $key => $value)
                    @php
                        $theme_array[] = $value->theme_id;
                    @endphp
                    <div class="col-lg-4 col-md-4 col-sm-6 card-wrapper">
                        <div class="product-card ">
                            <div class="product-card-inner border-primary">
                                <div class="product-card-image img-wrapper">
                                    <a href="{{ asset('themes/' . $value->theme_id . '/theme_img/img_1.png') }}"
                                        class="pdp-img" target="_blank" tabindex="0">
                                        <img
                                            src="{{ asset('themes/' . $value->theme_id . '/theme_img/img_1.png') }}">
                                    </a>
                                </div>
                                <div class="product-content">
                                    <div class="product-content-top d-flex align-items-center justify-content-between gap-2">
                                        <h4 class="text-capitalize mb-0">{{ $value->theme_id }}</h4>
                                            @if ($value->status == '1')
                                                <span class="bg-success  enable-label">{{ __('Enable') }}</span>
                                            @else
                                                <span class="bg-danger  enable-label">{{ __('Disable') }}</span>
                                            @endif

                                              <div class="checkbox-custom">
                                        <div class="btn-group card-option">
                                            <button type="button" class="btn"
                                                data-bs-toggle="dropdown" aria-haspopup="true"
                                                aria-expanded="false">
                                                <i class="ti ti-dots-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end" style="">
                                                @if ($value->status == '1')
                                                    <a href="#!" class="dropdown-item module_change"
                                                        data-id="{{ $value->theme_id }}">
                                                        <span>{{ __('Disable') }}</span>
                                                    </a>
                                                @else
                                                    <a href="#!" class="dropdown-item module_change"
                                                        data-id="{{ $value->theme_id }}">
                                                        <span>{{ __('Enable') }}</span>
                                                    </a>
                                                @endif

                                                <form action="{{ route('theme.enable') }}" method="POST"
                                                    id="form_{{ $value->theme_id }}">
                                                    @csrf
                                                    <input type="hidden" name="name"
                                                        value="{{ $value->theme_id }}">
                                                </form>

                                                {!! Form::open(['method' => 'DELETE', 'route' => ['addon.destroy', $value->theme_id], 'class' => 'd-inline']) !!}
                                                <button type="button" class="dropdown-item show_confirm" data-confirm="{{ __('Are You Sure?') }}"
                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}" data-text-yes="{{ __('Yes') }}" data-text-no="{{ __('No') }}" >
                                                    <span class="text-danger">{{ __('Remove') }}</span>
                                                </button>
                                                {!! Form::close() !!}

                                            </div>
                                        </div>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <!-- Themes Section End -->

            <!-- Add More Extensions -->
            <hr class="my-4">
            <div class="col-md-12">
                <div class="row justify-content-center px-0">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body package-card-inner gap-3 flex-wrap d-flex align-items-center">
                                <div class="package-itm">
                                    <a href="#"
                                        target="new">
                                        <img src=""
                                            alt="">
                                    </a>
                                </div>
                                <div class="package-content flex-grow-1">
                                    <h4>{{ __('Add More Extensions') }}</h4>
                                    <div class="text-muted">
                                        {{ __('Upload new extensions to enhance your store functionality.') }}
                                    </div>
                                </div>
                                <div title="{{ __('Add More Extensions') }}" data-bs-toggle="tooltip" class="text-end d-flex all-button-box justify-content-md-end justify-content-center">
                                    <a href="{{ route('module.add') }}" class="btn btn-primary btn-badge d-flex align-items-center justify-content-center">
                                        <i class="ti ti-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        $(document).on('click', '.module_change', function() {
            var id = $(this).attr('data-id');
            $('#loader').show();
            $('#form_' + id).submit();
        });

        document.getElementById('moduleSearch').addEventListener('keyup', function() {
            let query = this.value.toLowerCase();
            let modules = document.querySelectorAll('.module-card');

            modules.forEach(function(module) {
                let moduleName = module.getAttribute('data-name');
                if (moduleName.includes(query)) {
                    module.style.display = 'block';
                } else {
                    module.style.display = 'none';
                }
            });
        });
    </script>
@endpush
