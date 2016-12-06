<?php

namespace App;

class TumblrHelper
{
    public static function upload($image_path)
    {
        $blog_name = env('TUMBLR_BLOG_NAME');
        $consumer_key = env('TUMBLR_CONSUMER_KEY');
        $consumer_secret = env('TUMBLR_CONSUMER_SECRET');
        $token = env('TUMBLR_TOKEN');
        $token_secret = env('TUMBLR_TOKEN_SECRET');

        $client = new \Tumblr\API\Client($consumer_key, $consumer_secret);
        $client->setToken($token, $token_secret);

        $response = $client->createPost($blog_name, [
            'type' => 'photo',
            'data' => $image_path,
            'state' => env('TUMBLR_POST_STATE', 'published'),
        ]);

        $post_id = $response->id;
        $permalink = 'https://' . $blog_name . '.tumblr.com/post/' . $post_id;

        return [
            'response' => $response,
            'permalink' => $permalink,
        ];
    }
}
