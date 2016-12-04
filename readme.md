Shmitcam
========

A web app that auto-refreshes a given JPEG to make it look like a semi-live webcam stream.

Also includes a button to upload to Tumblr at no extra charge!

Installation
------------
1. Clone the repo:
```
git clone https://github.com/JoeAnzalone/Shmitcam
```

2. Install via [Composer](https://getcomposer.org/):
```
composer install
```

3. Fill out all the required environment variables in `.env`

4. Set up the cron job:
```
* * * * * /usr/local/bin/php-7.0 /home/joe/shmitcam/artisan schedule:run >> /dev/null 2>&1
```

License
-------
Shmitcam is open-sourced software licensed under the [BSD 3-Clause license.](https://opensource.org/licenses/BSD-3-Clause)

Shmitcam uses the wonderful [Lumen PHP Framework](https://lumen.laravel.com), which is licensed under [the MIT license.](https://opensource.org/licenses/MIT)
