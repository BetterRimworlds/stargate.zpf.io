<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $gateAddresses = json_decode(file_get_contents(storage_path('gates') . '/known.json'));

        return view('welcome', [
            'gateAddresses' => $gateAddresses,
        ]);
    }
}
