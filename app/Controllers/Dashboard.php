<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index(): string
    {
        //echo "fdasfa";
        return view('dashboard/index');
    }
}
