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
            }
        }

        $lStates = collect($oData->locuSta)->map(function ($item, $key){
                        return [
                            'id' => $item->idSta,
                            'text' => $item->nameSta,
                        ];
                    })->toArray();

        $personalData->sexId = $hrsType->where('class', $personalData->sexCl)->where('type', $personalData->sexTp)->first()->id;
        $personalData->maritalId = $hrsType->where('class', $personalData->maritalCl)->where('type', $personalData->maritalTp)->first()->id;
        $personalData->educationId = $hrsType->where('class', $personalData->educationCl)->where('type', $personalData->educationTp)->first()->id;
        $personalData->bloodId = $hrsType->where('class', $personalData->bloodCl)->where('type', $personalData->bloodTp)->first()->id;
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
                                                ->with('lStates', $lStates);
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
            'emergs_tel_num' => $request->emergencyPhone,
            'emergs_con' => $request->emergencyContac,

            'street' => $request->street,
            'street_num_ext' => $request->outsideNumber,
            'street_num_int' => $request->insideNumber,
            'neighborhood' => $request->colony,
            'locality' => $request->locality,
            'state' => $request->state,
            'zip_code' => $request->postalCode,
            'county' => $request->municipality,

            'mate' => $request->spouse,
            'mate_dt_bir_n' => $request->birthdaySpouce,
            'fk_cl_cat_sex_mate' => $hrsType->where('id', $request->sexSpouce)->first()->class,
            'fk_tp_cat_sex_mate' => $hrsType->where('id', $request->sexSpouce)->first()->type,

            'benefs' => $request->beneficiary,

            'son_1' => !is_null($lChilds->where('id', 1)->first()) ? $lChilds->where('id', 1)->first()['name'] : '',
            'son_dt_bir_1_n' => !is_null($lChilds->where('id', 1)->first()) ? $lChilds->where('id', 1)->first()['birthday']: '',
            'fk_cl_cat_sex_son_1' => !is_null($lChilds->where('id', 1)->first()) ? $hrsType->where('id', $lChilds->where('id', 1)->first()['sex'])->first()->class : '',
            'fk_tp_cat_sex_son_1' => !is_null($lChilds->where('id', 1)->first()) ? $hrsType->where('id', $lChilds->where('id', 1)->first()['sex'])->first()->type : '',
            'son_2' => !is_null($lChilds->where('id', 2)->first()) ? $lChilds->where('id', 2)->first()['name'] : '',
            'son_dt_bir_2_n' => !is_null($lChilds->where('id', 2)->first()) ? $lChilds->where('id', 2)->first()['birthday']: '',
            'fk_cl_cat_sex_son_2' => !is_null($lChilds->where('id', 2)->first()) ? $hrsType->where('id', $lChilds->where('id', 2)->first()['sex'])->first()->class : '',
            'fk_tp_cat_sex_son_2' => !is_null($lChilds->where('id', 2)->first()) ? $hrsType->where('id', $lChilds->where('id', 2)->first()['sex'])->first()->type : '',
            'son_3' => !is_null($lChilds->where('id', 3)->first()) ? $lChilds->where('id', 3)->first()['name'] : '',
            'son_dt_bir_3_n' => !is_null($lChilds->where('id', 3)->first()) ? $lChilds->where('id', 3)->first()['birthday']: '',
            'fk_cl_cat_sex_son_3' => !is_null($lChilds->where('id', 3)->first()) ? $hrsType->where('id', $lChilds->where('id', 3)->first()['sex'])->first()->class : '',
            'fk_tp_cat_sex_son_3' => !is_null($lChilds->where('id', 3)->first()) ? $hrsType->where('id', $lChilds->where('id', 3)->first()['sex'])->first()->type : '',
            'son_4' => !is_null($lChilds->where('id', 4)->first()) ? $lChilds->where('id', 4)->first()['name'] : '',
            'son_dt_bir_4_n' => !is_null($lChilds->where('id', 4)->first()) ? $lChilds->where('id', 4)->first()['birthday']: '',
            'fk_cl_cat_sex_son_4' => !is_null($lChilds->where('id', 4)->first()) ? $hrsType->where('id', $lChilds->where('id', 4)->first()['sex'])->first()->class : '',
            'fk_tp_cat_sex_son_4' => !is_null($lChilds->where('id', 4)->first()) ? $hrsType->where('id', $lChilds->where('id', 4)->first()['sex'])->first()->type : '',
            'son_5' => !is_null($lChilds->where('id', 5)->first()) ? $lChilds->where('id', 5)->first()['name'] : '',
            'son_dt_bir_5_n' => !is_null($lChilds->where('id', 5)->first()) ? $lChilds->where('id', 5)->first()['birthday']: '',
            'fk_cl_cat_sex_son_5' => !is_null($lChilds->where('id', 5)->first()) ? $hrsType->where('id', $lChilds->where('id', 5)->first()['sex'])->first()->class : '',
            'fk_tp_cat_sex_son_5' => !is_null($lChilds->where('id', 5)->first()) ? $hrsType->where('id', $lChilds->where('id', 5)->first()['sex'])->first()->type : '',
        ];

        $strBody = json_encode($body);

        $config = \App\Utils\Configuration::getConfigurations();
        // $client = new Client([
        //     'base_uri' => $config->urlSync,
        //     'timeout' => 30.0,
        // ]);

        // $response = $client->request('GET', 'getInfoDatePersonal/' . json_encode($body));
        // $jsonString = $response->getBody()->getContents();
        // $data = json_decode($jsonString);
    }
}
