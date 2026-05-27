<?php
/**
 * ============================================
 * ProductVault - Final Complete Setup Script
 * ============================================
 * ضع هذا الملف في: C:\xampp\htdocs\takwincart\public\vault_final_setup.php
 * ثم افتحه في المتصفح: http://localhost/takwincart/vault_final_setup.php
 * بعد الانتهاء، احذف الملف فوراً
 */

$base = dirname(__DIR__); // C:\xampp\htdocs\takwincart
$modulePath = $base . '/packages/workdo/ProductVault';
$srcPath = $modulePath . '/src';
$publicPath = $base . '/public';

$errors = [];
$success = [];

// ==========================================
// 1. Create directory structure
// ==========================================
$dirs = [
    $modulePath,
    $srcPath,
    $srcPath . '/Providers',
    $srcPath . '/Listeners',
    $srcPath . '/Http/Controllers',
    $srcPath . '/Http/Middleware',
    $srcPath . '/Entities',
    $srcPath . '/Routes',
    $srcPath . '/Resources/views/admin',
    $srcPath . '/Resources/views/dashboard',
    $srcPath . '/Resources/views/layouts',
    $srcPath . '/Database/migrations',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            $success[] = "Created: $dir";
        } else {
            $errors[] = "Failed to create: $dir";
        }
    }
}

// Helper function
function write($path, $content, &$errors, &$success) {
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    if (file_put_contents($path, $content)) {
        $success[] = basename($path);
    } else {
        $errors[] = "Failed: $path";
    }
}

// ==========================================
// 2. module.json
// ==========================================
write($modulePath . '/module.json', json_encode([
    "name" => "ProductVault",
    "version" => "1.0.0",
    "package_name" => "product-vault",
    "display_name" => "Product Vault",
    "description" => "Digital products marketplace - super admin adds products, merchants browse and purchase",
    "author" => "Workdo",
    "is_enable" => true,
    "required_modules" => []
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), $errors, $success);

// ==========================================
// 3. composer.json
// ==========================================
write($modulePath . '/composer.json', json_encode([
    "name" => "workdo/product-vault",
    "description" => "ProductVault Module for BixelCart",
    "type" => "library",
    "require" => [],
    "autoload" => [
        "psr-4" => [
            "Workdo\\ProductVault\\" => "src/"
        ]
    ],
    "minimum-stability" => "dev"
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), $errors, $success);

// ==========================================
// 4. ServiceProvider (الملف الرئيسي)
// ==========================================
$serviceProvider = <<<'PHP'
<?php

namespace Workdo\ProductVault\Providers;

use Illuminate\Support\ServiceProvider;

class ProductVaultServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'product-vault');

        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'ProductVault');

        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');
    }
}
PHP;
write($srcPath . '/Providers/ProductVaultServiceProvider.php', $serviceProvider, $errors, $success);

// ==========================================
// 5. RouteServiceProvider (مثل LandingPage)
// ==========================================
$routeServiceProvider = <<<'PHP'
<?php

namespace Workdo\ProductVault\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'Workdo\ProductVault\Http\Controllers';

    public function boot()
    {
        parent::boot();
    }

    public function map()
    {
        $this->mapWebRoutes();
    }

    protected function mapWebRoutes()
    {
        Route::middleware(['web', 'auth', 'xss', 'setlocate', 'verified'])
            ->namespace($this->namespace)
            ->group(__DIR__ . '/../Routes/web.php');
    }
}
PHP;
write($srcPath . '/Providers/RouteServiceProvider.php', $routeServiceProvider, $errors, $success);

// ==========================================
// 6. EventServiceProvider
// ==========================================
$eventServiceProvider = <<<'PHP'
<?php

namespace Workdo\ProductVault\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\SuperAdminMenuEvent;
use Workdo\ProductVault\Listeners\SuperAdminMenuListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        SuperAdminMenuEvent::class => [
            SuperAdminMenuListener::class,
        ],
    ];
}
PHP;
write($srcPath . '/Providers/EventServiceProvider.php', $eventServiceProvider, $errors, $success);

// ==========================================
// 7. SuperAdminMenuListener
// ==========================================
$menuListener = <<<'PHP'
<?php

namespace Workdo\ProductVault\Listeners;

use App\Events\SuperAdminMenuEvent;

class SuperAdminMenuListener
{
    public function handle(SuperAdminMenuEvent $event)
    {
        $menu = $event->menu;

        // Parent menu item for Product Vault
        $menu->add([
            'title' => 'Product Vault',
            'icon' => ' vault',
            'name' => 'product-vault',
            'parent' => null,
            'order' => 45,
            'is_admin' => 0,
            'route' => 'product-vault.index',
            'module' => 'ProductVault',
            'permission' => 'product-vault manage',
        ]);

        // Sub-menu: Add Product
        $menu->add([
            'title' => 'Add Product',
            'icon' => '',
            'name' => 'product-vault-create',
            'parent' => 'product-vault',
            'order' => 10,
            'is_admin' => 0,
            'route' => 'product-vault.create',
            'module' => 'ProductVault',
            'permission' => 'product-vault create',
        ]);
    }
}
PHP;
write($srcPath . '/Listeners/SuperAdminMenuListener.php', $menuListener, $errors, $success);

// ==========================================
// 8. Routes/web.php (بدون /admin/ prefix)
// ==========================================
$routes = <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;
use Workdo\ProductVault\Http\Controllers\ProductVaultAdminController;
use Workdo\ProductVault\Http\Controllers\VaultDashboardController;

Route::get('product-vault', [ProductVaultAdminController::class, 'index'])->name('product-vault.index')->middleware('permission:product-vault manage');
Route::get('product-vault/create', [ProductVaultAdminController::class, 'create'])->name('product-vault.create')->middleware('permission:product-vault create');
Route::post('product-vault', [ProductVaultAdminController::class, 'store'])->name('product-vault.store')->middleware('permission:product-vault create');
Route::get('product-vault/{id}/edit', [ProductVaultAdminController::class, 'edit'])->name('product-vault.edit')->middleware('permission:product-vault edit');
Route::put('product-vault/{id}', [ProductVaultAdminController::class, 'update'])->name('product-vault.update')->middleware('permission:product-vault edit');
Route::delete('product-vault/{id}', [ProductVaultAdminController::class, 'destroy'])->name('product-vault.destroy')->middleware('permission:product-vault delete');
Route::get('product-vault/{id}', [ProductVaultAdminController::class, 'show'])->name('product-vault.show')->middleware('permission:product-vault manage');

// Merchant dashboard routes
Route::get('vault-marketplace', [VaultDashboardController::class, 'marketplace'])->name('vault.marketplace');
Route::get('vault-marketplace/{id}', [VaultDashboardController::class, 'show'])->name('vault.marketplace.show');
Route::get('vault-library', [VaultDashboardController::class, 'library'])->name('vault.library');
Route::post('vault-purchase/{id}', [VaultDashboardController::class, 'purchase'])->name('vault.purchase');
PHP;
write($srcPath . '/Routes/web.php', $routes, $errors, $success);

// ==========================================
// 9. Entity: VaultProduct
// ==========================================
$vaultProduct = <<<'PHP'
<?php

namespace Workdo\ProductVault\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VaultProduct extends Model
{
    use HasFactory;

    protected $table = 'vault_products';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'category',
        'price',
        'preview_image',
        'file_path',
        'file_type',
        'file_size',
        'demo_url',
        'status',
        'is_featured',
        'downloads_count',
        'created_by',
        'workspace_id',
        'store_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_featured' => 'boolean',
        'downloads_count' => 'integer',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function purchases()
    {
        return $this->hasMany(VaultPurchase::class, 'product_id');
    }
}
PHP;
write($srcPath . '/Entities/VaultProduct.php', $vaultProduct, $errors, $success);

// ==========================================
// 10. Entity: VaultPurchase
// ==========================================
$vaultPurchase = <<<'PHP'
<?php

namespace Workdo\ProductVault\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VaultPurchase extends Model
{
    use HasFactory;

    protected $table = 'vault_purchases';

    protected $fillable = [
        'product_id',
        'user_id',
        'workspace_id',
        'store_id',
        'price_paid',
        'payment_status',
        'transaction_id',
        'purchased_at',
        'download_token',
        'download_count',
        'expires_at',
    ];

    protected $casts = [
        'price_paid' => 'decimal:2',
        'purchased_at' => 'datetime',
        'expires_at' => 'datetime',
        'download_count' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(VaultProduct::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
PHP;
write($srcPath . '/Entities/VaultPurchase.php', $vaultPurchase, $errors, $success);

// ==========================================
// 11. Controller: ProductVaultAdminController (بدون abort)
// ==========================================
$adminController = <<<'PHP'
<?php

namespace Workdo\ProductVault\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Workdo\ProductVault\Entities\VaultProduct;

class ProductVaultAdminController extends Controller
{
    public function __construct()
    {
        // لا نستخدم abort هنا - مثل LandingPage
    }

    public function index(Request $request)
    {
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $query = VaultProduct::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(15);

        $categories = VaultProduct::distinct()->pluck('category')->filter();

        return view('product-vault::admin.index', compact('products', 'categories'));
    }

    public function create()
    {
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $categories = VaultProduct::distinct()->pluck('category')->filter();

        return view('product-vault::admin.create', compact('categories'));
    }

    public function store(Request $request)
    {
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'preview_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'file_path' => 'required|file|max:51200',
            'demo_url' => 'nullable|url|max:500',
            'status' => 'required|in:active,draft',
            'is_featured' => 'nullable|boolean',
        ]);

        $data = $request->all();

        // Generate slug
        $slug = \Str::slug($request->name);
        $count = VaultProduct::where('slug', $slug)->count();
        if ($count > 0) {
            $slug = $slug . '-' . ($count + 1);
        }
        $data['slug'] = $slug;

        // Upload preview image
        if ($request->hasFile('preview_image')) {
            $image = $request->file('preview_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(storage_path('app/public/vault_images'), $imageName);
            $data['preview_image'] = 'vault_images/' . $imageName;
        }

        // Upload product file
        if ($request->hasFile('file_path')) {
            $file = $request->file('file_path');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(storage_path('app/public/vault_files'), $fileName);
            $data['file_path'] = 'vault_files/' . $fileName;
            $data['file_type'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
        }

        $data['is_featured'] = $request->has('is_featured') ? true : false;
        $data['downloads_count'] = 0;
        $data['created_by'] = Auth::id();
        $data['workspace_id'] = 1;

        VaultProduct::create($data);

        return redirect()->route('product-vault.index')
            ->with('success', __('Product added successfully.'));
    }

    public function edit($id)
    {
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $product = VaultProduct::findOrFail($id);
        $categories = VaultProduct::distinct()->pluck('category')->filter();

        return view('product-vault::admin.edit', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'preview_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'file_path' => 'nullable|file|max:51200',
            'demo_url' => 'nullable|url|max:500',
            'status' => 'required|in:active,draft',
            'is_featured' => 'nullable|boolean',
        ]);

        $product = VaultProduct::findOrFail($id);
        $data = $request->except(['_token', '_method']);

        // Update slug if name changed
        $slug = \Str::slug($request->name);
        if ($slug !== $product->slug) {
            $count = VaultProduct::where('slug', $slug)->where('id', '!=', $id)->count();
            if ($count > 0) {
                $slug = $slug . '-' . ($count + 1);
            }
            $data['slug'] = $slug;
        }

        // Upload new preview image
        if ($request->hasFile('preview_image')) {
            // Delete old
            if ($product->preview_image && Storage::exists('public/' . $product->preview_image)) {
                Storage::delete('public/' . $product->preview_image);
            }
            $image = $request->file('preview_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(storage_path('app/public/vault_images'), $imageName);
            $data['preview_image'] = 'vault_images/' . $imageName;
        }

        // Upload new file
        if ($request->hasFile('file_path')) {
            if ($product->file_path && Storage::exists('public/' . $product->file_path)) {
                Storage::delete('public/' . $product->file_path);
            }
            $file = $request->file('file_path');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(storage_path('app/public/vault_files'), $fileName);
            $data['file_path'] = 'vault_files/' . $fileName;
            $data['file_type'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
        }

        $data['is_featured'] = $request->has('is_featured') ? true : false;

        $product->update($data);

        return redirect()->route('product-vault.index')
            ->with('success', __('Product updated successfully.'));
    }

    public function destroy($id)
    {
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $product = VaultProduct::findOrFail($id);

        // Delete files
        if ($product->preview_image && Storage::exists('public/' . $product->preview_image)) {
            Storage::delete('public/' . $product->preview_image);
        }
        if ($product->file_path && Storage::exists('public/' . $product->file_path)) {
            Storage::delete('public/' . $product->file_path);
        }

        $product->delete();

        return redirect()->route('product-vault.index')
            ->with('success', __('Product deleted successfully.'));
    }

    public function show($id)
    {
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $product = VaultProduct::findOrFail($id);

        return view('product-vault::admin.show', compact('product'));
    }
}
PHP;
write($srcPath . '/Http/Controllers/ProductVaultAdminController.php', $adminController, $errors, $success);

// ==========================================
// 12. Controller: VaultDashboardController (بدون abort)
// ==========================================
$dashboardController = <<<'PHP'
<?php

namespace Workdo\ProductVault\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Workdo\ProductVault\Entities\VaultProduct;
use Workdo\ProductVault\Entities\VaultPurchase;

class VaultDashboardController extends Controller
{
    public function marketplace(Request $request)
    {
        $query = VaultProduct::active();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Featured products first
        $featured = VaultProduct::active()->featured()->orderBy('created_at', 'desc')->take(4)->get();
        $products = $query->orderBy('created_at', 'desc')->paginate(12);
        $categories = VaultProduct::active()->distinct()->pluck('category')->filter();

        return view('product-vault::dashboard.index', compact('products', 'featured', 'categories'));
    }

    public function show($id)
    {
        $product = VaultProduct::active()->findOrFail($id);

        // Check if user already purchased
        $purchased = false;
        $purchase = null;
        if (Auth::check()) {
            $purchase = VaultPurchase::where('product_id', $id)
                ->where('user_id', Auth::id())
                ->where('payment_status', 'completed')
                ->first();
            $purchased = !empty($purchase);
        }

        return view('product-vault::dashboard.show', compact('product', 'purchased', 'purchase'));
    }

    public function library(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', __('Please login first.'));
        }

        $purchases = VaultPurchase::where('user_id', Auth::id())
            ->where('payment_status', 'completed')
            ->with('product')
            ->orderBy('purchased_at', 'desc')
            ->paginate(12);

        return view('product-vault::dashboard.library', compact('purchases'));
    }

    public function purchase(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', __('Please login first.'));
        }

        $product = VaultProduct::active()->findOrFail($id);

        // Check if already purchased
        $existing = VaultPurchase::where('product_id', $id)
            ->where('user_id', Auth::id())
            ->where('payment_status', 'completed')
            ->first();

        if ($existing) {
            return redirect()->route('vault.marketplace.show', $id)
                ->with('info', __('You already own this product.'));
        }

        // Create purchase (free for now - payment integration later)
        VaultPurchase::create([
            'product_id' => $id,
            'user_id' => Auth::id(),
            'workspace_id' => Auth::user()->workspace_id ?? 1,
            'store_id' => Auth::user()->current_store ?? null,
            'price_paid' => $product->price > 0 ? $product->price : 0,
            'payment_status' => $product->price > 0 ? 'pending' : 'completed',
            'transaction_id' => Str::uuid(),
            'purchased_at' => now(),
            'download_token' => Str::random(32),
            'download_count' => 0,
            'expires_at' => now()->addYear(),
        ]);

        if ($product->price <= 0) {
            // Free product - complete immediately
            $product->increment('downloads_count');
        }

        return redirect()->route('vault.marketplace.show', $id)
            ->with('success', __('Product added to your library!'));
    }
}
PHP;
write($srcPath . '/Http/Controllers/VaultDashboardController.php', $dashboardController, $errors, $success);

// ==========================================
// 13. View: admin/index.blade.php (layouts.app)
// ==========================================
$adminIndex = <<<'BLADE'
@extends('layouts.app')

@section('title') __('Product Vault - All Products') @endsection

@section('content')
<div class="main-content">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">{{ __('Product Vault') }}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Products') }}</li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="{{ route('product-vault.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ __('Add Product') }}
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('Image') }}</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Price') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Featured') }}</th>
                            <th>{{ __('Downloads') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                @if($product->preview_image)
                                <img src="{{ Storage::url($product->preview_image) }}" alt="{{ $product->name }}" style="width:50px;height:50px;object-fit:cover;border-radius:4px;">
                                @else
                                <span class="badge badge-secondary">No Image</span>
                                @endif
                            </td>
                            <td>{{ $product->name }}</td>
                            <td><span class="badge badge-info">{{ $product->category }}</span></td>
                            <td>{{ number_format($product->price, 2) }}</td>
                            <td>
                                <span class="badge badge-{{ $product->status == 'active' ? 'success' : 'warning' }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </td>
                            <td>
                                @if($product->is_featured)
                                <span class="badge badge-primary">Yes</span>
                                @else
                                <span class="badge badge-light">No</span>
                                @endif
                            </td>
                            <td>{{ $product->downloads_count }}</td>
                            <td>
                                <div class="action-btn">
                                    <a href="{{ route('product-vault.edit', $product->id) }}" class="btn btn-sm btn-info" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('product-vault.show', $product->id) }}" class="btn btn-sm btn-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    {!! Form::open(['method' => 'DELETE', 'route' => ['product-vault.destroy', $product->id], 'style' => 'display:inline']) !!}
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    {!! Form::close() !!}
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">{{ __('No products found.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
BLADE;
write($srcPath . '/Resources/views/admin/index.blade.php', $adminIndex, $errors, $success);

// ==========================================
// 14. View: admin/create.blade.php
// ==========================================
$adminCreate = <<<'BLADE'
@extends('layouts.app')

@section('title') __('Add New Product') @endsection

@section('content')
<div class="main-content">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">{{ __('Add Product') }}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('product-vault.index') }}">{{ __('Product Vault') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Add') }}</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            {!! Form::open(['route' => 'product-vault.store', 'method' => 'POST', 'files' => true, 'enctype' => 'multipart/form-data']) !!}
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('name', __('Product Name'), ['class' => 'form-label']) !!}
                        {!! Form::text('name', null, ['class' => 'form-control', 'required']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('category', __('Category'), ['class' => 'form-label']) !!}
                        {!! Form::text('category', null, ['class' => 'form-control', 'required', 'list' => 'category-list']) !!}
                        <datalist id="category-list">
                            @foreach($categories as $cat)
                            <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('price', __('Price (0 = Free)'), ['class' => 'form-label']) !!}
                        {!! Form::number('price', '0', ['class' => 'form-control', 'required', 'step' => '0.01', 'min' => '0']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('status', __('Status'), ['class' => 'form-label']) !!}
                        {!! Form::select('status', ['active' => 'Active', 'draft' => 'Draft'], 'active', ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('short_description', __('Short Description'), ['class' => 'form-label']) !!}
                        {!! Form::textarea('short_description', null, ['class' => 'form-control', 'rows' => '2']) !!}
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('description', __('Full Description'), ['class' => 'form-label']) !!}
                        {!! Form::textarea('description', null, ['class' => 'form-control editor', 'rows' => '5', 'required']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('preview_image', __('Preview Image'), ['class' => 'form-label']) !!}
                        {!! Form::file('preview_image', ['class' => 'form-control', 'accept' => 'image/*']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('file_path', __('Product File'), ['class' => 'form-label']) !!}
                        {!! Form::file('file_path', ['class' => 'form-control', 'required']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('demo_url', __('Demo URL'), ['class' => 'form-label']) !!}
                        {!! Form::url('demo_url', null, ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="custom-control custom-checkbox mt-4">
                            {!! Form::checkbox('is_featured', 1, false, ['class' => 'custom-control-input', 'id' => 'is_featured']) !!}
                            <label class="custom-control-label" for="is_featured">{{ __('Featured Product') }}</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 text-right mt-3">
                    <a href="{{ route('product-vault.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">{{ __('Save Product') }}</button>
                </div>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
</div>
@endsection
BLADE;
write($srcPath . '/Resources/views/admin/create.blade.php', $adminCreate, $errors, $success);

// ==========================================
// 15. View: admin/edit.blade.php
// ==========================================
$adminEdit = <<<'BLADE'
@extends('layouts.app')

@section('title') __('Edit Product') @endsection

@section('content')
<div class="main-content">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">{{ __('Edit Product') }}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('product-vault.index') }}">{{ __('Product Vault') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Edit') }}</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            {!! Form::model($product, ['route' => ['product-vault.update', $product->id], 'method' => 'PUT', 'files' => true, 'enctype' => 'multipart/form-data']) !!}
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('name', __('Product Name'), ['class' => 'form-label']) !!}
                        {!! Form::text('name', null, ['class' => 'form-control', 'required']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('category', __('Category'), ['class' => 'form-label']) !!}
                        {!! Form::text('category', null, ['class' => 'form-control', 'required', 'list' => 'category-list']) !!}
                        <datalist id="category-list">
                            @foreach($categories as $cat)
                            <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('price', __('Price (0 = Free)'), ['class' => 'form-label']) !!}
                        {!! Form::number('price', null, ['class' => 'form-control', 'required', 'step' => '0.01', 'min' => '0']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('status', __('Status'), ['class' => 'form-label']) !!}
                        {!! Form::select('status', ['active' => 'Active', 'draft' => 'Draft'], null, ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('short_description', __('Short Description'), ['class' => 'form-label']) !!}
                        {!! Form::textarea('short_description', null, ['class' => 'form-control', 'rows' => '2']) !!}
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('description', __('Full Description'), ['class' => 'form-label']) !!}
                        {!! Form::textarea('description', null, ['class' => 'form-control editor', 'rows' => '5', 'required']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('preview_image', __('Preview Image'), ['class' => 'form-label']) !!}
                        @if($product->preview_image)
                        <img src="{{ Storage::url($product->preview_image) }}" style="width:80px;height:80px;object-fit:cover;border-radius:4px;margin-bottom:5px;display:block;">
                        @endif
                        {!! Form::file('preview_image', ['class' => 'form-control', 'accept' => 'image/*']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('file_path', __('Product File'), ['class' => 'form-label']) !!}
                        <p class="text-muted small">Current: {{ basename($product->file_path ?? 'none') }}</p>
                        {!! Form::file('file_path', ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('demo_url', __('Demo URL'), ['class' => 'form-label']) !!}
                        {!! Form::url('demo_url', null, ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="custom-control custom-checkbox mt-4">
                            {!! Form::checkbox('is_featured', 1, $product->is_featured, ['class' => 'custom-control-input', 'id' => 'is_featured']) !!}
                            <label class="custom-control-label" for="is_featured">{{ __('Featured Product') }}</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 text-right mt-3">
                    <a href="{{ route('product-vault.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">{{ __('Update Product') }}</button>
                </div>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
</div>
@endsection
BLADE;
write($srcPath . '/Resources/views/admin/edit.blade.php', $adminEdit, $errors, $success);

// ==========================================
// 16. View: admin/show.blade.php
// ==========================================
$adminShow = <<<'BLADE'
@extends('layouts.app')

@section('title') __('Product Details') @endsection

@section('content')
<div class="main-content">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">{{ __('Product Details') }}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('product-vault.index') }}">{{ __('Product Vault') }}</a></li>
                    <li class="breadcrumb-item active">{{ $product->name }}</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    @if($product->preview_image)
                    <img src="{{ Storage::url($product->preview_image) }}" alt="{{ $product->name }}" class="img-fluid" style="border-radius:8px;">
                    @else
                    <div class="text-center p-5 bg-light" style="border-radius:8px;">
                        <i class="fas fa-image text-muted" style="font-size:64px;"></i>
                    </div>
                    @endif
                </div>
                <div class="col-md-8">
                    <h3>{{ $product->name }}</h3>
                    <div class="mb-2">
                        <span class="badge badge-info">{{ $product->category }}</span>
                        <span class="badge badge-{{ $product->status == 'active' ? 'success' : 'warning' }}">{{ ucfirst($product->status) }}</span>
                        @if($product->is_featured)
                        <span class="badge badge-primary">Featured</span>
                        @endif
                    </div>
                    <h4 class="text-primary">{{ number_format($product->price, 2) }}</h4>
                    <hr>
                    <p><strong>{{ __('Short Description:') }}</strong></p>
                    <p>{{ $product->short_description }}</p>
                    <p><strong>{{ __('Description:') }}</strong></p>
                    <div>{!! $product->description !!}</div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>{{ __('File Type:') }}</strong> {{ strtoupper($product->file_type ?? 'N/A') }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>{{ __('File Size:') }}</strong> {{ $product->file_size ? number_format($product->file_size / 1024, 2) . ' KB' : 'N/A' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>{{ __('Downloads:') }}</strong> {{ $product->downloads_count }}</p>
                        </div>
                    </div>
                    @if($product->demo_url)
                    <a href="{{ $product->demo_url }}" target="_blank" class="btn btn-info mt-2">
                        <i class="fas fa-external-link-alt"></i> {{ __('Live Demo') }}
                    </a>
                    @endif
                    <a href="{{ route('product-vault.edit', $product->id) }}" class="btn btn-primary mt-2">
                        <i class="fas fa-edit"></i> {{ __('Edit Product') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
BLADE;
write($srcPath . '/Resources/views/admin/show.blade.php', $adminShow, $errors, $success);

// ==========================================
// 17. View: dashboard/index.blade.php (Marketplace)
// ==========================================
$dashIndex = <<<'BLADE'
@extends('layouts.app')

@section('title') __('Digital Marketplace') @endsection

@section('content')
<div class="main-content">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">{{ __('Digital Marketplace') }}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Marketplace') }}</li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="{{ route('vault.library') }}" class="btn btn-outline-primary">
                    <i class="fas fa-book"></i> {{ __('My Library') }}
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    @if($featured->count() > 0)
    <div class="mb-4">
        <h4 class="mb-3"><i class="fas fa-star text-warning"></i> {{ __('Featured Products') }}</h4>
        <div class="row">
            @foreach($featured as $fp)
            <div class="col-md-3 mb-3">
                <div class="card" style="border:2px solid #ffcc02;">
                    <div class="card-body text-center">
                        @if($fp->preview_image)
                        <img src="{{ Storage::url($fp->preview_image) }}" alt="{{ $fp->name }}" class="card-img-top" style="height:150px;object-fit:cover;">
                        @else
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height:150px;">
                            <i class="fas fa-box text-muted" style="font-size:48px;"></i>
                        </div>
                        @endif
                        <h6 class="mt-2">{{ Str::limit($fp->name, 30) }}</h6>
                        <p class="text-primary font-weight-bold">{{ $fp->price > 0 ? number_format($fp->price, 2) : __('Free') }}</p>
                        <a href="{{ route('vault.marketplace.show', $fp->id) }}" class="btn btn-sm btn-primary">{{ __('View') }}</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <h4 class="mb-3">{{ __('All Products') }}</h4>
    <div class="row">
        @forelse($products as $product)
        <div class="col-md-4 col-lg-3 mb-4">
            <div class="card h-100">
                @if($product->preview_image)
                <img src="{{ Storage::url($product->preview_image) }}" alt="{{ $product->name }}" class="card-img-top" style="height:180px;object-fit:cover;">
                @else
                <div class="bg-light d-flex align-items-center justify-content-center" style="height:180px;">
                    <i class="fas fa-box text-muted" style="font-size:48px;"></i>
                </div>
                @endif
                <div class="card-body d-flex flex-column">
                    <span class="badge badge-info mb-1">{{ $product->category }}</span>
                    <h6 class="card-title">{{ Str::limit($product->name, 40) }}</h6>
                    <p class="card-text text-muted small">{{ Str::limit($product->short_description ?? '', 80) }}</p>
                    <div class="mt-auto d-flex justify-content-between align-items-center">
                        <span class="text-primary font-weight-bold">{{ $product->price > 0 ? number_format($product->price, 2) : __('Free') }}</span>
                        <a href="{{ route('vault.marketplace.show', $product->id) }}" class="btn btn-sm btn-outline-primary">
                            {{ __('View') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="fas fa-store text-muted" style="font-size:64px;"></i>
            <h5 class="mt-3 text-muted">{{ __('No products available yet.') }}</h5>
        </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $products->links() }}
    </div>
</div>
@endsection
BLADE;
write($srcPath . '/Resources/views/dashboard/index.blade.php', $dashIndex, $errors, $success);

// ==========================================
// 18. View: dashboard/show.blade.php
// ==========================================
$dashShow = <<<'BLADE'
@extends('layouts.app')

@section('title') $product->name @endsection

@section('content')
<div class="main-content">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">{{ __('Product Details') }}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('vault.marketplace') }}">{{ __('Marketplace') }}</a></li>
                    <li class="breadcrumb-item active">{{ $product->name }}</li>
                </ul>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    @if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        {{ session('info') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-5">
                    @if($product->preview_image)
                    <img src="{{ Storage::url($product->preview_image) }}" alt="{{ $product->name }}" class="img-fluid" style="border-radius:8px;">
                    @else
                    <div class="text-center p-5 bg-light" style="border-radius:8px;">
                        <i class="fas fa-image text-muted" style="font-size:80px;"></i>
                    </div>
                    @endif
                    @if($product->demo_url)
                    <a href="{{ $product->demo_url }}" target="_blank" class="btn btn-info btn-block mt-3">
                        <i class="fas fa-external-link-alt"></i> {{ __('Live Demo') }}
                    </a>
                    @endif
                </div>
                <div class="col-md-7">
                    <h2>{{ $product->name }}</h2>
                    <div class="mb-3">
                        <span class="badge badge-info">{{ $product->category }}</span>
                        @if($product->is_featured)
                        <span class="badge badge-warning"><i class="fas fa-star"></i> Featured</span>
                        @endif
                    </div>
                    <h3 class="text-primary mb-3">
                        @if($product->price > 0)
                        {{ number_format($product->price, 2) }}
                        @else
                        <span class="text-success">{{ __('FREE') }}</span>
                        @endif
                    </h3>
                    <hr>
                    <p>{{ $product->short_description }}</p>
                    <div class="mb-3">{!! $product->description !!}</div>
                    <div class="row mb-3">
                        <div class="col-4">
                            <small class="text-muted">{{ __('Type') }}</small>
                            <p class="font-weight-bold">{{ strtoupper($product->file_type ?? 'N/A') }}</p>
                        </div>
                        <div class="col-4">
                            <small class="text-muted">{{ __('Size') }}</small>
                            <p class="font-weight-bold">{{ $product->file_size ? number_format($product->file_size / 1024, 2) . ' KB' : 'N/A' }}</p>
                        </div>
                        <div class="col-4">
                            <small class="text-muted">{{ __('Downloads') }}</small>
                            <p class="font-weight-bold">{{ $product->downloads_count }}</p>
                        </div>
                    </div>
                    <hr>
                    @if($purchased)
                    <button class="btn btn-success" disabled>
                        <i class="fas fa-check-circle"></i> {{ __('Already Purchased') }}
                    </button>
                    <a href="{{ route('vault.library') }}" class="btn btn-outline-primary">
                        <i class="fas fa-book"></i> {{ __('Go to Library') }}
                    </a>
                    @else
                    <form method="POST" action="{{ route('vault.purchase', $product->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-cart"></i>
                            @if($product->price > 0)
                            {{ __('Purchase') }} - {{ number_format($product->price, 2) }}
                            @else
                            {{ __('Get Free') }}
                            @endif
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
BLADE;
write($srcPath . '/Resources/views/dashboard/show.blade.php', $dashShow, $errors, $success);

// ==========================================
// 19. View: dashboard/library.blade.php
// ==========================================
$dashLibrary = <<<'BLADE'
@extends('layouts.app')

@section('title') __('My Library') @endsection

@section('content')
<div class="main-content">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">{{ __('My Library') }}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('My Library') }}</li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="{{ route('vault.marketplace') }}" class="btn btn-primary">
                    <i class="fas fa-store"></i> {{ __('Browse Marketplace') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        @forelse($purchases as $purchase)
        <div class="col-md-4 col-lg-3 mb-4">
            <div class="card h-100">
                @if($purchase->product && $purchase->product->preview_image)
                <img src="{{ Storage::url($purchase->product->preview_image) }}" alt="{{ $purchase->product->name }}" class="card-img-top" style="height:150px;object-fit:cover;">
                @else
                <div class="bg-light d-flex align-items-center justify-content-center" style="height:150px;">
                    <i class="fas fa-box text-muted" style="font-size:48px;"></i>
                </div>
                @endif
                <div class="card-body">
                    <h6 class="card-title">{{ $purchase->product ? $purchase->product->name : __('Product Deleted') }}</h6>
                    <p class="text-muted small">
                        {{ __('Purchased:') }} {{ $purchase->purchased_at->format('M d, Y') }}
                    </p>
                    <span class="badge badge-success">{{ ucfirst($purchase->payment_status) }}</span>
                </div>
                @if($purchase->product)
                <div class="card-footer text-center">
                    <a href="{{ route('vault.marketplace.show', $purchase->product_id) }}" class="btn btn-sm btn-outline-primary">
                        {{ __('View') }}
                    </a>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="fas fa-book-open text-muted" style="font-size:64px;"></i>
            <h5 class="mt-3 text-muted">{{ __('Your library is empty.') }}</h5>
            <a href="{{ route('vault.marketplace') }}" class="btn btn-primary mt-2">
                <i class="fas fa-store"></i> {{ __('Browse Products') }}
            </a>
        </div>
        @endforelse
    </div>

    @if($purchases->count() > 0)
    <div class="d-flex justify-content-center mt-4">
        {{ $purchases->links() }}
    </div>
    @endif
</div>
@endsection
BLADE;
write($srcPath . '/Resources/views/dashboard/library.blade.php', $dashLibrary, $errors, $success);

// ==========================================
// 20. Migration: vault_products
// ==========================================
$migrationProducts = <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vault_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('short_description')->nullable();
            $table->string('category')->default('general');
            $table->decimal('price', 10, 2)->default(0);
            $table->string('preview_image')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_type', 50)->nullable();
            $table->integer('file_size')->default(0);
            $table->string('demo_url')->nullable();
            $table->enum('status', ['active', 'draft'])->default('active');
            $table->boolean('is_featured')->default(false);
            $table->integer('downloads_count')->default(0);
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('workspace_id')->default(0);
            $table->bigInteger('store_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vault_products');
    }
};
PHP;
write($srcPath . '/Database/migrations/2024_01_01_000001_create_vault_products_table.php', $migrationProducts, $errors, $success);

// ==========================================
// 21. Migration: vault_purchases
// ==========================================
$migrationPurchases = <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vault_purchases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('product_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('workspace_id')->default(0);
            $table->bigInteger('store_id')->nullable();
            $table->decimal('price_paid', 10, 2)->default(0);
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->timestamp('purchased_at')->nullable();
            $table->string('download_token')->nullable();
            $table->integer('download_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('vault_products')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vault_purchases');
    }
};
PHP;
write($srcPath . '/Database/migrations/2024_01_01_000002_create_vault_purchases_table.php', $migrationPurchases, $errors, $success);

// ==========================================
// 22. Database operations
// ==========================================
try {
    // Load Laravel app
    require $base . '/vendor/autoload.php';
    $app = require_once $base . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    $db = \Illuminate\Support\Facades\DB::connection();

    // Check if tables exist, create if not
    if (!\Schema::hasTable('vault_products')) {
        \Artisan::call('migrate', ['--force' => true]);
        $success[] = "Migration: vault_products table created";
    } else {
        $success[] = "Migration: vault_products table already exists";
    }

    if (!\Schema::hasTable('vault_purchases')) {
        // Run the specific migration
        $migration = require $srcPath . '/Database/migrations/2024_01_01_000002_create_vault_purchases_table.php';
        $migration->up();
        $success[] = "Migration: vault_purchases table created";
    } else {
        $success[] = "Migration: vault_purchases table already exists";
    }

    // Register in add_on_managers
    $existing = $db->table('add_on_managers')->where('module', 'ProductVault')->first();
    if (!$existing) {
        $db->table('add_on_managers')->insert([
            'module' => 'ProductVault',
            'name' => 'Product Vault',
            'monthly_price' => 0,
            'yearly_price' => 0,
            'image' => '',
            'is_enable' => 1,
            'package_name' => 'product-vault',
            'is_display' => 1,
        ]);
        $success[] = "DB: ProductVault registered in add_on_managers";
    } else {
        // Update to ensure correct values
        $db->table('add_on_managers')->where('module', 'ProductVault')->update([
            'is_enable' => 1,
            'package_name' => 'product-vault',
            'is_display' => 1,
        ]);
        $success[] = "DB: ProductVault already in add_on_managers (updated)";
    }

    // Add permissions
    $permissions = [
        'product-vault manage',
        'product-vault create',
        'product-vault edit',
        'product-vault delete',
    ];

    foreach ($permissions as $permName) {
        $exists = $db->table('permissions')->where('name', $permName)->first();
        if (!$exists) {
            $db->table('permissions')->insert([
                'name' => $permName,
                'guard_name' => 'web',
                'module' => 'ProductVault',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $success[] = "Permission added: $permName";
        } else {
            $success[] = "Permission exists: $permName";
        }
    }

    // Assign permissions to super admin role (role id = 1 usually)
    $adminRole = $db->table('roles')->where('name', 'super admin')->orWhere('id', 1)->first();
    if ($adminRole) {
        foreach ($permissions as $permName) {
            $perm = $db->table('permissions')->where('name', $permName)->first();
            if ($perm) {
                $exists = $db->table('permission_role')
                    ->where('permission_id', $perm->id)
                    ->where('role_id', $adminRole->id)
                    ->first();
                if (!$exists) {
                    $db->table('permission_role')->insert([
                        'permission_id' => $perm->id,
                        'role_id' => $adminRole->id,
                    ]);
                    $success[] = "Permission assigned: $permName to role {$adminRole->name}";
                }
            }
        }
    }

    // Assign permissions directly to super admin user
    $adminUser = $db->table('users')->where('type', 'super admin')->first();
    if ($adminUser) {
        foreach ($permissions as $permName) {
            $perm = $db->table('permissions')->where('name', $permName)->first();
            if ($perm) {
                $exists = $db->table('permission_user')
                    ->where('permission_id', $perm->id)
                    ->where('user_id', $adminUser->id)
                    ->first();
                if (!$exists) {
                    $db->table('permission_user')->insert([
                        'permission_id' => $perm->id,
                        'user_id' => $adminUser->id,
                    ]);
                    $success[] = "Permission assigned: $permName to admin user";
                }
            }
        }
    }

    // Clear all caches
    \Artisan::call('cache:clear');
    \Artisan::call('config:clear');
    \Artisan::call('view:clear');
    \Artisan::call('route:clear');
    $success[] = "All caches cleared";

    // Create storage directories
    if (!is_dir(storage_path('app/public/vault_images'))) {
        mkdir(storage_path('app/public/vault_images'), 0755, true);
        $success[] = "Created: storage/app/public/vault_images";
    }
    if (!is_dir(storage_path('app/public/vault_files'))) {
        mkdir(storage_path('app/public/vault_files'), 0755, true);
        $success[] = "Created: storage/app/public/vault_files";
    }

} catch (\Exception $e) {
    $errors[] = "DB Error: " . $e->getMessage();
}

// ==========================================
// Output results
// ==========================================
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProductVault - Final Setup</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #1a1a2e; color: #eee; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        h1 { text-align: center; color: #00d4aa; border-bottom: 2px solid #00d4aa; padding-bottom: 15px; }
        .status-box { padding: 15px; border-radius: 8px; margin: 10px 0; }
        .success-box { background: #0a3d2a; border-left: 4px solid #00d4aa; }
        .error-box { background: #3d0a0a; border-left: 4px solid #ff4444; }
        .info-box { background: #0a2a3d; border-left: 4px solid #4488ff; }
        .file-list { max-height: 400px; overflow-y: auto; }
        .file-list li { padding: 4px 0; border-bottom: 1px solid #333; font-family: monospace; font-size: 13px; }
        .warning { color: #ffaa00; font-weight: bold; }
        a { color: #00d4aa; }
        code { background: #333; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>ProductVault - Final Setup Complete</h1>

        <?php if (count($errors) > 0): ?>
        <div class="status-box error-box">
            <h3 style="color:#ff4444;">Errors (<?php echo count($errors); ?>)</h3>
            <ul>
                <?php foreach ($errors as $err): ?>
                <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="status-box success-box">
            <h3 style="color:#00d4aa;">Success (<?php echo count($success); ?>)</h3>
            <ul class="file-list">
                <?php foreach ($success as $s): ?>
                <li style="color:#00d4aa;">&#10003; <?php echo htmlspecialchars($s); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="status-box info-box">
            <h3>Next Steps</h3>
            <p>1. <strong class="warning">Delete this file NOW:</strong> <code>public/vault_final_setup.php</code></p>
            <p>2. Access the module at: <a href="<?php echo URL::to('/product-vault'); ?>" target="_blank"><code>/product-vault</code></a></p>
            <p>3. If routes don't work, clear Laravel cache:</p>
            <ul>
                <li><code>php artisan route:clear</code></li>
                <li><code>php artisan cache:clear</code></li>
                <li><code>php artisan config:clear</code></li>
            </ul>
            <p>4. Marketplace for merchants: <a href="<?php echo URL::to('/vault-marketplace'); ?>" target="_blank"><code>/vault-marketplace</code></a></p>
            <p>5. Library: <a href="<?php echo URL::to('/vault-library'); ?>" target="_blank"><code>/vault-library</code></a></p>
        </div>
    </div>
</body>
</html>
