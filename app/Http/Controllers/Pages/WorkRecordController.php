<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\DatePersonal\registerProofPersonal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
use Carbon\Carbon;
use App\Utils\usersInSystemUtils;
use \App\Utils\delegationUtils;
use App\Constants\SysConst;

class WorkRecordController extends Controller
{
    public function index(){
        $dateEmployees = $this->getDataEmployees(Auth::user()->id);

        $dateEmployees = usersInSystemUtils::FilterUsersInSystem($dateEmployees, 'id');
        $manualRoute = "http://192.168.1.251/dokuwiki/doku.php?id=wiki:constancia";
        return view('data_personal.work_record') ->with('dataUser', $dateEmployees)->with('manualRoute', $manualRoute);
    }

    public function indexManager(){
        delegationUtils::getAutorizeRolUser([SysConst::ADMINISTRADOR, SysConst::GH]);
        $dateEmployees = $this->getDataEmployees();

        $dateEmployees = usersInSystemUtils::FilterUsersInSystem($dateEmployees, 'id');
        $manualRoute = "http://192.168.1.251/dokuwiki/doku.php?id=wiki:conscolabs";
        return view('data_personal.work_record') ->with('dataUser', $dateEmployees)->with('manualRoute', $manualRoute);
    }

    public function indexManagerLow(){
        $dateEmployees = $this->getDataEmployeesLow();

        $dateEmployees = usersInSystemUtils::FilterUsersInSystem($dateEmployees, 'id');
        return view('data_personal.work_record_low') -> with ('dataUser', $dateEmployees);
    }

    public static function getDataEmployees($id=null){
        $lEmployees = DB::table('users as u')
                        ->where('u.is_active', 1)
                        ->where('u.is_delete', 0) -> where('u.id', '!=', 1);
                        if ($id != null) {
                            $lEmployees = $lEmployees->where('u.id', $id);                            
                        }
                        $lEmployees = $lEmployees->select(DB::raw("CONCAT(u.first_name, ' ', u.last_name, ' ', u.short_name) AS name"), 'u.id', 'u.company_id')
                        ->get();
        return $lEmployees;
    }

    public static function getDataEmployeesLow($id=null){
        $lEmployees = DB::table('users as u')
                        ->where('u.is_active', 0)
                        ->where('u.is_delete', 0) -> where('u.id', '!=', 1);
                        if ($id != null) {
                            $lEmployees = $lEmployees->where('u.id', $id);                            
                        }
                        $lEmployees = $lEmployees->select(DB::raw("CONCAT(u.first_name, ' ', u.last_name, ' ', u.short_name) AS name"), 'u.id', 'u.company_id')
                        ->get();
        return $lEmployees;
    }


    public static function getWorkRecord(Request $request){
        $mpdf = new \Mpdf\Mpdf(['format' => 'Letter']);
        $lEmployeesID = DB::table('users as u')
                        ->join('ext_company as ex', 'ex.id_company', '=', 'u.company_id')
                        ->where('u.is_active', 1)
                        ->where('u.is_delete', 0)
                        ->where('u.id', $request->employee_id)
                        ->select('u.*', 'ex.id_company')
                        ->first();
        $Salary = $request->isSalary;
        $config = \App\Utils\Configuration::getConfigurations();
        $name = self::getDataEmployees($lEmployeesID->id);
        switch ($lEmployeesID->id_company) {
            case '1':
                $ruta=asset('img/aeth_carta.jpg');

                break;
            case '2':
                $ruta=asset('img/tron_carta.jpg');

                break;
            case '3':
                $ruta=asset('img/tron_carta.jpg');

                break;
            case '4':
                $ruta=asset('img/swap_carta.jpg');

                break;
            case '5':
                $ruta=asset('img/ame_carta.jpg');

                break;
            
            default:
                $ruta=asset('img/aeth_carta.jpg');
                
                break;
        }
        $mpdf->SetTitle('Constancia laboral '.$name[0]->name);

        $imagen = imagecreatefromjpeg($ruta);

        $mpdf->SetWatermarkImage($ruta);
        $date = Carbon::now();
        $dateFormat = $date->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        $firma=asset('img/firmaSorayaGH.png');
        $client = new Client([
            'base_uri' => $config->urlSync,
            'timeout' => 30.0,
        ]);

        try {
            
            $response = $client->request('GET', 'getInfoDatePersonal/' . $lEmployeesID->external_id_n);
            $jsonString = $response->getBody()->getContents();
            $data = json_decode($jsonString);
        }
        catch (\Throwable $th) {
            return false;
        }

        $fechaString = Carbon::parse($data->benefitData);
        $dateFormatBenefit = $fechaString->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        
         $html = '<body style="background-image: url('.$ruta.'); background-size: cover;  background-repeat: no-repeat; margin: 0; width: 100% !important; "> 
         
         <div style= "text-align: justify;">
         <p align=right >Morelia, Michoacán, '.$dateFormat.'</p> <br><br><br><br><br> 
         <p> <strong> '.$config->work_record->encabezado1.' </p> <p><strong>'.$config->work_record->encabezado2.' </p> <br> 
         <p>'.$config->work_record->paragraph1.'<strong> '.$config->work_record->paragraph2.' '.$name[0]->name. ' </strong>' .$config->work_record->paragraph3.
         '<strong> '.$config->work_record->paragraph4. ' '.$data->rfc. '</strong> '.$config->work_record->paragraph5.'<strong> '.$config->work_record->paragraph6. ' '.$data->nss. 
         '</strong> '.$config->work_record->paragraph7.'<strong> '.$data->nameCompany.', </strong> ';
         if(($data->rfcCompany == 'AME100902KLA') || ($data->rfcCompany == 'AET131112RQ2')){
            $html .= '<strong> S.A. DE C.V., </strong>';
         } else {
            $html .= '';
         }
         $html .= ''.$config->work_record->paragraph3.'<strong> '.$config->work_record->paragraph8. ' '.$data->rgg_fiscal. ' '
         .$config->work_record->paragraph9.' '.$data->rfcCompany. '</strong> '.$config->work_record->paragraph10.' Eje Norte Sur 451,';
         if(($lEmployeesID->id_company) == 5){
            $html .= ' Planta Baja Ciudad Industrial. C. P. 58200, ';
         } else {
            $html .= ' Col Ciudad Industrial. C. P. 58200, ';
         }
         $html .= 'desempeñando el puesto de '.$data->position.', desde el día '.$dateFormatBenefit;
         if (($request->isSalary)==true) {
            // $html .= ' '.$config->work_record->paragraph11.' '.$data->salary. '.</p> <br><br><br><br>';
            $html .= ' '.$config->work_record->paragraph11.' '.number_format($data->salary, 2, '.', '.'). '.</p> <br><br><br><br>';
         } else {
            $html .= '. '. '</p> <br><br><br><br>';
         }
         $html.= '</div>';
         $html .= '<p>'.$config->work_record->paragraph12.'</p> <br><br>
         <p align=center> <strong>'.$config->work_record->paragraph13.' </strong></p>
         <div style = "text-align: center;">
         <img align=center src="'.$firma.'">
         </div>
         <p align=center> <strong>'.$config->work_record->paragraph14.' '.ucwords(strtolower($data->nameGh)). ' </strong></p>
         <p align=center> <strong> Gerente de Gestión Humana </strong></p>
         </body>' ;
        

        // Escribir el HTML en el PDF
        $mpdf->WriteHTML($html);

        // Generar el PDF y ofrecerlo para su descarga
        // $mpdf->Output('ejemplo.pdf', 'D');

         $base64 = base64_encode($mpdf->Output('Constancia_laboral.pdf', 'S'));
        // $base64 = base64_encode($mpdf->Output(' ', 'S'));

        
        self::registrarClic($request->employee_id, $request->isSalary, $lEmployeesID->id_company);

        return json_encode(['success' => true, "pdf"=> $base64]);
        

        
    }

    public static function getWorkRecordLow(Request $request){
        $mpdf = new \Mpdf\Mpdf(['format' => 'Letter']);
        $lEmployeesID = DB::table('users as u')
                        ->join('ext_company as ex', 'ex.id_company', '=', 'u.company_id')
                        ->where('u.is_active', 0)
                        ->where('u.is_delete', 0)
                        ->where('u.id', $request->employee_id)
                        ->select('u.*', 'ex.id_company')
                        ->first();
        $Salary = $request->isSalary;
        $config = \App\Utils\Configuration::getConfigurations();
        $name = self::getDataEmployeesLow($lEmployeesID->id);
        switch ($lEmployeesID->id_company) {
            case '1':
                $ruta=asset('img/aeth_carta.jpg');

                break;
            case '2':
                $ruta=asset('img/tron_carta.jpg');

                break;
            case '3':
                $ruta=asset('img/tron_carta.jpg');

                break;
            case '4':
                $ruta=asset('img/swap_carta.jpg');

                break;
            case '5':
                $ruta=asset('img/ame_carta.jpg');

                break;
            
            default:
                $ruta=asset('img/aeth_carta.jpg');
                
                break;
        }
        $mpdf->SetTitle('Constancia laboral '.$name[0]->name);

        $imagen = imagecreatefromjpeg($ruta);

        $mpdf->SetWatermarkImage($ruta);
        $date = Carbon::now();
        $dateFormat = $date->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        $firma=asset('img/firmaSorayaGH.png');
        $client = new Client([
            'base_uri' => $config->urlSync,
            'timeout' => 30.0,
        ]);

        try {
            
            $response = $client->request('GET', 'getInfoDatePersonal/' . $lEmployeesID->external_id_n);
            $jsonString = $response->getBody()->getContents();
            $data = json_decode($jsonString);
        }
        catch (\Throwable $th) {
            return false;
        }

        $fechaString = Carbon::parse($data->benefitData);
        $fechaStringEnd = Carbon::parse($lEmployeesID->last_dismiss_date_n);
        $dateFormatBenefit = $fechaString->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        $dateFormatBenefitEnd = $fechaStringEnd->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        
         $html = '<body style="background-image: url('.$ruta.'); background-size: cover;  background-repeat: no-repeat; margin: 0; width: 100% !important; "> 
         
         <div style= "text-align: justify;">
         <p align=right >Morelia, Michoacán, '.$dateFormat.'</p> <br><br><br><br><br> 
         <p> <strong> '.$config->work_record->encabezado1.' </p> <p><strong>'.$config->work_record->encabezado2.' </p> <br> 
         <p>'.$config->work_record->paragraph1.'<strong> '.$config->work_record->paragraph2.' '.$name[0]->name. ' </strong>' .$config->work_record->paragraph3.
         '<strong> '.$config->work_record->paragraph4. ' '.$data->rfc. '</strong> '.$config->work_record->paragraph5.'<strong> '.$config->work_record->paragraph6. ' '.$data->nss. 
         '</strong> '.$config->work_record->paragraph15.'<strong> '.$data->nameCompany.', </strong> ';
         if(($data->rfcCompany == 'AME100902KLA') || ($data->rfcCompany == 'AET131112RQ2')){
            $html .= '<strong> S.A. DE C.V., </strong>';
         } else {
            $html .= '';
         }
         $html .= ''.$config->work_record->paragraph3.'<strong> '.$config->work_record->paragraph8. ' '.$data->rgg_fiscal. ' '
         .$config->work_record->paragraph9.' '.$data->rfcCompany. '</strong> '.$config->work_record->paragraph10.' Eje Norte Sur 451,';
         if(($lEmployeesID->id_company) == 5){
            $html .= ' Planta Baja Ciudad Industrial. C. P. 58200, ';
         } else {
            $html .= ' Col Ciudad Industrial. C. P. 58200, ';
         }
         $html .= 'desempeñando el puesto de '.$data->position.', desde el día '.$dateFormatBenefit. ' hasta el día '.$dateFormatBenefitEnd;
         if (($request->isSalary)==true) {
            // $html .= ' '.$config->work_record->paragraph11.' '.$data->salary. '.</p> <br><br><br><br>';
            $html .= ' '.$config->work_record->paragraph11.' '.number_format($data->salary, 2, '.', '.'). '.</p> <br><br><br><br>';
         } else {
            $html .= '. '. '</p> <br><br><br><br>';
         }
         $html.= '</div>';
         $html .= '<p>'.$config->work_record->paragraph12.'</p> <br><br>
         <p align=center> <strong>'.$config->work_record->paragraph13.' </strong></p>
         <div style = "text-align: center;">
         <img align=center src="'.$firma.'">
         </div>
         <p align=center> <strong>'.$config->work_record->paragraph14.' '.ucwords(strtolower($data->nameGh)). ' </strong></p>
         <p align=center> <strong> Gerente de Gestión Humana </strong></p>
         </body>' ;
        

        // Escribir el HTML en el PDF
        $mpdf->WriteHTML($html);

        // Generar el PDF y ofrecerlo para su descarga
        // $mpdf->Output('ejemplo.pdf', 'D');

         $base64 = base64_encode($mpdf->Output('Constancia_laboral.pdf', 'S'));
        // $base64 = base64_encode($mpdf->Output(' ', 'S'));

        
        self::registrarClic($request->employee_id, $request->isSalary, $lEmployeesID->id_company);

        return json_encode(['success' => true, "pdf"=> $base64]);
        

        
    }

    public static function registrarClic($id_emp_pro, $isSalary, $company)
    {
        $log = new registerProofPersonal();
        $log->id_user_appl = auth()->user()->id; 
        $log->id_user_proof = $id_emp_pro; 
        $log->id_comp = $company; 
        $log->isSalary = $isSalary; 
        $log->save();

    }
}
