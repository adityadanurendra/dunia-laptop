<?php

namespace App\Services\Store;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class StorefrontDataService
{
    /**
     * Default public site settings when database entries are not yet seeded.
     */
    protected function defaultSettings(): array
    {
        return [
            'site_title' => 'Dunia Laptop - Toko Laptop Baru & Bekas Terpercaya',
            'site_tagline' => 'Pusat Laptop Terbaik, Bergaransi Resmi & Toko di Indonesia',
            'company_name' => 'PT Dunia Laptop Indonesia',
            'contact_whatsapp' => '081234567890',
            'contact_email' => 'cs@dunialaptop.com',
            'store_address' => 'Jl. Affandi (Gejayan) No. 45, Sleman, D.I. Yogyakarta 55281',
            'operating_hours' => 'Senin - Minggu, 09:00 - 21:00 WIB',
            'theme_primary_color' => '#2563eb',
            'theme_accent_color' => '#0ea5e9',
        ];
    }

    /**
     * Retrieve public site settings via DB facade with safe type casting and color sanitization.
     */
    public function getPublicSettings(): array
    {
        $settings = $this->defaultSettings();

        if (Schema::hasTable('site_settings')) {
            $rows = DB::table('site_settings')
                ->where('is_public', true)
                ->select(['key', 'value', 'type'])
                ->get();

            foreach ($rows as $row) {
                $settings[$row->key] = match ($row->type) {
                    'boolean' => filter_var($row->value, FILTER_VALIDATE_BOOLEAN),
                    'integer' => (int) $row->value,
                    'json' => json_decode((string) $row->value, true) ?? [],
                    default => (string) $row->value,
                };
            }
        }

        // Strict hex color validation to prevent CSS/code injection (FR-41)
        $primaryHex = $this->sanitizeHexColor((string) ($settings['theme_primary_color'] ?? ''), '#2563eb');
        $accentHex = $this->sanitizeHexColor((string) ($settings['theme_accent_color'] ?? ''), '#0ea5e9');

        $settings['theme_primary_color'] = $primaryHex;
        $settings['theme_accent_color'] = $accentHex;
        $settings['theme_primary_rgb'] = $this->hexToRgbChannels($primaryHex);
        $settings['theme_accent_rgb'] = $this->hexToRgbChannels($accentHex);
        $settings['whatsapp_url'] = $this->buildWhatsappUrl((string) ($settings['contact_whatsapp'] ?? ''));

        return $settings;
    }

    /**
     * Retrieve active homepage sections ordered by sort_order via DB facade.
     */
    public function getActiveHomepageSections(): Collection
    {
        if (! Schema::hasTable('homepage_sections')) {
            return collect();
        }

        return DB::table('homepage_sections')
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function (object $section) {
                if (is_string($section->content_data) && $section->content_data !== '') {
                    $decoded = json_decode($section->content_data, true);
                    $section->content_data = is_array($decoded) ? $decoded : [];
                } elseif (! is_array($section->content_data)) {
                    $section->content_data = [];
                }

                return $section;
            });
    }

    /**
     * Retrieve total item quantity in the current customer's or session's cart via DB facade.
     */
    public function getCartItemCount(?int $userId, ?string $sessionId = null): int
    {
        if (! Schema::hasTable('carts') || ! Schema::hasTable('cart_items')) {
            return 0;
        }

        if ($userId === null && empty($sessionId)) {
            return 0;
        }

        $query = DB::table('cart_items')
            ->join('carts', 'cart_items.cart_id', '=', 'carts.id');

        if ($userId !== null) {
            $query->where('carts.user_id', $userId);
        } else {
            $query->where('carts.session_id', $sessionId);
        }

        return (int) $query->sum('cart_items.quantity');
    }

    /**
     * Resolve an uploaded or public image path into a valid browser URL,
     * returning null if no physical file exists so views can render clean SVG fallbacks.
     */
    public static function resolveImageUrl(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $trimmed = trim($path);

        if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
            return $trimmed;
        }

        $normalized = ltrim($trimmed, '/');

        if (file_exists(public_path('storage/' . $normalized))) {
            return asset('storage/' . $normalized);
        }

        if (file_exists(public_path($normalized))) {
            return asset($normalized);
        }

        if (Storage::disk('public')->exists($normalized)) {
            return Storage::disk('public')->url($normalized);
        }

        return null;
    }

    /**
     * Validate a 6-digit hex color code strictly.
     */
    public function sanitizeHexColor(string $color, string $fallback): string
    {
        $trimmed = trim($color);
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $trimmed) === 1) {
            return strtolower($trimmed);
        }

        return $fallback;
    }

    /**
     * Convert #RRGGBB hex string into space-separated RGB channels ("R G B") for Tailwind CSS variables.
     */
    public function hexToRgbChannels(string $hex): string
    {
        $clean = ltrim($hex, '#');
        $r = hexdec(substr($clean, 0, 2));
        $g = hexdec(substr($clean, 2, 2));
        $b = hexdec(substr($clean, 4, 2));

        return "{$r} {$g} {$b}";
    }

    /**
     * Build a clean wa.me link from local Indonesian phone numbers.
     */
    protected function buildWhatsappUrl(string $rawPhone): string
    {
        $digits = preg_replace('/\D+/', '', $rawPhone) ?? '';
        if ($digits === '') {
            return '#';
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }

        return 'https://wa.me/' . $digits;
    }
}

