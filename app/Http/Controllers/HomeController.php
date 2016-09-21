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
            'shmitcam_js_global' => [
                'url' => route('cam.url.latest'),
                'refresh_interval' => intval(env('WEBCAM_REFRESH_INTERVAL'))
            ],
        ];
        return view('home', $locals);
    }
}
