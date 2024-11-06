<?php namespace App\Http\Controllers\Utils;

use App\Constants\SysConst;
use App\Models\Adm\OrgChartJob;
use App\Utils\orgChartUtils;
use App\Utils\EmployeeVacationUtils;
use App\Utils\usersInSystemUtils;
use GuzzleHttp\Client;

class notificationsAppMobile {
    public static function remeberNotification() {
        $lOrgCharts = OrgChartJob::where('is_boss', 1)
                                ->where('is_deleted', 0)
                                ->pluck('id_org_chart_job');

        $lUsers = \DB::table('users')
                    ->whereIn('org_chart_job_id', $lOrgCharts)
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->get();

        foreach ($lUsers as $user) {
            try {
                $lChildAreas = orgChartUtils::getAllChildsToRevice($user->org_chart_job_id);
                $lEmployees = EmployeeVacationUtils::getlEmployees($lChildAreas);
                $lEmployees = usersInSystemUtils::FilterUsersInSystem($lEmployees, 'id');
                $arrEmployees =  $lEmployees->pluck('id')->toArray();
    
                $totApplicationsPending = \DB::table('applications')
                                            ->whereIn('user_id', $arrEmployees)
                                            ->where('is_deleted', 0)
                                            ->where('request_status_id', SysConst::APPLICATION_ENVIADO)
                                            ->count();
    
                $totPermissionPending = \DB::table('hours_leave')
                                            ->whereIn('user_id', $arrEmployees)
                                            ->where('is_deleted', 0)
                                            ->where('request_status_id', SysConst::APPLICATION_ENVIADO)
                                            ->count();
    
                $tot = $totApplicationsPending + $totPermissionPending;

                if ($tot > 0) {
                    $config = \App\Utils\Configuration::getConfigurations();
        
                    $headers = [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'X-API-Key' => $config->apiKeyPghMobile
                    ];
        
                    $body = '{
                        "title": "Tienes ' . $tot . ' solicitudes sin atender",
                        "body": "",
                        "data": {
                            "isNewToBadge": 0,
                            "countBadge": 0,
                            "sound": false,
                            "alert": false,
                        },
                        "badge": ' . $tot . ',
                        "sound": null,
                        "channelId": "silent",
                        "user_ids": [],
                        "external_ids": [ ' . $user->id . ' ]
                    }';
        
                    $client = new Client([
                        'base_uri' => $config->urlNotificationAppMobile,
                        'timeout' => 30.0,
                        'headers' => $headers,
                        'verify' => false
                    ]);
        
                    $request = new \GuzzleHttp\Psr7\Request('POST', '', $headers, $body);
                    $response = $client->sendAsync($request)->wait();
                    $jsonString = $response->getBody()->getContents();
                    $data = json_decode($jsonString);
                }
            } catch (\Throwable $th) {
                \Log::error($th);
            }
        }
    }
}