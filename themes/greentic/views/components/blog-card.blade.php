@props(['blog', 'store'])

<div class="card h-full flex flex-col">
    <a href="{{ route('page.article', [$store->slug, $blog->slug]) }}">
        <img src="{{ get_file($blog->cover_image_path) }}"
            alt="{{ __('Blog Image') }}" class="w-full h-60 object-cover"
            loading="lazy" />
    </a>
    <div class="p-4 flex flex-col flex-grow justify-between">
        <div class="flex flex-col">
            <div class="flex items-center mb-4 gap-4">
                <span class="bg-accent bg-opacity-10 text-white text-xs font-bold px-2 py-1 rounded uppercase">
                    {{ $blog->category->name ?? __('FARM') }}
                </span>
                <span class="text-gray-500 text-sm">{{ $blog->created_at }}</span>
            </div>
            <h3 class="font-bold text-xl mb-3 line-clamp-2">
                <a href="{{ route('page.article', [$store->slug, $blog->slug]) }}"
                    class="hover:text-primary transition">
                    {{ $blog->title }}
                </a>
            </h3>
            <p class="text-gray-600 mb-4 line-clamp-2">
                {{ $blog->short_description }}
            </p>
        </div>
        <a href="{{ route('page.article', [$store->slug, $blog->slug]) }}"
            class="mt-auto text-primary font-semibold hover:text-primary-dark transition flex items-center gap-2">
            {{ $themeSettings['article_card_btn'] ?? __('Read More') }}
            <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</div>