<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request, SearchService $service): View
    {
        $query = trim((string) $request->query('q', ''));

        $results = $query !== '' ? $service->search($query) : null;

        return view('pages.search', [
            'query' => $query,
            'results' => $results,
        ]);
    }

    public function suggest(Request $request, SearchService $service): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if ($query === '') {
            return response()->json(['doctors' => [], 'hospitals' => [], 'guides' => [], 'patient_cases' => []]);
        }

        return response()->json($service->suggest($query));
    }
}
