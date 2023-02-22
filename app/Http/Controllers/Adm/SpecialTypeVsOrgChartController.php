<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Adm\SpecialTypeVsOrgChart;

class SpecialTypeVsOrgChartController extends Controller
{
    public function getData(){
        // $lSpecialTypeVsOrgChart = \DB::table('cat_special_type as s')
        //                             ->leftJoin('cat_special_vs_org_chart as so', 'so.cat_special_id', '=', 's.id_special_type')
        //                             ->leftJoin('users as u', 'u.id', '=', 'so.user_id_n')
        //                             ->leftJoin('org_chart_jobs as o', 'o.id_org_chart_job', '=', 'so.org_chart_job_id_n')
        //                             ->leftJoin('ext_departments as d', 'd.id_department', '=', 'so.depto_id_n')
        //                             ->leftJoin('ext_company as c', 'c.id_company', '=', 'so.company_id_n')
        //                             ->where(function($query){
        //                                 $query->where('so.is_deleted', 0)->orWhere('so.is_deleted', null);
        //                             })
        //                             ->where('s.is_deleted', 0)
        //                             ->select(
        //                                 's.name as special_name',
        //                                 'so.*',
        //                                 'u.full_name_ui as user_name',
        //                                 'o.job_name_ui as org_chart_name',
        //                                 'd.department_name_ui as depto_name',
        //                                 'c.company_name_ui as company_name',
        //                             )
        //                             ->get();

        $lSpecialTypeVsOrgChart = \DB::table('cat_special_vs_org_chart as so')
                                    ->leftJoin('cat_special_type as st', 'st.id_special_type', '=', 'so.cat_special_id')
                                    ->leftJoin('users as u', 'u.id', '=', 'so.user_id_n')
                                    ->leftJoin('org_chart_jobs as o', 'o.id_org_chart_job', '=', 'so.org_chart_job_id_n')
                                    ->leftJoin('ext_company as c', 'c.id_company', '=', 'so.company_id_n')
                                    ->leftJoin('org_chart_jobs as or', 'or.id_org_chart_job', '=', 'so.revisor_id')
                                    ->where('st.is_deleted', 0)
                                    ->where('so.is_deleted', 0)
                                    ->select(
                                        'st.name as special_name',
                                        'so.*',
                                        'u.full_name_ui as user_name',
                                        'o.job_name_ui as org_chart_name',
                                        'c.company_name_ui as company_name',
                                        'or.job_name_ui as revisor_name',
                                    )
                                    ->get();

        return $lSpecialTypeVsOrgChart;
    }

    public function index(){
        $lSpecialTypeVsOrgChart = $this->getData();

        $lSpecialType = \DB::table('cat_special_type')
                            ->where('is_deleted', 0)
                            ->select('id_special_type as id', 'name as text')
                            ->get()
                            ->toArray();

        $lUsers = \DB::table('users')
                    ->where('id', '!=', 1)
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->select('id', 'full_name_ui as text')
                    ->get()
                    ->toArray();

        $lOrgChart = \DB::table('org_chart_jobs')
                        ->where('id_org_chart_job', '!=', 1)
                        ->where('is_deleted', 0)
                        ->select('id_org_chart_job as id', 'job_name_ui as text')
                        ->get()
                        ->toArray();

        $lCompanies = \DB::table('ext_company')
                        ->where('is_active', 1)
                        ->where('is_deleted', 0)
                        ->select('id_company as id', 'company_name_ui as text')
                        ->get()
                        ->toArray();

        return view('Adm.SpecialTypeVsOrgChart')->with('lSpecialTypeVsOrgChart', $lSpecialTypeVsOrgChart)
                                                ->with('lUsers', $lUsers)
                                                ->with('lSpecialType', $lSpecialType)
                                                ->with('lOrgChart', $lOrgChart)
                                                ->with('lCompanies', $lCompanies);
    }

    public function checkAssignSpecial($request){
        /**
         * 0: Área
         * 1: Usuario
         * 2: Empresa
         */
        switch ($request->assign_by) {
            case 0:
                $oSpecial = SpecialTypeVsOrgChart::where('cat_special_id', $request->cat_special_id)
                                                ->where('org_chart_job_id_n', $request->org_chart_job_id)
                                                ->where('is_deleted', 0)
                                                ->first();
                break;
            case 1:
                $oSpecial = SpecialTypeVsOrgChart::where('cat_special_id', $request->cat_special_id)
                                                ->where('user_id_n', $request->user_id)
                                                ->where('is_deleted', 0)
                                                ->first();
                break;
            case 2:
                $oSpecial = SpecialTypeVsOrgChart::where('cat_special_id', $request->cat_special_id)
                                                ->where('company_id_n', $request->company_id)
                                                ->where('is_deleted', 0)
                                                ->first();
                break;
            
            default:
                break;
        }

        if(!is_null($oSpecial)){
            return false;
        }else{
            return true;
        }
    }

    public function save(Request $request){
        try {
            if(!$this->checkAssignSpecial($request)){
                return json_encode(['success' => false, 'message' => 'Ya existe una asignación para esta solicitud especial', 'icon' => 'error']);
            }

            \DB::beginTransaction();
            $oSpecialTypeVsOrgChart = new SpecialTypeVsOrgChart();
            $oSpecialTypeVsOrgChart->cat_special_id = $request->cat_special_id;
            $oSpecialTypeVsOrgChart->user_id_n = $request->user_id;
            $oSpecialTypeVsOrgChart->org_chart_job_id_n = $request->org_chart_job_id;
            $oSpecialTypeVsOrgChart->company_id_n = $request->company_id;
            $oSpecialTypeVsOrgChart->revisor_id = $request->revisor_id;
            $oSpecialTypeVsOrgChart->created_by = \Auth::user()->id;
            $oSpecialTypeVsOrgChart->updated_by = \Auth::user()->id;
            $oSpecialTypeVsOrgChart->save();

            $lSpecialTypeVsOrgChart = $this->getData();
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollback();
            return json_encode(['success' => false, 'message' => 'Error al guardar el registro', 'icon' => 'error']);
        }
        return json_encode(['success' => true, 'lSpecialTypeVsOrgChart' => $lSpecialTypeVsOrgChart]);
    }

    public function update(Request $request){
        try {
            if(!$this->checkAssignSpecial($request)){
                return json_encode(['success' => false, 'message' => 'Ya existe una asignación para esta solicitud especial', 'icon' => 'error']);
            }
            \DB::beginTransaction();
            $oSpecialTypeVsOrgChart = SpecialTypeVsOrgChart::findOrFail($request->id);
            $oSpecialTypeVsOrgChart->cat_special_id = $request->cat_special_id;
            $oSpecialTypeVsOrgChart->user_id_n = $request->user_id;
            $oSpecialTypeVsOrgChart->org_chart_job_id_n = $request->org_chart_job_id;
            $oSpecialTypeVsOrgChart->company_id_n = $request->company_id;
            $oSpecialTypeVsOrgChart->revisor_id = $request->revisor_id;
            $oSpecialTypeVsOrgChart->updated_by = \Auth::user()->id;
            $oSpecialTypeVsOrgChart->update();

            $lSpecialTypeVsOrgChart = $this->getData();
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollback();
            return json_encode(['success' => false, 'message' => 'Error al actualizar el registro', 'icon' => 'error']);
        }
        return json_encode(['success' => true, 'lSpecialTypeVsOrgChart' => $lSpecialTypeVsOrgChart]);
    }

    public function delete(Request $request){
        try {
            \DB::beginTransaction();
            $oSpecialTypeVsOrgChart = SpecialTypeVsOrgChart::findOrFail($request->id);
            $oSpecialTypeVsOrgChart->is_deleted = 1;
            $oSpecialTypeVsOrgChart->updated_by = \Auth::user()->id;
            $oSpecialTypeVsOrgChart->update();

            $lSpecialTypeVsOrgChart = $this->getData();
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollback();
            return json_encode(['success' => false, 'message' => 'Error al actualizar el registro', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lSpecialTypeVsOrgChart' => $lSpecialTypeVsOrgChart]);
    }
}
