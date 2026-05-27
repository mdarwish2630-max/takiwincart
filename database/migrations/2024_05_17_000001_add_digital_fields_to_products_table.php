<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // نوع التوصيل الرقمي: file = ملف تحميل، code = كود/PIN يرسل للعميل
            $table->string('digital_type')->default('file')->after('product_type');
            // الكود / الرقم / PIN (لشحن، eSIM)
            $table->text('digital_key')->nullable()->after('digital_type');
            // الحد الأقصى للتحميلات
            $table->integer('max_downloads')->default(5)->after('digital_key');
            // مدة صلاحية التحميل بالأيام (null = بلا انتهاء)
            $table->integer('download_expiry_days')->nullable()->after('max_downloads');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->text('digital_key')->nullable()->after('downloadable_product');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['digital_type', 'digital_key', 'max_downloads', 'download_expiry_days']);
        });
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['digital_key']);
        });
    }
};
