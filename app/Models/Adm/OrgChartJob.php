<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class OrgChartJob extends Model
{
    protected $table = 'org_chart_jobs';
    protected $primaryKey = 'id_org_chart_job';

    protected $fillable = [
        'job_code',
        'job_name',
        'job_name_ui',
        'top_org_chart_job_id_n',
        'positions',
        'is_area',
        'area_code',
        'area_name',
        'area_name_ui',
        'external_id_n',
        'is_deleted',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    public function parent()
    {
        return $this->belongsTo('App\Models\Adm\OrgChartJob', 'top_org_chart_job_id_n')->where('is_deleted', 0);
    }

    public function children()
    {
        return $this->hasMany('App\Models\Adm\OrgChartJob', 'top_org_chart_job_id_n')->where('is_deleted', 0);
    }

    public function getChildrens(){
        $child = $this->children()->get();
        foreach($child as $c){
            $c->child = $c->getChildrens();
        }
        return $child;
    }

    public function getArrayChilds(){
        $arrayChilds = [];
        if(isset($this->child)){
            foreach($this->child as $c){
                array_push($arrayChilds, [$c->id_org_chart_job]);
                array_push($arrayChilds, $c->getArrayChilds());
            }
            $arrayChilds = Arr::collapse($arrayChilds);
            return $arrayChilds;
        }else{
            return null;
        }
    }
}
