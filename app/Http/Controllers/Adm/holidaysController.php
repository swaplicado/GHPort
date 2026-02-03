<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Adm\Holiday;

class holidaysController extends Controller
{
    public function saveHolidaysFromJSON($lSiieHolidays)
    {
        $lHolidays = Holiday::all()->keyBy(function ($holiday) {
            return $holiday->id_holiday . '_' . $holiday->year;
        });

        try {
            foreach ($lSiieHolidays as $jSiieHoliday) {

                $key = $jSiieHoliday->id_holiday . '_' . $jSiieHoliday->year;

                if (isset($lHolidays[$key])) {
                    $this->updHoliday($jSiieHoliday, $lHolidays[$key]);
                } else {
                    $this->insertHoliday($jSiieHoliday);
                }
            }
        } catch (\Throwable $th) {
            \Log::error($th);
            return false;
        }

        return true;
    }
    private function updHoliday($jSiieHoliday, $idHoliday)
    {
        Holiday::where('id', $idHoliday)
                    ->update(
                            [
                                'name' => $jSiieHoliday->name,
                                'fecha' => $jSiieHoliday->dt_date,
                                'year' => $jSiieHoliday->year,
                                'external_key' => $jSiieHoliday->id_holiday,
                                'is_deleted' => $jSiieHoliday->is_deleted,
                            ]
                        );
    }

    private function insertHoliday($jSiieHoliday)
    {
        $oHoliday = new Holiday();
        $oHoliday->name = $jSiieHoliday->name;
        $oHoliday->fecha = $jSiieHoliday->dt_date;
        $oHoliday->year = $jSiieHoliday->year;
        $oHoliday->external_key = $jSiieHoliday->id_holiday;
        $oHoliday->is_deleted = $jSiieHoliday->is_deleted;
        $oHoliday->created_by = 1;
        $oHoliday->updated_by = 1;

        $oHoliday->save();
    }
}
