<?php

namespace App\Http\Controllers;

use App\Models\Facility;

class DashboardController extends Controller
{
    public function getAdminDashboard()
    {
        $facilities = Facility::with('primaryImage')->latest()->take(6)->get();
        $facilityCount = Facility::count();

        return view('dashboards.admin.dashboard', compact('facilities', 'facilityCount'));
    }
}
