<?php

namespace App\Http\Controllers;

use App\Models\SystemNotify;

class HomeController extends Controller
{
    //

    public function index()
    {
        $notify = SystemNotify::take(5)->orderBy('id', 'DESC')->get();
        return view('Home.index', ['notify' => $notify]);
    }
}
