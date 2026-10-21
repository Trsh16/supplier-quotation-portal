<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class SupplierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $suppliers = Supplier::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('company_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('company_name')
            ->paginate(10);

        return response()->json($suppliers);
    }

    public function store(Request $request): JsonResponse
    {
        $request->merge([
            'company_name' => preg_replace(
                '/\s+/',
                ' ',
                trim((string) $request->input('company_name'))
            ),
        ]);

        $normalizedName = Str::lower($request->input('company_name', ''));

        $validator = Validator::make(
            $request->all(),
            [
                'company_name' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:50'],
                'address' => ['nullable', 'string', 'max:1000'],
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $duplicate = Supplier::where(
            'normalized_company_name',
            $normalizedName
        )->exists();

        if ($duplicate) {
            return response()->json([
                'message' => 'Supplier dengan nama perusahaan tersebut sudah terdaftar.',
                'errors' => [
                    'company_name' => [
                        'Nama perusahaan sudah digunakan.',
                    ],
                ],
            ], 422);
        }

        $supplier = Supplier::create([
            'company_name' => $validated['company_name'],
            'normalized_company_name' => $normalizedName,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        return response()->json([
            'message' => 'Supplier berhasil ditambahkan.',
            'data' => $supplier,
        ], 201);
    }
}
