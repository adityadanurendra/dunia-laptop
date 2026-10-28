<?php

namespace App\Http\Controllers\Store;

use App\Enums\ProductCondition;
use App\Http\Controllers\Controller;
use App\Services\Catalog\ProductQueryService;
use App\Services\Store\StorefrontDataService;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected StorefrontDataService $storefrontDataService,
        protected ProductQueryService $productQueryService,
    ) {}

    /**
     * Render the dynamic storefront homepage driven by homepage_sections and site_settings.
     */
    public function index(): View
    {
        $siteSettings = $this->storefrontDataService->getPublicSettings();
        $sections = $this->storefrontDataService->getActiveHomepageSections();

        $hasCatalogTables = Schema::hasTable('products')
            && Schema::hasTable('categories')
            && Schema::hasTable('brands')
            && Schema::hasTable('product_images')
            && Schema::hasTable('product_specifications');

        $sectionPayloads = [];

        foreach ($sections as $section) {
            $content = is_array($section->content_data) ? $section->content_data : [];

            if (! $hasCatalogTables) {
                $sectionPayloads[$section->id] = collect();
                continue;
            }

            switch ($section->section_type) {
                case 'category_showcase':
                    $slugs = isset($content['categories_slug']) && is_array($content['categories_slug'])
                        ? $content['categories_slug']
                        : null;
                    $categories = $this->productQueryService->getActiveCategories($slugs);
                    if ($categories->isEmpty() && ! empty($slugs)) {
                        $categories = $this->productQueryService->getActiveCategories();
                    }
                    $sectionPayloads[$section->id] = $categories;
                    break;

                case 'featured_products':
                    $limit = max(1, min(12, (int) ($content['limit'] ?? 4)));
                    $featuredSkus = isset($content['featured_skus']) && is_array($content['featured_skus'])
                        ? $content['featured_skus']
                        : [];
                    $featured = $this->productQueryService->getFeaturedProducts($limit, $featuredSkus);
                    if ($featured->isEmpty() && ! empty($featuredSkus)) {
                        $featured = $this->productQueryService->getFeaturedProducts($limit);
                    }
                    $sectionPayloads[$section->id] = $featured;
                    break;

                case 'promo_grid':
                case 'used_laptops':
                    $sectionPayloads[$section->id] = $this->productQueryService->getLatestProducts(
                        2,
                        ProductCondition::USED->value
                    );
                    break;

                case 'latest_products':
                    $limit = max(1, min(12, (int) ($content['limit'] ?? 6)));
                    $sectionPayloads[$section->id] = $this->productQueryService->getLatestProducts($limit);
                    break;

                default:
                    $sectionPayloads[$section->id] = collect();
                    break;
            }
        }

        return view('store.home', [
            'siteSettings' => $siteSettings,
            'sections' => $sections,
            'sectionPayloads' => $sectionPayloads,
        ]);
    }
}

