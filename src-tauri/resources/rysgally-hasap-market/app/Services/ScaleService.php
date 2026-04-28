<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ScaleService
{
    protected $scaleIp;
    protected $scalePort;
    protected $timeout;

    public function __construct()
    {
        $this->scaleIp = config('scale.ip', '192.168.1.100');
        $this->scalePort = config('scale.port', 8080);
        $this->timeout = config('scale.timeout', 10);
    }

    /**
     * Export single product to scale
     */
    public function exportProduct(Product $product): bool
    {
        // Only export weighable products
        if (!$product->isWeight()) {
            return false;
        }

        $scaleData = $this->formatProductForScale($product);

        try {
            $response = Http::timeout($this->timeout)
                ->post($this->getScaleUrl('/api/products'), $scaleData);

            if ($response->successful()) {
                Log::info("Product {$product->id} exported to scale successfully");
                return true;
            }

            Log::error("Failed to export product {$product->id} to scale: " . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error("Error exporting product {$product->id} to scale: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Export storage item to scale
     */
    public function exportStorage(Storage $storage): bool
    {
        if (!$storage->product || !$storage->product->isWeight()) {
            return false;
        }

        $scaleData = $this->formatStorageForScale($storage);

        try {
            $response = Http::timeout($this->timeout)
                ->post($this->getScaleUrl('/api/storage'), $scaleData);

            if ($response->successful()) {
                Log::info("Storage {$storage->id} exported to scale successfully");
                return true;
            }

            Log::error("Failed to export storage {$storage->id} to scale: " . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error("Error exporting storage {$storage->id} to scale: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Export all weighable products to scale
     */
    public function exportAllWeighableProducts(): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        $weighableProducts = Product::where('unit_type', 'weight')->get();

        foreach ($weighableProducts as $product) {
            if ($this->exportProduct($product)) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "Product ID: {$product->id}";
            }
        }

        return $results;
    }

    /**
     * Export all weighable storage items to scale
     */
    public function exportAllWeighableStorage(): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        $weighableStorage = Storage::with('product')
            ->whereHas('product', function($query) {
                $query->where('unit_type', 'weight');
            })
            ->get();

        foreach ($weighableStorage as $storage) {
            if ($this->exportStorage($storage)) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "Storage ID: {$storage->id}";
            }
        }

        return $results;
    }

    /**
     * Delete product from scale
     */
    public function deleteProductFromScale(Product $product): bool
    {
        if (!$product->isWeight()) {
            return false;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->delete($this->getScaleUrl('/api/products/' . $product->product_code));

            if ($response->successful()) {
                Log::info("Product {$product->id} deleted from scale successfully");
                return true;
            }

            Log::error("Failed to delete product {$product->id} from scale: " . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error("Error deleting product {$product->id} from scale: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format product data for scale API
     */
    protected function formatProductForScale(Product $product): array
    {
        return [
            'product_code' => $product->product_code,
            'name' => $product->name,
            'price_per_kg' => $product->final_price,
            'unit' => 'kg',
            'discount' => $product->discount ?? 0,
            'expiry_date' => $product->expiry_date ? $product->expiry_date->format('Y-m-d') : null,
        ];
    }

    /**
     * Format storage data for scale API
     */
    protected function formatStorageForScale(Storage $storage): array
    {
        $product = $storage->product;

        return [
            'product_code' => $product->product_code,
            'name' => $product->name,
            'price_per_kg' => $storage->selling_price ?? $product->final_price,
            'received_price' => $storage->received_price ?? $product->received_price,
            'discount' => $storage->discount ?? $product->discount ?? 0,
            'expiry_date' => $storage->expiry_date ?? $product->expiry_date?->format('Y-m-d'),
            'batch_number' => $storage->batch_number,
            'quantity' => $storage->quantity,
        ];
    }

    /**
     * Get full scale API URL
     */
    protected function getScaleUrl(string $endpoint): string
    {
        return "http://{$this->scaleIp}:{$this->scalePort}{$endpoint}";
    }

    /**
     * Test connection to scale
     */
    public function testConnection(): bool
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->getScaleUrl('/api/health'));

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Scale connection test failed: " . $e->getMessage());
            return false;
        }
    }
}
