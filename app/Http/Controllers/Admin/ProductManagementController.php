<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductManagementController extends Controller
{
    /**
     * Display a listing of laptop products with filters and search.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $condition = $request->query('condition');
        $categoryId = $request->query('category_id');
        $brandId = $request->query('brand_id');
        $stockStatus = $request->query('stock_status');
        $status = $request->query('status');

        $categories = DB::table('categories')->orderBy('name')->get();
        $brands = DB::table('brands')->orderBy('name')->get();

        $query = DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('product_specifications', 'products.id', '=', 'product_specifications.product_id')
            ->whereNull('products.deleted_at')
            ->select(
                'products.*',
                'categories.name as category_name',
                'brands.name as brand_name',
                'product_specifications.processor',
                'product_specifications.ram_gb',
                'product_specifications.storage_capacity',
                'product_specifications.gpu'
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                  ->orWhere('products.sku', 'like', "%{$search}%")
                  ->orWhere('product_specifications.processor', 'like', "%{$search}%");
            });
        }

        if ($condition && in_array($condition, ['new', 'used'])) {
            $query->where('products.condition', $condition);
        }

        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }

        if ($brandId) {
            $query->where('products.brand_id', $brandId);
        }

        if ($status) {
            $query->where('products.status', $status);
        }

        if ($stockStatus === 'low_stock') {
            $query->where('products.stock', '<=', 2)->where('products.stock', '>', 0);
        } elseif ($stockStatus === 'out_of_stock') {
            $query->where('products.stock', '<=', 0);
        } elseif ($stockStatus === 'in_stock') {
            $query->where('products.stock', '>', 0);
        }

        $products = $query->orderBy('products.id', 'desc')->paginate(15)->withQueryString();

        // Attach primary images to each product
        $productIds = $products->pluck('id')->toArray();
        $primaryImages = DB::table('product_images')
            ->whereIn('product_id', $productIds)
            ->where('is_primary', true)
            ->pluck('image_path', 'product_id');

        return view('admin.products.index', [
            'products' => $products,
            'primaryImages' => $primaryImages,
            'categories' => $categories,
            'brands' => $brands,
            'search' => $search,
            'condition' => $condition,
            'categoryId' => $categoryId,
            'brandId' => $brandId,
            'stockStatus' => $stockStatus,
            'status' => $status,
        ]);
    }

    /**
     * Show the form for creating a new laptop product.
     */
    public function create(): View
    {
        $categories = DB::table('categories')->orderBy('name')->get();
        $brands = DB::table('brands')->orderBy('name')->get();

        return view('admin.products.create', [
            'categories' => $categories,
            'brands' => $brands,
        ]);
    }

    /**
     * Store a newly created laptop product and specifications in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // General Information
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:60|unique:products,sku',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'condition' => 'required|in:new,used',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'weight_grams' => 'required|integer|min:100',
            'description' => 'nullable|string',
            'status' => 'required|in:published,draft,archived',

            // Specifications
            'processor' => 'required|string|max:150',
            'ram_gb' => 'required|integer|min:2|max:256',
            'ram_type' => 'nullable|string|max:50',
            'storage_capacity' => 'required|string|max:50',
            'storage_type' => 'nullable|string|max:50',
            'gpu' => 'required|string|max:150',
            'screen_size' => 'required|numeric|min:10|max:25',
            'screen_resolution' => 'nullable|string|max:60',
            'operating_system' => 'nullable|string|max:100',
            'warranty' => 'nullable|string|max:100',
            'physical_condition' => 'nullable|string|max:100',
            'battery_condition' => 'nullable|string|max:100',

            // Gallery Images
            'images.*' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        DB::transaction(function () use ($request, $validated) {
            // Unique slug generation
            $slugBase = Str::slug($validated['name']);
            $slug = $slugBase;
            $count = 1;
            while (DB::table('products')->where('slug', $slug)->exists()) {
                $slug = "{$slugBase}-{$count}";
                $count++;
            }

            // 1. Insert product
            $productId = DB::table('products')->insertGetId([
                'category_id' => $validated['category_id'],
                'brand_id' => $validated['brand_id'],
                'name' => $validated['name'],
                'slug' => $slug,
                'sku' => strtoupper(trim($validated['sku'])),
                'condition' => $validated['condition'],
                'price' => $validated['price'],
                'stock' => $validated['stock'],
                'weight_grams' => $validated['weight_grams'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Insert specifications
            DB::table('product_specifications')->insert([
                'product_id' => $productId,
                'processor' => $validated['processor'],
                'ram_gb' => $validated['ram_gb'],
                'ram_type' => $validated['ram_type'] ?? 'DDR4',
                'storage_capacity' => $validated['storage_capacity'],
                'storage_type' => $validated['storage_type'] ?? 'NVMe PCIe SSD',
                'gpu' => $validated['gpu'],
                'screen_size' => $validated['screen_size'],
                'screen_resolution' => $validated['screen_resolution'] ?? 'FHD (1920x1080)',
                'operating_system' => $validated['operating_system'] ?? null,
                'warranty' => $validated['warranty'] ?? null,
                'physical_condition' => $validated['condition'] === 'used' ? ($validated['physical_condition'] ?? '95% Mulus') : '100% Baru Segel Resmi',
                'battery_condition' => $validated['condition'] === 'used' ? ($validated['battery_condition'] ?? 'Normal / Tahan 3-4 Jam') : '100% Baru',
                'additional_specs' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Upload and store gallery images
            if ($request->hasFile('images')) {
                $isFirst = true;
                $sort = 1;
                foreach ($request->file('images') as $file) {
                    $path = $file->store('products', 'public');
                    DB::table('product_images')->insert([
                        'product_id' => $productId,
                        'image_path' => $path,
                        'is_primary' => $isFirst,
                        'sort_order' => $sort,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $isFirst = false;
                    $sort++;
                }
            }
        });

        return redirect()->route('admin.products.index')
            ->with('success', "Laptop '{$validated['name']}' berhasil ditambahkan ke katalog!");
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(int $id): View
    {
        $product = DB::table('products')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (! $product) {
            abort(404, 'Produk tidak ditemukan.');
        }

        $specs = DB::table('product_specifications')
            ->where('product_id', $id)
            ->first();

        $images = DB::table('product_images')
            ->where('product_id', $id)
            ->orderBy('sort_order')
            ->get();

        $categories = DB::table('categories')->orderBy('name')->get();
        $brands = DB::table('brands')->orderBy('name')->get();

        return view('admin.products.edit', [
            'product' => $product,
            'specs' => $specs,
            'images' => $images,
            'categories' => $categories,
            'brands' => $brands,
        ]);
    }

    /**
     * Update the specified laptop product and specifications in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $product = DB::table('products')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (! $product) {
            abort(404, 'Produk tidak ditemukan.');
        }

        $validated = $request->validate([
            // General Information
            'name' => 'required|string|max:255',
            'sku' => "required|string|max:60|unique:products,sku,{$id}",
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'condition' => 'required|in:new,used',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'weight_grams' => 'required|integer|min:100',
            'description' => 'nullable|string',
            'status' => 'required|in:published,draft,archived',

            // Specifications
            'processor' => 'required|string|max:150',
            'ram_gb' => 'required|integer|min:2|max:256',
            'ram_type' => 'nullable|string|max:50',
            'storage_capacity' => 'required|string|max:50',
            'storage_type' => 'nullable|string|max:50',
            'gpu' => 'required|string|max:150',
            'screen_size' => 'required|numeric|min:10|max:25',
            'screen_resolution' => 'nullable|string|max:60',
            'operating_system' => 'nullable|string|max:100',
            'warranty' => 'nullable|string|max:100',
            'physical_condition' => 'nullable|string|max:100',
            'battery_condition' => 'nullable|string|max:100',

            // Gallery Images
            'images.*' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        DB::transaction(function () use ($request, $validated, $id, $product) {
            // Update slug if name changed
            $slug = $product->slug;
            if ($validated['name'] !== $product->name) {
                $slugBase = Str::slug($validated['name']);
                $slug = $slugBase;
                $count = 1;
                while (DB::table('products')->where('slug', $slug)->where('id', '!=', $id)->exists()) {
                    $slug = "{$slugBase}-{$count}";
                    $count++;
                }
            }

            // 1. Update product
            DB::table('products')->where('id', $id)->update([
                'category_id' => $validated['category_id'],
                'brand_id' => $validated['brand_id'],
                'name' => $validated['name'],
                'slug' => $slug,
                'sku' => strtoupper(trim($validated['sku'])),
                'condition' => $validated['condition'],
                'price' => $validated['price'],
                'stock' => $validated['stock'],
                'weight_grams' => $validated['weight_grams'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'updated_at' => now(),
            ]);

            // 2. Upsert specifications
            DB::table('product_specifications')->updateOrInsert(
                ['product_id' => $id],
                [
                    'processor' => $validated['processor'],
                    'ram_gb' => $validated['ram_gb'],
                    'ram_type' => $validated['ram_type'] ?? 'DDR4',
                    'storage_capacity' => $validated['storage_capacity'],
                    'storage_type' => $validated['storage_type'] ?? 'NVMe PCIe SSD',
                    'gpu' => $validated['gpu'],
                    'screen_size' => $validated['screen_size'],
                    'screen_resolution' => $validated['screen_resolution'] ?? 'FHD (1920x1080)',
                    'operating_system' => $validated['operating_system'] ?? null,
                    'warranty' => $validated['warranty'] ?? null,
                    'physical_condition' => $validated['condition'] === 'used' ? ($validated['physical_condition'] ?? '95% Mulus') : '100% Baru Segel Resmi',
                    'battery_condition' => $validated['condition'] === 'used' ? ($validated['battery_condition'] ?? 'Normal / Tahan 3-4 Jam') : '100% Baru',
                    'updated_at' => now(),
                ]
            );

            // 3. Upload new images if provided
            if ($request->hasFile('images')) {
                $hasExistingPrimary = DB::table('product_images')
                    ->where('product_id', $id)
                    ->where('is_primary', true)
                    ->exists();

                $highestSort = (int) DB::table('product_images')
                    ->where('product_id', $id)
                    ->max('sort_order');

                $isFirst = ! $hasExistingPrimary;
                $sort = $highestSort + 1;

                foreach ($request->file('images') as $file) {
                    $path = $file->store('products', 'public');
                    DB::table('product_images')->insert([
                        'product_id' => $id,
                        'image_path' => $path,
                        'is_primary' => $isFirst,
                        'sort_order' => $sort,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $isFirst = false;
                    $sort++;
                }
            }
        });

        return redirect()->route('admin.products.index')
            ->with('success', "Informasi laptop '{$validated['name']}' berhasil diperbarui!");
    }

    /**
     * Fast inline update for stock, price, or publication status.
     */
    public function quickUpdate(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'status' => 'nullable|in:published,draft,archived',
        ]);

        $updateData = [];
        if (isset($validated['price'])) {
            $updateData['price'] = $validated['price'];
        }
        if (isset($validated['stock'])) {
            $updateData['stock'] = $validated['stock'];
        }
        if (isset($validated['status'])) {
            $updateData['status'] = $validated['status'];
        }

        if (! empty($updateData)) {
            $updateData['updated_at'] = now();
            DB::table('products')->where('id', $id)->whereNull('deleted_at')->update($updateData);
        }

        return redirect()->back()->with('success', 'Perubahan cepat harga/stok berhasil disimpan.');
    }

    /**
     * Soft delete a laptop product.
     */
    public function destroy(int $id): RedirectResponse
    {
        $product = DB::table('products')->where('id', $id)->whereNull('deleted_at')->first();
        if (! $product) {
            abort(404, 'Produk tidak ditemukan.');
        }

        DB::table('products')->where('id', $id)->update([
            'deleted_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', "Produk '{$product->name}' berhasil dihapus (soft delete).");
    }

    /**
     * Delete an image from product gallery.
     */
    public function deleteImage(int $id, int $imageId): RedirectResponse
    {
        $image = DB::table('product_images')
            ->where('id', $imageId)
            ->where('product_id', $id)
            ->first();

        if (! $image) {
            abort(404, 'Gambar tidak ditemukan.');
        }

        // Delete from storage if exists
        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        DB::table('product_images')->where('id', $imageId)->delete();

        // If deleted was primary, assign primary to first remaining image
        if ($image->is_primary) {
            $firstRemaining = DB::table('product_images')
                ->where('product_id', $id)
                ->orderBy('sort_order')
                ->first();

            if ($firstRemaining) {
                DB::table('product_images')
                    ->where('id', $firstRemaining->id)
                    ->update(['is_primary' => true]);
            }
        }

        return redirect()->back()->with('success', 'Foto produk berhasil dihapus.');
    }

    /**
     * Set a photo as primary cover.
     */
    public function setPrimaryImage(int $id, int $imageId): RedirectResponse
    {
        $image = DB::table('product_images')
            ->where('id', $imageId)
            ->where('product_id', $id)
            ->first();

        if (! $image) {
            abort(404, 'Gambar tidak ditemukan.');
        }

        DB::transaction(function () use ($id, $imageId) {
            DB::table('product_images')
                ->where('product_id', $id)
                ->update(['is_primary' => false]);

            DB::table('product_images')
                ->where('id', $imageId)
                ->update(['is_primary' => true]);
        });

        return redirect()->back()->with('success', 'Foto utama produk berhasil diperbarui.');
    }
}
