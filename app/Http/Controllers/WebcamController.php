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

    /**
     * Upload the current webcam image to Tumblr
     *
     * @return \Illuminate\Http\Response
     */
    public function uploadTumblr()
    {
        if (!env('WEBCAM_ONLINE')) {
            return false;
        }

        $blog_name = env('TUMBLR_BLOG_NAME');
        $consumer_key = env('TUMBLR_CONSUMER_KEY');
        $consumer_secret = env('TUMBLR_CONSUMER_SECRET');
        $token = env('TUMBLR_TOKEN');
        $token_secret = env('TUMBLR_TOKEN_SECRET');

        $client = new \Tumblr\API\Client($consumer_key, $consumer_secret);
        $client->setToken($token, $token_secret);

        $image_path = env('WEBCAM_IMAGE_PATH');

        $response = $client->createPost($blog_name, [
            'type' => 'photo',
            'data' => $image_path,
        ]);

        $post_id = $response->id;
        $permalink = 'https://' . $blog_name . '.tumblr.com/post/' . $post_id;

        return [
            'success' => true,
            'post_id' => $post_id,
            'permalink' => $permalink,
        ];
    }
}
