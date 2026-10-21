<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SupplierQrController extends Controller
{
    public function index()
    {
        $url = url('/supplier-offer');

        return view('supplier-qr', compact('url'));
    }

    public function image(): Response
    {
        $svg = QrCode::format('svg')
            ->size(350)
            ->margin(2)
            ->generate($url = url('/supplier-offer'));

        return response($svg, 200)
            ->header('Content-Type', 'image/svg+xml');
    }
}
