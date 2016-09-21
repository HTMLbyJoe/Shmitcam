<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class WebcamController extends Controller
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
     * Show the latest webcam still
     *
     * @return \Illuminate\Http\Response
     */
    public function still()
    {
        if (env('WEBCAM_ONLINE')) {
            $image_path = env('WEBCAM_IMAGE_PATH');
        } else {
            $image_path = env('WEBCAM_OFFLINE_IMAGE_PATH');
        }

        $file = File::get($image_path);
        $type = File::mimeType($image_path);

        $response = response($file, 200);
        $response->header('Content-Type', $type);

        return $response;
    }
}
