<?php

namespace App\Http\Controllers;

use App\Models\GpsTraker;
use Illuminate\Http\Request;

class GpsTrakerController extends Controller
{
    public function index()
    {
        $url = "http://93.104.213.107/sharing/8cacef2890a0d46a58814b0da0e13298";
        $data = GpsTraker::fetchData($url);

        return view('GpsTraker.index', compact('data'));
    }
}
