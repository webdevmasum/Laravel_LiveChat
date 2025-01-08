<?php

namespace App\Http\Controllers\Web\Backend\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $users =User::whereNot('id', auth()->id())->get();
        return view('dashboard',compact('users'));
    }
}
