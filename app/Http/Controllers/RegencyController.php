<?php

namespace App\Http\Controllers;

use App\Models\Lini;
use App\Models\Regency;
use Illuminate\Http\Request;

class RegencyController extends Controller
{
    public function getByProvince($id){
        return response()->json(Regency::where('province_id', $id)->get());
    }

    public function get($id){
        return response()->json(Regency::with('linis')->where('id', $id)->first());
    }
}
