<?php namespace App\Utils;

use Illuminate\Foundation\Auth\User;
class RuleApplicabilityResolver
{
    public const PERCEPTION = [
        'VACATIONS' => 1,
        'INCIDENCE' => 2,
        'PERMISSION' => 3
    ];

    public static function applies($perceptionType, $incidentType = null)
    {
        switch ($perceptionType) {
            case self::PERCEPTION['VACATIONS']:
                return true;
            case self::PERCEPTION['INCIDENCE']:
                return true;
            case self::PERCEPTION['PERMISSION']:
                return true;
            default:
                return false;
        }
    }
}