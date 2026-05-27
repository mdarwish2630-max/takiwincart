<?php

use Illuminate\Support\Facades\Route;
use Workdo\ProductVault\Http\Controllers\ProductVaultAdminController;
use Workdo\ProductVault\Http\Controllers\VaultDashboardController;

// ==========================================
// Admin Routes (Super Admin)
// ==========================================
Route::middleware(['web', 'auth'])->prefix('product-vault')->name('product-vault.')->group(function () {
    Route::get('/purchases', [ProductVaultAdminController::class, 'purchasesIndex'])->name('purchases.index');
    Route::get('/purchases/{id}', [ProductVaultAdminController::class, 'purchaseShow'])->name('purchases.show');
    Route::post('/purchases/{id}/approve', [ProductVaultAdminController::class, 'approvePurchase'])->name('purchases.approve');
    Route::post('/purchases/{id}/reject', [ProductVaultAdminController::class, 'rejectPurchase'])->name('purchases.reject');
    Route::get('/', [ProductVaultAdminController::class, 'index'])->name('index');
    Route::get('/create', [ProductVaultAdminController::class, 'create'])->name('create');
    Route::post('/', [ProductVaultAdminController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [ProductVaultAdminController::class, 'edit'])->name('edit');
    Route::put('/{id}', [ProductVaultAdminController::class, 'update'])->name('update');
    Route::delete('/{id}', [ProductVaultAdminController::class, 'destroy'])->name('destroy');
});

// ==========================================
// User Marketplace (browse products)
// ==========================================
Route::middleware(['web', 'auth'])->prefix('vault-marketplace')->name('vault-marketplace.')->group(function () {
    Route::get('/', [VaultDashboardController::class, 'index'])->name('index');
    Route::get('/{id}', [VaultDashboardController::class, 'show'])->name('show');
});

// ==========================================
// User Library & Checkout
// ==========================================
Route::middleware(['web', 'auth'])->prefix('vault-library')->name('vault-library.')->group(function () {
    Route::get('/', [VaultDashboardController::class, 'library'])->name('index');
    Route::get('/checkout/{id}', [VaultDashboardController::class, 'checkout'])->name('checkout');
    Route::post('/checkout/{id}', [VaultDashboardController::class, 'processCheckout'])->name('process-checkout');
    Route::post('/upload-receipt/{purchaseId}', [VaultDashboardController::class, 'uploadReceipt'])->name('upload-receipt');
});