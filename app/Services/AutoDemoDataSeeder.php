<?php

/**
 * ================================================================
 * AutoDemoDataSeeder - MaxCart / TakiwinCart
 * ================================================================
 * يملأ المتجر الجديد بـ 36 منتج ديمو في 12 تصنيف
 * يشمل: تصنيفات + منتجات + تاغز + ريفيوز + صور غلاف وهمية
 *
 * الاستخدام:
 *   $seeder = new \App\Services\AutoDemoDataSeeder();
 *   $seeder->run($storeId, $userId);
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
    private string $themeName;
    private string $uploadDir;

    private const THEMES = ['greentic', 'stylique', 'techzonix'];

    private const DEMO_CATEGORY_IMAGES = [
        'templates'           => 'template-store.png',
        'design-assets'       => 'creative-design-assets.png',
        'social-media'        => 'social-media-assets.png',
        'ai-prompts'          => 'ai-prompts.png',
        'ebooks-pdf'          => 'notion-productivity.png',
        'code-scripts'        => 'code-developer-assets.png',
        'business-tools'      => 'creative-design-assets.png',
        'productivity'        => 'notion-productivity.png',
        'courses-education'   => 'template-store.png',
        'audio-media'         => 'social-media-assets.png',
        'photos-video'        => 'creative-design-assets.png',
        'themes-store'        => 'template-store.png',
    ];

    private const DEMO_CATEGORY_ICONS = [
        'templates'           => 'icon-template-store.png',
        'design-assets'       => 'icon-creative-design-assets.png',
        'social-media'        => 'icon-social-media-assets.png',
        'ai-prompts'          => 'icon-ai-prompts.png',
        'ebooks-pdf'          => 'icon-notion-productivity.png',
        'code-scripts'        => 'icon-code-developer-assets.png',
        'business-tools'      => 'icon-creative-design-assets.png',
        'productivity'        => 'icon-notion-productivity.png',
        'courses-education'   => 'icon-template-store.png',
        'audio-media'         => 'icon-social-media-assets.png',
        'photos-video'        => 'icon-creative-design-assets.png',
        'themes-store'        => 'icon-template-store.png',
    ];

    private const DEMO_PRODUCT_IMAGES = [
        'templates'           => 'prod-template-store.png',
        'design-assets'       => 'prod-creative-design-assets.png',
        'social-media'        => 'prod-social-media-assets.png',
        'ai-prompts'          => 'prod-ai-prompts.png',
        'ebooks-pdf'          => 'prod-notion-productivity.png',
        'code-scripts'        => 'prod-code-developer-assets.png',
        'business-tools'      => 'prod-creative-design-assets.png',
        'productivity'        => 'prod-notion-productivity.png',
        'courses-education'   => 'prod-template-store.png',
        'audio-media'         => 'prod-social-media-assets.png',
        'photos-video'        => 'prod-creative-design-assets.png',
        'themes-store'        => 'prod-template-store.png',
    ];

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

    public function run(int $storeId, int $userId, string $lang = 'ar', ?string $theme = null): void
    {
        $this->storeId  = $storeId;
        $this->userId   = $userId;
        $this->lang     = $lang;

        if ($theme) {
            $this->themeName = $theme;
        } else {
            $store = DB::table('stores')->where('id', $storeId)->first();
            $this->themeName = $store->theme_id ?? 'greentic';
        }

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

    private function copyDemoImage(string $demoFile, string $subFolder = ''): string
    {
        $demoSource = base_path("themes/{$this->themeName}/assets/images/{$demoFile}");

        if (!File::exists($demoSource) && str_starts_with($demoFile, 'icon-')) {
            $altFile = substr($demoFile, 5);
            $altSource = base_path("themes/{$this->themeName}/assets/images/{$altFile}");
            if (File::exists($altSource)) {
                $demoSource = $altSource;
            }
        }

        if (!File::exists($demoSource)) {
            foreach (self::THEMES as $fallbackTheme) {
                if ($fallbackTheme === $this->themeName) continue;
                $fallbackPath = base_path("themes/{$fallbackTheme}/assets/images/{$demoFile}");
                if (File::exists($fallbackPath)) {
                    $demoSource = $fallbackPath;
                    break;
                }
                if (str_starts_with($demoFile, 'icon-')) {
                    $altFile = substr($demoFile, 5);
                    $altPath = base_path("themes/{$fallbackTheme}/assets/images/{$altFile}");
                    if (File::exists($altPath)) {
                        $demoSource = $altPath;
                        break;
                    }
                }
            }
        }

        if (!File::exists($demoSource)) {
            return '';
        }

        $targetDir = base_path($this->uploadDir . ($subFolder ? "/{$subFolder}" : ''));
        if (!File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $filename = uniqid() . '_' . $demoFile;
        $targetPath = $targetDir . '/' . $filename;
        File::copy($demoSource, $targetPath);

        return $this->uploadDir . ($subFolder ? "/{$subFolder}" : '') . "/{$filename}";
    }

    private function seedCategories(): void
    {
        foreach ($this->getCategoriesData() as $cat) {
            $demoFile = self::DEMO_CATEGORY_IMAGES[$cat['slug']] ?? null;
            $imagePath = $demoFile ? $this->copyDemoImage($demoFile, 'categories') : '';

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
            ['name' => 'قوالب | Templates',                          'slug' => 'templates'],
            ['name' => 'أصول التصميم | Design Assets',                'slug' => 'design-assets'],
            ['name' => 'باقات السوشيال ميديا | Social Media Kits',   'slug' => 'social-media'],
            ['name' => 'الذكاء الاصطناعي | AI & Prompts',            'slug' => 'ai-prompts'],
            ['name' => 'كتب ومراجع | eBooks & PDFs',                  'slug' => 'ebooks-pdf'],
            ['name' => 'أكواد وبرمجيات | Code & Scripts',             'slug' => 'code-scripts'],
            ['name' => 'أدوات أعمال | Business Tools',                'slug' => 'business-tools'],
            ['name' => 'أدوات إنتاجية | Productivity',                'slug' => 'productivity'],
            ['name' => 'دورات تعليمية | Courses & Education',         'slug' => 'courses-education'],
            ['name' => 'صوتيات ووسائط | Audio & Media',              'slug' => 'audio-media'],
            ['name' => 'صور وفيديو | Photos & Video',                'slug' => 'photos-video'],
            ['name' => 'قوالب متاجر | Themes & Store Kits',           'slug' => 'themes-store'],
        ];
    }

    private function seedTags(): void
    {
        foreach ($this->getTagsData() as $tag) {
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
            'html', 'css', 'responsive', 'bootstrap', 'wordpress', 'shopify',
            'landing-page', 'email-template', 'ui-kit', 'website-template',
            'notion', 'productivity', 'crm', 'planning', 'organization',
            'dashboard', 'tracker', 'workflow', 'template', 'management',
            'instagram', 'youtube', 'tiktok', 'social-media', 'content-creator',
            'story', 'reel', 'thumbnail', 'branding', 'canva',
            'chatgpt', 'midjourney', 'ai', 'prompts', 'stable-diffusion',
            'dall-e', 'ai-art', 'automation', 'machine-learning', 'copywriting',
            'fonts', 'icons', 'mockups', 'illustrations', 'textures',
            'graphics', 'vectors', 'svg', 'gradients', 'printable',
            'react', 'vue', 'javascript', 'python', 'api',
            'boilerplate', 'component', 'laravel', 'nodejs', 'tailwind',
            'ebook', 'pdf', 'guide', 'business', 'finance',
            'course', 'education', 'tutorial', 'video', 'music',
            'photo', 'video-editing', 'stock', 'theme', 'ecommerce',
        ];
    }

    private function seedProducts(): void
    {
        foreach ($this->getAllProductsData() as $catSlug => $products) {
            $categoryId = $this->categoryIdMap[$catSlug];

            foreach ($products as $idx => $p) {
                $slug = Str::slug($p['name']);
                $existingSlug = $slug;
                $counter = 1;
                while (DB::table('products')->where('slug', $existingSlug)->where('store_id', $this->storeId)->exists()) {
                    $existingSlug = $slug . '-' . $counter++;
                }
                $slug = $existingSlug;

                $tagIds = [];
                foreach ($p['tags'] as $tagName) {
                    if (isset($this->tagIdMap[$tagName])) {
                        $tagIds[] = $this->tagIdMap[$tagName];
                    }
                }

                $isTrending = ($idx < 2) ? 1 : 0;

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
                    'digital_type'          => 'file',
                    'max_downloads'         => 5,
                    'download_expiry_days'  => 30,
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

                $this->seedProductImages($productId, $catSlug, $idx);
                $this->seedReviews($productId, $categoryId, $p['avg_rating'] ?? 4.5);
            }
        }
    }

    private function getGalleryCount(string $catSlug, int $idx): int
    {
        return 2 + (($idx + ord($catSlug[0])) % 3);
    }

    private function seedProductImages(int $productId, string $catSlug, int $idx): void
    {
        $imageCount = $this->getGalleryCount($catSlug, $idx);
        $demoFile = self::DEMO_PRODUCT_IMAGES[$catSlug] ?? null;

        for ($i = 1; $i <= $imageCount; $i++) {
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
                'title'       => $this->lang === 'ar' ? $title['ar'] : $title['en'],
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

    private function getAllProductsData(): array
    {
        return [
            'templates'           => $this->getTemplatesProducts(),
            'design-assets'       => $this->getDesignAssetsProducts(),
            'social-media'        => $this->getSocialMediaProducts(),
            'ai-prompts'          => $this->getAIPromptsProducts(),
            'ebooks-pdf'          => $this->getEbooksProducts(),
            'code-scripts'        => $this->getCodeScriptsProducts(),
            'business-tools'      => $this->getBusinessToolsProducts(),
            'productivity'        => $this->getProductivityProducts(),
            'courses-education'   => $this->getCoursesProducts(),
            'audio-media'         => $this->getAudioMediaProducts(),
            'photos-video'        => $this->getPhotosVideoProducts(),
            'themes-store'        => $this->getThemesStoreProducts(),
        ];
    }

    // ========== TEMPLATES (3 products) ==========
    private function getTemplatesProducts(): array
    {
        return [
            [
                'name' => 'Startup Landing Page Pro',
                'price' => 29.99, 'sale_price' => 19.99,
                'description' => '<p>مجموعة من 15 قالب صفحة هبوط عالية التحويل مصممة خصيصاً للشركات الناشئة. كل قالب متجاوب، محسّن للسرعة، ويشمل أقسام البطل، شبكة الميزات، جداول الأسعار، الشهادات، وأزرار الإجراء.</p>',
                'detail' => '<p>HTML5, CSS3, JavaScript, Bootstrap 5 | Pages: 15 | Responsive: Yes</p>',
                'specification' => '<p>ZIP | 4.2 MB | Documentation: Included</p>',
                'tags' => ['html', 'css', 'responsive', 'bootstrap', 'landing-page'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'Email Template Mega Pack',
                'price' => 19.99, 'sale_price' => 14.99,
                'description' => '<p>أكثر من 50 قالب إيميل تسويقي احترافي يشمل سلاسل الترحيب، حملات ترويجية، نشرات، إيميلات معاملات، وتسلسلات إعادة التفاعل. مختبر على جميع عملاء البريد الرئيسية.</p>',
                'detail' => '<p>HTML | Compatible: Gmail, Outlook, Apple Mail | Designs: 50+</p>',
                'specification' => '<p>ZIP | 1.8 MB | Dark Mode: Yes</p>',
                'tags' => ['email-template', 'html', 'css', 'responsive', 'branding'],
                'avg_rating' => 4.4,
            ],
            [
                'name' => 'Resume & Portfolio Kit',
                'price' => 24.99, 'sale_price' => 0,
                'description' => '<p>قوالب سيرة ذاتية وبورتفوليو بتصميم عصري واحترافي. يشمل 12 تصميم سيرة ذاتية، 6 تخطيطات بورتفوليو، قوالب خطابات تقديم، ودليل بناء العلامة الشخصية.</p>',
                'detail' => '<p>DOCX + PSD | Designs: 18 | Languages: Arabic + English</p>',
                'specification' => '<p>ZIP | 3.5 MB | Customizable Colors: Yes</p>',
                'tags' => ['html', 'responsive', 'website-template', 'branding'],
                'avg_rating' => 4.5,
            ],
        ];
    }

    // ========== DESIGN ASSETS (3 products) ==========
    private function getDesignAssetsProducts(): array
    {
        return [
            [
                'name' => '3,000+ Vector Icon Pack',
                'price' => 39.99, 'sale_price' => 0,
                'description' => '<p>مجموعة ضخمة تضم أكثر من 3000 أيقونة متجهة في 30+ فئة تشمل الأعمال، التقنية، المالية، السوشيال ميديا، والتجارة الإلكترونية. بصيغ SVG و PNG وأحجام متعددة.</p>',
                'detail' => '<p>SVG, PNG, Figma, Sketch | Categories: 30+ | Styles: Line, Solid, Duo-tone</p>',
                'specification' => '<p>ZIP | 2.5 GB | Commercial License: Yes</p>',
                'tags' => ['icons', 'graphics', 'vectors', 'svg', 'branding'],
                'avg_rating' => 4.8,
            ],
            [
                'name' => 'Brand Identity Starter Kit',
                'price' => 49.99, 'sale_price' => 39.99,
                'description' => '<p>طقم هوية بصرية متكامل يشمل قوالب لوجو، بطاقات أعمال، رؤوس أوراق، بانرات سوشيال ميديا، ودليل العلامة التجارية. 8 ثيمات ألوان محددة مسبقاً.</p>',
                'detail' => '<p>AI, PSD, PNG | Theme Presets: 8 | Brand Guide: Yes</p>',
                'specification' => '<p>ZIP | 45 MB | Logo Variants: 50+</p>',
                'tags' => ['graphics', 'branding', 'mockups', 'printable'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'Infographic Builder Elements',
                'price' => 29.99, 'sale_price' => 0,
                'description' => '<p>أكثر من 500 عنصر إنفوجرافيك يشمل رسوم بيانية، مخططات، جداول زمنية، عمليات، وعناصر تصور بيانات. متوافق مع Illustrator و PowerPoint و Canva.</p>',
                'detail' => '<p>AI, PPTX, Canva | Elements: 500+ | Chart Types: 20+</p>',
                'specification' => '<p>ZIP | 85 MB | Editable: Fully</p>',
                'tags' => ['graphics', 'vectors', 'branding'],
                'avg_rating' => 4.5,
            ],
        ];
    }

    // ========== SOCIAL MEDIA (3 products) ==========
    private function getSocialMediaProducts(): array
    {
        return [
            [
                'name' => 'Instagram Growth Kit 2025',
                'price' => 24.99, 'sale_price' => 19.99,
                'description' => '<p>أدوات تسويق إنستقرام شاملة: 200+ قالب بوست، 100 قالب ستوري، أغلفة هايلايت، قوالب ريلز، وتقويم محتوى 30 يوم. يشمل أدلة الهاشتاقات واستراتيجية التفاعل.</p>',
                'detail' => '<p>Canva + PSD | Posts: 200+ | Stories: 100 | Reels: 50+</p>',
                'specification' => '<p>Canva Link | 120+ Designs | Hashtag Guide: Yes</p>',
                'tags' => ['instagram', 'social-media', 'content-creator', 'canva', 'branding'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'LinkedIn Professional Pack',
                'price' => 19.99, 'sale_price' => 0,
                'description' => '<p>قوالب إنستقرام مهنية تشمل قوالب بوست، تصميمات بانر، تخطيطات مقالات، ودليل تحسين الملف الشخصي. مثالية للمحترفين والمديرين التنفيذيين والباحثين عن عمل.</p>',
                'detail' => '<p>Canva | Styles: Professional | Sections: 8</p>',
                'specification' => '<p>Canva Link | 20 Designs | Personal Branding: Yes</p>',
                'tags' => ['social-media', 'content-creator', 'branding', 'canva'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'YouTube Channel Starter',
                'price' => 34.99, 'sale_price' => 0,
                'description' => '<p>كل ما تحتاجه لإطلاق قناة يوتيوب احترافية: قوالب فنونات، تصاميم بانر، قوالب شاشات بداية ونهاية، وتقويم محتوى. يشمل دليل استراتيجي وبحث كلمات مفتاحية.</p>',
                'detail' => '<p>PSD + Canva | Thumbnails: 30+ | Banner: 15 | Overlays: 20</p>',
                'specification' => '<p>ZIP + Canva Link | 65+ Designs | Strategy Guide: Yes</p>',
                'tags' => ['youtube', 'thumbnail', 'social-media', 'content-creator'],
                'avg_rating' => 4.8,
            ],
        ];
    }

    // ========== AI PROMPTS (3 products) ==========
    private function getAIPromptsProducts(): array
    {
        return [
            [
                'name' => 'ChatGPT Prompt Master Library',
                'price' => 29.99, 'sale_price' => 19.99,
                'description' => '<p>مكتبة منظمة تضم 500+ برومبت ChatGPT محسّن في 25 فئة تشمل التسويق، البرمجة، الكتابة، استراتيجيات الأعمال، التعليم، والمشاريع الإبداعية. يشمل تعليمات الاستخدام.</p>',
                'detail' => '<p>ChatGPT | Prompts: 500+ | Categories: 25 | Chaining Techniques: Yes</p>',
                'specification' => '<p>PDF + TXT + Notion | 8.5 MB | Examples: Yes</p>',
                'tags' => ['chatgpt', 'prompts', 'ai', 'copywriting', 'automation'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'AI Image Generation Prompt Bible',
                'price' => 24.99, 'sale_price' => 0,
                'description' => '<p>دليل شامل مع 300+ برومبت لمنصات توليد الصور بالذكاء الاصطناعي مثل Midjourney وDALL-E و Stable Diffusion. يشمل أنماط فنية، تصوير منتجات، تصميم لوجوهات.</p>',
                'detail' => '<p>Midjourney, DALL-E, Stable Diffusion | Prompts: 300+ | Control Params: Yes</p>',
                'specification' => '<p>PDF + TXT | 3.2 MB | Negative Prompts: Yes</p>',
                'tags' => ['midjourney', 'dall-e', 'prompts', 'ai-art', 'ai'],
                'avg_rating' => 4.8,
            ],
            [
                'name' => 'AI Business Automation Kit',
                'price' => 49.99, 'sale_price' => 39.99,
                'description' => '<p>مجموعة أدوات أتمتة بالذكاء الاصطناعي للشركات: قوالب تدفق المحادثات، سكربتات خدمة العملاء، مكتبات برومبت تحليل البيانات، وأدلة التكامل مع المنصات الشائعة.</p>',
                'detail' => '<p>Multi-platform | Workflows: 20+ | Integration Guides: 10+</p>',
                'specification' => '<p>PDF + JSON + Notion | 5.5 MB | ROI Framework: Yes</p>',
                'tags' => ['chatgpt', 'ai', 'automation', 'business'],
                'avg_rating' => 4.6,
            ],
        ];
    }

    // ========== EBOOKS & PDFS (3 products) ==========
    private function getEbooksProducts(): array
    {
        return [
            [
                'name' => 'Digital Marketing Mastery Guide',
                'price' => 34.99, 'sale_price' => 24.99,
                'description' => '<p>دليل شامل يتجاوز 200 صفحة يغطي جميع جوانب التسويق الرقمي: SEO، إعلانات PPC، تسويق السوشيال ميديا، التسويق عبر البريد الإلكتروني، واستراتيجية المحتوى والتحليلات.</p>',
                'detail' => '<p>PDF | Pages: 200+ | Language: Arabic + English | Updated: 2025</p>',
                'specification' => '<p>PDF | 15 MB | Case Studies: 10+ | Analytics Templates: 5</p>',
                'tags' => ['ebook', 'pdf', 'guide', 'business'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'Startup Business Blueprint',
                'price' => 39.99, 'sale_price' => 0,
                'description' => '<p>دليل شامل للمشاريع الناشئة يشمل لوحة نموذج العمل، تقنيات التحقق من السوق، استراتيجيات جمع التمويل، بناء الفريق، منهجيات تطوير المنتج، واستراتيجيات النمو.</p>',
                'detail' => '<p>PDF | Pages: 180 | Templates: 15 | Financial Models: 5</p>',
                'specification' => '<p>PDF | 12 MB | Pitch Deck Template: Yes | Checklist: Yes</p>',
                'tags' => ['ebook', 'pdf', 'business', 'finance'],
                'avg_rating' => 4.8,
            ],
            [
                'name' => 'Freelancer Success Playbook',
                'price' => 19.99, 'sale_price' => 0,
                'description' => '<p>دليل عملي للمستقلين يشمل استقطاب العملاء، استراتيجيات التسعير، كتابة العروض، إدارة المشاريع، وبناء عمل مستقل مستدام. يشمل قوالب عقود وفواتير.</p>',
                'detail' => '<p>PDF | Pages: 120 | Contract Templates: 5 | Invoice Templates: 3</p>',
                'specification' => '<p>PDF | 8 MB | Portfolio Guide: Yes | Time Tracking: Yes</p>',
                'tags' => ['ebook', 'pdf', 'guide', 'business'],
                'avg_rating' => 4.5,
            ],
        ];
    }

    // ========== CODE & SCRIPTS (3 products) ==========
    private function getCodeScriptsProducts(): array
    {
        return [
            [
                'name' => 'Laravel E-Commerce Starter',
                'price' => 59.99, 'sale_price' => 44.99,
                'description' => '<p>تطبيق متجر Laravel جاهز للإنتاج مع مصادقة المستخدمين، إدارة المنتجات، سلة التسوق، نظام الدفع (Stripe/PayPal)، إدارة الطلبات، ولوحة تحكم. يشمل دعم متعدد اللغات.</p>',
                'detail' => '<p>Laravel 11, PHP 8.2, MySQL | API: RESTful | Multi-language: Yes</p>',
                'specification' => '<p>ZIP | 8.2 MB | Full Documentation | API Docs: Swagger</p>',
                'tags' => ['laravel', 'api', 'boilerplate', 'component', 'ecommerce'],
                'avg_rating' => 4.9,
            ],
            [
                'name' => 'Python Data Automation Scripts',
                'price' => 34.99, 'sale_price' => 0,
                'description' => '<p>مجموعة من 50+ سكربت Python للأتمتة يشمل معالجة البيانات، Web Scraping، تكامل API، إدارة الملفات، أتمتة البريد الإلكتروني، وعمليات قواعد البيانات. كل سكربت موثق بالكامل.</p>',
                'detail' => '<p>Python 3.10+ | Libraries: requests, pandas, selenium | Scripts: 50+</p>',
                'specification' => '<p>ZIP | 3.8 MB | Error Handling: Yes | Logging: Yes</p>',
                'tags' => ['python', 'api', 'automation', 'boilerplate'],
                'avg_rating' => 4.6,
            ],
            [
                'name' => 'REST API Boilerplate Pro',
                'price' => 44.99, 'sale_price' => 0,
                'description' => '<p>نقطة بداية REST API احترافية مع مصادقة JWT، تحديد المعدل، التحقق من الطلبات، طبقات التخزين المؤقت، معالجة الطوابير، والتعامل الشامل مع الأخطاء. يشمل Docker.</p>',
                'detail' => '<p>Node.js, Express, MongoDB | Docker: Yes | Swagger: Yes | Redis: Yes</p>',
                'specification' => '<p>ZIP | 4.5 MB | CI/CD: GitHub Actions | Testing: Jest</p>',
                'tags' => ['nodejs', 'api', 'boilerplate', 'javascript'],
                'avg_rating' => 4.7,
            ],
        ];
    }

    // ========== BUSINESS TOOLS (3 products) ==========
    private function getBusinessToolsProducts(): array
    {
        return [
            [
                'name' => 'Financial Model Templates',
                'price' => 39.99, 'sale_price' => 0,
                'description' => '<p>نماذج مالية احترافية تشمل نماذج بيانات مالية ثلاثية، تقييمات DCF، تنبؤات الميزانية، تدفقات نقدية، وتحليلات السيناريو. لوحات تحكم ديناميكية وجداول حساسية.</p>',
                'detail' => '<p>Excel | Models: 3-Statement, DCF, Budget | Dashboards: 5</p>',
                'specification' => '<p>XLSX | 12 MB | Sensitivity Tables: Yes | Charts: 20+</p>',
                'tags' => ['business', 'finance', 'management'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'Project Management Toolkit',
                'price' => 29.99, 'sale_price' => 0,
                'description' => '<p>مجموعة أدوات إدارة المشاريع شاملة تشمل قوالب مخطط جانت، أطر تقييم المخاطر، خطط تواصل أصحاب المصلحة، لوحات تخطيط Sprint، وقوالب مراجعات استرجاعية.</p>',
                'detail' => '<p>Notion + Excel | Frameworks: Agile, Scrum, Kanban | Templates: 25+</p>',
                'specification' => '<p>ZIP + Notion | 5 MB | Reporting Dashboards: 3</p>',
                'tags' => ['management', 'planning', 'tracker', 'template'],
                'avg_rating' => 4.6,
            ],
            [
                'name' => 'HR & Hiring Templates',
                'price' => 24.99, 'sale_price' => 0,
                'description' => '<p>أدوات موارد بشرية أساسية تشمل قوالب وصف وظائف، بطاقات تقييم المقابلات، قوائم فحص الإعداد، نماذج مراجعات الأداء، أطر كتيب الموظفين، وإجراءات فصل الموظفين.</p>',
                'detail' => '<p>DOCX + PDF + Excel | Templates: 30+ | Compliance Guide: Yes</p>',
                'specification' => '<p>ZIP | 8 MB | Salary Benchmarks: Yes | Org Charts: 5</p>',
                'tags' => ['business', 'management', 'template'],
                'avg_rating' => 4.4,
            ],
        ];
    }

    // ========== PRODUCTIVITY (3 products) ==========
    private function getProductivityProducts(): array
    {
        return [
            [
                'name' => 'Notion Life OS Template',
                'price' => 29.99, 'sale_price' => 0,
                'description' => '<p>مساحة عمل Notion شاملة تشمل متتبع عادات، نظام يوميات، مخطط أهداف، قائمة قراءة، متتبع مصروفات، مخطط وجبات، ولوحة إدارة مشاريع. أكثر من 30 قاعدة بيانات مترابطة.</p>',
                'detail' => '<p>Notion | Databases: 30+ | Smart Filters: Yes | Formula Views: Yes</p>',
                'specification' => '<p>Notion Template | Setup Guide: Yes | Cross-references: 50+</p>',
                'tags' => ['notion', 'productivity', 'management', 'tracker', 'template'],
                'avg_rating' => 4.8,
            ],
            [
                'name' => 'Meeting & Agenda Templates',
                'price' => 14.99, 'sale_price' => 0,
                'description' => '<p>مجموعة أدوات إدارة الاجتماعات احترافية تشمل قوالب جدول أعمال، صيغ محاضر اجتماعات، متتبع بنود العمل، سجل القرارات، وقوالب متابعة. تغطي اجتماعات المجلس والمزايين.</p>',
                'detail' => '<p>Notion + DOCX | Meeting Types: 8 | Action Tracker: Yes</p>',
                'specification' => '<p>ZIP | 2 MB | Facilitation Guide: Yes | Time Tips: Yes</p>',
                'tags' => ['notion', 'management', 'template', 'planning'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'Content Calendar System',
                'price' => 19.99, 'sale_price' => 0,
                'description' => '<p>نظام تخطيط محتوى استراتيجي مع تقويم تحرير، قوالب بحث مواضيع، خط أنابيب محتوى، قوائم نشر، ولوحات تتبع الأداء. يدعم التخطيط متعدد المنصات عبر المدونات والسوشيال ميديا.</p>',
                'detail' => '<p>Notion + Excel | Platforms: 5 | Pipeline: Yes | Performance: Yes</p>',
                'specification' => '<p>ZIP | 3.2 MB | Multi-platform: Blog, Social, Email, Video</p>',
                'tags' => ['planning', 'management', 'workflow', 'social-media'],
                'avg_rating' => 4.7,
            ],
        ];
    }

    // ========== COURSES & EDUCATION (3 products) ==========
    private function getCoursesProducts(): array
    {
        return [
            [
                'name' => 'Full-Stack Web Dev Course',
                'price' => 59.99, 'sale_price' => 44.99,
                'description' => '<p>دورة تطوير ويب شاملة تغطي HTML, CSS, JavaScript, React, Node.js, قواعد البيانات، والنشر. تشمل 80+ درس فيديو، تمارين برمجية، مشاريع واقعية، وأوراق غش، وشهادة إتمام.</p>',
                'detail' => '<p>Video + Source Code | Hours: 80+ | Projects: 15 Real-world | Certificate: Yes</p>',
                'specification' => '<p>ZIP (MP4 + ZIP) | 2.5 GB | Level: Beginner to Advanced | Lifetime Access</p>',
                'tags' => ['course', 'education', 'html', 'javascript', 'api'],
                'avg_rating' => 4.8,
            ],
            [
                'name' => 'UI/UX Design Masterclass',
                'price' => 44.99, 'sale_price' => 0,
                'description' => '<p>دورة تصميم واجهات المستخدم احترافية تغطي التفكير التصميمي، النماذج الأولية، النماذج، البحث المستخدم، اختبارات الاستخدام، وأنظمة التصميم. تشمل 40+ تمرين عملي وملفات Figma.</p>',
                'detail' => '<p>Figma + Video | Exercises: 40+ | Case Studies: 8 | Portfolio Guide: Yes</p>',
                'specification' => '<p>ZIP (MP4 + FIG) | 1.8 GB | Certificate: Yes | Figma Files: Included</p>',
                'tags' => ['course', 'education', 'design', 'template'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'Digital Marketing Course',
                'price' => 49.99, 'sale_price' => 0,
                'description' => '<p>دورة تسويق رقمي منظمة تغطي أساسيات SEO، إعلانات Google، إعلانات فيسبوك، أتمتة التسويق عبر البريد الإلكتروني، واستراتيجية السوشيال ميديا، وتحسين التحويلات.</p>',
                'detail' => '<p>Video + PDF Resources | Campaign Templates: 20 | Budget Calculator: Yes</p>',
                'specification' => '<p>ZIP (MP4 + PDF) | 1.5 GB | Analytics Dashboard: Yes | Cert Prep: Yes</p>',
                'tags' => ['course', 'education', 'business'],
                'avg_rating' => 4.6,
            ],
        ];
    }

    // ========== AUDIO & MEDIA (3 products) ==========
    private function getAudioMediaProducts(): array
    {
        return [
            [
                'name' => 'Cinematic Music Collection',
                'price' => 39.99, 'sale_price' => 0,
                'description' => '<p>مجموعة من 50 مقطوعة موسيقية سينمائية خالية من حقوق الطبع والنشر مناسبة للفيديوهات والعروض التقديمية والبودكاست. تشمل مقطوعات أوركسترالية، موسيقى خلفية هادئة، وموسيقى شركات.</p>',
                'detail' => '<p>WAV + MP3 | Tracks: 50 | Genres: Orchestral, Ambient, Corporate | Loops: Yes</p>',
                'specification' => '<p>ZIP | 850 MB | License: Royalty-Free | Duration: 2-5 min each</p>',
                'tags' => ['music', 'audio'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'Podcast Starter Sound Kit',
                'price' => 24.99, 'sale_price' => 0,
                'description' => '<p>مجموعة موارد صوتية شاملة لصناع البودكاست تشمل موسيقى مقدمة وخاتمة، مؤثرات انتقالية، حلقات موسيقية خلفية، مكتبة مؤثرات صوتية، وأصوات طبيعة. يشمل نصوص تعليق صوتي.</p>',
                'detail' => '<p>WAV + MP3 | Intros: 10 | Outros: 10 | SFX: 200+ | Background: 30 loops</p>',
                'specification' => '<p>ZIP | 320 MB | Voice-over Scripts: 5 | Quick-Start Guide: Yes</p>',
                'tags' => ['music', 'audio'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'Music Loops & Sample Pack',
                'price' => 29.99, 'sale_price' => 0,
                'description' => '<p>مجموعة متنوعة تضم 200+ حلقة موسيقية وعينات تشمل إيقاعات هيب هوب، حلقات إلكتروني، عزف جيتار، أنماط طبول، وباص. جميع العينات خالية من حقوق الطبع والنشر بصيغة WAV.</p>',
                'detail' => '<p>WAV | Genre: Hip-hop, Electronic, Acoustic, Drums, Bass | BPM: 80-160</p>',
                'specification' => '<p>ZIP | 450 MB | License: Royalty-Free | Quality: 44.1kHz/16bit</p>',
                'tags' => ['music', 'audio'],
                'avg_rating' => 4.6,
            ],
        ];
    }

    // ========== PHOTOS & VIDEO (3 products) ==========
    private function getPhotosVideoProducts(): array
    {
        return [
            [
                'name' => 'Professional Stock Photo Bundle',
                'price' => 34.99, 'sale_price' => 0,
                'description' => '<p>مجموعة منتقاة تضم 500+ صورة فوتوغرافية عالية الدقة تغطي فئات الأعمال، التقنية، نمط الحياة، الطعام، الطبيعة، والهندسة المعمارية. جميع الصور 4K ومصنفة ألوانياً.</p>',
                'detail' => '<p>JPG + PNG | Resolution: 4K | Categories: 8 | Color Graded: Yes</p>',
                'specification' => '<p>ZIP | 1.2 GB | Model Releases: Included | Commercial Use: Yes</p>',
                'tags' => ['photo', 'stock'],
                'avg_rating' => 4.7,
            ],
            [
                'name' => 'Video Intro & Outro Templates',
                'price' => 29.99, 'sale_price' => 0,
                'description' => '<p>مجموعة من 30 قالب مقدمة وخاتمة فيديو احترافية متوافقة مع After Effects و Premiere Pro. تشمل أنماط الشركات، الإبداعية، التقنية، والسينمائية مع نصوص وألوان قابلة للتخصيص.</p>',
                'detail' => '<p>After Effects + Premiere Pro | Templates: 30 | Styles: Corporate, Creative, Tech, Cinematic</p>',
                'specification' => '<p>ZIP | 650 MB | Tutorial Videos: Yes | Easy Customization: Yes</p>',
                'tags' => ['video'],
                'avg_rating' => 4.5,
            ],
            [
                'name' => 'Mockup Generator Collection',
                'price' => 39.99, 'sale_price' => 0,
                'description' => '<p>مجموعة ضخمة تضم 200+ قالب موك آب تشمل موك آب iPhone، MacBook، لقطات شاشة مواقع، موك آب تيشيرتات، عبوات تغليف، مواد مطبوعية، ومواد العلامة التجارية. بصيغة PSD مع كائنات ذكية.</p>',
                'detail' => '<p>PSD | Categories: 10+ | Smart Objects: Yes | Photorealistic: Yes</p>',
                'specification' => '<p>ZIP | 2.8 GB | Photoshop Required: CS6+ | Video Tutorial: Yes</p>',
                'tags' => ['mockups', 'graphics', 'branding'],
                'avg_rating' => 4.7,
            ],
        ];
    }

    // ========== THEMES & STORE KITS (3 products) ==========
    private function getThemesStoreProducts(): array
    {
        return [
            [
                'name' => 'Modern Store Theme Pro',
                'price' => 49.99, 'sale_price' => 39.99,
                'description' => '<p>ثيم متجر إلكتروني متميز بتصميم حديث يشمل تنقل ميغا، معاينة سريعة للمنتجات، قائمة المفضلة، تصفية متقدمة، وتدفق سداد محسّن. 10+ تخطيطات صفحة رئيسية ومتجاوب بالكامل.</p>',
                'detail' => '<p>HTML5, CSS3, JavaScript, Bootstrap 5 | Homepage Variants: 10+ | Speed: Optimized</p>',
                'specification' => '<p>ZIP | 6.3 MB | RTL Support: Yes | Documentation: Full</p>',
                'tags' => ['html', 'css', 'responsive', 'bootstrap', 'ecommerce', 'website-template'],
                'avg_rating' => 4.8,
            ],
            [
                'name' => 'Restaurant & Food Theme',
                'price' => 39.99, 'sale_price' => 0,
                'description' => '<p>ثيم مصمم خصيصاً للمطاعم والمقاهي والمتاجر الغذائية. يشمل إدارة القائمة، نظام حجوز الطاولات، معرض صور، ملفات الطهاة، ومراجعات العملاء مع دعم كامل للعربية.</p>',
                'detail' => '<p>HTML5, CSS3, JS, Bootstrap 5 | RTL: Yes | Reservation: Yes | Menu: Dynamic</p>',
                'specification' => '<p>ZIP | 5.2 MB | Color Schemes: 3 | Delivery/Pickup: Yes</p>',
                'tags' => ['html', 'css', 'responsive', 'bootstrap', 'website-template'],
                'avg_rating' => 4.6,
            ],
            [
                'name' => 'SaaS Dashboard Theme',
                'price' => 59.99, 'sale_price' => 0,
                'description' => '<p>ثيم لوحة تحكم شاملة لتطبيقات SaaS يشمل رسوم بيانية تحليلية، لوحات إدارة المستخدمين، إدارة الاشتراكات، واجهات الفوترة، وأعدادات. دعم كامل للوضع الداكن/الفاتح.</p>',
                'detail' => '<p>HTML5, CSS3, JS, Bootstrap 5, Chart.js | Dark/Light Mode | Real-time: Yes</p>',
                'specification' => '<p>ZIP | 7.8 MB | Components: 50+ | Charts: 8 Types | Notifications: Real-time</p>',
                'tags' => ['html', 'css', 'responsive', 'bootstrap', 'dashboard'],
                'avg_rating' => 4.8,
            ],
        ];
    }
}