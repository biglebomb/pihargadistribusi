<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\RouteLiniRequest;
use App\Models\Lini;
use App\Models\MaterialGroup;
use App\Models\Regency;
use App\Models\RouteCost;
use App\Models\RouteLini;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use function foo\func;

/**
 * Class RouteLiniCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class RouteLiniCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\RouteLini');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/routelini');
        $this->crud->setEntityNameStrings('routelini', 'route_linis');
    }

    protected function setupListOperation()
    {
        // TODO: remove setFromDb() and manually define Columns, maybe Filters
        $this->crud->setFromDb();
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(RouteLiniRequest::class);

        // TODO: remove setFromDb() and manually define Fields
        $this->crud->setFromDb();
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function findRoute($from, $to)
    {
        $routelinis = RouteLini::all();
        $routes = $this->getRoute($routelinis, $to, $from);
        if (sizeof($routes) == 0) {
            $response['message'] = "Tidak dapat menemukan rute.";
            return response()->json($response);
        }

        return response()->json($this->decodeRoutes($routes));
    }

    public function findRouteByRegency($regency_id, $group)
    {
        $regency = Regency::with('linis')->where('id', $regency_id)->first();
        if($group == "all"){
            $routelinis = RouteLini::with(['toLini', 'fromLini'])->get();
        } else {
            $routecosts = RouteCost::with(['routeLini'])->select([
                'rute_id',
                DB::raw("(SELECT nama from material_group WHERE id = (SELECT jenis FROM materials WHERE id = material_id)) as jenis"),
                ])->having('jenis', '=', $group)->distinct()
                ->get();
            $routecosts = $routecosts->map(function ($routecost) {
                return collect($routecost)->only(['rute_id'])->toArray();
            });
            $routelinis = RouteLini::whereIn('id', $routecosts)->get();
        }
        $routes = [];
        if ($regency) {
            if (sizeof($regency->linis) == 0) {
                $response['message'] = "$regency->name tidak memiliki lini.";
                return response()->json($response);
            }
            foreach ($regency->linis as $lini) {
                $available_routes = $this->getRouteByLini($routelinis, $lini->kode_plant);
                if (sizeof($available_routes) != 0)
                    array_push($routes, $available_routes);
            }
            if(!$this->decodeRoutes($routes)){
                $message = "Tidak dapat menemukan rute pengiriman ke $regency->name";
                if($group == "All") {
                    $response['message'] = $message.".";
                } else {
                    $response['success'] = false;
                    $response['message'] = $message." dengan grup material $group.";
                }
                return $response;
            }
            return response()->json($this->decodeRoutes($routes));
        }
    }

    public function decodeRoutes($routes)
    {
        if (sizeof($routes) == 0) return false;
        $array = array_map('array_filter', $routes);
        $routes = array_filter($array);
        $objects = new RecursiveIteratorIterator(new RecursiveArrayIterator($routes), RecursiveIteratorIterator::LEAVES_ONLY);
        $text = "";
        foreach ($objects as $name => $object) {
            $path = $object;
            for ($depth = $objects->getDepth() - 1; $depth >= 0; $depth--) {
                $key = $objects->getSubIterator($depth)->key();
                if (!is_int($key) && !empty($key))
                    $path .= ">" . $key;
            }
            $text .= $path . "|";
        }
        $routes = array_filter(explode("|", $text));
        $response['success'] = true;
        $response['result'] = [];
        $j = 0;
        foreach ($routes as $route) {
            $items = explode(">", $route);
            $response['result'][$j]['no'] = $j+1;
            $response['result'][$j]['route'] = [];
            $response['result'][$j]['supply_plant'] = $this->getSupplyPlant($route);
            foreach ($items as $i) {
                $lini = Lini::where('kode_plant', $i)->first();
                $detailroute['kode'] = $i;
                $detailroute['name'] = $lini->nama_plant;
                array_push($response['result'][$j]['route'], $detailroute);
            }
            $response['result'][$j]['subroute'] = [];
            $response['result'][$j]['total_cost'] = 0;
            for ($i = 1; $i < sizeof($items); $i++) {
                $find = RouteLini::where('kode_plant_asal', $items[$i - 1])->where('kode_plant_tujuan', $items[$i])->first();
                if ($find) {
                    array_push($response['result'][$j]['subroute'], $find);
                }
                $response['result'][$j]['total_cost'] += $find->total_cost;
            }
            $j++;
        }
        return $response;
    }

    public function getRoute($routes, $to, $from, $depth = 0)
    {
        if ($depth != 5) {
            $graph = [];
            foreach ($routes as $route) {
                if($route->fromLini->lini == 2){
                    if($route->toLini->lini == 2)
                        continue;
                }
                if ($route->kode_plant_tujuan == $to) {
                    $graph[$route->kode_plant_tujuan][] = $route->kode_plant_asal == $from ? $route->kode_plant_asal : $this->getRoute($routes, $route->kode_plant_asal, $from, $depth++);
                }
            }
            return $graph;
        }
    }

    public function getRouteByLini($routes, $to, $depth = 0)
    {
        if ($depth != 5) {
            $graph = [];
            foreach ($routes as $route) {
                if($route->fromLini->lini == 2){
                    if($route->toLini->lini == 2)
                        continue;
                }
                if ($route->kode_plant_tujuan == $to) {
                    $graph[$route->kode_plant_tujuan][] = $this->getRouteByLini($routes, $route->kode_plant_asal, $depth++) ?: $route->kode_plant_asal;
                }
            }
            return $graph;
        }
    }

    public function getSupplyPlant($kode){
        $kode = substr($kode, 0, 1);
        $name = "";
        switch ($kode){
            case "B": $name = "PKG"; break;
            case "C": $name = "PKC"; break;
            case "D": $name = "PKT"; break;
            case "E": $name = "PIM"; break;
            case "F": $name = "PSP"; break;
        }
        return $name;
    }
}
