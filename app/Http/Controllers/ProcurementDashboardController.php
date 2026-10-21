<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use Illuminate\Http\Request;

class ProcurementDashboardController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total' => Quotation::count(),
            'submitted' => Quotation::where('status', 'submitted')->count(),
            'under_review' => Quotation::where('status', 'under_review')->count(),
            'accepted' => Quotation::where('status', 'accepted')->count(),
        ];

        $search = trim((string) $request->query('search', ''));

        $query = Quotation::with([
            'supplier',
            'items.standardGood',
            'items.unit',
        ])->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('public_token', 'like', "%{$search}%")
                    ->orWhere('sales_name', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                        $supplierQuery
                            ->where('company_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items', function ($itemQuery) use ($search) {
                        $itemQuery->where('supplier_item_name', 'like', "%{$search}%");
                    });
            });
        }

        $quotations = $query->paginate(10)->withQueryString();

        return view('procurement-dashboard', compact(
            'stats',
            'quotations',
            'search'
        ));
    }
}
