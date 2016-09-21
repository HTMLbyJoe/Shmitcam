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
    }

    /**
     * Show the webcam
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $locals = [
            'stream_url' => route('cam.url.latest'),
            'webcam_info' => ['url' => route('cam.url.latest')],
        ];
        return view('home', $locals);
    }
}
