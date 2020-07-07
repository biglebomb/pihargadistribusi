<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RouteLini extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'routelini';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];
    protected $appends = ['total_cost', 'kode_plant_asal_name', 'kode_plant_tujuan_name'];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function routeCost(){
        return $this->hasMany(RouteCost::class, 'rute_id', 'id');
    }

    public function fromLini(){
        return $this->belongsTo(Lini::class, 'kode_plant_asal', 'kode_plant');
    }

    public function toLini(){
        return $this->belongsTo(Lini::class, 'kode_plant_tujuan', 'kode_plant');
    }

    public function material(){
        return $this->belongsTo(Material::class, 'material_id', 'id');
    }

    public function materialGroup(){
        return $this->material()->first()->materialGroup()->first();
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getTotalCostAttribute(){
        return $this->routeCost()->select(['cost_id', DB::raw('round(AVG(`cost_per_ton`), 0) AS cost_per_ton')])->groupBy('cost_id')->get()->sum('cost_per_ton');
    }

    public function getKodePlantAsalNameAttribute(){
        return $this->fromLini()->first()->nama_plant;
    }

    public function getKodePlantTujuanNameAttribute(){
        return $this->toLini()->first()->nama_plant;
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
