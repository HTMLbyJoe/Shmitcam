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
        $blog_username = env('TUMBLR_BLOG_NAME');
        $blog_url = 'https://' . $blog_username . '.tumblr.com';

        $locals = [
            'stream_url' => route('cam.url.latest'),
            'shmitcam_js_global' => [
                'url' => route('cam.url.latest'),
                'refresh_interval' => intval(env('WEBCAM_REFRESH_INTERVAL')),
                'tumblr_blog' => ['username' => $blog_username, 'url' => $blog_url],
            ],
        ];
        return view('home', $locals);
    }
}
