<?php

/**
 * ================================================================
 * AutoDemoDataSeeder - MaxCart / TakiwinCart
 * ================================================================
 * يملأ المتجر الجديد بـ 120 منتج ديمو في 6 تصنيفات
 * يشمل: تصنيفات + منتجات + تاغز + ريفيوز + صور غلاف وهمية
 *
 * الاستخدام:
 *   $seeder = new \App\Services\AutoDemoDataSeeder();
 *   $seeder->run($storeId, $userId);
 *
 * يمكن استدعاؤه من:
 *   1. Registration Controller بعد إنشاء المتجر
 *   2. Store Model Observer (Store::created event)
 *   3. Artisan Command يدوي
 * ================================================================
 */

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AutoDemoDataSeeder
{
    private int $storeId;
    private int $userId;
    private string $lang;
    private array $tagIdMap = [];
    private array $categoryIdMap = [];

    // اسم الثيم الحالي
    private string $themeName;

    // مجلد الرفع الخاص بهالمتجر
    private string $uploadDir;

    // القوالب الثابتة
    private const THEMES = ['greentic', 'stylique', 'techzonix'];

    // أسماء ملفات صور الديمو في الثيم
    private const DEMO_CATEGORY_IMAGES = [
        'template-store'        => 'template-store.png',
        'notion-productivity'  => 'notion-productivity.png',
        'social-media-assets'  => 'social-media-assets.png',
        'ai-prompts'            => 'ai-prompts.png',
        'creative-design-assets'=> 'creative-design-assets.png',
        'code-developer-assets' => 'code-developer-assets.png',
    ];

    private const DEMO_CATEGORY_ICONS = [
        'template-store'        => 'icon-template-store.png',
        'notion-productivity'  => 'icon-notion-productivity.png',
        'social-media-assets'  => 'icon-social-media-assets.png',
        'ai-prompts'            => 'icon-ai-prompts.png',
        'creative-design-assets'=> 'icon-creative-design-assets.png',
        'code-developer-assets' => 'icon-code-developer-assets.png',
    ];

    private const DEMO_PRODUCT_IMAGES = [
        'template-store'        => 'prod-template-store.png',
        'notion-productivity'  => 'prod-notion-productivity.png',
        'social-media-assets'  => 'prod-social-media-assets.png',
        'ai-prompts'            => 'prod-ai-prompts.png',
        'creative-design-assets'=> 'prod-creative-design-assets.png',
        'code-developer-assets' => 'prod-code-developer-assets.png',
    ];

    // ============ أسماء المراجعين الوهمية ============
    private array $reviewers = [
        ['name' => 'أحمد محمد', 'avatar' => 'uploads/profile/reviewer1.png'],
        ['name' => 'سارة علي', 'avatar' => 'uploads/profile/reviewer2.png'],
        ['name' => 'خالد حسن', 'avatar' => 'uploads/profile/reviewer3.png'],
        ['name' => 'نورة سعيد', 'avatar' => 'uploads/profile/reviewer4.png'],
        ['name' => 'عمر فهد', 'avatar' => 'uploads/profile/reviewer5.png'],
        ['name' => 'ريم عبدالله', 'avatar' => 'uploads/profile/reviewer6.png'],
        ['name' => 'محمد يوسف', 'avatar' => 'uploads/profile/reviewer7.png'],
        ['name' => 'ليلى أحمد', 'avatar' => 'uploads/profile/reviewer8.png'],
    ];

    // ============ نصوص المراجعات الوهمية ============
    private array $reviewTexts = [
        ['ar' => 'منتج ممتاز! جودة عالية جداً وأنصح فيه بشدة.', 'en' => 'Excellent product! Very high quality, highly recommend.'],
        ['ar' => 'ما شاء الله، الدفع كان سريع والمنتج يليق بالسعر.', 'en' => 'Instant delivery as described. Great value for the price.'],
        ['ar' => 'استخدمته في مشروعي وكان مفيد جداً. شكراً!', 'en' => 'Used it in my project and it was very helpful. Thanks!'],
        ['ar' => 'تصميم احترافي وسهل الاستخدام. أنصح بالشراء.', 'en' => 'Professional design and easy to use. Definitely worth buying.'],
        ['ar' => 'جودة الملفات ممتازة والشرح وافي.', 'en' => 'Great quality files and well-documented.'],
        ['ar' => 'تقييمي 5 نجوم. منتج يستاهل كل ريال.', 'en' => '5 stars all the way. Worth every penny.'],
        ['ar' => 'كنت محتاج هذا المنتج من زمان. شكراً على التوفير.', 'en' => 'I needed this for a long time. Thanks for providing it!'],
        ['ar' => 'التواصل مع البائع كان ممتاز والمنتج كما هو موصوف.', 'en' => 'Great communication and product matches the description perfectly.'],
    ];

    private array $reviewTitles = [
        ['ar' => 'ممتاز!', 'en' => 'Excellent!'],
        ['ar' => 'منتج رائع', 'en' => 'Great Product'],
        ['ar' => 'يستاهل الشراء', 'en' => 'Worth Buying'],
        ['ar' => 'جودة عالية', 'en' => 'High Quality'],
        ['ar' => 'أنصح فيه', 'en' => 'Highly Recommended'],
        ['ar' => 'مفيد جداً', 'en' => 'Very Useful'],
        ['ar' => 'كنت محتاجه', 'en' => 'Just What I Needed'],
        ['ar' => 'سعره مناسب', 'en' => 'Good Value'],
    ];

    /**
     * تشغيل السييدر
     */
    public function run(int $storeId, int $userId, string $lang = 'ar', ?string $theme = null): void
    {
        $this->storeId  = $storeId;
        $this->userId   = $userId;
        $this->lang     = $lang;

        // نحدد الثيم: من البارامتر أو من جدول stores
        if ($theme) {
            $this->themeName = $theme;
        } else {
            $store = DB::table('stores')->where('id', $storeId)->first();
            $this->themeName = $store->theme_id ?? 'greentic';
        }

        // مجلد رفع هذا المتجر
        $this->uploadDir = 'uploads/' . $storeId;

        DB::beginTransaction();
        try {
            $this->seedCategories();
            $this->seedTags();
            $this->seedProducts();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('AutoDemoDataSeeder failed: ' . $e->getMessage());
            throw $e;
        }
    }

    // ============================================================
    //  0. نسخ صورة ديمو إلى مجلد المتجر وإرجاع المسار الجديد
    // ============================================================
    /**
     * تنسخ صورة ديمو من مجلد الثيم إلى uploads/{store_id}/
     * وتسجّلها في DB كأنها مرفوعة من المستأجر
     *
     * @param string $demoFile  اسم الملف في themes/{theme}/assets/images/{demoFile}
     * @param string $subFolder مجلد فرعي مثل 'categories' أو 'products'
     * @return string المسار النسفي للمخلص: uploads/{store_id}/{subFolder}/{uniqid}.png
     */
    private function copyDemoImage(string $demoFile, string $subFolder = ''): string
    {
        // 1. نبحث في ثيم المتجر أولاً
        $demoSource = base_path("themes/{$this->themeName}/assets/images/{$demoFile}");

        // 1.5 لو الملف يبدأ بـ icon- وما لقيناه، جرب بدون icon-
        if (!File::exists($demoSource) && str_starts_with($demoFile, 'icon-')) {
            $altFile = substr($demoFile, 5); // شيل icon-
            $altSource = base_path("themes/{$this->themeName}/assets/images/{$altFile}");
            if (File::exists($altSource)) {
                $demoSource = $altSource;
                \Log::info("AutoDemoDataSeeder: using alt name '{$altFile}' for icon '{$demoFile}'");
            }
        }

        // 2. لو ما لقيناها، نبحث في باقي الثيمات (fallback)
        if (!File::exists($demoSource)) {
            $found = false;
            foreach (self::THEMES as $fallbackTheme) {
                if ($fallbackTheme === $this->themeName) continue;
                $fallbackPath = base_path("themes/{$fallbackTheme}/assets/images/{$demoFile}");
                if (File::exists($fallbackPath)) {
                    $demoSource = $fallbackPath;
                    \Log::info("AutoDemoDataSeeder: using fallback from {$fallbackTheme} for {$demoFile}");
                    $found = true;
                    break;
                }
                // جرب بدون icon- في الثيم البديل أيضاً
                if (!$found && str_starts_with($demoFile, 'icon-')) {
                    $altFile = substr($demoFile, 5);
                    $altPath = base_path("themes/{$fallbackTheme}/assets/images/{$altFile}");
                    if (File::exists($altPath)) {
                        $demoSource = $altPath;
                        \Log::info("AutoDemoDataSeeder: using alt name '{$altFile}' from {$fallbackTheme}");
                        $found = true;
                        break;
                    }
                }
            }
            if (!$found) {
                \Log::warning("AutoDemoDataSeeder: demo image not found in any theme: {$demoFile}");
                return '';
            }
        }

        // ننشئ مجلد المتجر لو مش موجود
        $targetDir = base_path($this->uploadDir . ($subFolder ? "/{$subFolder}" : ''));
        if (!File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        // اسم فريد عشان ما يتكرر
        $filename = uniqid() . '_' . $demoFile;
        $targetPath = $targetDir . '/' . $filename;

        // نسخ الفعلي
        File::copy($demoSource, $targetPath);

        // نرجع المسار النسفي (زي ما Utility::upload_file ترجعه)
        return $this->uploadDir . ($subFolder ? "/{$subFolder}" : '') . "/{$filename}";
    }

    // ============================================================
    //  1. التصنيفات (6 كاتيجوريز)
    // ============================================================
    private function seedCategories(): void
    {
        $categories = $this->getCategoriesData();

        foreach ($categories as $cat) {
            // نسخ صورة الكاتيغوري (بنر) من الثيم إلى uploads/{store_id}/categories/
            $demoFile = self::DEMO_CATEGORY_IMAGES[$cat['slug']] ?? null;
            $imagePath = $demoFile ? $this->copyDemoImage($demoFile, 'categories') : '';

            // نسخ أيقونة الكاتيغوري من الثيم إلى uploads/{store_id}/categories/
            $iconFile = self::DEMO_CATEGORY_ICONS[$cat['slug']] ?? null;
            $iconPath = $iconFile ? $this->copyDemoImage($iconFile, 'categories') : '';

            $id = DB::table('categories')->insertGetId([
                'name'       => $cat['name'],
                'slug'       => $cat['slug'],
                'image_path' => $imagePath,
                'icon_path'  => $iconPath,
                'parent_id'  => 0,
                'trending'   => 1,
                'status'     => 1,
                'store_id'   => $this->storeId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->categoryIdMap[$cat['slug']] = $id;
        }
    }

    private function getCategoriesData(): array
    {
        return [
            [
                'name' => 'متجرو القوالب | Template Store',
                'slug' => 'template-store',
            ],
            [
                'name' => 'نوتشن والإنتاجية | Notion & Productivity',
                'slug' => 'notion-productivity',
            ],
            [
                'name' => 'أصول السوشيال ميديا | Social Media Assets',
                'slug' => 'social-media-assets',
            ],
            [
                'name' => 'برومبتات الذكاء الاصطناعي | AI Prompts',
                'slug' => 'ai-prompts',
            ],
            [
                'name' => 'أصول التصميم الإبداعي | Creative Design Assets',
                'slug' => 'creative-design-assets',
            ],
            [
                'name' => 'أصول المطورين والأكواد | Code & Developer Assets',
                'slug' => 'code-developer-assets',
            ],
        ];
    }

    // ============================================================
    //  2. التاغز (Tags)
    // ============================================================
    private function seedTags(): void
    {
        $tags = $this->getTagsData();

        foreach ($tags as $tag) {
            $id = DB::table('tags')->insertGetId([
                'name'       => $tag,
                'slug'       => Str::slug($tag),
                'store_id'   => $this->storeId,
                'created_by' => $this->userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->tagIdMap[$tag] = $id;
        }
    }

    private function getTagsData(): array
    {
        return [
            // Template Store
            'html', 'css', 'responsive', 'bootstrap', 'wordpress', 'shopify',
            'landing-page', 'email-template', 'ui-kit', 'website-template',
            // Notion
            'notion', 'productivity', 'crm', 'planning', 'organization',
            'dashboard', 'tracker', 'workflow', 'template', 'management',
            // Social Media
            'instagram', 'youtube', 'tiktok', 'social-media', 'content-creator',
            'story', 'reel', 'thumbnail', 'branding', 'canva',
            // AI Prompts
            'chatgpt', 'midjourney', 'ai', 'prompts', 'stable-diffusion',
            'dall-e', 'ai-art', 'automation', 'machine-learning', 'copywriting',
            // Creative Design
            'fonts', 'icons', 'mockups', 'illustrations', 'textures',
            'graphics', 'vectors', 'svg', 'gradients', 'printable',
            // Code
            'react', 'vue', 'javascript', 'python', 'api',
            'boilerplate', 'component', 'laravel', 'nodejs', 'tailwind',
        ];
    }

    // ============================================================
    //  3. المنتجات (120 منتج)
    // ============================================================
    private function seedProducts(): void
    {
        $allProducts = $this->getAllProductsData();

        foreach ($allProducts as $catSlug => $products) {
            $categoryId = $this->categoryIdMap[$catSlug];

            foreach ($products as $idx => $p) {
                $slug = Str::slug($p['name']);
                // ensure uniqueness
                $existingSlug = $slug;
                $counter = 1;
                while (DB::table('products')->where('slug', $existingSlug)->where('store_id', $this->storeId)->exists()) {
                    $existingSlug = $slug . '-' . $counter++;
                }
                $slug = $existingSlug;

                // Build tag_id string
                $tagIds = [];
                foreach ($p['tags'] as $tagName) {
                    if (isset($this->tagIdMap[$tagName])) {
                        $tagIds[] = $this->tagIdMap[$tagName];
                    }
                }

                $isTrending = ($idx < 3) ? 1 : 0; // first 3 per category are trending

                // نسخ صورة غلاف المنتج من الثيم إلى uploads/{store_id}/products/
                $demoFile = self::DEMO_PRODUCT_IMAGES[$catSlug] ?? null;
                $coverImagePath = $demoFile ? $this->copyDemoImage($demoFile, 'products') : '';

                $productId = DB::table('products')->insertGetId([
                    'name'                  => $p['name'],
                    'slug'                  => $slug,
                    'tag_id'                => implode(',', $tagIds),
                    'category_id'           => $categoryId,
                    'brand_id'              => null,
                    'label_id'              => null,
                    'tax_status'            => 'included',
                    'trending'              => $isTrending,
                    'status'                => 1,
                    'track_stock'           => 0,
                    'stock_order_status'    => null,
                    'price'                 => $p['price'],
                    'sale_price'            => $p['sale_price'] ?? 0,
                    'product_type'          => 'digital',
                    'digital_type'          => $p['digital_type'] ?? 'file',
                    'digital_key'           => $p['digital_key'] ?? null,
                    'max_downloads'         => $p['max_downloads'] ?? 5,
                    'download_expiry_days'  => $p['download_expiry_days'] ?? 30,
                    'product_stock'         => 999,
                    'low_stock_threshold'   => 0,
                    'stock_status'          => 'in_stock',
                    'variant_product'       => 0,
                    'cover_image_path'      => $coverImagePath,
                    'description'           => $p['description'],
                    'detail'                => $p['detail'],
                    'specification'         => $p['specification'],
                    'average_rating'        => $p['avg_rating'] ?? 4.5,
                    'store_id'              => $this->storeId,
                    'created_by'            => $this->userId,
                    'is_active'             => 1,
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);

                // Insert gallery images (placeholders)
                $this->seedProductImages($productId, $catSlug, $idx);

                // Insert reviews
                $this->seedReviews($productId, $categoryId, $p['avg_rating'] ?? 4.5);
            }
        }
    }

    /**
     * عدد صور المعرض ثابت لكل منتج
     */
    private function getGalleryCount(string $catSlug, int $idx): int
    {
        return 2 + (($idx + ord($catSlug[0])) % 3);
    }

    private function seedProductImages(int $productId, string $catSlug, int $idx): void
    {
        $imageCount = $this->getGalleryCount($catSlug, $idx);
        $demoFile = self::DEMO_PRODUCT_IMAGES[$catSlug] ?? null;

        for ($i = 1; $i <= $imageCount; $i++) {
            // كل صورة معرض تنسخ بشكل مستقل باسم فريد
            $imagePath = $demoFile ? $this->copyDemoImage($demoFile, 'products') : '';

            DB::table('product_images')->insert([
                'product_id'  => $productId,
                'image_path'  => $imagePath,
                'image_url'   => null,
                'store_id'    => $this->storeId,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    private function seedReviews(int $productId, int $categoryId, float $avgRating): void
    {
        $numReviews = rand(2, 4);
        for ($i = 0; $i < $numReviews; $i++) {
            $reviewer = $this->reviewers[array_rand($this->reviewers)];
            $text = $this->reviewTexts[array_rand($this->reviewTexts)];
            $title = $this->reviewTitles[array_rand($this->reviewTitles)];
            $rating = min(5, max(3, (int) round($avgRating + (rand(-1, 1) * 0.5))));

            DB::table('testimonials')->insert([
                'category_id' => $categoryId,
                'product_id'  => $productId,
                'rating_no'   => $rating,
                'title'       => $text['title'] ?? ($this->lang === 'ar' ? $title['ar'] : $title['en']),
                'description' => $this->lang === 'ar' ? $text['ar'] : $text['en'],
                'avatar'      => $reviewer['avatar'],
                'username'    => $reviewer['name'],
                'status'      => 1,
                'store_id'    => $this->storeId,
                'user_id'     => $this->userId,
                'created_at'  => now()->subDays(rand(1, 60)),
                'updated_at'  => now(),
            ]);
        }
    }

    // ============================================================
    //  بيانات المنتجات الكاملة - 120 منتج
    // ============================================================
    private function getAllProductsData(): array
    {
        return [
            // ==============================
            // 1. TEMPLATE STORE (20 منتج)
            // ==============================
            'template-store' => $this->getTemplateStoreProducts(),

            // ==============================
            // 2. NOTION & PRODUCTIVITY (20 منتج)
            // ==============================
            'notion-productivity' => $this->getNotionProducts(),

            // ==============================
            // 3. SOCIAL MEDIA ASSETS (20 منتج)
            // ==============================
            'social-media-assets' => $this->getSocialMediaProducts(),

            // ==============================
            // 4. AI PROMPTS (20 منتج)
            // ==============================
            'ai-prompts' => $this->getAIPromptProducts(),

            // ==============================
            // 5. CREATIVE DESIGN ASSETS (20 منتج)
            // ==============================
            'creative-design-assets' => $this->getCreativeDesignProducts(),

            // ==============================
            // 6. CODE & DEVELOPER ASSETS (20 منتج)
            // ==============================
            'code-developer-assets' => $this->getCodeDevProducts(),
        ];
    }

    // ==============================
    // 1. TEMPLATE STORE - 20 منتج
    // ==============================
    private function getTemplateStoreProducts(): array
    {
        return [
            [
                'name' => 'قالب متجر إلكتروني احترافي | Professional E-Commerce Template',
                'price' => 29.99, 'sale_price' => 19.99,
                'description' => '<p>قالب متجر إلكتروني متكامل وجاهز للإطلاق مباشرة. تصميم عصري ومتجاوب مع جميع الأجهزة. يتضمن صفحة رئيسية، قائمة منتجات، صفحة تفاصيل المنتج، سلة التسوق، وصفحة الدفع.</p><p>A complete, ready-to-launch e-commerce website template. Modern responsive design that works on all devices. Includes homepage, product listing, product detail, cart, and checkout pages.</p>',
                'detail' => '<p>تقنيات: HTML5, CSS3, JavaScript, Bootstrap 5 | Tech: HTML5, CSS3, JavaScript, Bootstrap 5</p>',
                'specification' => '<p>الصيغة: ZIP | الصفحات: 12 | متجاوب: نعم | التوثيق: متضمن | الحجم: 4.2 MB</p>',
                'tags' => ['html', 'css', 'responsive', 'bootstrap', 'website-template'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'قالب ووردبريس للشركات | Corporate WordPress Theme',
                'price' => 39.99, 'sale_price' => 29.99,
                'description' => '<p>قالب ووردبريس احترافي للشركات والمؤسسات. تصميم أنيق مع دعم كامل للعربية RTL. يتضمن أكثر من 10 صفحات مخصصة وأكثر من 20 عنصر واجهة.</p><p>Professional WordPress theme for businesses and corporations. Elegant design with full RTL support. Includes 10+ custom pages and 20+ UI components.</p>',
                'detail' => '<p>CMS: WordPress 6.x | Builder: Elementor | دعم RTL: نعم | RTL Support: Yes</p>',
                'specification' => '<p>الصيغة: ZIP | الصفحات: 15 | المكونات: 20+ | الحجم: 12.5 MB</p>',
                'tags' => ['wordpress', 'responsive', 'ui-kit', 'website-template'],
                'avg_rating' => 4.8,
            ],
            [
                'name' => 'قالب صفحة هبوط للتسويق | Marketing Landing Page',
                'price' => 19.99, 'sale_price' => 14.99,
                'description' => '<p>قالب صفحة هبوط احترافي مخصص للحملات التسويقية. معد لزيادة معدل التحويل مع أقسام للشهادات والأسئلة الشائعة وCTA.</p><p>Professional landing page template optimized for marketing campaigns. Designed to maximize conversion rates with testimonials, FAQ, and CTA sections.</p>',
                'detail' => '<p>التقنيات: HTML5, CSS3, JavaScript | Tech: HTML5, CSS3, JavaScript</p>',
                'specification' => '<p>الصيغة: ZIP | الأقسام: 8 | متجاوب: نعم | الحجم: 2.1 MB</p>',
                'tags' => ['html', 'css', 'landing-page', 'responsive', 'bootstrap'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'قالب متجر Shopify للأزياء | Shopify Fashion Store',
                'price' => 49.99, 'sale_price' => 0,
                'description' => '<p>قالب Shopify متكامل لمتاجر الأزياء والملابس. تصميم عصري مع دعم كامل للعربية. يتضمن تصفية المنتجات، قائمة المفضلة، ومراجعات العملاء.</p><p>Complete Shopify theme for fashion and clothing stores. Modern design with full Arabic support. Includes product filtering, wishlist, and customer reviews.</p>',
                'detail' => '<p>المنصة: Shopify | القسم: أزياء وملابس | RTL: مدعوم</p>',
                'specification' => '<p>الصيغة: ZIP | التوافق: Shopify 2.0+ | الحجم: 8.7 MB</p>',
                'tags' => ['shopify', 'responsive', 'website-template'],
                'avg_rating' => 4.6,
            ],
            [
                'name' => 'قالب إيميل تسويقي (5 تصميمات) | Email Marketing Templates',
                'price' => 14.99, 'sale_price' => 9.99,
                'description' => '<p>مجموعة من 5 قوالب إيميل تسويقي احترافية. متوافقة مع جميع برامج البريد الإلكتروني الرئيسية. تشمل: ترحيب، عرض خاص، نشرة أخبار، تأكيد طلب، وإعادة تفاعل.</p><p>A set of 5 professional marketing email templates. Compatible with all major email clients. Includes: welcome, special offer, newsletter, order confirmation, and re-engagement.</p>',
                'detail' => '<p>الصيغة: HTML | المتوافق: Gmail, Outlook, Apple Mail, Yahoo</p>',
                'specification' => '<p>التصاميم: 5 | المتوافقية: 100% | الحجم: 1.8 MB</p>',
                'tags' => ['email-template', 'html', 'css', 'responsive'],
                'avg_rating' => 4.4,
            ],
            [
                'name' => 'قالب سيرة ذاتية احترافية | Professional Resume CV Template',
                'price' => 9.99, 'sale_price' => 0,
                'description' => '<p>قالب سيرة ذاتية (CV) عصري واحترافي بتصميمين مختلفين. جاهز للتعديل على Word وPhotoshop. مناسب لجميع المجالات.</p><p>Modern and professional CV resume template with 2 different designs. Ready to edit in Word and Photoshop. Suitable for all industries.</p>',
                'detail' => '<p>البرامج: Microsoft Word, Adobe Photoshop | الصفحات: 2+1</p>',
                'specification' => '<p>الصيغة: DOCX + PSD | التصاميم: 2 | الحجم: 3.5 MB</p>',
                'tags' => ['html', 'responsive', 'website-template'],
                'avg_rating' => 4.3,
            ],
            [
                'name' => 'قالب لوحة تحكم إدارية | Admin Dashboard Template',
                'price' => 35.99, 'sale_price' => 24.99,
                'description' => '<p>قالب لوحة تحكم إدارية متكامل مع رسوم بيانية وجداول وإشعارات. مبني على Bootstrap 5 مع مكونات جاهزة للاستخدام.</p><p>Complete admin dashboard template with charts, tables, and notifications. Built on Bootstrap 5 with ready-to-use components.</p>',
                'detail' => '<p>التقنيات: HTML5, CSS3, JS, Bootstrap 5, Chart.js | Pages: 25+</p>',
                'specification' => '<p>الصيغة: ZIP | المكونات: 50+ | الرسوم البيانية: 6 أنواع | الحجم: 6.3 MB</p>',
                'tags' => ['html', 'css', 'bootstrap', 'ui-kit', 'responsive'],
                'avg_rating' => 4.6,
            ],
            [
                'name' => 'قالب بورتفوليو شخصي | Personal Portfolio Template',
                'price' => 15.99, 'sale_price' => 0,
                'description' => '<p>قالب بورتفوليو شخصي أنيق لعرض أعمالك ومشاريعك. يتضمن قسم المشاريع، حولي، المهارات، والتواصل. تصميم متجاوب مع أنيميشن سلس.</p><p>Elegant personal portfolio template to showcase your work and projects. Includes projects section, about, skills, and contact. Responsive design with smooth animations.</p>',
                'detail' => '<p>التقنيات: HTML5, CSS3, JavaScript, AOS Animations</p>',
                'specification' => '<p>الصيغة: ZIP | الأقسام: 6 | الأنيميشن: نعم | الحجم: 2.8 MB</p>',
                'tags' => ['html', 'css', 'responsive', 'landing-page', 'website-template'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'قالب مدونة متعدد الأغراض | Multi-Purpose Blog Template',
                'price' => 19.99, 'sale_price' => 12.99,
                'description' => '<p>قالب مدونة احترافي يدعم RTL بالكامل. تصميم نظيف يركز على المحتوى مع خيارات تخصيص متعددة وأوضاع داكنة/فاتحة.</p><p>Professional blog template with full RTL support. Clean content-focused design with multiple customization options and dark/light modes.</p>',
                'detail' => '<p>التقنيات: HTML5, CSS3, JavaScript | RTL: مدعوم بالكامل</p>',
                'specification' => '<p>الصيغة: ZIP | الصفحات: 8 | الأوضاع: داكن + فاتح | الحجم: 3.2 MB</p>',
                'tags' => ['html', 'css', 'responsive', 'website-template', 'bootstrap'],
                'avg_rating' => 4.4,
            ],
            [
                'name' => 'قالب متجر سوق التطبيقات | App Marketplace Template',
                'price' => 34.99, 'sale_price' => 0,
                'description' => '<p>قالب متكامل لسوق التطبيقات والبرمجيات. يتضمن صفحة التطبيق، التقييمات، مقارنة الأسعار، ولوحة تحكم المطور.</p><p>Complete template for software and app marketplace. Includes app page, reviews, pricing comparison, and developer dashboard.</p>',
                'detail' => '<p>التقنيات: HTML5, CSS3, JavaScript, Bootstrap 5</p>',
                'specification' => '<p>الصيغة: ZIP | الصفحات: 18 | الحجم: 5.9 MB</p>',
                'tags' => ['html', 'css', 'bootstrap', 'ui-kit', 'responsive'],
                'avg_rating' => 4.3,
            ],
            [
                'name' => 'قالب وكالة سفر وسياحة | Travel Agency Template',
                'price' => 27.99, 'sale_price' => 19.99,
                'description' => '<p>قالب وكالة سفر وسياحة متكامل مع نظام حجز الوهمي. تصميم جذاب مع معرض الصور وجداول الأسعار وأقسام الوجهات.</p><p>Complete travel agency template with booking system UI. Attractive design with photo gallery, pricing tables, and destination sections.</p>',
                'detail' => '<p>التقنيات: HTML5, CSS3, JavaScript, Bootstrap 5, Owl Carousel</p>',
                'specification' => '<p>الصيغة: ZIP | الصفحات: 10 | الحجم: 7.1 MB</p>',
                'tags' => ['html', 'css', 'responsive', 'landing-page', 'bootstrap'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'قالب مطعم وكافيه | Restaurant & Cafe Template',
                'price' => 22.99, 'sale_price' => 0,
                'description' => '<p>قالب متجر متكامل للمطاعم والمقاهي. يتضمن قائمة الطعام، نظام حجز الطاولات، معرض الصور، وتقييمات الزبائن.</p><p>Complete template for restaurants and cafes. Includes food menu, table reservation system, photo gallery, and customer reviews.</p>',
                'detail' => '<p>التقنيات: HTML5, CSS3, JavaScript, Bootstrap 5</p>',
                'specification' => '<p>الصيغة: ZIP | الصفحات: 9 | الحجم: 4.5 MB</p>',
                'tags' => ['html', 'css', 'responsive', 'website-template', 'bootstrap'],
                'avg_rating' => 4.6,
            ],
            [
                'name' => 'قالب صفحة قادم قريباً | Coming Soon Template',
                'price' => 7.99, 'sale_price' => 0,
                'description' => '<p>قالب صفحة "قادم قريباً" أنيق مع عداد تنازلي، نموذج اشتراك، ووسائل التواصل. 3 تصاميم مختلفة.</p><p>Elegant "Coming Soon" page template with countdown timer, subscription form, and social links. 3 different designs included.</p>',
                'detail' => '<p>التقنيات: HTML5, CSS3, JavaScript | التصاميم: 3</p>',
                'specification' => '<p>الصيغة: ZIP | التصاميم: 3 | الحجم: 1.2 MB</p>',
                'tags' => ['html', 'css', 'landing-page', 'responsive'],
                'avg_rating' => 4.2,
            ],
            [
                'name' => 'قالب عروض أسعار | Invoice & Quote Template Pack',
                'price' => 12.99, 'sale_price' => 8.99,
                'description' => '<p>مجموعة من 5 قوالب فواتير وعروض أسعار احترافية. بصيغ Excel وPDF قابلة للتعديل. تشمل فاتورة ضريبية، عرض سعر، وإيصال.</p><p>A pack of 5 professional invoice and quote templates. Editable in Excel and PDF formats. Includes tax invoice, price quote, and receipt.</p>',
                'detail' => '<p>الصيغ: XLSX, PDF | القوالب: 5 | قابلة للتعديل: نعم</p>',
                'specification' => '<p>الصيغة: ZIP | الملفات: 10 (5 XLSX + 5 PDF) | الحجم: 2.0 MB</p>',
                'tags' => ['email-template', 'website-template'],
                'avg_rating' => 4.3,
            ],
            [
                'name' => 'قالب متجر متعدد البائعين | Multi-Vendor Marketplace',
                'price' => 59.99, 'sale_price' => 44.99,
                'description' => '<p>قالب متجر متعدد البائعين متكامل مع لوحة تحكم البائع وصفحة المتجر الشخصية. مناسب لمنصات مثل Amazon وeBay.</p><p>Complete multi-vendor marketplace template with vendor dashboard and personal store page. Suitable for platforms like Amazon and eBay.</p>',
                'detail' => '<p>التقنيات: HTML5, CSS3, JS, Bootstrap 5 | Pages: 20+</p>',
                'specification' => '<p>الصيغة: ZIP | الصفحات: 22 | الحجم: 9.8 MB</p>',
                'tags' => ['html', 'css', 'bootstrap', 'responsive', 'website-template'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'قالب تقارير سنوية | Annual Report Template',
                'price' => 24.99, 'sale_price' => 0,
                'description' => '<p>قالب تقرير سنوي احترافي بتصميم عصري. 24 صفحة جاهزة مع رسوم بيانية وجداول. قابل للتعديل على InDesign وPowerPoint.</p><p>Professional annual report template with modern design. 24 ready-made pages with charts and tables. Editable in InDesign and PowerPoint.</p>',
                'detail' => '<p>البرامج: Adobe InDesign, Microsoft PowerPoint | Pages: 24</p>',
                'specification' => '<p>الصيغة: INDD + PPTX | الصفحات: 24 | الحجم: 15.3 MB</p>',
                'tags' => ['website-template', 'ui-kit'],
                'avg_rating' => 4.4,
            ],
            [
                'name' => 'قالب متجر NFT و crypto | NFT & Crypto Marketplace',
                'price' => 39.99, 'sale_price' => 29.99,
                'description' => '<p>قالب متجر NFT ومنصة تداول العملات الرقمية. تصميم داكن عصري مع دعم محفظة MetaMask وأقسام المزادات.</p><p>NFT store and crypto trading platform template. Dark modern design with MetaMask wallet support and auction sections.</p>',
                'detail' => '<p>التقنيات: HTML5, CSS3, JavaScript, Web3.js</p>',
                'specification' => '<p>الصيغة: ZIP | الصفحات: 14 | الوضع: داكن | الحجم: 5.4 MB</p>',
                'tags' => ['html', 'css', 'responsive', 'website-template'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'قالب عقارات احترافي | Real Estate Template',
                'price' => 32.99, 'sale_price' => 0,
                'description' => '<p>قالب عقارات متكامل مع بحث متقدم وخريطة تفاعلية. يتضمن صفحات العقارات، الوكلاء، ونموذج الاتصال.</p><p>Complete real estate template with advanced search and interactive map. Includes property pages, agents, and contact form.</p>',
                'detail' => '<p>التقنيات: HTML5, CSS3, JS, Bootstrap 5, Leaflet Maps</p>',
                'specification' => '<p>الصيغة: ZIP | الصفحات: 12 | الخريطة: نعم | الحجم: 6.7 MB</p>',
                'tags' => ['html', 'css', 'responsive', 'bootstrap', 'website-template'],
                'avg_rating' => 4.6,
            ],
            [
                'name' => 'قالب عرض تقديمي أعمال | Business Pitch Deck',
                'price' => 16.99, 'sale_price' => 11.99,
                'description' => '<p>قالب عرض تقديمي احترافي للشركات الناشئة. 30 شريحة جاهزة مع رسوم بيانية وأيقونات. قابل للتعديل على PowerPoint وGoogle Slides.</p><p>Professional pitch deck template for startups. 30 ready-made slides with charts and icons. Editable in PowerPoint and Google Slides.</p>',
                'detail' => '<p>البرامج: PowerPoint, Google Slides | الشرائح: 30</p>',
                'specification' => '<p>الصيغة: PPTX | الشرائح: 30 | الحجم: 8.2 MB</p>',
                'tags' => ['website-template', 'ui-kit'],
                'avg_rating' => 4.4,
            ],
            [
                'name' => 'قالب متجر ألعاب فيديو | Gaming Store Template',
                'price' => 29.99, 'sale_price' => 0,
                'description' => '<p>قالب متجر ألعاب فيديو عصري مع تصميم داكن جذاب. يتضمن صفحات الألعاب، العروض، ومراجعات اللاعبين.</p><p>Modern video game store template with attractive dark design. Includes game pages, deals, and player reviews.</p>',
                'detail' => '<p>التقنيات: HTML5, CSS3, JavaScript, Bootstrap 5</p>',
                'specification' => '<p>الصيغة: ZIP | الصفحات: 11 | الحجم: 4.8 MB</p>',
                'tags' => ['html', 'css', 'responsive', 'bootstrap', 'website-template'],
                'avg_rating' => 4.5,
            ],
        ];
    }

    // ==============================
    // 2. NOTION & PRODUCTIVITY - 20 منتج
    // ==============================
    private function getNotionProducts(): array
    {
        return [
            [
                'name' => 'قالب إدارة علاقات العملاء CRM | CRM Dashboard',
                'price' => 19.99, 'sale_price' => 14.99,
                'description' => '<p>قالب نوتشن متكامل لإدارة علاقات العملاء مع لوحة تحكم، تتبع الصفقات، وجداول العملاء. يشمل أنظمة تنبيه ذكية وتقارير شهرية.</p><p>Complete Notion template for CRM with dashboard, deal tracking, and client tables. Includes smart alert systems and monthly reports.</p>',
                'detail' => '<p>المنصة: Notion | الأقسام: 8 | القوالب الفرعية: 15+</p>',
                'specification' => '<p>الصيغة: Notion Template | الأقسام: 8 | الحجم: مشاركة رابط</p>',
                'tags' => ['notion', 'crm', 'dashboard', 'tracker', 'management'],
                'avg_rating' => 4.8,
            ],
            [
                'name' => 'قالب إدارة المشاريع | Project Management Hub',
                'price' => 24.99, 'sale_price' => 0,
                'description' => '<p>نظام إدارة مشاريع شامل على نوتشن مع لوحة كانبان، مخطط جانت، تتبع المهام، وإدارة الفريق. مناسب للفرق الصغيرة والمتوسطة.</p><p>Comprehensive project management system on Notion with Kanban board, Gantt chart, task tracking, and team management. Suitable for small and medium teams.</p>',
                'detail' => '<p>المنصة: Notion | الأنظمة: Kanban, Gantt, Sprint</p>',
                'specification' => '<p>الصيغة: Notion Template | الأنظمة: 5 | الحجم: مشاركة رابط</p>',
                'tags' => ['notion', 'management', 'planning', 'tracker', 'workflow'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'قالب تتبع العادات اليومية | Daily Habit Tracker',
                'price' => 9.99, 'sale_price' => 0,
                'description' => '<p>قالب نوتشن لتتبع العادات اليومية والأسبوعية مع رسوم بيانية للتقدم وإحصائيات شهرية. يشمل تتبع اللياقة، القراءة، والمذاكرة.</p><p>Notion template for tracking daily and weekly habits with progress charts and monthly statistics. Includes fitness, reading, and study tracking.</p>',
                'detail' => '<p>المنصة: Notion | الفترة: يومي + أسبوعي + شهري</p>',
                'specification' => '<p>الصيغة: Notion Template | الأقسام: 4 | الحجم: مشاركة رابط</p>',
                'tags' => ['notion', 'tracker', 'productivity', 'organization'],
                'avg_rating' => 4.6,
            ],
            [
                'name' => 'قالب تخطيط الميزانية الشهرية | Monthly Budget Planner',
                'price' => 12.99, 'sale_price' => 8.99,
                'description' => '<p>نظام متكامل لإدارة الميزانية الشخصية والادخار. يتضمن تتبع المصروفات، أهداف الادخار، وتقارير شهرية مع رسوم بيانية.</p><p>Complete system for personal budget and savings management. Includes expense tracking, savings goals, and monthly reports with charts.</p>',
                'detail' => '<p>المنصة: Notion | التقارير: شهرية + سنوية</p>',
                'specification' => '<p>الصيغة: Notion Template | الأقسام: 6 | الحجم: مشاركة رابط</p>',
                'tags' => ['notion', 'planning', 'tracker', 'organization', 'dashboard'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'قالب تخطيط الوجبات | Meal Planning System',
                'price' => 11.99, 'sale_price' => 0,
                'description' => '<p>نظام تخطيط وجبات أسبوعي مع قائمة المكونات والتسوق. يشمل وصفات صحية، تتبع السعرات، وجداول تنويع الوجبات.</p><p>Weekly meal planning system with ingredient and shopping lists. Includes healthy recipes, calorie tracking, and meal variety schedules.</p>',
                'detail' => '<p>المنصة: Notion | الوصفات: 50+ | الفترة: أسبوعي</p>',
                'specification' => '<p>الصيغة: Notion Template | الأقسام: 5 | الحجم: مشاركة رابط</p>',
                'tags' => ['notion', 'planning', 'organization', 'tracker'],
                'avg_rating' => 4.4,
            ],
            [
                'name' => 'قالب تقويم المحتوى | Content Calendar System',
                'price' => 16.99, 'sale_price' => 12.99,
                'description' => '<p>نظام تقويم محتوى شامل لمنصات التواصل الاجتماعي. يتضمن جدول النشر، أفكار المحتوى، وتتبع الأداء لكل منصة.</p><p>Comprehensive content calendar system for social media platforms. Includes posting schedule, content ideas, and performance tracking for each platform.</p>',
                'detail' => '<p>المنصة: Notion | المنصات: Instagram, Twitter, YouTube, TikTok, LinkedIn</p>',
                'specification' => '<p>الصيغة: Notion Template | المنصات: 5 | الحجم: مشاركة رابط</p>',
                'tags' => ['notion', 'planning', 'workflow', 'management', 'social-media'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'قالب تتبع الوظائف | Job Application Tracker',
                'price' => 14.99, 'sale_price' => 0,
                'description' => '<p>قالب نوتشن لتتبع طلبات التوظيف مع حالة كل طلب، تفاصيل المقابلات، ملاحظات، وإحصائيات النجاح.</p><p>Notion template for tracking job applications with status, interview details, notes, and success statistics.</p>',
                'detail' => '<p>المنصة: Notion | التتبع: حالة + مقابلات + ملاحظات</p>',
                'specification' => '<p>الصيغة: Notion Template | الأقسام: 4 | الحجم: مشاركة رابط</p>',
                'tags' => ['notion', 'tracker', 'organization', 'template'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'قالب قائمة القراءة | Reading List & Book Tracker',
                'price' => 8.99, 'sale_price' => 0,
                'description' => '<p>نظام متابعة القراءة مع قوائم: أريد قراءتها، أقرأ حالياً، انتهيت. يتضمن مراجعات، تقييمات، وإحصائيات القراءة.</p><p>Reading tracking system with lists: want to read, currently reading, finished. Includes reviews, ratings, and reading statistics.</p>',
                'detail' => '<p>المنصة: Notion | القوائم: 3 + مراجعات</p>',
                'specification' => '<p>الصيغة: Notion Template | الأقسام: 5 | الحجم: مشاركة رابط</p>',
                'tags' => ['notion', 'tracker', 'organization', 'template'],
                'avg_rating' => 4.3,
            ],
            [
                'name' => 'قالب تخطيط السفر | Travel Planner System',
                'price' => 13.99, 'sale_price' => 9.99,
                'description' => '<p>نظام تخطيط سفر متكامل مع جداول الرحلات، الحجوزات، الميزانية، وقائمة التجهيزات. يشمل ملاحظات ومكان لتوثيق الذكريات.</p><p>Complete travel planning system with itinerary tables, bookings, budget, and packing list. Includes notes and a place to document memories.</p>',
                'detail' => '<p>المنصة: Notion | الأقسام: 7 | الميزانية: نعم</p>',
                'specification' => '<p>الصيغة: Notion Template | الأقسام: 7 | الحجم: مشاركة رابط</p>',
                'tags' => ['notion', 'planning', 'organization', 'tracker'],
                'avg_rating' => 4.6,
            ],
            [
                'name' => 'قالب تتبع اللياقة البدنية | Fitness Tracker Pro',
                'price' => 11.99, 'sale_price' => 0,
                'description' => '<p>نظام متكامل لتتبع التمارين الرياضية مع جداول التدريب، تتبع الوزن، الأهداف، وخطط التغذية. يشمل أنماط تمارين متعددة.</p><p>Complete exercise tracking system with training tables, weight tracking, goals, and nutrition plans. Includes multiple exercise patterns.</p>',
                'detail' => '<p>المنصة: Notion | الأنماط: بناء عضلات + حرق + لياقة عامة</p>',
                'specification' => '<p>الصيغة: Notion Template | الأقسام: 6 | الحجم: مشاركة رابط</p>',
                'tags' => ['notion', 'tracker', 'planning', 'dashboard'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'قالب دفتر اليوميات | Personal Journal System',
                'price' => 7.99, 'sale_price' => 0,
                'description' => '<p>قالب نوتشن ليوميات شخصية مع كتابة يومية، تأملات، أهداف شهرية، وصندوق شكر. تصميم بسيط وهادئ.</p><p>Notion template for personal journaling with daily writing, reflections, monthly goals, and gratitude box. Simple and calm design.</p>',
                'detail' => '<p>المنصة: Notion | الفترة: يومي + شهري</p>',
                'specification' => '<p>الصيغة: Notion Template | الأقسام: 4 | الحجم: مشاركة رابط</p>',
                'tags' => ['notion', 'productivity', 'organization', 'template'],
                'avg_rating' => 4.4,
            ],
            [
                'name' => 'قالب إدارة الاشتراكات | Subscription Tracker',
                'price' => 9.99, 'sale_price' => 0,
                'description' => '<p>نظام لتتبع جميع اشتراكاتك المدفوعة مع تكلفة شهرية وسنوية، تواريخ التجديد، وإشعارات التنبيه.</p><p>System to track all your paid subscriptions with monthly and yearly cost, renewal dates, and alert notifications.</p>',
                'detail' => '<p>المنصة: Notion | التتبع: شهري + سنوي + تنبيهات</p>',
                'specification' => '<p>الصيغة: Notion Template | الأقسام: 3 | الحجم: مشاركة رابط</p>',
                'tags' => ['notion', 'tracker', 'planning', 'organization'],
                'avg_rating' => 4.3,
            ],
            [
                'name' => 'قالب إدارة الفريق | Team Collaboration Hub',
                'price' => 22.99, 'sale_price' => 16.99,
                'description' => '<p>نظام تعاون فرق شامل مع توزيع المهام، جدول الاجتماعات، مشاركة الملفات، وتتبع التقدم. مناسب للفرق عن بُعد.</p><p>Comprehensive team collaboration system with task assignment, meeting schedule, file sharing, and progress tracking. Suitable for remote teams.</p>',
                'detail' => '<p>المنصة: Notion | الميزات: مهام + اجتماعات + ملفات</p>',
                'specification' => '<p>الصيغة: Notion Template | الأقسام: 8 | الحجم: مشاركة رابط</p>',
                'tags' => ['notion', 'management', 'workflow', 'dashboard', 'planning'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'قالب خطة عمل المشروع | Business Plan Template',
                'price' => 18.99, 'sale_price' => 0,
                'description' => '<p>قالب خطة عمل احترافي مع تحليل السوق، استراتيجية التسويق، التوقعات المالية، وتخطيط العمليات. مناسب للمشاريع الناشئة.</p><p>Professional business plan template with market analysis, marketing strategy, financial projections, and operations planning. Suitable for startups.</p>',
                'detail' => '<p>المنصة: Notion | الأقسام: 12 | الجداول: 8+</p>',
                'specification' => '<p>الصيغة: Notion Template | الأقسام: 12 | الحجم: مشاركة رابط</p>',
                'tags' => ['notion', 'planning', 'management', 'template'],
                'avg_rating' => 4.6,
            ],
            [
                'name' => 'قالب نظام الدراسة | Study Management System',
                'price' => 12.99, 'sale_price' => 9.99,
                'description' => '<p>نظام إدارة دراسة شامل مع جدول المواد، تتبع الواجبات، ملاحظات المحاضرات، وتحضير الاختبارات. يشمل تقنيات الدراسة الفعالة.</p><p>Complete study management system with subject schedule, homework tracking, lecture notes, and exam preparation. Includes effective study techniques.</p>',
                'detail' => '<p>المنصة: Notion | الأقسام: 7 | الفصل: كامل</p>',
                'specification' => '<p>الصيغة: Notion Template | الأقسام: 7 | الحجم: مشاركة رابط</p>',
                'tags' => ['notion', 'planning', 'tracker', 'organization', 'management'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'قالب إدارة المخزون | Inventory Management',
                'price' => 19.99, 'sale_price' => 0,
                'description' => '<p>نظام إدارة مخزون على نوتشن مع تتبع المنتجات، تنبيهات نقص المخزون، وقيمة المخزون الإجمالية. مناسب للمتاجر الصغيرة.</p><p>Notion-based inventory management system with product tracking, low stock alerts, and total inventory value. Suitable for small stores.</p>',
                'detail' => '<p>المنصة: Notion | التنبيهات: نعم | التقارير: شهرية</p>',
                'specification' => '<p>الصيغة: Notion Template | الأقسام: 5 | الحجم: مشاركة رابط</p>',
                'tags' => ['notion', 'management', 'dashboard', 'tracker'],
                'avg_rating' => 4.4,
            ],
            [
                'name' => 'قالب أهداف OKR | OKR Goal Setting System',
                'price' => 14.99, 'sale_price' => 0,
                'description' => '<p>نظام تحديد الأهداف OKR على نوتشن مع تتبع ربع سنوي، مؤشرات الأداء، وتقييم التقدم. مناسب للأفراد والفرق.</p><p>OKR goal setting system on Notion with quarterly tracking, KPIs, and progress evaluation. Suitable for individuals and teams.</p>',
                'detail' => '<p>المنصة: Notion | الفترة: ربع سنوي | المؤشرات: نعم</p>',
                'specification' => '<p>الصيغة: Notion Template | الأقسام: 4 | الحجم: مشاركة رابط</p>',
                'tags' => ['notion', 'planning', 'tracker', 'management'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'قالب إدارة الحصص المالية | Financial Portfolio Tracker',
                'price' => 16.99, 'sale_price' => 12.99,
                'description' => '<p>نظام تتبع المحفظة الاستثمارية مع سجل الصفقات، توزيع الأصول، الأرباح والخسائر، وتقارير الأداء.</p><p>Investment portfolio tracking system with trade log, asset allocation, profit/loss, and performance reports.</p>',
                'detail' => '<p>المنصة: Notion | التقارير: شهرية + سنوية</p>',
                'specification' => '<p>الصيغة: Notion Template | الأقسام: 5 | الحجم: مشاركة رابط</p>',
                'tags' => ['notion', 'dashboard', 'tracker', 'management'],
                'avg_rating' => 4.3,
            ],
            [
                'name' => 'قالب بدء العمل الحر | Freelancer Starter Kit',
                'price' => 21.99, 'sale_price' => 0,
                'description' => '<p>مجموعة متكاملة للمستقلين الجدد مع تتبع المشاريع، فواتير العملاء، إدارة الوقت، وتخطيط الدخل. كل ما تحتاجه في مكان واحد.</p><p>Complete starter kit for new freelancers with project tracking, client invoices, time management, and income planning. Everything you need in one place.</p>',
                'detail' => '<p>المنصة: Notion | الأقسام: 10 | الفواتير: نعم</p>',
                'specification' => '<p>الصيغة: Notion Template | الأقسام: 10 | الحجم: مشاركة رابط</p>',
                'tags' => ['notion', 'management', 'planning', 'dashboard', 'template'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'قالب نظام عقود العملاء | Client Contracts Hub',
                'price' => 17.99, 'sale_price' => 0,
                'description' => '<p>نظام إدارة عقود العملاء مع تتبع صلاحية العقود، شروط الدفع، ملاحظات المشاريع، وتنبيهات التجديد.</p><p>Client contracts management system with contract expiry tracking, payment terms, project notes, and renewal alerts.</p>',
                'detail' => '<p>المنصة: Notion | التنبيهات: نعم | العقود: غير محدود</p>',
                'specification' => '<p>الصيغة: Notion Template | الأقسام: 4 | الحجم: مشاركة رابط</p>',
                'tags' => ['notion', 'crm', 'management', 'tracker', 'template'],
                'avg_rating' => 4.4,
            ],
        ];
    }

    // ==============================
    // 3. SOCIAL MEDIA ASSETS - 20 منتج
    // ==============================
    private function getSocialMediaProducts(): array
    {
        return [
            [
                'name' => 'باقة بوستات انستقرام (100 تصميم) | Instagram Posts Pack',
                'price' => 19.99, 'sale_price' => 14.99,
                'description' => '<p>مجموعة من 100 تصميم بوست انستقرام قابل للتعديل على Canva. تشمل منشورات تسويقية، اقتباسات، عروض، ومنشورات تفاعلية. ألوان وأحجام متنوعة.</p><p>A collection of 100 editable Instagram post designs on Canva. Includes marketing posts, quotes, offers, and interactive posts. Various colors and sizes.</p>',
                'detail' => '<p>الأداة: Canva | التصاميم: 100 | القابلية: قابل للتعديل بالكامل</p>',
                'specification' => '<p>الصيغة: Canva Link | التصاميم: 100 | الحجم: تحميل مباشر</p>',
                'tags' => ['instagram', 'social-media', 'content-creator', 'canva', 'branding'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'باقة ستوري انستقرام (50 تصميم) | Instagram Stories Pack',
                'price' => 14.99, 'sale_price' => 0,
                'description' => '<p>50 قالب ستوري انستقرام احترافي قابل للتعديل. تشمل ستوريات تسويقية، استبيانات، ألعاب تفاعلية، وستوريات تعريفية.</p><p>50 professional editable Instagram story templates. Includes marketing stories, polls, interactive games, and introductory stories.</p>',
                'detail' => '<p>الأداة: Canva | التصاميم: 50 | الأبعاد: 1080x1920</p>',
                'specification' => '<p>الصيغة: Canva Link | التصاميم: 50 | الحجم: تحميل مباشر</p>',
                'tags' => ['instagram', 'story', 'social-media', 'content-creator', 'canva'],
                'avg_rating' => 4.6,
            ],
            [
                'name' => 'باقة صور مصغرة يوتيوب (30 تصميم) | YouTube Thumbnail Pack',
                'price' => 16.99, 'sale_price' => 11.99,
                'description' => '<p>30 قالب صورة مصغرة لليوتيوب عالي الجودة. تصميمات جذابة تزيد نسبة النقر (CTR). تشمل قوالب تعليمية، ترفيهية، وتقنية.</p><p>30 high-quality YouTube thumbnail templates. Attractive designs that increase click-through rate (CTR). Includes educational, entertainment, and tech templates.</p>',
                'detail' => '<p>الأداة: Photoshop + Canva | التصاميم: 30 | الأبعاد: 1280x720</p>',
                'specification' => '<p>الصيغة: PSD + Canva Link | التصاميم: 30 | الحجم: 85 MB</p>',
                'tags' => ['youtube', 'thumbnail', 'social-media', 'content-creator'],
                'avg_rating' => 4.8,
            ],
            [
                'name' => 'باقة قوالب تيك توك (40 تصميم) | TikTok Video Templates',
                'price' => 17.99, 'sale_price' => 0,
                'description' => '<p>40 قالب فيديو تيك توك احترافي قابل للتعديل على Canva. تشمل قوالب تعليمية، ترندات، تحديات، ومقارنات.</p><p>40 professional TikTok video templates editable on Canva. Includes educational, trend, challenge, and comparison templates.</p>',
                'detail' => '<p>الأداة: Canva | التصاميم: 40 | الأبعاد: 1080x1920</p>',
                'specification' => '<p>الصيغة: Canva Link | التصاميم: 40 | الحجم: تحميل مباشر</p>',
                'tags' => ['tiktok', 'reel', 'social-media', 'content-creator', 'canva'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'باقة هوية بصرية للسوشيال ميديا | Social Media Brand Kit',
                'price' => 29.99, 'sale_price' => 22.99,
                'description' => '<p>باقة هوية بصرية متكاملة لجميع منصات التواصل. تشمل: صور الملف الشخصي، صور الغلاف، قوالب البوستات، والستوري لكل منصة.</p><p>Complete visual identity kit for all social platforms. Includes: profile pictures, cover photos, post templates, and stories for each platform.</p>',
                'detail' => '<p>المنصات: Instagram, Facebook, Twitter, LinkedIn, YouTube, TikTok</p>',
                'specification' => '<p>الصيغة: Canva + PSD | العناصر: 60+ | الحجم: 120 MB</p>',
                'tags' => ['social-media', 'branding', 'instagram', 'content-creator', 'youtube'],
                'avg_rating' => 4.8,
            ],
            [
                'name' => 'باقة هايلايت كفر انستقرام (20 تصميم) | Highlight Covers Pack',
                'price' => 8.99, 'sale_price' => 0,
                'description' => '<p>20 تصميم هايلايت كفر لانستقرام بأنماط متنوعة: خطوط، ألوان، أيقونات. قابل للتعديل على Canva.</p><p>20 Instagram highlight cover designs in various styles: lines, colors, icons. Editable on Canva.</p>',
                'detail' => '<p>الأداة: Canva | التصاميم: 20 | الأنماط: متنوعة</p>',
                'specification' => '<p>الصيغة: Canva Link | التصاميم: 20 | الحجم: تحميل مباشر</p>',
                'tags' => ['instagram', 'story', 'branding', 'canva'],
                'avg_rating' => 4.3,
            ],
            [
                'name' => 'باقة قوالب إعلانات فيسبوك (25 تصميم) | Facebook Ad Templates',
                'price' => 18.99, 'sale_price' => 13.99,
                'description' => '<p>25 قالب إعلان فيسبوك احترافي معد لتحقيق أعلى معدل تحويل. تشمل إعلانات التحويل، المبيعات، التوعية، والمحتوى التفاعلي.</p><p>25 professional Facebook ad templates optimized for highest conversion rate. Includes conversion, sales, awareness, and interactive content ads.</p>',
                'detail' => '<p>الأداة: Canva + Photoshop | التصاميم: 25 | الأبعاد: متعددة</p>',
                'specification' => '<p>الصيغة: PSD + Canva Link | التصاميم: 25 | الحجم: 65 MB</p>',
                'tags' => ['social-media', 'content-creator', 'branding', 'canva'],
                'avg_rating' => 4.6,
            ],
            [
                'name' => 'باقة قوالب بانر يوتيوب (15 تصميم) | YouTube Banner Pack',
                'price' => 12.99, 'sale_price' => 0,
                'description' => '<p>15 تصميم بانر يوتيوب احترافي بأشكال وألوان مختلفة. قابل للتعديل بالكامل مع خطوط عربية وإنجليزية.</p><p>15 professional YouTube banner designs in different styles and colors. Fully editable with Arabic and English fonts.</p>',
                'detail' => '<p>الأداة: Photoshop | التصاميم: 15 | الأبعاد: 2560x1440</p>',
                'specification' => '<p>الصيغة: PSD | التصاميم: 15 | الحجم: 45 MB</p>',
                'tags' => ['youtube', 'social-media', 'branding', 'content-creator'],
                'avg_rating' => 4.4,
            ],
            [
                'name' => 'باقة قوالب بنتيريست (30 تصميم) | Pinterest Pin Templates',
                'price' => 13.99, 'sale_price' => 9.99,
                'description' => '<p>30 قالب بينتريست احترافي قابل للتعديل. تشمل قوالب مدونات، وصفات، أفكار ديكور، ونصائح. تصميمات جذابة تزيد التفاعل.</p><p>30 professional editable Pinterest templates. Includes blog, recipe, decor idea, and tips templates. Attractive designs that boost engagement.</p>',
                'detail' => '<p>الأداة: Canva | التصاميم: 30 | الأبعاد: 1000x1500</p>',
                'specification' => '<p>الصيغة: Canva Link | التصاميم: 30 | الحجم: تحميل مباشر</p>',
                'tags' => ['social-media', 'content-creator', 'canva', 'branding'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'باقة قوالب Reels/Shorts (35 تصميم) | Reels & Shorts Templates',
                'price' => 15.99, 'sale_price' => 0,
                'description' => '<p>35 قالب فيديو Reels و YouTube Shorts قابل للتعديل. تشمل قوالب تعليمية، تحديات، ترفيهية، ونصائح سريعة.</p><p>35 editable Reels and YouTube Shorts video templates. Includes educational, challenge, entertainment, and quick tips templates.</p>',
                'detail' => '<p>الأداة: Canva | التصاميم: 35 | الأبعاد: 1080x1920</p>',
                'specification' => '<p>الصيغة: Canva Link | التصاميم: 35 | الحجم: تحميل مباشر</p>',
                'tags' => ['reel', 'instagram', 'youtube', 'tiktok', 'content-creator'],
                'avg_rating' => 4.6,
            ],
            [
                'name' => 'باقة قوالب لينكد إن (20 تصميم) | LinkedIn Templates',
                'price' => 14.99, 'sale_price' => 0,
                'description' => '<p>20 قالب لينكد إن احترافي للبوستات والبطاقات الشخصية. تصميمات أنيقة ومهنية مناسبة لجميع المجالات.</p><p>20 professional LinkedIn templates for posts and personal cards. Elegant and professional designs suitable for all industries.</p>',
                'detail' => '<p>الأداة: Canva | التصاميم: 20 | الأسلوب: مهني</p>',
                'specification' => '<p>الصيغة: Canva Link | التصاميم: 20 | الحجم: تحميل مباشر</p>',
                'tags' => ['social-media', 'content-creator', 'branding', 'canva'],
                'avg_rating' => 4.4,
            ],
            [
                'name' => 'باقة صور رمزية احترافية (50 صورة) | Professional Avatar Pack',
                'price' => 11.99, 'sale_price' => 7.99,
                'description' => '<p>50 صورة رمزية احترافية للسوشيال ميديا. تشمل ذكور وإناث بأساليب مختلفة: مسطحة، كرتونية، وواقعية.</p><p>50 professional social media avatars. Includes male and female in different styles: flat, cartoon, and realistic.</p>',
                'detail' => '<p>الأنماط: مسطح + كرتوني + واقعي | الأحجام: متعددة</p>',
                'specification' => '<p>الصيغة: PNG | الصور: 50 | الحجم: 35 MB</p>',
                'tags' => ['social-media', 'branding', 'graphics'],
                'avg_rating' => 4.3,
            ],
            [
                'name' => 'باقة أيقونات السوشيال ميديا (200 أيقونة) | Social Media Icons Pack',
                'price' => 9.99, 'sale_price' => 0,
                'description' => '<p>200 أيقونة سوشيال ميديا وتسويق رقمي بصيغ SVG وPNG. تشمل أيقونات المنصات، أدوات التسويق، والتفاعل.</p><p>200 social media and digital marketing icons in SVG and PNG formats. Includes platform icons, marketing tools, and engagement icons.</p>',
                'detail' => '<p>الصيغ: SVG, PNG | الألوان: متعددة | الحجم: قابل للتغيير (SVG)</p>',
                'specification' => '<p>الصيغة: ZIP (SVG + PNG) | الأيقونات: 200 | الحجم: 8.5 MB</p>',
                'tags' => ['icons', 'social-media', 'graphics', 'vectors'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'باقة قوالب كاروسل انستقرام (20 تصميم) | Carousel Post Pack',
                'price' => 13.99, 'sale_price' => 0,
                'description' => '<p>20 قالب بوست كاروسيل انستقرام احترافي. تشمل كاروسيل تعليمي، قصص نجاح، نصائح، وعروض تسويقية.</p><p>20 professional Instagram carousel post templates. Includes educational carousel, success stories, tips, and marketing offers.</p>',
                'detail' => '<p>الأداة: Canva | التصاميم: 20 (5-10 شرائح لكل)</p>',
                'specification' => '<p>الصيغة: Canva Link | التصاميم: 20 | الحجم: تحميل مباشر</p>',
                'tags' => ['instagram', 'social-media', 'content-creator', 'canva'],
                'avg_rating' => 4.6,
            ],
            [
                'name' => 'باقة خلفيات سوشيال ميديا (30 خلفية) | Social Backgrounds Pack',
                'price' => 7.99, 'sale_price' => 0,
                'description' => '<p>30 خلفية احترافية للسوشيال ميديا وال presentations. تشمل خلفيات متدرجة، هندسية، وزخرفية.</p><p>30 professional backgrounds for social media and presentations. Includes gradient, geometric, and decorative backgrounds.</p>',
                'detail' => '<p>الصيغة: PNG + JPG | الدقة: 4K | الأنماط: متنوعة</p>',
                'specification' => '<p>الصيغة: ZIP | الخلفيات: 30 | الحجم: 55 MB</p>',
                'tags' => ['social-media', 'graphics', 'textures', 'branding'],
                'avg_rating' => 4.2,
            ],
            [
                'name' => 'باقة محتوى رمضان (40 تصميم) | Ramadan Content Pack',
                'price' => 19.99, 'sale_price' => 14.99,
                'description' => '<p>40 تصميم محتوى رمضاني شامل: بوستات، ستوريز، كاروسيل، وهايلايت. تصميمات إسلامية أنيقة قابلة للتعديل.</p><p>40 comprehensive Ramadan content designs: posts, stories, carousel, and highlights. Elegant Islamic designs editable on Canva.</p>',
                'detail' => '<p>الأداة: Canva | التصاميم: 40 | المناسبة: رمضان</p>',
                'specification' => '<p>الصيغة: Canva Link | التصاميم: 40 | الحجم: تحميل مباشر</p>',
                'tags' => ['instagram', 'story', 'social-media', 'content-creator', 'canva'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'باقة قوالب دعوات رقمية (20 تصميم) | Digital Invitation Pack',
                'price' => 15.99, 'sale_price' => 0,
                'description' => '<p>20 قالب دعوة رقمية احترافية: زفاف، ميلاد، تخرج، وحفلات. قابلة للتعديل على Canva مع إرسال مباشر.</p><p>20 professional digital invitation templates: wedding, birthday, graduation, and parties. Editable on Canva with direct sharing.</p>',
                'detail' => '<p>الأداة: Canva | التصاميم: 20 | الأنواع: 4</p>',
                'specification' => '<p>الصيغة: Canva Link | التصاميم: 20 | الحجم: تحميل مباشر</p>',
                'tags' => ['canva', 'social-media', 'branding', 'content-creator'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'باقة قوالب تغطية مباشرة | Live Stream Overlay Pack',
                'price' => 21.99, 'sale_price' => 0,
                'description' => '<p>20 قالب تغطية مباشرة احترافية لـ Twitch و YouTube. تشمل شريط علوي، إشعارات المتابعين، شاشة بداية ونهاية.</p><p>20 professional live stream overlays for Twitch and YouTube. Includes top bar, follower alerts, starting and ending screens.</p>',
                'detail' => '<p>المنصات: Twitch, YouTube Live | العناصر: 50+</p>',
                'specification' => '<p>الصيغة: ZIP (PNG + PSD) | التصاميم: 20 | الحجم: 95 MB</p>',
                'tags' => ['youtube', 'social-media', 'branding', 'graphics'],
                'avg_rating' => 4.4,
            ],
            [
                'name' => 'باقة استراتيجية محتوى (قالب Excel) | Content Strategy Template',
                'price' => 11.99, 'sale_price' => 0,
                'description' => '<p>قالب Excel شامل لتخطيط استراتيجية المحتوى. يتضمن جدول النشر، تحليل المنافسين، تقويم المحتوى، وتقارير الأداء.</p><p>Comprehensive Excel template for content strategy planning. Includes posting schedule, competitor analysis, content calendar, and performance reports.</p>',
                'detail' => '<p>الأداة: Microsoft Excel | الأقسام: 8 | الأشهر: 12</p>',
                'specification' => '<p>الصيغة: XLSX | الأقسام: 8 | الحجم: 1.5 MB</p>',
                'tags' => ['social-media', 'planning', 'content-creator', 'management'],
                'avg_rating' => 4.3,
            ],
        ];
    }

    // ==============================
    // 4. AI PROMPTS - 20 منتج
    // ==============================
    private function getAIPromptProducts(): array
    {
        return [
            [
                'name' => 'باقة برومبتات ChatGPT للتسويق | ChatGPT Marketing Prompts',
                'price' => 14.99, 'sale_price' => 9.99,
                'description' => '<p>200+ برومبت ChatGPT متخصص في التسويق الرقمي. يشمل: كتابة الإعلانات، استراتيجيات SEO، محتوى السوشيال ميديا، وأيميل ماركتنق.</p><p>200+ ChatGPT prompts specialized in digital marketing. Includes: ad copywriting, SEO strategies, social media content, and email marketing.</p>',
                'detail' => '<p>المنصة: ChatGPT | البرومبتات: 200+ | الأقسام: 8</p>',
                'specification' => '<p>الصيغة: PDF + TXT | البرومبتات: 200+ | الحجم: 2.3 MB</p>',
                'tags' => ['chatgpt', 'prompts', 'copywriting', 'ai', 'social-media'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'باقة برومبتات Midjourney للفن | Midjourney Art Prompts',
                'price' => 12.99, 'sale_price' => 0,
                'description' => '<p>150+ برومبت Midjourney لإنتاج صور فنية احترافية. يشمل: بورتريه، مناظر طبيعية، سريالية، وأنماط مختلفة مع معلمات التحكم.</p><p>150+ Midjourney prompts for professional art images. Includes: portraits, landscapes, surreal, and various styles with control parameters.</p>',
                'detail' => '<p>المنصة: Midjourney | البرومبتات: 150+ | الأنماط: 10+</p>',
                'specification' => '<p>الصيغة: PDF + TXT | البرومبتات: 150+ | الحجم: 1.8 MB</p>',
                'tags' => ['midjourney', 'prompts', 'ai-art', 'ai', 'stable-diffusion'],
                'avg_rating' => 4.8,
            ],
            [
                'name' => 'باقة برومبتات ChatGPT للبرمجة | ChatGPT Coding Prompts',
                'price' => 16.99, 'sale_price' => 11.99,
                'description' => '<p>180+ برومبت ChatGPT للمبرمجين. يشمل: كتابة أكواد، debugging، تحسين الأداء، شرح الخوارزميات، وتوليد اختبارات. يدعم لغات متعددة.</p><p>180+ ChatGPT prompts for programmers. Includes: code writing, debugging, performance optimization, algorithm explanation, and test generation. Multi-language support.</p>',
                'detail' => '<p>المنصة: ChatGPT | البرومبتات: 180+ | اللغات: 12+</p>',
                'specification' => '<p>الصيغة: PDF + TXT | البرومبتات: 180+ | الحجم: 2.5 MB</p>',
                'tags' => ['chatgpt', 'prompts', 'ai', 'automation'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'باقة برومبتات كتابة المحتوى | AI Content Writing Prompts',
                'price' => 13.99, 'sale_price' => 0,
                'description' => '<p>170+ برومبت لكتابة محتوى جذاب ومقنع. يشمل: مدونات، مقالات، نصوص المبيعات، قصص العلامات التجارية، والكتابة الإبداعية.</p><p>170+ prompts for compelling content writing. Includes: blogs, articles, sales copy, brand stories, and creative writing.</p>',
                'detail' => '<p>المنصة: ChatGPT | البرومبتات: 170+ | الأنواع: 8</p>',
                'specification' => '<p>الصيغة: PDF + TXT | البرومبتات: 170+ | الحجم: 2.1 MB</p>',
                'tags' => ['chatgpt', 'prompts', 'copywriting', 'ai'],
                'avg_rating' => 4.6,
            ],
            [
                'name' => 'باقة برومبتات Stable Diffusion | Stable Diffusion Master Pack',
                'price' => 18.99, 'sale_price' => 14.99,
                'description' => '<p>200+ برومبت Stable Diffusion مع نماذج موصى بها. يشمل: Negative prompts, LoRA triggers, أنماط فنية، ومعلمات الإعداد.</p><p>200+ Stable Diffusion prompts with recommended models. Includes: negative prompts, LoRA triggers, artistic styles, and setup parameters.</p>',
                'detail' => '<p>المنصة: Stable Diffusion | البرومبتات: 200+ | النماذج: 15+</p>',
                'specification' => '<p>الصيغة: PDF + TXT + JSON | البرومبتات: 200+ | الحجم: 3.2 MB</p>',
                'tags' => ['stable-diffusion', 'prompts', 'ai-art', 'ai', 'dall-e'],
                'avg_rating' => 4.8,
            ],
            [
                'name' => 'باقة برومبتات ريادة الأعمال | AI Business Prompts',
                'price' => 15.99, 'sale_price' => 0,
                'description' => '<p>120+ برومبت AI لمس entrepreneurs. يشمل: تحليل السوق، دراسات الجدوى، استراتيجيات النمو، وخطط التسويق.</p><p>120+ AI prompts for entrepreneurs. Includes: market analysis, feasibility studies, growth strategies, and marketing plans.</p>',
                'detail' => '<p>المنصة: ChatGPT | البرومبتات: 120+ | الأقسام: 6</p>',
                'specification' => '<p>الصيغة: PDF + TXT | البرومبتات: 120+ | الحجم: 1.6 MB</p>',
                'tags' => ['chatgpt', 'prompts', 'ai', 'automation'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'باقة برومبتات تعليمية | AI Education Prompts',
                'price' => 11.99, 'sale_price' => 0,
                'description' => '<p>100+ برومبت AI للمعلمين والطلاب. يشمل: تخطيط الدروس، شرح المفاهيم، إنشاء الاختبارات، وخطط الدراسة الشخصية.</p><p>100+ AI prompts for teachers and students. Includes: lesson planning, concept explanation, test creation, and personal study plans.</p>',
                'detail' => '<p>المنصة: ChatGPT | البرومبتات: 100+ | المراحل: جميعها</p>',
                'specification' => '<p>الصيغة: PDF + TXT | البرومبتات: 100+ | الحجم: 1.3 MB</p>',
                'tags' => ['chatgpt', 'prompts', 'ai', 'automation'],
                'avg_rating' => 4.4,
            ],
            [
                'name' => 'باقة برومبتات DALL-E 3 | DALL-E 3 Prompt Library',
                'price' => 14.99, 'sale_price' => 9.99,
                'description' => '<p>160+ برومبت DALL-E 3 محسّن للحصول على أفضل النتائج. يشمل: تصاميم جرافيك، إعلانات، لوجوهات، وتوضيحات.</p><p>160+ optimized DALL-E 3 prompts for best results. Includes: graphic designs, advertisements, logos, and illustrations.</p>',
                'detail' => '<p>المنصة: DALL-E 3 | البرومبتات: 160+ | الأنواع: 8</p>',
                'specification' => '<p>الصيغة: PDF + TXT | البرومبتات: 160+ | الحجم: 1.9 MB</p>',
                'tags' => ['dall-e', 'prompts', 'ai-art', 'ai'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'باقة برومبتات صناعة الأفلام | AI Filmmaking Prompts',
                'price' => 19.99, 'sale_price' => 0,
                'description' => '<p>100+ برومبت AI لصناعة الأفلام والفيديو. يشمل: كتابة السيناريو، وصف المشاهد، تصميم الشخصيات، والموسيقى التصويرية.</p><p>100+ AI prompts for filmmaking and video production. Includes: screenplay writing, scene description, character design, and soundtrack.</p>',
                'detail' => '<p>المنصة: ChatGPT + Midjourney | البرومبتات: 100+</p>',
                'specification' => '<p>الصيغة: PDF + TXT | البرومبتات: 100+ | الحجم: 2.0 MB</p>',
                'tags' => ['chatgpt', 'midjourney', 'prompts', 'ai', 'ai-art'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'باقة برومبتات التصوير الفوتوغرافي | AI Photography Prompts',
                'price' => 13.99, 'sale_price' => 0,
                'description' => '<p>130+ برومبت AI لتوليد صور فوتوغرافية واقعية. يشمل: بورتريه، منتجات، طعام، معماري، وأزياء مع إعدادات الكاميرا.</p><p>130+ AI prompts for realistic photography generation. Includes: portrait, products, food, architecture, and fashion with camera settings.</p>',
                'detail' => '<p>المنصات: Midjourney, Stable Diffusion | البرومبتات: 130+</p>',
                'specification' => '<p>الصيغة: PDF + TXT | البرومبتات: 130+ | الحجم: 1.7 MB</p>',
                'tags' => ['midjourney', 'stable-diffusion', 'prompts', 'ai-art', 'ai'],
                'avg_rating' => 4.6,
            ],
            [
                'name' => 'باقة برومبتات Excel و数据分析 | AI Excel & Data Analysis Prompts',
                'price' => 12.99, 'sale_price' => 0,
                'description' => '<p>90+ برومبت AI للتعامل مع Excel وتحليل البيانات. يشمل: معادلات معقدة، pivot tables, VBA macros, وتفسير البيانات.</p><p>90+ AI prompts for Excel and data analysis. Includes: complex formulas, pivot tables, VBA macros, and data interpretation.</p>',
                'detail' => '<p>المنصة: ChatGPT | البرومبتات: 90+ | المستوى: مبتدئ - متقدم</p>',
                'specification' => '<p>الصيغة: PDF + TXT | البرومبتات: 90+ | الحجم: 1.2 MB</p>',
                'tags' => ['chatgpt', 'prompts', 'ai', 'automation'],
                'avg_rating' => 4.4,
            ],
            [
                'name' => 'باقة برومبتات السيرة الذاتية | AI Resume & CV Prompts',
                'price' => 9.99, 'sale_price' => 0,
                'description' => '<p>80+ برومبت AI لتحسين السيرة الذاتية ورسائل التغطية. يشمل: صياغة الخبرات، تحسين الكلمات المفتاحية، والتحضير للمقابلات.</p><p>80+ AI prompts for improving resumes and cover letters. Includes: experience phrasing, keyword optimization, and interview preparation.</p>',
                'detail' => '<p>المنصة: ChatGPT | البرومبتات: 80+ | اللغات: عربي + إنجليزي</p>',
                'specification' => '<p>الصيغة: PDF + TXT | البرومبتات: 80+ | الحجم: 0.9 MB</p>',
                'tags' => ['chatgpt', 'prompts', 'ai', 'copywriting'],
                'avg_rating' => 4.3,
            ],
            [
                'name' => 'باقة برومبتات AI الشاملة | Ultimate AI Prompt Bundle',
                'price' => 39.99, 'sale_price' => 29.99,
                'description' => '<p>مجموعة شاملة تضم 1000+ برومبت لجميع مجالات الاستخدام. تجمع أفضل البرومبتات من كل المجموعات السابقة مع إضافات حصرية.</p><p>Comprehensive collection of 1000+ prompts for all use cases. Combines the best prompts from all previous collections with exclusive additions.</p>',
                'detail' => '<p>المنصات: ChatGPT, Midjourney, DALL-E, Stable Diffusion | البرومبتات: 1000+</p>',
                'specification' => '<p>الصيغة: PDF + TXT + JSON | البرومبتات: 1000+ | الحجم: 8.5 MB</p>',
                'tags' => ['chatgpt', 'midjourney', 'dall-e', 'stable-diffusion', 'prompts', 'ai'],
                'avg_rating' => 4.9,
            ],
            [
                'name' => 'باقة برومبتات SEO | AI SEO Optimization Prompts',
                'price' => 14.99, 'sale_price' => 0,
                'description' => '<p>110+ برومبت AI لتحسين محركات البحث. يشمل: بحث الكلمات المفتاحية، تحسين العناوين، meta descriptions, وبنية المقال.</p><p>110+ AI prompts for SEO optimization. Includes: keyword research, title optimization, meta descriptions, and article structure.</p>',
                'detail' => '<p>المنصة: ChatGPT | البرومبتات: 110+ | الأدوات: متعددة</p>',
                'specification' => '<p>الصيغة: PDF + TXT | البرومبتات: 110+ | الحجم: 1.4 MB</p>',
                'tags' => ['chatgpt', 'prompts', 'ai', 'copywriting'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'باقة برومبتات القصص القصيرة | AI Storytelling Prompts',
                'price' => 11.99, 'sale_price' => 0,
                'description' => '<p>85+ برومبت AI لكتابة القصص القصيرة والروايات. يشمل: تطوير الشخصيات، بناء العالم، حبكة القصة، والحوارات.</p><p>85+ AI prompts for short story and novel writing. Includes: character development, world building, plot construction, and dialogue.</p>',
                'detail' => '<p>المنصة: ChatGPT | البرومبتات: 85+ | الأنواع: 6</p>',
                'specification' => '<p>الصيغة: PDF + TXT | البرومبتات: 85+ | الحجم: 1.1 MB</p>',
                'tags' => ['chatgpt', 'prompts', 'ai', 'copywriting'],
                'avg_rating' => 4.4,
            ],
            [
                'name' => 'باقة برومبتات التصميم الداخلي | AI Interior Design Prompts',
                'price' => 15.99, 'sale_price' => 0,
                'description' => '<p>100+ برومبت AI لتصميم الديكورات الداخلية. يشمل: غرف المعيشة، المطابخ، المكاتب، والحدائق بأنماط متعددة.</p><p>100+ AI prompts for interior design. Includes: living rooms, kitchens, offices, and gardens in multiple styles.</p>',
                'detail' => '<p>المنصات: Midjourney, DALL-E | البرومبتات: 100+ | الأنماط: 12+</p>',
                'specification' => '<p>الصيغة: PDF + TXT | البرومبتات: 100+ | الحجم: 1.5 MB</p>',
                'tags' => ['midjourney', 'dall-e', 'prompts', 'ai-art', 'ai'],
                'avg_rating' => 4.6,
            ],
            [
                'name' => 'باقة برومبتات إنشاء الألعاب | AI Game Development Prompts',
                'price' => 17.99, 'sale_price' => 12.99,
                'description' => '<p>95+ برومبت AI لتطوير الألعاب. يشمل: تصميم الشخصيات، بيئة اللعبة، آليات اللعب، وكتابة الحوار.</p><p>95+ AI prompts for game development. Includes: character design, game environment, game mechanics, and dialogue writing.</p>',
                'detail' => '<p>المنصات: ChatGPT, Midjourney | البرومبتات: 95+</p>',
                'specification' => '<p>الصيغة: PDF + TXT | البرومبتات: 95+ | الحجم: 1.8 MB</p>',
                'tags' => ['chatgpt', 'midjourney', 'prompts', 'ai', 'ai-art'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'باقة برومبتات الموسيقى والصوت | AI Music & Audio Prompts',
                'price' => 13.99, 'sale_price' => 0,
                'description' => '<p>70+ برومبت AI لتوليد الموسيقى والأصوات. يشمل: موسيقى خلفية، مؤثرات صوتية، أصوات طبيعة، وبودكاست.</p><p>70+ AI prompts for music and audio generation. Includes: background music, sound effects, nature sounds, and podcast intros.</p>',
                'detail' => '<p>المنصات: Suno, Udio, ChatGPT | البرومبتات: 70+</p>',
                'specification' => '<p>الصيغة: PDF + TXT | البرومبتات: 70+ | الحجم: 1.0 MB</p>',
                'tags' => ['ai', 'prompts', 'automation'],
                'avg_rating' => 4.3,
            ],
            [
                'name' => 'باقة برومبتات الطبخ والوصفات | AI Cooking & Recipes Prompts',
                'price' => 10.99, 'sale_price' => 0,
                'description' => '<p>75+ برومبت AI للطبخ وإنشاء الوصفات. يشمل: وصفات حسب المكونات المتاحة، خطط التغذية، ونصائح المطاعم.</p><p>75+ AI prompts for cooking and recipe creation. Includes: recipes based on available ingredients, nutrition plans, and restaurant tips.</p>',
                'detail' => '<p>المنصة: ChatGPT | البرومبتات: 75+ | المطبخ: عربي + عالمي</p>',
                'specification' => '<p>الصيغة: PDF + TXT | البرومبتات: 75+ | الحجم: 0.8 MB</p>',
                'tags' => ['chatgpt', 'prompts', 'ai'],
                'avg_rating' => 4.2,
            ],
        ];
    }

    // ==============================
    // 5. CREATIVE DESIGN ASSETS - 20 منتج
    // ==============================
    private function getCreativeDesignProducts(): array
    {
        return [
            [
                'name' => 'باقة خطوط عربية احترافية (50 خط) | Professional Arabic Fonts Pack',
                'price' => 24.99, 'sale_price' => 19.99,
                'description' => '<p>مجموعة من 50 خط عربي احترافي بصيغ OTF وTTF. تشمل خطوط كوفي، نستعليق، ديواني، وخطوط حديثة. مناسبة للتصميم والطباعة.</p><p>A collection of 50 professional Arabic fonts in OTF and TTF formats. Includes Kufic, Naskh, Diwani, and modern fonts. Suitable for design and print.</p>',
                'detail' => '<p>الصيغ: OTF, TTF | الأنماط: 6 | الترخيص: شخصي + تجاري</p>',
                'specification' => '<p>الصيغة: ZIP | الخطوط: 50 | الحجم: 28.5 MB</p>',
                'tags' => ['fonts', 'graphics', 'printable'],
                'avg_rating' => 4.8,
            ],
            [
                'name' => 'باقة أيقونات 3D (500 أيقونة) | 3D Icons Mega Pack',
                'price' => 19.99, 'sale_price' => 0,
                'description' => '<p>500 أيقونة ثلاثية الأبعاد عالية الجودة بصيغ SVG وPNG. تشمل: أعمال، تقنية، تعليم، طبي، وتسوق. ألوان قابلة للتخصيص.</p><p>500 high-quality 3D icons in SVG and PNG formats. Includes: business, tech, education, medical, and shopping. Customizable colors.</p>',
                'detail' => '<p>الصيغ: SVG, PNG | الدقة: 512x512 | الألوان: قابلة للتخصيص</p>',
                'specification' => '<p>الصيغة: ZIP | الأيقونات: 500 | الحجم: 45 MB</p>',
                'tags' => ['icons', 'graphics', 'vectors', 'svg'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'باقة موك آب منتجات (80 موك آب) | Product Mockups Collection',
                'price' => 29.99, 'sale_price' => 22.99,
                'description' => '<p>80 موك آب احترافي لتقديم التصاميم. يشمل: موك آب أجهزة، عبوات، ملابس، ورق، وبطاقات أعمال. قابل للتعديل بالذكاء الاصطناعي.</p><p>80 professional mockups for design presentation. Includes: devices, packaging, clothing, paper, and business cards. AI-editable.</p>',
                'detail' => '<p>الأداة: Photoshop | الموك آب: 80 | الأنواع: 8</p>',
                'specification' => '<p>الصيغة: PSD | الموك آب: 80 | الحجم: 680 MB</p>',
                'tags' => ['mockups', 'graphics', 'branding'],
                'avg_rating' => 4.8,
            ],
            [
                'name' => 'باقة رسوم توضيحية (300 رسمة) | Illustration Pack',
                'price' => 22.99, 'sale_price' => 0,
                'description' => '<p>300 رسمة توضيحية بصيغ SVG وPNG. تشمل: أعمال، تعليم، صحة، تقنية، وأنماط حياة. أسلوب مسطح أنيق.</p><p>300 illustrations in SVG and PNG formats. Includes: business, education, health, tech, and lifestyle. Elegant flat style.</p>',
                'detail' => '<p>الصيغ: SVG, PNG | الأسلوب: Flat | الدقة: قابل للتكبير (SVG)</p>',
                'specification' => '<p>الصيغة: ZIP | الرسوم: 300 | الحجم: 85 MB</p>',
                'tags' => ['illustrations', 'graphics', 'vectors', 'svg'],
                'avg_rating' => 4.6,
            ],
            [
                'name' => 'باقة تدرجات لونية (200 تدرج) | Gradient Collection',
                'price' => 9.99, 'sale_price' => 0,
                'description' => '<p>200 تدرج لوني احترافي بصيغ AI, SVG, PNG. تشمل تدرجات دافئة، باردة، نيون، وترابية. مثالية للويب والطباعة.</p><p>200 professional color gradients in AI, SVG, PNG formats. Includes warm, cool, neon, and earthy gradients. Perfect for web and print.</p>',
                'detail' => '<p>الصيغ: AI, SVG, PNG | الأنماط: 6 | الدقة: 4K</p>',
                'specification' => '<p>الصيغة: ZIP | التدرجات: 200 | الحجم: 32 MB</p>',
                'tags' => ['gradients', 'graphics', 'textures'],
                'avg_rating' => 4.3,
            ],
            [
                'name' => 'باقة خلفيات نسيجية (150 خلفية) | Texture Backgrounds Pack',
                'price' => 12.99, 'sale_price' => 0,
                'description' => '<p>150 خلفية نسيجية عالية الدقة. تشمل: خشب، رخام، ملمس معدني، ورقي، وخرساني. مناسبة للتصميم الداخلي والجرافيك.</p><p>150 high-resolution texture backgrounds. Includes: wood, marble, metallic, paper, and concrete. Suitable for interior design and graphics.</p>',
                'detail' => '<p>الصيغة: JPG + PNG | الدقة: 4000x4000 | الأنماط: 8</p>',
                'specification' => '<p>الصيغة: ZIP | الخلفيات: 150 | الحجم: 520 MB</p>',
                'tags' => ['textures', 'graphics'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'باقة قوالب بطاقات أعمال (30 تصميم) | Business Card Templates',
                'price' => 14.99, 'sale_price' => 9.99,
                'description' => '<p>30 قالب بطاقة أعمال احترافية بصيغ PSD وAI. تشمل: أفقي وعمودي، بألوان وأنماط متنوعة. قابل للتعديل بالكامل.</p><p>30 professional business card templates in PSD and AI formats. Includes: horizontal and vertical, various colors and styles. Fully editable.</p>',
                'detail' => '<p>البرامج: Photoshop, Illustrator | القوالب: 30 | الأبعاد: 3.5x2 + 2x3.5</p>',
                'specification' => '<p>الصيغة: PSD + AI | القوالب: 30 | الحجم: 75 MB</p>',
                'tags' => ['mockups', 'graphics', 'branding', 'printable'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'باقة SVG للويب (1000 رسمة) | Web SVG Icons Bundle',
                'price' => 18.99, 'sale_price' => 0,
                'description' => '<p>1000 أيقونة SVG للويب والموبايل. تشمل: تنقل، اجتماعي، تجارة، إشعارات، ووسائط. محسنة للأداء والحجم.</p><p>1000 SVG icons for web and mobile. Includes: navigation, social, commerce, notifications, and media. Performance and size optimized.</p>',
                'detail' => '<p>الصيغة: SVG | الفئات: 20 | الدقة: قابل للتكبير</p>',
                'specification' => '<p>الصيغة: ZIP | الأيقونات: 1000 | الحجم: 3.5 MB</p>',
                'tags' => ['svg', 'icons', 'graphics', 'vectors'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'باقة لوغو عناصر (500 عنصر) | Logo Elements Pack',
                'price' => 21.99, 'sale_price' => 15.99,
                'description' => '<p>500 عنصر لتصميم اللوجوهات بصيغ SVG وAI. تشمل: أيقونات، إطارات، خطوط زخرفية، وأشكال هندسية.</p><p>500 logo design elements in SVG and AI formats. Includes: icons, frames, decorative lines, and geometric shapes.</p>',
                'detail' => '<p>الصيغ: SVG, AI | العناصر: 500 | الفئات: 15</p>',
                'specification' => '<p>الصيغة: ZIP | العناصر: 500 | الحجم: 22 MB</p>',
                'tags' => ['vectors', 'graphics', 'svg', 'branding'],
                'avg_rating' => 4.6,
            ],
            [
                'name' => 'باقة قوالب إنفوجرافيك (20 تصميم) | Infographic Templates',
                'price' => 16.99, 'sale_price' => 0,
                'description' => '<p>20 قالب إنفوجرافيك احترافي قابل للتعديل. يشمل: إحصائيات، جداول زمنية، مقارنات، وعمليات. مناسب للعروض التقديمية والتقارير.</p><p>20 editable professional infographic templates. Includes: statistics, timelines, comparisons, and processes. Suitable for presentations and reports.</p>',
                'detail' => '<p>الأداة: PowerPoint + Illustrator | القوالب: 20 | الأنواع: 6</p>',
                'specification' => '<p>الصيغة: PPTX + AI | القوالب: 20 | الحجم: 48 MB</p>',
                'tags' => ['graphics', 'vectors', 'branding'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'باقة خطوط إنجليزية حديثة (40 خط) | Modern English Fonts Pack',
                'price' => 19.99, 'sale_price' => 0,
                'description' => '<p>40 خط إنجليزي حديث ومتنوع. تشمل: Serif, Sans Serif, Handwriting, Display. مناسب للويب، الطباعة، والسوشيال ميديا.</p><p>40 modern and varied English fonts. Includes: Serif, Sans Serif, Handwriting, Display. Suitable for web, print, and social media.</p>',
                'detail' => '<p>الصيغ: OTF, TTF, WOFF2 | الأنماط: 5 | الويب: مدعوم</p>',
                'specification' => '<p>الصيغة: ZIP | الخطوط: 40 | الحجم: 18 MB</p>',
                'tags' => ['fonts', 'graphics', 'branding'],
                'avg_rating' => 4.6,
            ],
            [
                'name' => 'باقة موك آب هواتف (30 موك آب) | Phone Mockups Pack',
                'price' => 16.99, 'sale_price' => 12.99,
                'description' => '<p>30 موك آب هاتف iPhone وAndroid احترافي. يشمل: زوايا متعددة، ألوان مختلفة، وسياقات متنوعة. قابل للتعديل بالسحب والإفلات.</p><p>30 professional iPhone and Android phone mockups. Includes: multiple angles, different colors, and various contexts. Drag and drop editable.</p>',
                'detail' => '<p>الأداة: Photoshop | الأجهزة: iPhone + Android | الزوايا: 4</p>',
                'specification' => '<p>الصيغة: PSD | الموك آب: 30 | الحجم: 210 MB</p>',
                'tags' => ['mockups', 'graphics', 'branding'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'باقة أنماط سيف (100 نمط) | Seamless Patterns Pack',
                'price' => 11.99, 'sale_price' => 0,
                'description' => '<p>100 نمط سيف (بدون فواصل) عالي الجودة. يشمل: زهور، هندسي، مجرد، وتراثي. مناسب للخلفيات والملابس والورق.</p><p>100 high-quality seamless patterns. Includes: floral, geometric, abstract, and heritage. Suitable for backgrounds, clothing, and paper.</p>',
                'detail' => '<p>الصيغ: SVG, PNG | الدقة: 2000x2000 | الأنماط: 8</p>',
                'specification' => '<p>الصيغة: ZIP | الأنماط: 100 | الحجم: 42 MB</p>',
                'tags' => ['textures', 'graphics', 'vectors', 'svg'],
                'avg_rating' => 4.4,
            ],
            [
                'name' => 'باقة موك آب ملصقات (40 موك آب) | Poster & Flyer Mockups',
                'price' => 15.99, 'sale_price' => 0,
                'description' => '<p>40 موك آب ملصقات وفلايرات احترافية. يشمل: ملصقات حائط، فلايرات، رول آب، وبانرات. في سياقات واقعية متعددة.</p><p>40 professional poster and flyer mockups. Includes: wall posters, flyers, roll-ups, and banners. In multiple realistic contexts.</p>',
                'detail' => '<p>الأداة: Photoshop | الموك آب: 40 | السياقات: 6</p>',
                'specification' => '<p>الصيغة: PSD | الموك آب: 40 | الحجم: 350 MB</p>',
                'tags' => ['mockups', 'graphics', 'printable'],
                'avg_rating' => 4.4,
            ],
            [
                'name' => 'باقة رموز تعبيرية (500 إيموجي) | Custom Emoji Pack',
                'price' => 13.99, 'sale_price' => 0,
                'description' => '<p>500 إيموجي مخصص بصيغ SVG وPNG. تشمل: تعبيرات، مشاعر، أشياء، وأعلام. بأسلوب فني موحد.</p><p>500 custom emojis in SVG and PNG formats. Includes: expressions, feelings, objects, and flags. Uniform artistic style.</p>',
                'detail' => '<p>الصيغ: SVG, PNG | الدقة: 128x128 | الأسلوب: موحد</p>',
                'specification' => '<p>الصيغة: ZIP | الإيموجي: 500 | الحجم: 28 MB</p>',
                'tags' => ['icons', 'graphics', 'svg'],
                'avg_rating' => 4.3,
            ],
            [
                'name' => 'باقة قوالب شهادات وجوائز | Certificate & Award Templates',
                'price' => 12.99, 'sale_price' => 0,
                'description' => '<p>20 قالب شهادة وجائزة احترافية. يشمل: شهادات إتمام، جوائز تقدير، شهادات تقدير، ودبلومات. قابل للتعديل على Word وIllustrator.</p><p>20 professional certificate and award templates. Includes: completion certificates, appreciation awards, and diplomas. Editable in Word and Illustrator.</p>',
                'detail' => '<p>البرامج: Word, Illustrator | القوالب: 20 | الأنواع: 4</p>',
                'specification' => '<p>الصيغة: DOCX + AI | القوالب: 20 | الحجم: 35 MB</p>',
                'tags' => ['graphics', 'printable', 'branding'],
                'avg_rating' => 4.4,
            ],
            [
                'name' => 'باقة موك آب لابتوب وأجهزة (25 موك آب) | Laptop & Device Mockups',
                'price' => 17.99, 'sale_price' => 0,
                'description' => '<p>25 موك آب لابتوب واجهزة لوحية احترافية. يشمل: MacBook, iPad, وغيرها بزوايا وسياقات متنوعة.</p><p>25 professional laptop and tablet mockups. Includes: MacBook, iPad, and others in various angles and contexts.</p>',
                'detail' => '<p>الأداة: Photoshop | الأجهزة: MacBook + iPad + Generic | الزوايا: 3</p>',
                'specification' => '<p>الصيغة: PSD | الموك آب: 25 | الحجم: 280 MB</p>',
                'tags' => ['mockups', 'graphics', 'branding'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'باقة هندسة UI/UX (150 مكون) | UI/UX Design Kit',
                'price' => 34.99, 'sale_price' => 24.99,
                'description' => '<p>150 مكون UI/UX بصيغ Figma وSketch. يشمل: أزرار، نماذج، بطاقات، تنقل، ورسوم بيانية. نظام تصميم متكامل.</p><p>150 UI/UX components in Figma and Sketch formats. Includes: buttons, forms, cards, navigation, and charts. Complete design system.</p>',
                'detail' => '<p>الأدوات: Figma, Sketch | المكونات: 150 | النظام: Design System</p>',
                'specification' => '<p>الصيغة: FIG + SKETCH | المكونات: 150 | الحجم: 15 MB</p>',
                'tags' => ['graphics', 'vectors', 'branding'],
                'avg_rating' => 4.8,
            ],
            [
                'name' => 'باقة عناصر زفاف (200 عنصر) | Wedding Design Elements',
                'price' => 18.99, 'sale_price' => 0,
                'description' => '<p>200 عنصر تصميم زفاف بصيغ SVG وPNG. يشمل: أزهار، إطارات، زخارف، وفروع. أنيق ورقيق.</p><p>200 wedding design elements in SVG and PNG formats. Includes: flowers, frames, ornaments, and branches. Elegant and delicate.</p>',
                'detail' => '<p>الصيغ: SVG, PNG | الدقة: عالية | اللون: ذهبي + أبيض</p>',
                'specification' => '<p>الصيغة: ZIP | العناصر: 200 | الحجم: 38 MB</p>',
                'tags' => ['graphics', 'vectors', 'svg', 'printable'],
                'avg_rating' => 4.5,
            ],
        ];
    }

    // ==============================
    // 6. CODE & DEVELOPER ASSETS - 20 منتج
    // ==============================
    private function getCodeDevProducts(): array
    {
        return [
            [
                'name' => 'قالب React Dashboard احترافي | React Admin Dashboard',
                'price' => 39.99, 'sale_price' => 29.99,
                'description' => '<p>قالب React Dashboard متكامل مع TypeScript. يشمل: لوحة تحكم، جداول، رسوم بيانية، نموذج تسجيل الدخول، وإدارة المستخدمين. مبني على Material-UI و Redux Toolkit.</p><p>Complete React Dashboard template with TypeScript. Includes: dashboard, tables, charts, login form, and user management. Built on Material-UI and Redux Toolkit.</p>',
                'detail' => '<p>التقنيات: React 18, TypeScript, Material-UI, Redux Toolkit, Chart.js</p>',
                'specification' => '<p>الصيغة: ZIP | المكونات: 40+ | الحجم: 12.5 MB</p>',
                'tags' => ['react', 'component', 'javascript', 'api', 'boilerplate'],
                'avg_rating' => 4.8,
            ],
            [
                'name' => 'قالب Vue.js Admin احترافي | Vue.js Admin Template',
                'price' => 34.99, 'sale_price' => 0,
                'description' => '<p>قالب Vue.js Admin متكامل مع Vue 3 و Composition API. يشمل: لوحة تحكم، إدارة CRUD، مصادقة JWT، و dark mode.</p><p>Complete Vue.js Admin template with Vue 3 and Composition API. Includes: dashboard, CRUD management, JWT auth, and dark mode.</p>',
                'detail' => '<p>التقنيات: Vue 3, Vite, Pinia, Tailwind CSS, Vue Router</p>',
                'specification' => '<p>الصيغة: ZIP | المكونات: 35+ | الحجم: 9.8 MB</p>',
                'tags' => ['vue', 'component', 'javascript', 'tailwind', 'boilerplate'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'Laravel Boilerplate - نقطة بداية SaaS | Laravel SaaS Starter',
                'price' => 49.99, 'sale_price' => 39.99,
                'description' => '<p>نقطة بداية Laravel متكاملة لبناء تطبيقات SaaS. يشمل: مصادقة، اشتراكات (Stripe/PayPal)، لوحة تحكم، API RESTful، و multi-tenancy.</p><p>Complete Laravel starter for building SaaS applications. Includes: authentication, subscriptions (Stripe/PayPal), dashboard, RESTful API, and multi-tenancy.</p>',
                'detail' => '<p>التقنيات: Laravel 11, PHP 8.2, MySQL, Redis, Stripe</p>',
                'specification' => '<p>الصيغة: ZIP | التوثيق: كامل | الحجم: 8.2 MB</p>',
                'tags' => ['laravel', 'api', 'boilerplate', 'component', 'tailwind'],
                'avg_rating' => 4.9,
            ],
            [
                'name' => 'قالب Node.js REST API | Node.js API Boilerplate',
                'price' => 29.99, 'sale_price' => 0,
                'description' => '<p>نقطة بداية Node.js REST API مع Express و MongoDB. يشمل: مصادقة JWT، rate limiting, إدارة الملفات، logging، وتوثيق Swagger.</p><p>Node.js REST API starter with Express and MongoDB. Includes: JWT auth, rate limiting, file management, logging, and Swagger documentation.</p>',
                'detail' => '<p>التقنيات: Node.js, Express, MongoDB, Mongoose, JWT, Swagger</p>',
                'specification' => '<p>الصيغة: ZIP | التوثيق: Swagger + README | الحجم: 4.5 MB</p>',
                'tags' => ['nodejs', 'api', 'javascript', 'boilerplate'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'مكتبة أكواد Python مفيدة (50 سكربت) | Python Scripts Library',
                'price' => 19.99, 'sale_price' => 14.99,
                'description' => '<p>50 سكربت Python مفيد وجاهز للاستخدام. يشمل: web scraping, معالجة بيانات، أتمتة، تعامل مع API، وأدوات سطر الأوامر.</p><p>50 useful and ready-to-use Python scripts. Includes: web scraping, data processing, automation, API handling, and CLI tools.</p>',
                'detail' => '<p>اللغة: Python 3.10+ | المكتبات: requests, pandas, selenium, beautifulsoup4</p>',
                'specification' => '<p>الصيغة: ZIP | السكربتات: 50 | الحجم: 3.8 MB</p>',
                'tags' => ['python', 'api', 'automation', 'boilerplate'],
                'avg_rating' => 4.6,
            ],
            [
                'name' => 'Next.js E-Commerce Boilerplate | قالب متجر Next.js',
                'price' => 44.99, 'sale_price' => 34.99,
                'description' => '<p>نقطة بداية متجر إلكتروني بـ Next.js و TypeScript. يشمل: سلة التسوق، الدفع (Stripe)، لوحة تحكم، SEO، و CMS headless.</p><p>E-commerce starter with Next.js and TypeScript. Includes: shopping cart, Stripe payment, dashboard, SEO, and headless CMS.</p>',
                'detail' => '<p>التقنيات: Next.js 14, TypeScript, Tailwind CSS, Prisma, Stripe</p>',
                'specification' => '<p>الصيغة: ZIP | الصفحات: 15+ | الحجم: 7.5 MB</p>',
                'tags' => ['react', 'javascript', 'tailwind', 'api', 'boilerplate'],
                'avg_rating' => 4.8,
            ],
            [
                'name' => 'Flutter E-Commerce App UI Kit | واجهة تطبيق متجر Flutter',
                'price' => 35.99, 'sale_price' => 0,
                'description' => '<p>واجهة مستخدم Flutter لمتجر إلكتروني متكامل. يشمل: تسجيل دخول، قائمة منتجات، تفاصيل المنتج، سلة، وcheckout. متوافق مع iOS و Android.</p><p>Flutter UI kit for a complete e-commerce app. Includes: login, product list, product detail, cart, and checkout. iOS and Android compatible.</p>',
                'detail' => '<p>التقنيات: Flutter 3.x, Dart, Provider, Dio</p>',
                'specification' => '<p>الصيغة: ZIP | الشاشات: 25+ | الحجم: 15 MB</p>',
                'tags' => ['component', 'boilerplate', 'javascript'],
                'avg_rating' => 4.6,
            ],
            [
                'name' => 'WordPress Plugin Builder Template | قالب بناء إضافة ووردبريس',
                'price' => 24.99, 'sale_price' => 0,
                'description' => '<p>نقطة بداية لبناء إضافات ووردبريس احترافية. يشمل: إنشاء جدول قاعدة البيانات، shortcodes, AJAX, إعدادات، و widgets.</p><p>Professional WordPress plugin builder starter. Includes: database table creation, shortcodes, AJAX, settings, and widgets.</p>',
                'detail' => '<p>التقنيات: PHP, WordPress API, jQuery, AJAX</p>',
                'specification' => '<p>الصيغة: ZIP | التوثيق: كامل | الحجم: 2.5 MB</p>',
                'tags' => ['api', 'boilerplate', 'component'],
                'avg_rating' => 4.4,
            ],
            [
                'name' => 'Tailwind CSS Component Library | مكتبة مكونات Tailwind',
                'price' => 22.99, 'sale_price' => 16.99,
                'description' => '<p>مكتبة مكونات Tailwind CSS متكاملة مع 80+ مكون جاهز. يشمل: أزرار، نماذج، بطاقات، تنقل، جداول، و modal.</p><p>Complete Tailwind CSS component library with 80+ ready components. Includes: buttons, forms, cards, navigation, tables, and modals.</p>',
                'detail' => '<p>التقنيات: Tailwind CSS 3.x, Alpine.js | المكونات: 80+</p>',
                'specification' => '<p>الصيغة: ZIP | المكونات: 80+ | الحجم: 3.2 MB</p>',
                'tags' => ['tailwind', 'component', 'javascript', 'react'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'Chrome Extension Boilerplate | قالب إضافة كروم',
                'price' => 19.99, 'sale_price' => 0,
                'description' => '<p>نقطة بداية لبناء إضافة Chrome احترافية. يشمل: popup, background script, content script, options page, و storage API.</p><p>Professional Chrome extension builder starter. Includes: popup, background script, content script, options page, and storage API.</p>',
                'detail' => '<p>التقنيات: JavaScript, Chrome APIs, HTML, CSS</p>',
                'specification' => '<p>الصيغة: ZIP | التوثيق: كامل | الحجم: 1.8 MB</p>',
                'tags' => ['javascript', 'boilerplate', 'api', 'component'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'Django REST API Boilerplate | قالب Django API',
                'price' => 27.99, 'sale_price' => 0,
                'description' => '<p>نقطة بداية Django REST API متكاملة. يشمل: مصادقة JWT، تصفية، بحث، ترقيم الصفحات، و Dokcer setup.</p><p>Complete Django REST API starter. Includes: JWT auth, filtering, searching, pagination, and Docker setup.</p>',
                'detail' => '<p>التقنيات: Django 5, DRF, PostgreSQL, Docker, JWT</p>',
                'specification' => '<p>الصيغة: ZIP | التوثيق: Swagger + README | الحجم: 5.1 MB</p>',
                'tags' => ['api', 'boilerplate', 'python'],
                'avg_rating' => 4.6,
            ],
            [
                'name' => 'React Native Mobile App Template | قالب تطبيق React Native',
                'price' => 39.99, 'sale_price' => 29.99,
                'description' => '<p>قالب تطبيق موبايل متكامل بـ React Native. يشمل: مصادقة، تنقل، إشعارات، خرائط، وكاميرا. جاهز للنشر على App Store و Google Play.</p><p>Complete mobile app template with React Native. Includes: auth, navigation, notifications, maps, and camera. Ready for App Store and Google Play.</p>',
                'detail' => '<p>التقنيات: React Native, Expo, TypeScript, Redux</p>',
                'specification' => '<p>الصيغة: ZIP | الشاشات: 20+ | الحجم: 18 MB</p>',
                'tags' => ['react', 'component', 'javascript', 'boilerplate'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'Gutenberg Block Development Kit | طقم تطوير بلوكات Gutenberg',
                'price' => 24.99, 'sale_price' => 0,
                'description' => '<p>طقم تطوير بلوكات Gutenberg لووردبريس. يشمل: 5 بلوكات جاهزة، react boilerplate، وأدوات التطوير. يعمل مع WordPress 6.x.</p><p>Gutenberg block development kit for WordPress. Includes: 5 ready blocks, React boilerplate, and dev tools. Works with WordPress 6.x.</p>',
                'detail' => '<p>التقنيات: React, WordPress Block API, PHP</p>',
                'specification' => '<p>الصيغة: ZIP | البلوكات: 5+ | الحجم: 4.2 MB</p>',
                'tags' => ['react', 'component', 'javascript', 'api'],
                'avg_rating' => 4.4,
            ],
            [
                'name' => 'SQL Query Templates Pack | باقة قوالب SQL',
                'price' => 14.99, 'sale_price' => 0,
                'description' => '<p>100 قالب SQL متخصص لقواعد البيانات. يشمل: استعلامات معقدة، إجراءات مخزنة، triggers، دوال، وتحسين الأداء. MySQL و PostgreSQL.</p><p>100 specialized SQL templates for databases. Includes: complex queries, stored procedures, triggers, functions, and performance optimization. MySQL and PostgreSQL.</p>',
                'detail' => '<p>قواعد البيانات: MySQL, PostgreSQL | الاستعلامات: 100</p>',
                'specification' => '<p>الصيغة: ZIP (SQL files) | القوالب: 100 | الحجم: 2.8 MB</p>',
                'tags' => ['api', 'boilerplate'],
                'avg_rating' => 4.3,
            ],
            [
                'name' => 'GraphQL API Boilerplate | قالب GraphQL API',
                'price' => 29.99, 'sale_price' => 0,
                'description' => '<p>نقطة بداية GraphQL API متكاملة. يشمل: مصادقة، real-time subscriptions, file upload، و rate limiting. مع Apollo Server و Prisma.</p><p>Complete GraphQL API starter. Includes: authentication, real-time subscriptions, file upload, and rate limiting. With Apollo Server and Prisma.</p>',
                'detail' => '<p>التقنيات: Apollo Server, Prisma, PostgreSQL, Redis, JWT</p>',
                'specification' => '<p>الصيغة: ZIP | التوثيق: كامل | الحجم: 5.5 MB</p>',
                'tags' => ['api', 'boilerplate', 'javascript', 'nodejs'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'SaaS Landing Page Kit (Next.js) | مجموعة صفحة هبوط SaaS',
                'price' => 32.99, 'sale_price' => 24.99,
                'description' => '<p>مجموعة صفحات هبوط لتطبيقات SaaS مبني على Next.js. يشمل: صفحة رئيسية، تسعير، الميزات، الشهادات، وصفحة الدفع.</p><p>SaaS landing page collection built on Next.js. Includes: homepage, pricing, features, testimonials, and checkout page.</p>',
                'detail' => '<p>التقنيات: Next.js 14, TypeScript, Tailwind CSS, Framer Motion</p>',
                'specification' => '<p>الصيغة: ZIP | الصفحات: 8 | الحجم: 6.8 MB</p>',
                'tags' => ['react', 'tailwind', 'javascript', 'component', 'boilerplate'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'Chatbot Widget Component | مكوّن شات بوت',
                'price' => 19.99, 'sale_price' => 0,
                'description' => '<p>مكون شات بوت ويب قابل للتخصيص. يدعم النصوص، الصور، الأزرار السريعة، والإيموجي. متوافق مع React و Vue و vanilla JS.</p><p>Customizable web chatbot widget. Supports text, images, quick buttons, and emojis. Compatible with React, Vue, and vanilla JS.</p>',
                'detail' => '<p>التقنيات: JavaScript, WebSocket, CSS3 | التوافق: React, Vue, Vanilla</p>',
                'specification' => '<p>الصيغة: ZIP | الإصدارات: React + Vue + Vanilla | الحجم: 2.1 MB</p>',
                'tags' => ['react', 'vue', 'component', 'javascript'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'CI/CD Pipeline Templates | قوالب أنابيط CI/CD',
                'price' => 16.99, 'sale_price' => 0,
                'description' => '<p>مجموعة قوالب CI/CD جاهزة لـ GitHub Actions, GitLab CI, و Jenkins. يشمل: Laravel, Node.js, Python, Docker, و AWS deployment.</p><p>Ready CI/CD template collection for GitHub Actions, GitLab CI, and Jenkins. Includes: Laravel, Node.js, Python, Docker, and AWS deployment.</p>',
                'detail' => '<p>المنصات: GitHub Actions, GitLab CI, Jenkins | التقنيات: 5+</p>',
                'specification' => '<p>الصيغة: ZIP (YAML files) | القوالب: 15 | الحجم: 0.5 MB</p>',
                'tags' => ['api', 'boilerplate', 'nodejs', 'laravel', 'python'],
                'avg_rating' => 4.4,
            ],
            [
                'name' => 'Electron Desktop App Template | قالب تطبيق سطح مكتب',
                'price' => 28.99, 'sale_price' => 19.99,
                'description' => '<p>قالب تطبيق سطح مكتب بـ Electron + React. يشمل: إطار windowless، system tray, auto-update، و native menus. جاهز للنشر على Windows و Mac و Linux.</p><p>Desktop app template with Electron + React. Includes: frameless window, system tray, auto-update, and native menus. Ready for Windows, Mac, and Linux.</p>',
                'detail' => '<p>التقنيات: Electron, React, TypeScript, electron-builder</p>',
                'specification' => '<p>الصيغة: ZIP | التوثيق: كامل | الحجم: 8.5 MB</p>',
                'tags' => ['react', 'component', 'javascript', 'boilerplate'],
                'avg_rating' => 4.6,
            ],
        ];
    }
}
