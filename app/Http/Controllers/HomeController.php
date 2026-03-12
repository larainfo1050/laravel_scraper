<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class HomeController extends Controller
{
    private string $geminiApiKey;

    public function __construct()
    {
        $this->geminiApiKey = config('services.gemini.api_key');
    }

    public function index(): View
    {
        $products = Product::latest()->take(20)->get();
        $stats = $this->getBasicStats();
        
        return view('welcome', compact('products', 'stats'));
    }

    public function products(): JsonResponse
    {
        $products = Product::all();
        
        return response()->json([
            'success' => true,
            'count' => $products->count(),
            'data' => $products,
        ]);
    }

    public function analytics(): JsonResponse
    {
        $stats = $this->getBasicStats();
        
        return response()->json([
            'success' => true,
            'analytics' => $stats,
        ]);
    }

    public function aiAnalysis(): JsonResponse
    {
        try {
            $products = Product::all();
            
            if ($products->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'No products found. Please scrape products first.',
                ]);
            }

            // Prepare product data
            $productData = $this->prepareProductData($products);
            
            // Create prompt for AI
            $prompt = $this->createAnalysisPrompt($productData);
            
            // Call Gemini API
            $aiInsights = $this->callGeminiAPI($prompt);
            
            return response()->json([
                'success' => true,
                'ai_analysis' => [
                    'total_products' => $products->count(),
                    'basic_stats' => $productData['stats'],
                    'ai_insights' => $aiInsights,
                    'price_recommendations' => $this->getPriceRecommendations($products),
                    'category_analysis' => $this->getCategoryAnalysis($products),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function prepareProductData($products): array
    {
        $stats = [
            'total' => $products->count(),
            'avg_price' => round($products->avg('price'), 2),
            'min_price' => $products->min('price'),
            'max_price' => $products->max('price'),
            'avg_rating' => round($products->avg('rating'), 2),
        ];

        $byCategory = $products->groupBy('category')->map(function ($items, $category) {
            return [
                'category' => $category,
                'count' => $items->count(),
                'avg_price' => round($items->avg('price'), 2),
                'min_price' => $items->min('price'),
                'max_price' => $items->max('price'),
                'avg_rating' => round($items->avg('rating'), 2),
            ];
        })->values();

        return [
            'stats' => $stats,
            'by_category' => $byCategory,
        ];
    }

    private function createAnalysisPrompt(array $data): string
    {
        $stats = $data['stats'];
        $categories = $data['by_category'];

        $prompt = "Analyze this e-commerce product data and provide detailed insights:\n\n";
        $prompt .= "Overall Statistics:\n";
        $prompt .= "- Total Products: {$stats['total']}\n";
        $prompt .= "- Average Price: \${$stats['avg_price']}\n";
        $prompt .= "- Price Range: \${$stats['min_price']} - \${$stats['max_price']}\n";
        $prompt .= "- Average Rating: {$stats['avg_rating']}/5\n\n";
        
        $prompt .= "Category Breakdown:\n";
        foreach ($categories as $cat) {
            $prompt .= "- {$cat['category']}: {$cat['count']} products, Avg Price: \${$cat['avg_price']}, Rating: {$cat['avg_rating']}/5\n";
        }
        
        $prompt .= "\nProvide analysis on:\n";
        $prompt .= "1. Price trends and which category offers best value\n";
        $prompt .= "2. Quality insights based on ratings\n";
        $prompt .= "3. High vs Low price product comparison\n";
        $prompt .= "4. Recommendations for budget buyers\n";
        $prompt .= "5. Premium product insights\n";
        $prompt .= "6. Market positioning suggestions\n";
        $prompt .= "\nKeep the response clear, structured, and actionable.";

        return $prompt;
    }

    // KEEP ONLY THIS ONE - USING gemini-2.5-flash-lite
    private function callGeminiAPI(string $prompt): string
    {
        try {
            \Log::info("Starting Gemini 2.5 Flash Lite AI analysis");
            
            // Use Gemini 2.5 Flash Lite - same as your translation
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(60)
                ->retry(2, 2000)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key={$this->geminiApiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 2048,
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                \Log::info("Gemini API response received");
                
                $aiText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                
                if ($aiText) {
                    \Log::info("AI analysis succeeded with Gemini 2.5 Flash Lite");
                    return trim($aiText);
                }
                
                throw new \Exception('No text in AI response');
            } else {
                $errorBody = $response->body();
                \Log::error("Gemini API Error", [
                    'status' => $response->status(),
                    'body' => $errorBody
                ]);
                throw new \Exception('Gemini API Error: ' . $errorBody);
            }

        } catch (\Exception $e) {
            \Log::error("Gemini API Exception: " . $e->getMessage());
            throw new \Exception('Failed to get AI analysis: ' . $e->getMessage());
        }
    }

    private function getPriceRecommendations($products): array
    {
        $avgPrice = $products->avg('price');
        
        return [
            'expensive' => $products->where('price', '>', $avgPrice * 1.5)
                ->sortByDesc('price')
                ->take(5)
                ->values()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'title' => $p->title,
                    'price' => $p->price,
                    'category' => $p->category,
                    'rating' => $p->rating,
                ]),
            'best_value' => $products->where('rating', '>=', 3.5)
                ->where('price', '<', $avgPrice)
                ->sortByDesc('rating')
                ->take(5)
                ->values()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'title' => $p->title,
                    'price' => $p->price,
                    'rating' => $p->rating,
                    'category' => $p->category,
                ]),
            'budget_friendly' => $products->sortBy('price')
                ->take(5)
                ->values()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'title' => $p->title,
                    'price' => $p->price,
                    'rating' => $p->rating,
                    'category' => $p->category,
                ]),
        ];
    }

    private function getCategoryAnalysis($products): array
    {
        return $products->groupBy('category')->map(function ($items, $category) {
            $avgPrice = $items->avg('price');
            $avgRating = $items->avg('rating');
            $bestProduct = $items->sortByDesc('rating')->first();
            
            return [
                'category' => $category,
                'total_products' => $items->count(),
                'avg_price' => round($avgPrice, 2),
                'min_price' => $items->min('price'),
                'max_price' => $items->max('price'),
                'avg_rating' => round($avgRating, 2),
                'quality_score' => round(($avgRating / 5) * 100, 1),
                'best_product' => [
                    'title' => $bestProduct->title,
                    'price' => $bestProduct->price,
                    'rating' => $bestProduct->rating,
                ],
            ];
        })->values()->toArray();
    }

    private function getBasicStats(): array
    {
        $products = Product::all();
        
        if ($products->isEmpty()) {
            return [
                'total_products' => 0,
                'avg_price' => 0,
                'min_price' => 0,
                'max_price' => 0,
                'avg_rating' => 0,
                'categories' => [],
            ];
        }

        return [
            'total_products' => $products->count(),
            'avg_price' => round($products->avg('price'), 2),
            'min_price' => $products->min('price'),
            'max_price' => $products->max('price'),
            'avg_rating' => round($products->avg('rating'), 2),
            'categories' => $products->groupBy('category')->map(function ($items, $category) {
                return [
                    'category' => $category,
                    'count' => $items->count(),
                    'avg_price' => round($items->avg('price'), 2),
                    'min_price' => $items->min('price'),
                    'max_price' => $items->max('price'),
                    'avg_rating' => round($items->avg('rating'), 2),
                ];
            })->values(),
        ];
    }
}