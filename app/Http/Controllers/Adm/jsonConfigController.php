<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class jsonConfigController extends Controller
{
    public function index(){
        $config = \App\Utils\Configuration::getConfigurations();

        $lOrgChartJobs = \DB::table('org_chart_jobs')->where('is_deleted', 0)->get();
        
        return view('Adm.jsonConfig')->with('data', $config)->with('lOrgchart',$lOrgChartJobs);
    }

    public function update(Request $request){

        \App\Utils\Configuration::setConfiguration('root_node', $request->id_root);
        \App\Utils\Configuration::setConfiguration('default_node', $request->id_default);

        return json_encode(['success' => true, 'message' => 'Registro actualizadó con exitó']);
    }
}