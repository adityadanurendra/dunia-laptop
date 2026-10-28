<?php

namespace App\Services\Catalog;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductQueryService
{
    /**
     * Build the base Query Builder instance for products joined with
     * active categories, active brands, primary image, and specifications.
     */
    protected function baseProductQuery(string $status = ProductStatus::PUBLISHED->value): Builder
    {
        $primaryImageSubquery = DB::table('product_images')
            ->select('product_id', DB::raw('MIN(image_path) as primary_image'))
            ->where('is_primary', true)
            ->groupBy('product_id');

        $query = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoinSub($primaryImageSubquery, 'primary_images', function ($join) {
                $join->on('products.id', '=', 'primary_images.product_id');
            })
            ->leftJoin('product_specifications', 'products.id', '=', 'product_specifications.product_id')
            ->whereNull('products.deleted_at')
            ->where('categories.is_active', true)
            ->where('brands.is_active', true)
            ->select([
                'products.id',
                'products.category_id',
                'products.brand_id',
                'products.name',
                'products.slug',
                'products.sku',
                'products.condition',
                'products.price',
                'products.stock',
                'products.weight_grams',
                'products.description',
                'products.status',
                'products.created_at',
                'categories.name as category_name',
                'categories.slug as category_slug',
                'brands.name as brand_name',
                'brands.slug as brand_slug',
                'brands.logo as brand_logo',
                'primary_images.primary_image',
                'product_specifications.processor',
                'product_specifications.ram_gb',
                'product_specifications.ram_type',
                'product_specifications.storage_capacity',
                'product_specifications.storage_type',
                'product_specifications.gpu',
                'product_specifications.screen_size',
                'product_specifications.screen_resolution',
                'product_specifications.physical_condition',
                'product_specifications.battery_condition',
                'product_specifications.operating_system',
                'product_specifications.warranty',
            ]);

        if ($status !== '') {
            $query->where('products.status', $status);
        }

        return $query;
    }

    /**
     * Apply search, multi-criteria filters, and sorting onto a product query.
     */
    protected function applyFiltersAndSort(Builder $query, array $filters): Builder
    {
        // 1. Search by name, SKU, or description
        if (isset($filters['search']) && trim((string) $filters['search']) !== '') {
            $keyword = '%' . trim((string) $filters['search']) . '%';
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('products.name', 'like', $keyword)
                    ->orWhere('products.sku', 'like', $keyword)
                    ->orWhere('products.description', 'like', $keyword);
            });
        }

        // 2. Condition filter: new vs used
        if (! empty($filters['condition'])) {
            $condition = $filters['condition'] instanceof ProductCondition
                ? $filters['condition']->value
                : strtolower(trim((string) $filters['condition']));

            if (in_array($condition, [ProductCondition::NEW->value, ProductCondition::USED->value], true)) {
                $query->where('products.condition', $condition);
            }
        }

        // 3. Category filter (slug, array of slugs, or category_id)
        if (! empty($filters['category_id']) && is_numeric($filters['category_id'])) {
            $query->where('products.category_id', (int) $filters['category_id']);
        } elseif (! empty($filters['category'])) {
            if (is_array($filters['category'])) {
                $slugs = array_filter(array_map('strval', $filters['category']));
                if (! empty($slugs)) {
                    $query->whereIn('categories.slug', $slugs);
                }
            } elseif (is_int($filters['category'])) {
                $query->where('products.category_id', $filters['category']);
            } else {
                $query->where('categories.slug', trim((string) $filters['category']));
            }
        }

        // 4. Brand filter (slug, array of slugs, or brand_id)
        if (! empty($filters['brand_id']) && is_numeric($filters['brand_id'])) {
            $query->where('products.brand_id', (int) $filters['brand_id']);
        } elseif (! empty($filters['brand'])) {
            if (is_array($filters['brand'])) {
                $slugs = array_filter(array_map('strval', $filters['brand']));
                if (! empty($slugs)) {
                    $query->whereIn('brands.slug', $slugs);
                }
            } elseif (is_int($filters['brand'])) {
                $query->where('products.brand_id', $filters['brand']);
            } else {
                $query->where('brands.slug', trim((string) $filters['brand']));
            }
        }

        // 5. Minimum & Maximum Price Range
        $minPrice = (isset($filters['min_price']) && $filters['min_price'] !== '' && is_numeric($filters['min_price']))
            ? max(0, (float) $filters['min_price'])
            : null;
        $maxPrice = (isset($filters['max_price']) && $filters['max_price'] !== '' && is_numeric($filters['max_price']))
            ? max(0, (float) $filters['max_price'])
            : null;

        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        if ($minPrice !== null) {
            $query->where('products.price', '>=', $minPrice);
        }

        if ($maxPrice !== null) {
            $query->where('products.price', '<=', $maxPrice);
        }

        // 6. Stock availability filter (stock > 0)
        if (! empty($filters['in_stock']) && filter_var($filters['in_stock'], FILTER_VALIDATE_BOOLEAN)) {
            $query->where('products.stock', '>', 0);
        }

        // 7. Sorting (latest, price_asc, price_desc)
        $sort = isset($filters['sort']) ? strtolower(trim((string) $filters['sort'])) : 'latest';

        match ($sort) {
            'price_asc' => $query->orderBy('products.price', 'asc')->orderByDesc('products.id'),
            'price_desc' => $query->orderBy('products.price', 'desc')->orderByDesc('products.id'),
            default => $query->orderByDesc('products.created_at')->orderByDesc('products.id'),
        };

        return $query;
    }

    /**
     * Get paginated catalog products matching search, filters, and sort criteria.
     */
    public function getCatalogProducts(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $status = array_key_exists('status', $filters) && is_string($filters['status'])
            ? $filters['status']
            : ProductStatus::PUBLISHED->value;

        $query = $this->baseProductQuery($status);
        $this->applyFiltersAndSort($query, $filters);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get featured products for homepage sections.
     */
    public function getFeaturedProducts(int $limit = 4, array $skus = [], ?string $condition = null): Collection
    {
        $query = $this->baseProductQuery(ProductStatus::PUBLISHED->value)
            ->where('products.stock', '>', 0);

        if (! empty($skus)) {
            $query->whereIn('products.sku', $skus);
        }

        if ($condition !== null && in_array($condition, [ProductCondition::NEW->value, ProductCondition::USED->value], true)) {
            $query->where('products.condition', $condition);
        }

        return $query
            ->orderByDesc('products.price')
            ->orderByDesc('products.id')
            ->limit($limit)
            ->get();
    }

    /**
     * Get latest published products for homepage sections.
     */
    public function getLatestProducts(int $limit = 8, ?string $condition = null): Collection
    {
        $query = $this->baseProductQuery(ProductStatus::PUBLISHED->value);

        if ($condition !== null && in_array($condition, [ProductCondition::NEW->value, ProductCondition::USED->value], true)) {
            $query->where('products.condition', $condition);
        }

        return $query
            ->orderByDesc('products.created_at')
            ->orderByDesc('products.id')
            ->limit($limit)
            ->get();
    }

    /**
     * Retrieve full published product detail by slug, including all gallery images
     * and decoded additional_specs JSON.
     */
    public function getProductBySlug(string $slug): ?object
    {
        $product = $this->baseProductQuery(ProductStatus::PUBLISHED->value)
            ->addSelect('product_specifications.additional_specs')
            ->where('products.slug', $slug)
            ->first();

        if (! $product) {
            return null;
        }

        $images = DB::table('product_images')
            ->where('product_id', $product->id)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $product->images = $images;

        if (! $product->primary_image && $images->isNotEmpty()) {
            $product->primary_image = $images->first()->image_path;
        }

        $decodedAdditionalSpecs = [];
        if (is_string($product->additional_specs) && $product->additional_specs !== '') {
            $parsed = json_decode($product->additional_specs, true);
            if (is_array($parsed)) {
                $decodedAdditionalSpecs = $parsed;
            }
        } elseif (is_array($product->additional_specs)) {
            $decodedAdditionalSpecs = $product->additional_specs;
        }

        $product->additional_specs = $decodedAdditionalSpecs;

        return $product;
    }

    /**
     * Get related published products within the same category.
     */
    public function getRelatedProducts(int $excludeProductId, int $categoryId, int $limit = 4): Collection
    {
        return $this->baseProductQuery(ProductStatus::PUBLISHED->value)
            ->where('products.category_id', $categoryId)
            ->where('products.id', '!=', $excludeProductId)
            ->orderByDesc('products.stock')
            ->orderByDesc('products.created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get active categories along with their count of published products.
     */
    public function getActiveCategories(?array $onlySlugs = null): Collection
    {
        $productCountsSub = DB::table('products')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->select('products.category_id', DB::raw('COUNT(products.id) as products_count'))
            ->whereNull('products.deleted_at')
            ->where('products.status', ProductStatus::PUBLISHED->value)
            ->where('brands.is_active', true)
            ->groupBy('products.category_id');

        $query = DB::table('categories')
            ->leftJoinSub($productCountsSub, 'prod_counts', function ($join) {
                $join->on('categories.id', '=', 'prod_counts.category_id');
            })
            ->where('categories.is_active', true)
            ->select([
                'categories.id',
                'categories.name',
                'categories.slug',
                'categories.description',
                DB::raw('COALESCE(prod_counts.products_count, 0) as products_count'),
            ]);

        if (! empty($onlySlugs)) {
            $query->whereIn('categories.slug', $onlySlugs);
        }

        $categories = $query->orderBy('categories.name', 'asc')->get();

        if (! empty($onlySlugs)) {
            $slugOrder = array_flip(array_values($onlySlugs));

            return $categories
                ->sortBy(fn ($cat) => $slugOrder[$cat->slug] ?? PHP_INT_MAX)
                ->values();
        }

        return $categories;
    }

    /**
     * Get active brands along with their count of published products.
     */
    public function getActiveBrands(): Collection
    {
        $productCountsSub = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.brand_id', DB::raw('COUNT(products.id) as products_count'))
            ->whereNull('products.deleted_at')
            ->where('products.status', ProductStatus::PUBLISHED->value)
            ->where('categories.is_active', true)
            ->groupBy('products.brand_id');

        return DB::table('brands')
            ->leftJoinSub($productCountsSub, 'prod_counts', function ($join) {
                $join->on('brands.id', '=', 'prod_counts.brand_id');
            })
            ->where('brands.is_active', true)
            ->select([
                'brands.id',
                'brands.name',
                'brands.slug',
                'brands.logo',
                DB::raw('COALESCE(prod_counts.products_count, 0) as products_count'),
            ])
            ->orderBy('brands.name', 'asc')
            ->get();
    }
}

