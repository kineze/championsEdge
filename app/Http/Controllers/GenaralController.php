<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Facility;
use App\Models\Subscription;
use App\Models\TrainingSession;
use Carbon\Carbon;

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

    public function trainingSessions()
    {
        return view('site.training-sessions');
    }

    public function trainingSessionShow(TrainingSession $trainingSession)
    {
        return view('site.training-sessions.show', compact('trainingSession'));
    }

    public function contact(){


        return view('site.contact');
    }

    public function home(){

        if (auth()->check()) {
                $user = auth()->user();

                if ($user->hasRole('Admin')) {
                    return redirect()->route('adminDashboard');
                }

                if ($user->hasRole('Marketer')) {

                    if ($user && $user->marketer->is_blocked) {
                        Auth::guard('web')->logout();
                            return redirect()->route('blocked');
                    }
                    return redirect()->route('marketerDashboard');
                }

                $hasTrainerSessions = TrainingSession::query()
                    ->where('trainer_id', $user->id)
                    ->exists();

                if ($user->hasRole('Trainer') || $hasTrainerSessions) {
                    return redirect()->route('trainerDashboard');
                }

                $hasMemberSubscription = Subscription::query()
                    ->where('user_id', $user->id)
                    ->where('is_blocked', false)
                    ->whereDate('subscription_end_date', '>=', Carbon::today()->toDateString())
                    ->exists();

                if ($hasMemberSubscription || $user->hasRole('Member')) {
                    return redirect()->route('memberDashboard');
                }

                return redirect('/');
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

        $hasTrainerSessions = TrainingSession::query()
            ->where('trainer_id', $user->id)
            ->exists();

        if ($user->hasRole('Trainer') || $hasTrainerSessions) {
            return redirect()->route('trainerDashboard');
        }

        $hasMemberSubscription = Subscription::query()
            ->where('user_id', $user->id)
            ->where('is_blocked', false)
            ->whereDate('subscription_end_date', '>=', Carbon::today()->toDateString())
            ->exists();

        if ($hasMemberSubscription || $user->hasRole('Member')) {
            return redirect()->route('memberDashboard');
        }

        return redirect('/');
    }
}
