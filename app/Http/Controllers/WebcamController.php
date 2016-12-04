<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use \Illuminate\Http\Request;

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
        $mime_type = File::mimeType($image_path);
        $last_modified_int = File::lastModified($image_path);
        $last_modified_gmt = gmdate('D, d M Y H:i:s \G\M\T', $last_modified_int);

        $response = response($file, 200);
        $response->header('Content-Type', $mime_type);
        $response->header('Last-Modified', $last_modified_gmt);

        return $response;
    }

    /**
     * Upload the current webcam image to Tumblr
     *
     * @return \Illuminate\Http\Response
     */
    public function uploadTumblr(Request $request)
    {
        if (!env('WEBCAM_ONLINE') || !self::allowedToCapture($request->ip())) {
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
            'state' => env('TUMBLR_POST_STATE', 'published'),
        ]);

        $post_id = $response->id;
        $permalink = 'https://' . $blog_name . '.tumblr.com/post/' . $post_id;

        return [
            'success' => true,
            'post_id' => $post_id,
            'permalink' => $permalink,
        ];
    }

    /**
     * Whether or not the requesting IP has permission
     * to upload a webcam snapshot to Tumblr
     * @return boolean
     */
    public static function allowedToCapture($ip_address)
    {
        $trusted_ips = config('trusted-ips');

        if (!empty($trusted_ips) && in_array($ip_address, $trusted_ips)) {
            // Allow whitelisted IPs to bypass rate limiting
            return true;
        }

        // TODO: Add rate limiting logic for untrusted IPs here
        return true;
    }
}
