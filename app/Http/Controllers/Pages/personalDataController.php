<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use \App\Utils\delegationUtils;

class personalDataController extends Controller
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
                    break;
                case "BLOOD":
                    $lBlood = $hrsType->where('class', $class->id)->map(function ($item, $key){
                                    return [
                                        'id' => $item->id,
                                        'text' => $item->name,
                                    ];
                                })->toArray();

                    $lBlood = array_combine(range(0, count($lBlood) - 1), array_values($lBlood));
                    break;
                case "CIVIL":
                    $lCivil = $hrsType->where('class', $class->id)->map(function ($item, $key){
                                    return [
                                        'id' => $item->id,
                                        'text' => $item->name,
                                    ];
                                })->toArray();

                    $lCivil = array_combine(range(0, count($lCivil) - 1), array_values($lCivil));
                    break;
                case "SCHOOLING":
                    $lSchooling = $hrsType->where('class', $class->id)->map(function ($item, $key){
                                        return [
                                            'id' => $item->id,
                                            'text' => $item->name,
                                        ];
                                    })->toArray();

                    $lSchooling = array_combine(range(0, count($lSchooling) - 1), array_values($lSchooling));
                    break;
                case "PARENTESCO":
                    $lParentesco = $hrsType->where('class', $class->id)->map(function ($item, $key){
                                        return [
                                            'id' => $item->id,
                                            'text' => $item->name,
                                        ];
                                    })->toArray();

                    $lParentesco = array_combine(range(0, count($lParentesco) - 1), array_values($lParentesco));
            }
        }

        $lStates = collect($oData->locuSta)->map(function ($item, $key){
                        return [
                            'id' => $item->idSta,
                            'text' => $item->nameSta,
                        ];
                    })->toArray();

        $personalData->sexCl = $personalData->sexCl != '' ? $personalData->sexCl : $lSex[0]['class'];
        $personalData->sexTp = $personalData->sexTp != '' ? $personalData->sexTp : $lSex[0]['type'];    
        $personalData->maritalCl = $personalData->maritalCl != '' ? $personalData->maritalCl : $lCivil[0]['class'];
        $personalData->maritalTp = $personalData->maritalTp != '' ? $personalData->maritalTp : $lCivil[0]['type'];
        $personalData->educationCl = $personalData->educationCl != '' ? $personalData->educationCl : $lSchooling[0]['class'];
        $personalData->educationTp = $personalData->educationTp != '' ? $personalData->educationTp : $lSchooling[0]['type'];
        $personalData->bloodCl = $personalData->bloodCl != '' ? $personalData->bloodCl : $lBlood[0]['class'];
        $personalData->bloodTp = $personalData->bloodTp != '' ? $personalData->bloodTp : $lBlood[0]['type'];
        $personalData->emergCl = $personalData->emergCl != '' ? $personalData->emergCl : $lParentesco[0]['class'];
        $personalData->emergTp = $personalData->emergTp != '' ? $personalData->emergTp : $lParentesco[0]['type'];
        $personalData->sexMateCl = $personalData->sexMateCl != '' ? $personalData->sexMateCl : $lSex[0]['class'];
        $personalData->sexMateTp = $personalData->sexMateTp != '' ? $personalData->sexMateTp : $lSex[0]['type'];
        $personalData->sexSonCl1 = $personalData->sexSonCl1 != '' ? $personalData->sexSonCl1 : $lSex[0]['class'];
        $personalData->sexSonTp1 = $personalData->sexSonTp1 != '' ? $personalData->sexSonTp1 : $lSex[0]['type'];
        $personalData->sexSonCl2 = $personalData->sexSonCl2 != '' ? $personalData->sexSonCl2 : $lSex[0]['class'];
        $personalData->sexSonTp2 = $personalData->sexSonTp2 != '' ? $personalData->sexSonTp2 : $lSex[0]['type'];
        $personalData->sexSonCl3 = $personalData->sexSonCl3 != '' ? $personalData->sexSonCl3 : $lSex[0]['class'];
        $personalData->sexSonTp3 = $personalData->sexSonTp3 != '' ? $personalData->sexSonTp3 : $lSex[0]['type'];
        $personalData->sexSonCl4 = $personalData->sexSonCl4 != '' ? $personalData->sexSonCl4 : $lSex[0]['class'];
        $personalData->sexSonTp4 = $personalData->sexSonTp4 != '' ? $personalData->sexSonTp4 : $lSex[0]['type'];
        $personalData->sexSonCl5 = $personalData->sexSonCl5 != '' ? $personalData->sexSonCl5 : $lSex[0]['class'];
        $personalData->sexSonTp5 = $personalData->sexSonTp5 != '' ? $personalData->sexSonTp5 : $lSex[0]['type'];
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

        return view('personalData.personalData')->with('personalData', $personalData)
                                                ->with('config', $config->dataPersonal)
                                                ->with('lSex', $lSex)
                                                ->with('lBlood', $lBlood)
                                                ->with('lCivil', $lCivil)
                                                ->with('lSchooling', $lSchooling)
                                                ->with('lStates', $lStates)
                                                ->with('lParentesco', $lParentesco);
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
            'base_uri' => $config->urlSync,
            'timeout' => 30.0,
        ]);

        $response = $client->request('GET', 'getPersonalInfo/' . $oEmp->external_id_n);
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);

        return $data;
    }

    public function updatePersonalData(Request $request){
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
            'lastname1' => $request->lastName,
            'lastname2' => $request->secondLastName,
            'names' => $request->names,
            'rfc' => $request->rfc,

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
            'emergs_con' => $request->emergencyContac,

            'fk_tp_cat_kin_emergs' => $hrsType->where('id', $request->parentesco)->first()->type,
            'fk_cl_cat_kin_emergs' => $hrsType->where('id', $request->parentesco)->first()->class,

            'street' => $request->street,
            'street_num_ext' => $request->outsideNumber,
            'street_num_int' => $request->insideNumber,
            'neighborhood' => $request->colony,
            'locality' => $request->locality,
            'fid_sta_n' => $request->state,
            // 'zip_code' => $request->postalCode,
            'county' => $request->municipality,
            'reference' => $request->reference,

            'mate' => $request->spouse,
            'mate_dt_bir_n' => $request->birthdaySpouce,
            'fk_cl_cat_sex_mate' => $hrsType->where('id', $request->sexSpouce)->first()->class,
            'fk_tp_cat_sex_mate' => $hrsType->where('id', $request->sexSpouce)->first()->type,

            'benefs' => $request->beneficiary,

            'son_1' => !is_null($lChilds->where('id', 0)->first()) ? $lChilds->where('id', 0)->first()['name'] : '',
            'son_dt_bir_1_n' => !is_null($lChilds->where('id', 0)->first()) ? $lChilds->where('id', 0)->first()['birthday']: '',
            'fk_cl_cat_sex_son_1' => !is_null($lChilds->where('id', 0)->first()) ? $hrsType->where('id', $lChilds->where('id', 0)->first()['sex'])->first()->class : 1,
            'fk_tp_cat_sex_son_1' => !is_null($lChilds->where('id', 0)->first()) ? $hrsType->where('id', $lChilds->where('id', 0)->first()['sex'])->first()->type : 1,
            'son_2' => !is_null($lChilds->where('id', 1)->first()) ? $lChilds->where('id', 1)->first()['name'] : '',
            'son_dt_bir_2_n' => !is_null($lChilds->where('id', 1)->first()) ? $lChilds->where('id', 1)->first()['birthday']: '',
            'fk_cl_cat_sex_son_2' => !is_null($lChilds->where('id', 1)->first()) ? $hrsType->where('id', $lChilds->where('id', 1)->first()['sex'])->first()->class : 1,
            'fk_tp_cat_sex_son_2' => !is_null($lChilds->where('id', 1)->first()) ? $hrsType->where('id', $lChilds->where('id', 1)->first()['sex'])->first()->type : 1,
            'son_3' => !is_null($lChilds->where('id', 2)->first()) ? $lChilds->where('id', 2)->first()['name'] : '',
            'son_dt_bir_3_n' => !is_null($lChilds->where('id', 2)->first()) ? $lChilds->where('id', 2)->first()['birthday']: '',
            'fk_cl_cat_sex_son_3' => !is_null($lChilds->where('id', 2)->first()) ? $hrsType->where('id', $lChilds->where('id', 2)->first()['sex'])->first()->class : 1,
            'fk_tp_cat_sex_son_3' => !is_null($lChilds->where('id', 2)->first()) ? $hrsType->where('id', $lChilds->where('id', 2)->first()['sex'])->first()->type : 1,
            'son_4' => !is_null($lChilds->where('id', 3)->first()) ? $lChilds->where('id', 3)->first()['name'] : '',
            'son_dt_bir_4_n' => !is_null($lChilds->where('id', 3)->first()) ? $lChilds->where('id', 3)->first()['birthday']: '',
            'fk_cl_cat_sex_son_4' => !is_null($lChilds->where('id', 3)->first()) ? $hrsType->where('id', $lChilds->where('id', 3)->first()['sex'])->first()->class : 1,
            'fk_tp_cat_sex_son_4' => !is_null($lChilds->where('id', 3)->first()) ? $hrsType->where('id', $lChilds->where('id', 3)->first()['sex'])->first()->type : 1,
            'son_5' => !is_null($lChilds->where('id', 4)->first()) ? $lChilds->where('id', 4)->first()['name'] : '',
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
                                $acentos = array('á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú');
                                $sinAcentos = array('a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U');
                                $item = strtr($item, array_combine($acentos, $sinAcentos));
                                return $item;
                            }
                        });

        $strBody = json_encode($body);

        $config = \App\Utils\Configuration::getConfigurations();
        $client = new Client([
            'base_uri' => $config->urlSync,
            'timeout' => 30.0,
            'headers' => [
                'Content-Type' => 'application/json', // Establecer el tipo de contenido como JSON
            ],
        ]);

        $response = $client->request('GET', 'insertPersonalInfo/' . $strBody);
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);

        if($data == "true"){
            return json_encode(['success' => true, 'message' => 'Datos actualizados correctamente']);
        }else{
            return json_encode(['success' => false, 'message' => 'Error al actualizar los datos', 'icon' => 'error']);
        }
    }
}
