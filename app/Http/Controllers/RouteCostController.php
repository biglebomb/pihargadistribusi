<?php

namespace App\Http\Controllers;

use App\Models\RouteCost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RouteCostController extends Controller
{
    public function get($id){
        $routecosts = RouteCost::with(['cost'])->select(['cost_id', DB::raw('round(AVG(`cost_per_ton`), 0) AS cost_per_ton')])->where('rute_id', $id)->groupBy(['cost_id'])->get();
        $response['success'] = false;
        if(sizeof($routecosts) > 0){
            $response['success'] = true;
            $response['result'] = $routecosts;
            return $response;
        }
        $response['message'] = "Tidak ada cost untuk rute ini";
        return $response;
    }
    public function getByCostType($id, $cost_id){
        $routecosts = RouteCost::with(['material', 'cost'])->where('rute_id', $id)->where('cost_id', $cost_id)->get();
        $response['success'] = false;
        if(sizeof($routecosts) > 0){
            $response['success'] = true;
            $response['result'] = $routecosts;
            return $response;
        }
        $response['message'] = "Tidak ada cost untuk rute ini";
        return $response;
    }
}
