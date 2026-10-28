<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    /**
     * Display a listing of user shipping addresses.
     */
    public function index(Request $request): JsonResponse
    {
        $addresses = DB::table('addresses')
            ->where('user_id', Auth::id())
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $addresses,
        ]);
    }

    /**
     * Store a newly created shipping address.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:25'],
            'address_line' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $userId = Auth::id();

        $existingCount = DB::table('addresses')->where('user_id', $userId)->count();
        $isDefault = $existingCount === 0 || filter_var($validated['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $addressId = DB::transaction(function () use ($userId, $validated, $isDefault) {
            if ($isDefault) {
                DB::table('addresses')->where('user_id', $userId)->update([
                    'is_default' => false,
                ]);
            }

            return DB::table('addresses')->insertGetId([
                'user_id' => $userId,
                'label' => trim($validated['label']),
                'recipient_name' => trim($validated['recipient_name']),
                'phone' => trim($validated['phone']),
                'address_line' => trim($validated['address_line']),
                'city' => trim($validated['city']),
                'province' => trim($validated['province']),
                'postal_code' => trim($validated['postal_code']),
                'is_default' => $isDefault,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Alamat pengiriman berhasil ditambahkan.',
                'address_id' => $addressId,
            ]);
        }

        return redirect()->back()->with('success', 'Alamat pengiriman baru berhasil disimpan.');
    }

    /**
     * Update an existing address.
     */
    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $userId = Auth::id();

        $address = DB::table('addresses')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (! $address) {
            abort(404, 'Alamat tidak ditemukan.');
        }

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:25'],
            'address_line' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $isDefault = filter_var($validated['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN);

        DB::transaction(function () use ($userId, $id, $validated, $isDefault) {
            if ($isDefault) {
                DB::table('addresses')->where('user_id', $userId)->where('id', '!=', $id)->update([
                    'is_default' => false,
                ]);
            }

            DB::table('addresses')->where('id', $id)->update([
                'label' => trim($validated['label']),
                'recipient_name' => trim($validated['recipient_name']),
                'phone' => trim($validated['phone']),
                'address_line' => trim($validated['address_line']),
                'city' => trim($validated['city']),
                'province' => trim($validated['province']),
                'postal_code' => trim($validated['postal_code']),
                'is_default' => $isDefault,
                'updated_at' => now(),
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Alamat pengiriman berhasil diperbarui.',
            ]);
        }

        return redirect()->back()->with('success', 'Alamat pengiriman berhasil diperbarui.');
    }

    /**
     * Set a specific address as default.
     */
    public function setDefault(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $userId = Auth::id();

        $address = DB::table('addresses')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (! $address) {
            abort(404, 'Alamat tidak ditemukan.');
        }

        DB::transaction(function () use ($userId, $id) {
            DB::table('addresses')->where('user_id', $userId)->update([
                'is_default' => false,
            ]);

            DB::table('addresses')->where('id', $id)->update([
                'is_default' => true,
                'updated_at' => now(),
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Alamat default berhasil diubah.',
            ]);
        }

        return redirect()->back()->with('success', 'Alamat pengiriman utama berhasil diubah.');
    }

    /**
     * Delete an address.
     */
    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $userId = Auth::id();

        $address = DB::table('addresses')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (! $address) {
            abort(404, 'Alamat tidak ditemukan.');
        }

        DB::transaction(function () use ($userId, $id, $address) {
            DB::table('addresses')->where('id', $id)->delete();

            // If the deleted address was default, promote the latest remaining address
            if ($address->is_default) {
                $latest = DB::table('addresses')
                    ->where('user_id', $userId)
                    ->orderByDesc('id')
                    ->first();

                if ($latest) {
                    DB::table('addresses')->where('id', $latest->id)->update([
                        'is_default' => true,
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Alamat berhasil dihapus.',
            ]);
        }

        return redirect()->back()->with('success', 'Alamat berhasil dihapus.');
    }
}

