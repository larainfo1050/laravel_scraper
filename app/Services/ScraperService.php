<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Http;

class ScraperService
{
    public function fetchProducts()
    {
        try {
            // Fetch from Fake Store API
            $response = Http::get('https://fakestoreapi.com/products');
            
            if (!$response->successful()) {
                return ['success' => false, 'error' => 'API request failed'];
            }

            $products = $response->json();
            $count = 0;

            foreach ($products as $item) {
                Product::create([
                    'title' => $item['title'],
                    'price' => $item['price'],
                    'description' => $item['description'],
                    'category' => $item['category'],
                    'image' => $item['image'],
                    'rating' => $item['rating']['rate'] ?? null,
                    'rating_count' => $item['rating']['count'] ?? 0,
                ]);
                $count++;
            }

            return [
                'success' => true,
                'message' => "Scraped {$count} products successfully",
                'count' => $count,
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getAnalytics()
    {
        return [
            'total_products' => Product::count(),
            'avg_price' => round(Product::avg('price'), 2),
            'min_price' => Product::min('price'),
            'max_price' => Product::max('price'),
            'avg_rating' => round(Product::avg('rating'), 2),
            'products_by_category' => Product::selectRaw('category, COUNT(*) as count, AVG(price) as avg_price')
                ->groupBy('category')
                ->get(),
        ];
    }
}