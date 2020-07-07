<?php

namespace App\Http\Controllers;

use App\Models\MaterialGroup;
use Illuminate\Http\Request;

class MaterialGroupController extends Controller
{
    public function get(){
        $group = MaterialGroup::all();
        return response()->json($group);
    }
}
