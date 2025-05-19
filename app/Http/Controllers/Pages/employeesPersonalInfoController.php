<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use \App\Utils\delegationUtils;
use Carbon\Carbon;
use App\Models\UserDataLogs\userDataLog;

class employeesPersonalInfoController extends Controller
{
    public function index(){
        $oData = self::getPersonalData(\Auth::user()->id);
        $personalData = $oData->personalInfo;

        $name = str_replace(',', '', $personalData->lastName.', '.$personalData->firstName);
        $tokens = explode(" ", $name);
        $personalData->lastName = isset($tokens[0]) ? $tokens[0] : '';
        $personalData->secondLastName = isset($tokens[1]) ? $tokens[1] : '';
        $personalData->names = isset($tokens[2]) ? $tokens[2] : '';
        for ($i=3; $i < count($tokens); $i++) {
            $personalData->names = $personalData->names.' '.$tokens[$i];
        }
        $personalData->fullName = $name;
        $config = \App\Utils\Configuration::getConfigurations();

        $hrsClass = $config->dataPersonalHrsClass;
        $hrsType = collect($config->dataPersonalHrsType);

        $lSex = [];
        $lBlood = [];
        $lCivil = [];
        $lSchooling = [];
        foreach ($hrsClass as $class) {
            switch ($class->key) {
                case "SEX":
                    $lSex = $hrsType->where('class', $class->id)->map(function ($item, $key){
                                    return [
                                        'id' => $item->id,
                                        'text' => $item->name,
                                    ];
                                })->toArray();

                    $arrSex = array_values($hrsType->where('class', $class->id)->toArray());
                    break;
                case "BLOOD":
                    $lBlood = $hrsType->where('class', $class->id)->map(function ($item, $key){
                                    return [
                                        'id' => $item->id,
                                        'text' => $item->name,
                                    ];
                                })->toArray();

                    $lBlood = array_combine(range(0, count($lBlood) - 1), array_values($lBlood));
                    $arrBlood = array_values($hrsType->where('class', $class->id)->toArray());
                    break;
                case "CIVIL":
                    $lCivil = $hrsType->where('class', $class->id)->map(function ($item, $key){
                                    return [
                                        'id' => $item->id,
                                        'text' => $item->name,
                                    ];
                                })->toArray();

                    $lCivil = array_combine(range(0, count($lCivil) - 1), array_values($lCivil));
                    $arrCivil = array_values($hrsType->where('class', $class->id)->toArray());
                    break;
                case "SCHOOLING":
                    $lSchooling = $hrsType->where('class', $class->id)->map(function ($item, $key){
                                        return [
                                            'id' => $item->id,
                                            'text' => $item->name,
                                        ];
                                    })->toArray();

                    $lSchooling = array_combine(range(0, count($lSchooling) - 1), array_values($lSchooling));
                    $arrSchooling = array_values($hrsType->where('class', $class->id)->toArray());
                    break;
                case "PARENTESCO":
                    $lParentesco = $hrsType->where('class', $class->id)->map(function ($item, $key){
                                        return [
                                            'id' => $item->id,
                                            'text' => $item->name,
                                        ];
                                    })->toArray();

                    $lParentesco = array_combine(range(0, count($lParentesco) - 1), array_values($lParentesco));
                    $arrParentesco = array_values($hrsType->where('class', $class->id)->toArray());
            }
        }

        $lStates = collect($oData->locuSta)->map(function ($item, $key){
                        return [
                            'id' => $item->idSta,
                            'text' => $item->nameSta,
                        ];
                    })->toArray();

        $personalData->sexCl = $personalData->sexCl != '' ? $personalData->sexCl : $arrSex[0]->class;
        $personalData->sexTp = $personalData->sexTp != '' ? $personalData->sexTp : $arrSex[0]->type;    
        $personalData->maritalCl = $personalData->maritalCl != '' ? $personalData->maritalCl : $arrCivil[0]->class;
        $personalData->maritalTp = $personalData->maritalTp != '' ? $personalData->maritalTp : $arrCivil[0]->type;
        $personalData->educationCl = $personalData->educationCl != '' ? $personalData->educationCl : $arrSchooling[0]->class;
        $personalData->educationTp = $personalData->educationTp != '' ? $personalData->educationTp : $arrSchooling[0]->type;
        $personalData->bloodCl = $personalData->bloodCl != '' ? $personalData->bloodCl : $arrBlood[0]->class;
        $personalData->bloodTp = $personalData->bloodTp != '' ? $personalData->bloodTp : $arrBlood[0]->type;
        $personalData->emergCl = $personalData->emergCl != '' ? $personalData->emergCl : $arrParentesco[0]->class;
        $personalData->emergTp = $personalData->emergTp != '' ? $personalData->emergTp : $arrParentesco[0]->type;
        $personalData->sexMateCl = $personalData->sexMateCl != '' ? $personalData->sexMateCl : $arrSex[0]->class;
        $personalData->sexMateTp = $personalData->sexMateTp != '' ? $personalData->sexMateTp : $arrSex[0]->type;
        $personalData->sexSonCl1 = $personalData->sexSonCl1 != '' ? $personalData->sexSonCl1 : $arrSex[0]->class;
        $personalData->sexSonTp1 = $personalData->sexSonTp1 != '' ? $personalData->sexSonTp1 : $arrSex[0]->type;
        $personalData->sexSonCl2 = $personalData->sexSonCl2 != '' ? $personalData->sexSonCl2 : $arrSex[0]->class;
        $personalData->sexSonTp2 = $personalData->sexSonTp2 != '' ? $personalData->sexSonTp2 : $arrSex[0]->type;
        $personalData->sexSonCl3 = $personalData->sexSonCl3 != '' ? $personalData->sexSonCl3 : $arrSex[0]->class;
        $personalData->sexSonTp3 = $personalData->sexSonTp3 != '' ? $personalData->sexSonTp3 : $arrSex[0]->type;
        $personalData->sexSonCl4 = $personalData->sexSonCl4 != '' ? $personalData->sexSonCl4 : $arrSex[0]->class;
        $personalData->sexSonTp4 = $personalData->sexSonTp4 != '' ? $personalData->sexSonTp4 : $arrSex[0]->type;
        $personalData->sexSonCl5 = $personalData->sexSonCl5 != '' ? $personalData->sexSonCl5 : $arrSex[0]->class;
        $personalData->sexSonTp5 = $personalData->sexSonTp5 != '' ? $personalData->sexSonTp5 : $arrSex[0]->type;
        $personalData->fidSta = $personalData->fidSta != 0 ? $personalData->fidSta : collect($oData->locuSta)->where('nameSta', 'Michoacán')->first()->idSta;

        $personalData->sexId = $hrsType->where('class', $personalData->sexCl)->where('type', $personalData->sexTp)->first()->id;
        $personalData->maritalId = $hrsType->where('class', $personalData->maritalCl)->where('type', $personalData->maritalTp)->first()->id;
        $personalData->educationId = $hrsType->where('class', $personalData->educationCl)->where('type', $personalData->educationTp)->first()->id;
        $personalData->bloodId = $hrsType->where('class', $personalData->bloodCl)->where('type', $personalData->bloodTp)->first()->id;
        $personalData->parentescoId = $hrsType->where('class', $personalData->emergCl)->where('type', $personalData->emergTp)->first()->id;
        $personalData->sexMateId = $hrsType->where('class', $personalData->sexMateCl)->where('type', $personalData->sexMateTp)->first()->id;
        $personalData->sexSonId1 = $hrsType->where('class', $personalData->sexSonCl1)->where('type', $personalData->sexSonTp1)->first()->id;
        $personalData->sexSonId2 = $hrsType->where('class', $personalData->sexSonCl2)->where('type', $personalData->sexSonTp2)->first()->id;
        $personalData->sexSonId3 = $hrsType->where('class', $personalData->sexSonCl3)->where('type', $personalData->sexSonTp3)->first()->id;
        $personalData->sexSonId4 = $hrsType->where('class', $personalData->sexSonCl4)->where('type', $personalData->sexSonTp4)->first()->id;
        $personalData->sexSonId5 = $hrsType->where('class', $personalData->sexSonCl5)->where('type', $personalData->sexSonTp5)->first()->id;
        $infoDates = self::close_dates();

        return view('personalData.personalData')->with('personalData', $personalData)
                                                ->with('config', $config->dataPersonal)
                                                ->with('lSex', $lSex)
                                                ->with('lBlood', $lBlood)
                                                ->with('lCivil', $lCivil)
                                                ->with('lSchooling', $lSchooling)
                                                ->with('lStates', $lStates)
                                                ->with('lParentesco', $lParentesco)
                                                ->with('infoDates',$infoDates);
    }

    public static function getPersonalData($id_employee){
        $oEmp = \DB::table('users')->where('id', $id_employee)->first();

        if(is_null($oEmp)){
            return null;
        }else if(is_null($oEmp->external_id_n)){
            return null;
        }

        $config = \App\Utils\Configuration::getConfigurations();
        $client = new Client([
            'base_uri' => '192.168.1.251:9001',
            'timeout' => 30.0,
        ]);

        $response = $client->request('GET', 'getPersonalInfo/' . $oEmp->external_id_n);
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);

        return $data;
    }

    public function updatePersonalData(Request $request){
        try {
            $oEmp = \DB::table('users')->where('id', delegationUtils::getIdUser())->first();
    
            if(is_null($oEmp)){
                return null;
            }else if(is_null($oEmp->external_id_n)){
                return null;
            }
    
            $config = \App\Utils\Configuration::getConfigurations();
            $hrsType = collect($config->dataPersonalHrsType);
    
            $lChilds = collect($request->childs);
    
            $body = [
                'id_bp' => $oEmp->external_id_n,
                'id_add' => $request->id_add,
                'id_con' => $request->id_con,
                'id_bpb' => $request->id_bpb,
                // 'lastname1' => $request->lastName,
                // 'lastname2' => $request->secondLastName,
                // 'names' => $request->names,
                // 'rfc' => $request->rfc,
    
                'fk_cl_cat_sex' => $hrsType->where('id', $request->sex)->first()->class,
                'fk_tp_cat_sex' => $hrsType->where('id', $request->sex)->first()->type,
                'fk_cl_cat_blo' => $hrsType->where('id', $request->bloodType)->first()->class,
                'fk_tp_cat_blo' => $hrsType->where('id', $request->bloodType)->first()->type,
                'fk_cl_cat_mar' => $hrsType->where('id', $request->maritalStatus)->first()->class,
                'fk_tp_cat_mar' => $hrsType->where('id', $request->maritalStatus)->first()->type,
                'fk_cl_cat_edu' => $hrsType->where('id', $request->schooling)->first()->class,
                'fk_tp_cat_edu' => $hrsType->where('id', $request->schooling)->first()->type,
    
                'email_01' => $request->personalMail,
                'email_02' => $request->companyMail,
                'tel_num_01' => $request->personalPhone,
                'tel_num_02' => $request->companyPhone,
                'tel_ext_02' => $request->ext,
                'emergs_tel_num' => $request->emergencyPhone,
                'emergs_con' => strtoupper($request->emergencyContac),
    
                'fk_tp_cat_kin_emergs' => $hrsType->where('id', $request->parentesco)->first()->type,
                'fk_cl_cat_kin_emergs' => $hrsType->where('id', $request->parentesco)->first()->class,
    
                'street' => strtoupper($request->street),
                'street_num_ext' => $request->outsideNumber,
                'street_num_int' => $request->insideNumber,
                'neighborhood' => strtoupper($request->colony),
                'locality' => strtoupper($request->locality),
                'fid_sta_n' => $request->state,
                // 'zip_code' => $request->postalCode,
                // 'zip_code_fiscal' => $request->zip_code_fiscal,
                'zip_code' => $request->zip_code,
                'county' => strtoupper($request->municipality),
                'reference' => strtoupper($request->reference),
    
                'mate' => strtoupper($request->spouse),
                'mate_dt_bir_n' => $request->birthdaySpouce,
                'fk_cl_cat_sex_mate' => $hrsType->where('id', $request->sexSpouce)->first()->class,
                'fk_tp_cat_sex_mate' => $hrsType->where('id', $request->sexSpouce)->first()->type,
    
                'benefs' => strtoupper($request->beneficiary),
    
                'son_1' => !is_null($lChilds->where('id', 0)->first()) ? strtoupper($lChilds->where('id', 0)->first()['name']) : '',
                'son_dt_bir_1_n' => !is_null($lChilds->where('id', 0)->first()) ? $lChilds->where('id', 0)->first()['birthday']: '',
                'fk_cl_cat_sex_son_1' => !is_null($lChilds->where('id', 0)->first()) ? $hrsType->where('id', $lChilds->where('id', 0)->first()['sex'])->first()->class : 1,
                'fk_tp_cat_sex_son_1' => !is_null($lChilds->where('id', 0)->first()) ? $hrsType->where('id', $lChilds->where('id', 0)->first()['sex'])->first()->type : 1,
                'son_2' => !is_null($lChilds->where('id', 1)->first()) ? strtoupper($lChilds->where('id', 1)->first()['name']) : '',
                'son_dt_bir_2_n' => !is_null($lChilds->where('id', 1)->first()) ? $lChilds->where('id', 1)->first()['birthday']: '',
                'fk_cl_cat_sex_son_2' => !is_null($lChilds->where('id', 1)->first()) ? $hrsType->where('id', $lChilds->where('id', 1)->first()['sex'])->first()->class : 1,
                'fk_tp_cat_sex_son_2' => !is_null($lChilds->where('id', 1)->first()) ? $hrsType->where('id', $lChilds->where('id', 1)->first()['sex'])->first()->type : 1,
                'son_3' => !is_null($lChilds->where('id', 2)->first()) ? strtoupper($lChilds->where('id', 2)->first()['name']) : '',
                'son_dt_bir_3_n' => !is_null($lChilds->where('id', 2)->first()) ? $lChilds->where('id', 2)->first()['birthday']: '',
                'fk_cl_cat_sex_son_3' => !is_null($lChilds->where('id', 2)->first()) ? $hrsType->where('id', $lChilds->where('id', 2)->first()['sex'])->first()->class : 1,
                'fk_tp_cat_sex_son_3' => !is_null($lChilds->where('id', 2)->first()) ? $hrsType->where('id', $lChilds->where('id', 2)->first()['sex'])->first()->type : 1,
                'son_4' => !is_null($lChilds->where('id', 3)->first()) ? strtoupper($lChilds->where('id', 3)->first()['name']) : '',
                'son_dt_bir_4_n' => !is_null($lChilds->where('id', 3)->first()) ? $lChilds->where('id', 3)->first()['birthday']: '',
                'fk_cl_cat_sex_son_4' => !is_null($lChilds->where('id', 3)->first()) ? $hrsType->where('id', $lChilds->where('id', 3)->first()['sex'])->first()->class : 1,
                'fk_tp_cat_sex_son_4' => !is_null($lChilds->where('id', 3)->first()) ? $hrsType->where('id', $lChilds->where('id', 3)->first()['sex'])->first()->type : 1,
                'son_5' => !is_null($lChilds->where('id', 4)->first()) ? strtoupper($lChilds->where('id', 4)->first()['name']) : '',
                'son_dt_bir_5_n' => !is_null($lChilds->where('id', 4)->first()) ? $lChilds->where('id', 4)->first()['birthday']: '',
                'fk_cl_cat_sex_son_5' => !is_null($lChilds->where('id', 4)->first()) ? $hrsType->where('id', $lChilds->where('id', 4)->first()['sex'])->first()->class : 1,
                'fk_tp_cat_sex_son_5' => !is_null($lChilds->where('id', 4)->first()) ? $hrsType->where('id', $lChilds->where('id', 4)->first()['sex'])->first()->type : 1,
            ];
    
            // $strBody = json_encode($body);
            $oBody = collect($body);
    
            $body = $oBody->map(function ($item, $key){
                                if(is_null($item)){
                                    return '';
                                }else{
                                    // $acentos = array('á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú');
                                    // $sinAcentos = array('a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U');
                                    // $item = strtr($item, array_combine($acentos, $sinAcentos));
                                    return $item;
                                }
                            });
    
            $strBody = json_encode($body, JSON_UNESCAPED_UNICODE);

            $strBody64 = base64_encode($strBody);
    
            $config = \App\Utils\Configuration::getConfigurations();
            $client = new Client([
                'base_uri' => $config->urlSync,
                'timeout' => 30.0,
                'headers' => [
                    'Content-Type' => 'application/json', // Establecer el tipo de contenido como JSON
                ],
            ]);
    
            $response = $client->request('GET', 'insertPersonalInfo/' . $strBody64);
            $jsonString = $response->getBody()->getContents();
            $data = json_decode($jsonString);
            $data = true;
    
            if($data == "true"){

                $user = delegationUtils::getUser();
                $o_userDataLog = userDataLog::where('user_id', $user->id)
                                            ->where('data_type_id', $config->closing_dates_type->DP)
                                            ->first();
                if ($o_userDataLog == null) {
                    $o_userDataLog = new userDataLog();
                    $o_userDataLog->user_id = $user->id;
                    $o_userDataLog->data_type_id = $config->closing_dates_type->DP;
                    $o_userDataLog->save();
                } else {
                    $o_userDataLog->updated_at = Carbon::now();
                    $o_userDataLog->save();
                }

                $user->can_change_dp = 0;
                $user->save();

                return json_encode(['success' => true, 'message' => 'Datos actualizados correctamente']);
            }else{
                return json_encode(['success' => false, 'message' => 'Error al actualizar los datos', 'icon' => 'error']);
            }

        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']);
        }
    }

    public function close_dates(){
        $config = \App\Utils\Configuration::getConfigurations();
        $tipo = 0;  // 1 actuales 2 futuros 3 no existen
        $days = 0;
        $start = '';
        $end = '';
        $diaActual = Carbon::now();
        $diaActual = $diaActual->format('Y-m-d');
        //fechas actuales
        $datesPassing = \DB::table('closing_dates')
                ->where('start_date','<=',$diaActual)
                ->where('end_date','>=',$diaActual)
                ->where('is_delete',0)
                ->where('type_id', $config->closing_dates_type->DP)
                ->where('is_global', 1)
                ->get();
        
        if(count($datesPassing) < 1){
            //fechas por pasar
            $datesPassing = \DB::table('closing_dates')
                ->where('start_date','>=',$diaActual)
                ->where('is_delete',0)
                ->where('type_id', $config->closing_dates_type->DP)
                ->where('is_global', 1)
                ->get();
            if(count($datesPassing) < 1){
                $tipo = 3;
                $days = 0;
            }else{
                $fechaActual = Carbon::now();
                $fechaComparacion = Carbon::parse($datesPassing[0]->start_date)->endOfDay();
                $days = $fechaActual->diffInDays($fechaComparacion);
                $tipo = 2;

                $start = $fechaComparacion->format('d-m-Y');
            }    
        }else{
            $fechaActual = Carbon::now();
            $fechaComparacion = Carbon::parse($datesPassing[0]->end_date)->endOfDay();
            $days = $fechaActual->diffInDays($fechaComparacion);
            $tipo = 1;

            $end = $fechaComparacion->format('d-m-Y');
        }

        if ($tipo == 2 || $tipo == 3) {
            $datesPassingUser = \DB::table('closing_dates_users as cu')
                    ->join('closing_dates as c', 'c.id_closing_dates', 'cu.closing_date_id')
                    ->where('c.start_date','<=',$diaActual)
                    ->where('c.end_date','>=',$diaActual)
                    ->where('cu.user_id', delegationUtils::getIdUser())
                    ->where('c.is_delete', 0)
                    ->where('cu.is_deleted', 0)
                    ->where('cu.is_closed', 0)
                    ->where('c.type_id', $config->closing_dates_type->DP)
                    ->get();

            if(count($datesPassingUser) >= 1) {
                $fechaActual = Carbon::now();
                $fechaComparacion = Carbon::parse($datesPassingUser[0]->end_date)->endOfDay();
                $days = $fechaActual->diffInDays($fechaComparacion);
                $tipo = 1;
                $end = $fechaComparacion->format('d-m-Y');
            } else {
                $datesPassingUser = \DB::table('closing_dates_users as cu')
                    ->join('closing_dates as c', 'c.id_closing_dates', 'cu.closing_date_id')
                    ->where('c.start_date','>=',$diaActual)
                    ->where('cu.user_id', delegationUtils::getIdUser())
                    ->where('c.is_delete', 0)
                    ->where('cu.is_deleted', 0)
                    ->where('cu.is_closed', 0)
                    ->where('c.type_id', $config->closing_dates_type->DP)
                    ->get();

                if (count($datesPassingUser) >= 1) {
                    $fechaActual = Carbon::now();
                    $fechaComparacion = Carbon::parse($datesPassingUser[0]->start_date)->endOfDay();
                    $days = $fechaActual->diffInDays($fechaComparacion);
                    $tipo = 2;
    
                    $start = $fechaComparacion->format('d-m-Y');
                }
            }

            $oUser = \DB::table('users')->where('id', delegationUtils::getIdUser())->first();
            if($oUser->can_change_dp){
                $tipo = 1;
            }
        }

        $coleccionDates = collect(['start_date' => $start, 'end_date' => $end, 'type' => $tipo, 'days' => $days]);

        return $coleccionDates;
        
    }
}
