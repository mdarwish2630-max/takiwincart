<?php
error_reporting(E_ALL); ini_set('display_errors',1);
require __DIR__ . '/../../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());
use Illuminate\Support\Facades\DB; use Illuminate\Support\Facades\Schema;

echo "<h2>ProductVault Setup</h2><pre>";

// 1. Tables
if (!Schema::hasTable('vault_products')) {
    DB::statement("CREATE TABLE vault_products (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL, description TEXT NOT NULL, price DECIMAL(10,2) NOT NULL DEFAULT 0.00, category VARCHAR(255) NULL, thumbnail VARCHAR(500) NULL, file_url VARCHAR(500) NULL, demo_url VARCHAR(500) NULL, is_active TINYINT(1) NOT NULL DEFAULT 1, created_by BIGINT UNSIGNED NULL, download_count INT UNSIGNED NOT NULL DEFAULT 0, purchase_count INT UNSIGNED NOT NULL DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "vault_products created\n";
} else echo "vault_products exists\n";

if (!Schema::hasTable('vault_purchases')) {
    DB::statement("CREATE TABLE vault_purchases (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL, store_id BIGINT UNSIGNED NULL, vault_product_id BIGINT UNSIGNED NOT NULL, product_name VARCHAR(255) NULL, price_paid DECIMAL(10,2) NOT NULL DEFAULT 0.00, status VARCHAR(50) NOT NULL DEFAULT 'pending', purchased_at TIMESTAMP NULL, imported TINYINT(1) NOT NULL DEFAULT 0, imported_at TIMESTAMP NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, FOREIGN KEY (vault_product_id) REFERENCES vault_products(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "vault_purchases created\n";
} else echo "vault_purchases exists\n";

// 2. Permissions
foreach (['Manage ProductVault','Create ProductVault','Edit ProductVault','Delete ProductVault','View ProductVault','Purchase ProductVault'] as $p) {
    if (!DB::table('permissions')->where('name',$p)->first()) {
        DB::table('permissions')->insert(['name'=>$p,'guard_name'=>'web','module'=>'ProductVault','created_by'=>0,'created_at'=>now(),'updated_at'=>now()]);
    }
}
echo "Permissions ready\n";

// 3. Assign to super admin
$sa = DB::table('roles')->where('name','super admin')->first();
if ($sa) {
    foreach (DB::table('permissions')->where('module','ProductVault')->get() as $perm) {
        if (!DB::table('permission_role')->where('permission_id',$perm->id)->where('role_id',$sa->id)->first())
            DB::table('permission_role')->insert(['permission_id'=>$perm->id,'role_id'=>$sa->id]);
    }
    echo "Permissions assigned to super admin\n";
}

// 4. add_on_managers
DB::table('add_on_managers')->where('module','ProductVault')->delete();
DB::table('add_on_managers')->insert(['module'=>'ProductVault','name'=>'ProductVault','monthly_price'=>'0','yearly_price'=>'0','image'=>'','is_enable'=>1,'package_name'=>'product-vault','is_display'=>1,'created_at'=>now(),'updated_at'=>now()]);
echo "add_on_managers registered\n";

// 5. Cache
foreach (glob(__DIR__.'/../../../storage/framework/{cache,sessions,views}/*') as $f) if (is_file($f)) unlink($f);
echo "Cache cleared\n";
echo "\nDone! Try: /product-vault</pre>";
