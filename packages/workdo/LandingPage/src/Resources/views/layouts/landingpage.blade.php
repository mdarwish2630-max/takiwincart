@php
    $settings = \Workdo\LandingPage\Entities\LandingPageSetting::settings();
    $logo = get_file('storage/uploads/landing_page_image');
    $superadmin = \App\Models\User::where('type', 'super admin')->first();
    $setting = getSuperAdminAllSetting();
    $SITE_RTL = $setting['SITE_RTL'] ?? 'on';
    $APP_NAME = env('APP_NAME', 'TakiwinCart');
    $matjarAssets = url('packages/workdo/LandingPage/src/Resources/assets');

    // Enforce locale from dashboard settings
    $defLang = $setting['defult_language'] ?? 'ar';
    if (!session()->has('LANGUAGE') && !\Cookie::get('LANGUAGE')) {
        \App::setLocale($defLang);
    }
    $currentLang = app()->getLocale();
    $isRTL = ($SITE_RTL == 'on') ? true : false;

    $menusettings = \Workdo\LandingPage\Entities\OwnerMenuSetting::where('created_by', $superadmin->id)->first();
    $topNavItems = [];
    if (isset($menusettings) && $menusettings->menus_id) {
        $topNavItems = \Workdo\LandingPage\Entities\OwnerMenuSetting::get_ownernav_menu($menusettings->menus_id);
    }
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLang }}" dir="{{ $isRTL ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>{{ isset($settings['metatitle']) ? $settings['metatitle'] : $APP_NAME }}</title>
    <meta name="base-url" content="{{ URL::to('/') }}">
    <meta name="keywords" content="{{ isset($settings['metakeyword']) ? $settings['metakeyword'] : $APP_NAME . ' - E-Commerce Platform' }}">
    <meta name="description" content="{{ isset($settings['metadesc']) ? $settings['metadesc'] : $APP_NAME . ' - E-Commerce Platform' }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta property="og:title" content="{{ isset($settings['metatitle']) ? $settings['metatitle'] : $APP_NAME }}">
    <meta property="og:description" content="{{ isset($settings['metadesc']) ? $settings['metadesc'] : $APP_NAME . ' - E-Commerce Platform' }}">
    <meta property="og:image" content="{{ get_file(isset($settings['metaimage']) ? $settings['metaimage'] : 'storage/uploads/landing_page_image/') }}{{ '?'.time() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ get_file($setting['favicon'] ?? '') }}?timestamp={{ time() }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ $matjarAssets }}/css/matjar-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
</head>
<body>

    {{-- ==================== TOP BAR ==================== --}}
    @if (isset($settings['topbar_status']) && $settings['topbar_status'] == 'on')
        <div style="background:var(--primary);color:#fff;text-align:center;padding:8px 0;font-size:14px;position:relative;z-index:1001;">
            {!! $settings['topbar_notification_msg'] !!}
        </div>
    @endif

    {{-- ==================== NAVBAR ==================== --}}
    @if (isset($settings['menubar_status']) && $settings['menubar_status'] == 'on')
    <nav class="navbar" style="top: {{ (isset($settings['topbar_status']) && $settings['topbar_status'] == 'on') ? '40px' : '0' }};">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="{{ \URL::to('/') }}">
                    @if (!empty($settings['site_logo']))
                        <img src="{{ file_exists($settings['site_logo']) ? get_file($settings['site_logo']) . '?timestamp=' . time() : $logo . '/' . $settings['site_logo'] . '?timestamp=' . time() }}" alt="{{ $APP_NAME }}" class="logo-image">
                    @else
                        <img src="{{ $matjarAssets }}/images/logo.png" alt="{{ $APP_NAME }}" class="logo-image">
                    @endif
                </a>
            </div>

            <div class="nav-menu" id="navMenu">
                <ul class="nav-links">
                    @if (!empty($topNavItems))
                        @foreach ($topNavItems as $navGroup)
                            @if (!empty($navGroup['items']) && count($navGroup['items']) > 1)
                            <li class="has-item" style="position:relative;">
                                <a href="#" class="nav-link-toggle">
                                    {{ $navGroup['name'] }} <i class="fas fa-chevron-down" style="font-size:10px;margin-right:4px;"></i>
                                </a>
                                <ul class="menu-dropdown" style="position:absolute;top:100%;{{ $SITE_RTL == 'on' ? 'right:0;' : 'left:0;' }}background:#fff;border-radius:12px;box-shadow:0 10px 40px rgba(0,0,0,0.08);padding:16px;min-width:220px;opacity:0;visibility:hidden;transition:all 0.3s;z-index:10;">
                                    @foreach ($navGroup['items'] as $nav)
                                        @if ($nav->type == 'page')
                                            <li style="margin-bottom:8px;">
                                                <a href="{{ url('landing-pages/' . $nav->slug) }}" target="{{ $nav->target }}" style="text-decoration:none;color:var(--text-gray);font-weight:500;padding:8px 0;display:block;font-size:14px;">{{ $nav->title }}</a>
                                                @if (!empty($nav->children) && isset($nav->children))
                                                    <ul style="margin-{{ $SITE_RTL == 'on' ? 'right' : 'left' }}:12px;">
                                                        @foreach ($nav->children[0] as $child)
                                                            @if (!empty($child))
                                                                <li style="margin-bottom:4px;">
                                                                    <a href="{{ $child->type == 'page' ? url('landing-pages/' . $child->slug) : $child->slug }}" target="{{ $child->target }}" style="text-decoration:none;color:var(--text-light);font-size:13px;padding:4px 0;display:block;">{{ $child->title }}</a>
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </li>
                                        @else
                                            <li style="margin-bottom:8px;">
                                                <a href="{{ $nav->slug }}" target="{{ $nav->target }}" style="text-decoration:none;color:var(--text-gray);font-weight:500;padding:8px 0;display:block;font-size:14px;">{{ $nav->title }}</a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </li>
                            @elseif (!empty($navGroup['items']) && count($navGroup['items']) == 1)
                                @php $singleNav = $navGroup['items'][0]; @endphp
                                <li>
                                    <a href="{{ $singleNav->type == 'page' ? url('landing-pages/' . $singleNav->slug) : $singleNav->slug }}" target="{{ $singleNav->target }}">{{ $navGroup['name'] }}</a>
                                </li>
                            @else
                                <li><a href="#">{{ $navGroup['name'] }}</a></li>
                            @endif
                        @endforeach
                    @endif
                </ul>
                <div class="nav-buttons">
                    <a class="btn-login" href="{{ route('login') }}">{{ __('Login') }}</a>
                    @if ($setting['SIGNUP'] == 'on')
                        <a class="btn-primary" href="{{ route('register') }}">{{ __('Start Free Now') }}</a>
                    @endif
                </div>
            </div>
            {{-- Language Switcher AR / EN --}}
            <div class="language-switch">
                <a href="{{ url('change-languages/ar') }}" class="lang-btn {{ $currentLang == 'ar' ? 'active' : '' }}">AR</a>
                <a href="{{ url('change-languages/en') }}" class="lang-btn {{ $currentLang == 'en' ? 'active' : '' }}">EN</a>
            </div>
            <div class="hamburger" id="hamburger">
                <span></span><span></span><span></span>
            </div>
        </div>
    </nav>
    @endif

    {{-- ==================== HERO SECTION ==================== --}}
    @if (isset($settings['home_status']) && $settings['home_status'] == 'on')
    <section class="hero" id="home">
        <div class="hero-grid">
            <div class="hero-content">
                @if (!empty($settings['home_offer_text']))
                    <span style="display:inline-block;background:rgba(125,59,237,0.1);color:var(--primary);padding:8px 20px;border-radius:9999px;font-weight:700;font-size:14px;margin-bottom:20px;">
                        {{ $settings['home_offer_text'] }}
                    </span>
                @endif
                <h1>{!! $settings['home_heading'] !!}</h1>
                <p class="hero-description">{!! $settings['home_description'] !!}</p>
                <div class="hero-buttons">
                    @if (!empty($settings['home_live_demo_link']))
                        <a href="{{ $settings['home_live_demo_link'] }}" class="btn-primary btn-large">{{ __('Live Demo') }} <i class="fas fa-play"></i></a>
                    @endif
                    @if (!empty($settings['home_buy_now_link']))
                        <a href="{{ $settings['home_buy_now_link'] }}" class="btn-outline btn-large">{{ __('Get Started') }}</a>
                    @endif
                </div>
                <div class="hero-features">
                    <span><i class="fas fa-check-circle"></i> {{ $settings['home_trusted_by'] ?? __('Easy to Use') }}</span>
                    <span><i class="fas fa-shield-alt"></i> {{ __('100% Secure') }}</span>
                    <span><i class="fas fa-headset"></i> {{ __('24/7 Support') }}</span>
                </div>
                <div class="hero-trial">
                    <i class="fas fa-calendar-alt"></i>
                    <span>{{ __('Free Trial Available') }}</span>
                </div>
            </div>
            <div class="hero-visual">
                <div class="visual-wrapper">
                    <div class="main-mockup">
                        @if (!empty($settings['home_banner']))
                            <img src="{{ file_exists($settings['home_banner']) ? get_file($settings['home_banner']) : $logo . '/' . $settings['home_banner'] }}" alt="{{ $APP_NAME }}">
                        @else
                            <img src="{{ $matjarAssets }}/images/quick-launch.png" alt="{{ $APP_NAME }}">
                        @endif
                    </div>
                    <div class="floating-card chart-card">
                        <div class="card-header">{{ __('Revenue') }} <i class="fas fa-chart-line"></i></div>
                        <div class="card-value">+45%</div>
                        <div class="progress-bar"><span style="width: 75%"></span></div>
                    </div>
                    <div class="floating-card order-card">
                        <i class="fas fa-shopping-cart"></i>
                        <div class="order-info">
                            <strong>{{ __('New Order') }} #1045</strong>
                            <span>{{ __('Pending') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- ==================== STATS BAR ==================== --}}
    <section class="stats-bar">
        <div class="stats-container">
            <div class="stat-item">
                <div class="stat-value" data-target="98">0</div>
                <div class="stat-label">{{ __('Customer Satisfaction') }}</div>
                <div class="stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-value" data-target="18000">0</div>
                <div class="stat-label">{{ __('Products Listed') }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" data-target="12000">0</div>
                <div class="stat-label">{{ __('Orders on Platform') }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" data-target="2500">0</div>
                <div class="stat-label">{{ __('Active Stores') }}</div>
            </div>
        </div>
    </section>

    {{-- ==================== ALL TOOLS SECTION ==================== --}}
    @if (isset($settings['feature_status']) && $settings['feature_status'] == 'on')
    <section class="all-tools">
        <div class="tools-grid">
            <div class="tools-visual">
                <div class="circle-decor"></div>
                @if (!empty($settings['highlight_feature_image']))
                    <div class="img-large">
                        <div class="orbit-fix"><img src="{{ file_exists($settings['highlight_feature_image']) ? get_file($settings['highlight_feature_image']) : $logo . '/' . $settings['highlight_feature_image'] }}" alt=""></div>
                    </div>
                @else
                    <div class="img-large">
                        <div class="orbit-fix"><img src="{{ $matjarAssets }}/images/MatjarCartsDashboard.png" alt=""></div>
                    </div>
                @endif
                <div class="orbit-layer">
                    @php
                        $feature_icons = json_decode($settings['feature_of_features'], true);
                        if (!is_array($feature_icons)) $feature_icons = [];
                    @endphp
                    @if (isset($feature_icons[1]))
                        <div class="img-medium">
                            <div class="orbit-fix">
                                @if (!empty($feature_icons[1]['feature_logo']))
                                    <img src="{{ file_exists($feature_icons[1]['feature_logo']) ? get_file($feature_icons[1]['feature_logo']) : $logo . '/' . $feature_icons[1]['feature_logo'] }}" alt="">
                                @else
                                    <img src="{{ $matjarAssets }}/images/E-commerceBranding.png" alt="">
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="img-medium">
                            <div class="orbit-fix"><img src="{{ $matjarAssets }}/images/E-commerceBranding.png" alt=""></div>
                        </div>
                    @endif
                    @if (isset($feature_icons[2]))
                        <div class="img-small">
                            <div class="orbit-fix">
                                @if (!empty($feature_icons[2]['feature_logo']))
                                    <img src="{{ file_exists($feature_icons[2]['feature_logo']) ? get_file($feature_icons[2]['feature_logo']) : $logo . '/' . $feature_icons[2]['feature_logo'] }}" alt="">
                                @else
                                    <img src="{{ $matjarAssets }}/images/GlobalCommerceVisualization.png" alt="">
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="img-small">
                            <div class="orbit-fix"><img src="{{ $matjarAssets }}/images/GlobalCommerceVisualization.png" alt=""></div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="tools-content">
                @if (!empty($settings['feature_title']))
                    <span style="display:block;text-transform:uppercase;color:var(--primary);font-weight:700;margin-bottom:8px;font-size:14px;">{{ $settings['feature_title'] }}</span>
                @endif
                <h2>{!! $settings['feature_heading'] !!}</h2>
                <p>{!! $settings['feature_description'] !!}</p>
                <div class="tools-list">
                    @if (is_array($feature_icons))
                        @foreach ($feature_icons as $fItem)
                            <div><i class="fas fa-check-circle"></i> <span>{!! $fItem['feature_heading'] ?? '' !!}</span></div>
                        @endforeach
                    @endif
                </div>
                @if (!empty($settings['feature_buy_now_link']))
                    <a href="{{ $settings['feature_buy_now_link'] }}" class="btn-primary">{{ __('Learn More') }}</a>
                @endif
            </div>
        </div>
    </section>
    @endif

    {{-- ==================== ALTERNATING FEATURE SECTIONS ==================== --}}
    @if (isset($settings['feature_status']) && $settings['feature_status'] == 'on')
        @php
            $other_features = json_decode($settings['other_features'], true);
        @endphp
        @if (is_array($other_features))
            @foreach ($other_features as $key => $value)
            <section class="feature-section">
                <div class="container">
                    <div class="feature-content {{ $key % 2 == 0 ? 'reverse' : '' }}">
                        @if ($key % 2 == 0)
                            <div class="feature-text">
                                <h2>{!! $value['other_features_heading'] !!}</h2>
                                <p>{!! $value['other_featured_description'] !!}</p>
                                <div class="checklist">
                                    <div><i class="fas fa-check"></i> {{ __('Easy to Use') }}</div>
                                    <div><i class="fas fa-check"></i> {{ __('Professional Templates') }}</div>
                                    <div><i class="fas fa-check"></i> {{ __('Local Support') }}</div>
                                    <div><i class="fas fa-check"></i> {{ __('Easy Customization') }}</div>
                                </div>
                                @if (!empty($value['other_feature_buy_now_link']))
                                    <a href="{{ $value['other_feature_buy_now_link'] }}" class="btn-primary">{{ __('Get Started Free') }}</a>
                                @endif
                            </div>
                        @endif
                        <div class="feature-image">
                            @if (!empty($value['other_features_image']))
                                <img src="{{ file_exists($value['other_features_image']) ? get_file($value['other_features_image']) : $logo . '/' . $value['other_features_image'] }}" alt="">
                            @else
                                <img src="{{ $matjarAssets }}/images/quick-launch.png" alt="">
                            @endif
                        </div>
                        @if ($key % 2 != 0)
                            <div class="feature-text">
                                <h2>{!! $value['other_features_heading'] !!}</h2>
                                <p>{!! $value['other_featured_description'] !!}</p>
                                <div class="checklist">
                                    <div><i class="fas fa-check"></i> {{ __('Easy to Use') }}</div>
                                    <div><i class="fas fa-check"></i> {{ __('Professional Templates') }}</div>
                                    <div><i class="fas fa-check"></i> {{ __('Local Support') }}</div>
                                    <div><i class="fas fa-check"></i> {{ __('Easy Customization') }}</div>
                                </div>
                                @if (!empty($value['other_feature_buy_now_link']))
                                    <a href="{{ $value['other_feature_buy_now_link'] }}" class="btn-primary">{{ __('Get Started Free') }}</a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </section>
            @endforeach
        @endif
    @endif

    @stack('campaignsPage')

    {{-- ==================== PRICING SECTION ==================== --}}
    @if (isset($settings['plan_status']) && $settings['plan_status'])
    <section class="pricing-section" id="plan">
        <div class="container">
            <div class="pricing-header">
                @if (!empty($settings['plan_title']))
                    <span style="display:block;text-transform:uppercase;color:var(--primary);font-weight:700;margin-bottom:8px;font-size:14px;">{{ $settings['plan_title'] }}</span>
                @endif
                <h2>{!! $settings['plan_heading'] !!}</h2>
                <p>{!! $settings['plan_description'] !!}</p>
            </div>
            <div class="pricing-grid">
                @php
                    $collection = \App\Models\Plan::where('is_disable', 1)->orderBy('price', 'asc')->get();
                @endphp
                @foreach ($collection as $key => $plan)
                    <div class="pricing-card {{ $key == 1 ? 'featured' : '' }}">
                        @if ($key == 1)
                            <div class="featured-badge">{{ __('Most Popular') }}</div>
                        @endif
                        <h3>{{ $plan->name }}</h3>
                        <div class="price">
                            <span class="currency">{{ $plan->currency ?? '' }}</span>
                            <span class="amount">{{ trim(default_currency_format_with_sym($plan->price)) }}</span>
                            <span class="period">/ {{ $plan->duration }}</span>
                        </div>
                        @if ($plan->trial == '1')
                            <p style="text-align:center;color:var(--success);font-size:14px;margin-bottom:8px;">{{ __('Free Trial') }}: {{ $plan->trial_days }} {{ __('days') }}</p>
                        @endif
                        @if ($plan->description)
                            <p style="text-align:center;color:var(--text-light);font-size:14px;margin-bottom:16px;">{!! $plan->description !!}</p>
                        @endif
                        <ul class="pricing-features">
                            <li><i class="fas fa-check"></i> {{ $plan->max_stores != -1 ? $plan->max_stores : __('Unlimited') }} {{ __('Store(s)') }}</li>
                            <li><i class="fas fa-check"></i> {{ $plan->max_products != -1 ? $plan->max_products : __('Unlimited') }} {{ __('Product(s)') }}</li>
                            @if ($plan->enable_domain == 'on')
                                <li><i class="fas fa-check"></i> {{ __('Custom Domain') }}</li>
                            @else
                                <li class="disabled"><i class="fas fa-times"></i> {{ __('Custom Domain') }}</li>
                            @endif
                            @if ($plan->enable_subdomain == 'on')
                                <li><i class="fas fa-check"></i> {{ __('Sub Domain') }}</li>
                            @else
                                <li class="disabled"><i class="fas fa-times"></i> {{ __('Sub Domain') }}</li>
                            @endif
                            @if ($plan->pwa_store == 'on')
                                <li><i class="fas fa-check"></i> {{ __('PWA Store') }}</li>
                            @else
                                <li class="disabled"><i class="fas fa-times"></i> {{ __('PWA Store') }}</li>
                            @endif
                            @if ($plan->enable_chatgpt == 'on')
                                <li><i class="fas fa-check"></i> {{ __('AI Assistant') }}</li>
                            @else
                                <li class="disabled"><i class="fas fa-times"></i> {{ __('AI Assistant') }}</li>
                            @endif
                        </ul>
                        @if ($setting['SIGNUP'] == 'on')
                            @if ($key == 1)
                                <button class="btn-primary" onclick="window.location='{{ route('register', ['plan_id' => \Illuminate\Support\Facades\Crypt::encrypt($plan->id)]) }}'">{{ __('Subscribe Now') }}</button>
                            @else
                                <button class="btn-outline" onclick="window.location='{{ route('register', ['plan_id' => \Illuminate\Support\Facades\Crypt::encrypt($plan->id)]) }}'">{{ __('Subscribe Now') }}</button>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ==================== SCREENSHOTS / TEMPLATES SLIDER ==================== --}}
    @if (isset($settings['screenshots_status']) && $settings['screenshots_status'] == 'on')
    <section class="templates-slider-section">
        <div class="container">
            <div class="slider-header">
                <div class="slider-title">
                    <h2>{!! $settings['screenshots_heading'] !!}</h2>
                    <p>{!! $settings['screenshots_description'] !!}</p>
                </div>
                <div class="slider-nav">
                    <button class="slider-prev"><i class="fas fa-chevron-{{ $SITE_RTL == 'on' ? 'right' : 'left' }}"></i></button>
                    <button class="slider-next"><i class="fas fa-chevron-{{ $SITE_RTL == 'on' ? 'left' : 'right' }}"></i></button>
                </div>
            </div>
        </div>
        <div class="slider-container">
            <div class="swiper template-swiper">
                <div class="swiper-wrapper">
                    @php
                        $screenshots = json_decode($settings['screenshots'], true);
                    @endphp
                    @if (is_array($screenshots))
                        @foreach ($screenshots as $key => $ss)
                            <div class="swiper-slide">
                                <div class="template-card">
                                    <div class="template-image">
                                        <img src="{{ file_exists($ss['screenshots']) ? get_file($ss['screenshots']) : $logo . '/' . $ss['screenshots'] }}" alt="">
                                    </div>
                                    <div class="template-info">
                                        <h4>{!! $ss['screenshots_heading'] ?? '' !!}</h4>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- ==================== DISCOVER / STEPS SECTION ==================== --}}
    @if (isset($settings['discover_status']) && $settings['discover_status'] == 'on')
    <section class="steps-section">
        <div class="container">
            <h2>{!! $settings['discover_heading'] !!}</h2>
            <p class="steps-subtitle">{!! $settings['discover_description'] !!}</p>
            <div class="steps-container">
                @php
                    $discover_items = json_decode($settings['discover_of_features'], true);
                @endphp
                @if (is_array($discover_items))
                    @foreach ($discover_items as $dKey => $dItem)
                        <div class="step-card">
                            <div class="step-number">{{ $dKey + 1 }}</div>
                            <h3>{!! $dItem['discover_heading'] ?? '' !!}</h3>
                            <p>{!! $dItem['discover_description'] ?? '' !!}</p>
                            @if (!empty($dItem['discover_logo']))
                                <div class="step-image">
                                    <img src="{{ file_exists($dItem['discover_logo']) ? get_file($dItem['discover_logo']) : $logo . '/' . $dItem['discover_logo'] }}" alt="">
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="steps-connector"></div>
        </div>
    </section>
    @endif

    {{-- ==================== CTA BANNER ==================== --}}
    <section class="cta-banner">
        <div class="container">
            <div class="cta-content">
                <div class="cta-text">
                    <h3>{!! $settings['home_heading'] ?? $APP_NAME !!}</h3>
                    <p>{{ __('Join thousands of successful merchants') }}</p>
                </div>
                <div class="cta-button">
                    @if (!empty($settings['home_live_demo_link']))
                        <button class="btn-white" onclick="window.location='{{ $settings['home_live_demo_link'] }}'">{{ __('Try Free Now') }} <i class="fas fa-arrow-{{ $SITE_RTL == 'on' ? 'left' : 'right' }}"></i></button>
                    @else
                        <button class="btn-white" onclick="window.location='{{ route('register') }}'">{{ __('Try Free Now') }} <i class="fas fa-arrow-{{ $SITE_RTL == 'on' ? 'left' : 'right' }}"></i></button>
                    @endif
                </div>
                <div class="cta-image">
                    <img src="{{ $matjarAssets }}/images/store-mockup.png" alt="Store">
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== TESTIMONIALS ==================== --}}
    @if (isset($settings['testimonials_status']) && $settings['testimonials_status'] == 'on')
    <section class="testimonial-section">
        <div class="container">
            @if (!empty($settings['testimonials_heading']))
                <h2 style="text-align:center;font-size:36px;font-weight:700;margin-bottom:48px;">{!! $settings['testimonials_heading'] !!}</h2>
            @endif
            <div class="swiper testimonial-swiper">
                <div class="swiper-wrapper">
                    @php
                        $testimonials = json_decode($settings['testimonials'], true);
                    @endphp
                    @if (is_array($testimonials))
                        @foreach ($testimonials as $tItem)
                            <div class="swiper-slide">
                                <div class="testimonial-card">
                                    <div class="testimonial-icon"><i class="fas fa-quote-right"></i></div>
                                    <div class="testimonial-content">
                                        <p class="testimonial-text">{!! $tItem['testimonials_description'] ?? '' !!}</p>
                                        <div class="testimonial-author">
                                            @if (!empty($tItem['testimonials_user_avtar']))
                                                <img src="{{ file_exists($tItem['testimonials_user_avtar']) ? get_file($tItem['testimonials_user_avtar']) : $logo . '/' . $tItem['testimonials_user_avtar'] }}" alt="" style="width:48px;height:48px;border-radius:50%;object-fit:cover;">
                                            @endif
                                            <span class="author-name">{{ $tItem['testimonials_user'] ?? '' }}</span>
                                            <span class="author-title">{{ $tItem['testimonials_designation'] ?? '' }}</span>
                                        </div>
                                        @if (!empty($tItem['testimonials_star']))
                                            <div class="testimonial-rating">
                                                @for ($s = 0; $s < intval($tItem['testimonials_star']); $s++)
                                                    <i class="fas fa-star"></i>
                                                @endfor
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- ==================== FAQ SECTION ==================== --}}
    @if (isset($settings['faq_status']) && $settings['faq_status'] == 'on')
    <section class="faq-section">
        <div class="container">
            @if (!empty($settings['faq_heading']))
                <h2>{!! $settings['faq_heading'] !!}</h2>
            @endif
            <div class="faq-container">
                @php
                    $faqs = json_decode($settings['faqs'], true);
                @endphp
                @if (is_array($faqs))
                    @foreach ($faqs as $fKey => $fItem)
                        <div class="faq-item {{ $fKey == 0 ? 'active' : '' }}">
                            <div class="faq-question">
                                <h4>{!! $fItem['faq_questions'] !!}</h4>
                                <i class="fas fa-plus faq-icon"></i>
                            </div>
                            <div class="faq-answer">
                                <p>{!! $fItem['faq_answer'] !!}</p>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </section>
    @endif

    {{-- ==================== FOOTER ==================== --}}
    @if (isset($settings['footer_status']) && $settings['footer_status'] == 'on')
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <div class="footer-logo">
                        @if (!empty($settings['footer_logo']))
                            <img src="{{ file_exists($settings['footer_logo']) ? get_file($settings['footer_logo']) : $logo . '/' . $settings['footer_logo'] }}" alt="{{ $APP_NAME }}" style="width:48px;height:48px;object-fit:contain;border-radius:8px;">
                        @else
                            <div class="logo-icon"><i class="fas fa-store"></i></div>
                        @endif
                        <span>{{ $APP_NAME }}</span>
                    </div>
                    <p>{!! $settings['footer_description'] ?? '' !!}</p>
                    @if (!empty($settings['all_rights_reserve_website_url']))
                        <div class="social-links">
                            <a href="{{ $settings['all_rights_reserve_website_url'] }}" target="_blank"><i class="fab fa-facebook-f"></i></a>
                            <a href="{{ $settings['all_rights_reserve_website_url'] }}" target="_blank"><i class="fab fa-twitter"></i></a>
                            <a href="{{ $settings['all_rights_reserve_website_url'] }}" target="_blank"><i class="fab fa-instagram"></i></a>
                        </div>
                    @endif
                </div>
                @php
                    $footer_sections = json_decode($settings['footer_sections_details'], true);
                @endphp
                @if (is_array($footer_sections))
                    @foreach ($footer_sections as $fs)
                        <div class="footer-col">
                          <h4>
    {!! is_array($fs['footer_section_heading'] ?? null) ? '' : ($fs['footer_section_heading'] ?? '') !!}
</h4>

<p>
    {!! is_array($fs['footer_section_text'] ?? null) ? '' : ($fs['footer_section_text'] ?? '') !!}
</p>
                        </div>
                    @endforeach
                @endif
            </div>
            @if (isset($settings['joinus_status']) && $settings['joinus_status'] == 'on')
                <div style="margin-top:40px;padding-top:24px;border-top:1px solid var(--border-color);">
                    <form method="POST" action="{{ url('join_us/store') }}">
                        @csrf
                        <div style="display:flex;gap:12px;max-width:500px;margin:0 auto;">
                            <input type="email" name="email" required placeholder="{{ __('Your email address') }}" style="flex:1;padding:12px 20px;border:1px solid var(--border-color);border-radius:var(--radius-sm);font-family:'Tajawal',sans-serif;font-size:14px;outline:none;">
                            <button type="submit" class="btn-primary">{{ __('Subscribe') }}</button>
                        </div>
                    </form>
                </div>
            @endif
            <div style="text-align:center;margin-top:24px;padding-top:24px;border-top:1px solid var(--border-color);">
                <p style="color:var(--text-light);font-size:14px;">
                    @if (!empty($settings['all_rights_reserve_text']))
                        {{ $settings['all_rights_reserve_text'] }}
                    @else
                        &copy; {{ date('Y') }} {{ $APP_NAME }}. {{ __('All Rights Reserved') }}.
                    @endif
                </p>
            </div>
        </div>
    </footer>
    @endif

    {{-- ==================== SCRIPTS ==================== --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="{{ $matjarAssets }}/js/matjar-script.js"></script>
    <script>
        // Initialize template swiper
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swiper !== 'undefined') {
                new Swiper('.template-swiper', {
                    slidesPerView: 1,
                    spaceBetween: 24,
                    loop: true,
                    navigation: {
                        nextEl: '.slider-next',
                        prevEl: '.slider-prev',
                    },
                    breakpoints: {
                        640: { slidesPerView: 2 },
                        1024: { slidesPerView: 3 },
                    }
                });

                new Swiper('.testimonial-swiper', {
                    slidesPerView: 1,
                    spaceBetween: 24,
                    loop: true,
                    autoplay: { delay: 5000 },
                    pagination: { el: '.swiper-pagination', clickable: true },
                });
            }

            // Dropdown menu hover
            var hasItems = document.querySelectorAll('.has-item');
            hasItems.forEach(function(item) {
                var dropdown = item.querySelector('.menu-dropdown');
                if (dropdown) {
                    item.addEventListener('mouseenter', function() { dropdown.style.opacity='1'; dropdown.style.visibility='visible'; });
                    item.addEventListener('mouseleave', function() { dropdown.style.opacity='0'; dropdown.style.visibility='hidden'; });
                }
            });

            // Mobile menu toggle
            var hamburger = document.getElementById('hamburger');
            var navMenu = document.getElementById('navMenu');
            if (hamburger && navMenu) {
                hamburger.addEventListener('click', function() {
                    navMenu.classList.toggle('active');
                    hamburger.classList.toggle('active');
                });
            }
        });
    </script>
</body>
</html>
