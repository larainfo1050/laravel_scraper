<?php

namespace App\Http\Controllers;

use App\Services\ScraperService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ScraperController extends Controller
{
    public function __construct(private ScraperService $scraperService)
    {
    }

    public function scrape(): JsonResponse
    {
        $result = $this->scraperService->fetchProducts();
        return response()->json($result);
    }

    public function analytics(): JsonResponse
    {
        $analytics = $this->scraperService->getAnalytics();
        return response()->json($analytics);
    }
}
