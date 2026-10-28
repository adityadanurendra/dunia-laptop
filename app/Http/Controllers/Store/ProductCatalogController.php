<?php

namespace App\Http\Controllers\Store;

use App\Enums\ProductCondition;
use App\Http\Controllers\Controller;
use App\Services\Catalog\ProductQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductCatalogController extends Controller
{
    public function __construct(
        protected ProductQueryService $productQueryService,
    ) {}

    /**
     * Render the product catalog page with search, multi-criteria filters, and sorting.
     */
    public function index(Request $request): View
    {
        $filters = $this->extractFilters($request);

        return $this->renderCatalogView($filters);
    }

    /**
     * Render the catalog pre-filtered by an active category slug (/category/{categorySlug}).
     */
    public function category(Request $request, string $categorySlug): View
    {
        $category = DB::table('categories')
            ->where('slug', $categorySlug)
            ->where('is_active', true)
            ->first();

        if (! $category) {
            abort(404);
        }

        $filters = $this->extractFilters($request);
        $filters['category'] = $category->slug;

        return $this->renderCatalogView($filters, $category);
    }

    /**
     * Render the detailed laptop view (/products/{slug}).
     */
    public function show(string $slug): View
    {
        $product = $this->productQueryService->getProductBySlug($slug);

        if (! $product) {
            abort(404);
        }

        $relatedProducts = $this->productQueryService->getRelatedProducts(
            (int) $product->id,
            (int) $product->category_id,
            4
        );

        return view('store.catalog.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    /**
     * Normalize and sanitize filter parameters from incoming request.
     */
    protected function extractFilters(Request $request): array
    {
        $condition = strtolower(trim((string) $request->query('condition', '')));
        if (! in_array($condition, [ProductCondition::NEW->value, ProductCondition::USED->value], true)) {
            $condition = '';
        }

        $sort = strtolower(trim((string) $request->query('sort', 'latest')));
        if (! in_array($sort, ['latest', 'price_asc', 'price_desc'], true)) {
            $sort = 'latest';
        }

        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');

        return [
            'search' => trim((string) $request->query('search', '')),
            'condition' => $condition,
            'category' => trim((string) $request->query('category', '')),
            'brand' => trim((string) $request->query('brand', '')),
            'min_price' => ($minPrice !== null && $minPrice !== '' && is_numeric($minPrice)) ? (string) max(0, (int) $minPrice) : '',
            'max_price' => ($maxPrice !== null && $maxPrice !== '' && is_numeric($maxPrice)) ? (string) max(0, (int) $maxPrice) : '',
            'in_stock' => $request->boolean('in_stock'),
            'sort' => $sort,
        ];
    }

    /**
     * Build catalog dataset and return the catalog Blade view.
     */
    protected function renderCatalogView(array $filters, ?object $resolvedCategory = null): View
    {
        $products = $this->productQueryService->getCatalogProducts($filters, 12);
        $categories = $this->productQueryService->getActiveCategories();
        $brands = $this->productQueryService->getActiveBrands();

        $activeCategory = $resolvedCategory;
        if (! $activeCategory && $filters['category'] !== '') {
            $activeCategory = $categories->firstWhere('slug', $filters['category']);
        }

        $activeBrand = null;
        if ($filters['brand'] !== '') {
            $activeBrand = $brands->firstWhere('slug', $filters['brand']);
        }

        return view('store.catalog.index', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'filters' => $filters,
            'activeCategory' => $activeCategory,
            'activeBrand' => $activeBrand,
        ]);
    }
}

