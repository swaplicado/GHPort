<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TutorialController extends Controller
{
    public function index()
    {
        return view('tutorial.index');
    }

    public function lideres()
    {
        return view('tutorial.lideres');
    }
}
