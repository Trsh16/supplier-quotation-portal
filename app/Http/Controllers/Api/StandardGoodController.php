<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MeasurementUnit;
use App\Models\StandardGood;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StandardGoodController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $goods = StandardGood::with('baseUnit')
            ->where('is_active', true)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return response()->json($goods);
    }

    public function units(): JsonResponse
    {
        $units = MeasurementUnit::query()
            ->orderBy('dimension')
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
                'dimension',
                'base_unit_id',
                'conversion_factor',
            ]);

        return response()->json($units);
    }
}
