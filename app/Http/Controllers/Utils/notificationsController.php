<?php

namespace App\Http\Controllers\Utils;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class notificationsController extends Controller
{
    public function cleanNotificationsToSee(){
        session()->put('notificationsToSee', null);
    }
}
