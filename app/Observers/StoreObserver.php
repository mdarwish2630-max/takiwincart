<?php

/**
 * ================================================================
 * StoreObserver - MaxCart / TakiwinCart
 * ================================================================
 * مراقب نموذج المتجر (Store Model Observer)
 * يعمل تلقائياً عند إنشاء أي متجر جديد ويمليه بالديمو داتا
 *
 * Installation:
 *   1. انسخ هذا الملف إلى: app/Observers/StoreObserver.php
 *   2. أضف الكود التالي في نهاية ملف app/Models/Store.php:
 *
 *      use App\Observers\StoreObserver;
 *
 *      protected static function booted(): void
 *      {
 *          static::observe(StoreObserver::class);
 *      }
 *
 * ================================================================
 */

namespace App\Observers;

use App\Services\AutoDemoDataSeeder;

class StoreObserver
{
    /**
     * Handle the Store "created" event.
     * عندما يتم إنشاء متجر جديد، نملأه بالديمو داتا تلقائياً
     */
    public function created($store): void
    {
        try {
            $seeder = new AutoDemoDataSeeder();
            $seeder->run(
                $store->id,
                $store->created_by,
                $store->default_language ?? 'ar'
            );

            \Log::info("AutoDemoData: Demo data seeded for store #{$store->id} ({$store->name})");
        } catch (\Exception $e) {
            // لا نوقف عملية إنشاء المتجر لو السييدر فشل
            \Log::error("AutoDemoData: Failed to seed demo data for store #{$store->id}: " . $e->getMessage());
        }
    }
}
