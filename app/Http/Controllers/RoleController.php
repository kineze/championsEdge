<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function roleManagement(){


        return view('dashboards.admin.settings.roleManagement');
    }
}
