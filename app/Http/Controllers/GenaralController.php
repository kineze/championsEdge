<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Facility;

class GenaralController extends Controller
{
    public function index(){


        return view('site.index');
    }

    public function about(){


        return view('site.about');
    }

    public function facilities(){


        return view('site.facilities');
    }

    public function facilityShowPublic(Facility $facility)
    {
        return view('site.facilities.show', compact('facility'));
    }

    public function contact(){


        return view('site.contact');
    }

    public function home(){

        if (auth()->check()) {
                $user = auth()->user();

                if ($user->hasRole('Marketer')) {

                    if ($user && $user->marketer->is_blocked) {
                        Auth::guard('web')->logout();
                            return redirect()->route('blocked');
                    }
                    return redirect()->route('marketerDashboard');
                } else{
                    return redirect()->route('adminDashboard');
                }
        }else{
            return redirect()->route('login');
        }
    }


    public function setDashboard(Request $request)
    {
        if (!auth()->check()) {
            return redirect('/');
        }

        $user = auth()->user();

        if ($user->hasRole('Admin')) {
            return redirect()->route('adminDashboard');
        }

        return redirect('/');
    }
}
