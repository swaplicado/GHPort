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

    public function getParent(){
        return $this->belongsTo('App\Models\Adm\OrgChartJob', 'top_org_chart_job_id_n')->where('is_deleted', 0);
    }

    public function children(){
        return $this->hasMany('App\Models\Adm\OrgChartJob', 'top_org_chart_job_id_n')->where('is_deleted', 0);
    }
    public function childrenToLevel($org_chart_id, $level_id){
        return $this->hasMany('App\Models\Adm\OrgChartJob', 'top_org_chart_job_id_n')
                    ->where('is_deleted', 0)
                    ->whereBetween('org_level_id', [$org_chart_id, $level_id]);
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

    public function getArrayParents(){
        $arrayParents = [];
        if(isset($this->parent)){
            foreach($this->parent as $p){
                array_push($arrayParents, [$p->id_org_chart_job]);
                array_push($arrayParents, $p->getArrayParents());
            }
            $arrayParents = Arr::collapse($arrayParents);
            return $arrayParents;
        }else{
            return null;
        }
    }

    public function getArrayParentsBoss(){
        $arrayParents = [];
        if(isset($this->parent)){
            foreach($this->parent as $p){
                if($p->is_boss){
                    array_push($arrayParents, [$p->id_org_chart_job]);
                }
                array_push($arrayParents, $p->getArrayParentsBoss());
            }
            $arrayParents = Arr::collapse($arrayParents);
            return $arrayParents;
        }else{
            return null;
        }
    }

    public function getChildrensNoBoss(){
        $child = $this->children()->get();
        foreach($child as $c){
            if($c->is_boss == 0){
                $c->child = $c->getChildrensNoBoss();
            }
        }
        return $child;
    }

    public function getParentsBoss(){
        $parent = $this->getParent()->get();
        foreach($parent as $c){
            if($c->is_boss == 0){
                $c->parent = $c->getParentsBoss();
            }
        }
        return $parent;
    }

    public function getAllParents(){
        $parent = $this->getParent()->get();
        foreach($parent as $c){
            $c->parent = $c->getAllParents();
        }
        return $parent;
    }

    public function getChildrensToLevel($org_chart_id, $level_id){
        $child = $this->childrenToLevel($org_chart_id, $level_id)->get();
        foreach($child as $c){
            if($c->org_level_id <= $level_id){
                $c->child = $c->getChildrensToLevel($org_chart_id, $level_id);
            }
        }
        return $child;
    }

    public function getChildrensToRevice(){
        $child = $this->children()->get();
        foreach($child as $c){
            if($c->is_boss == 0){
                $c->child = $c->getChildrensToRevice();
            }else{
                $boss = \DB::table('users')
                            ->where('org_chart_job_id', $c->id_org_chart_job)
                            ->where('is_active', 1)
                            ->where('is_delete', 0)
                            ->first();

                if(!is_null($boss)){
                    break;
                }else{
                    $c->child = $c->getChildrensToRevice();
                }
            }
        }

        return $child;
    }
}
