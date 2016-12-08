<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="/styles/main.css">
    <meta property="og:image" content="{{ $stream_url }}">
    <meta name="viewport" content="width=device-width">
    <script>
    var SHMITCAM = {!! json_encode($shmitcam_js_global) !!};
    </script>
</head>
<body>
    <div class="page">
        <div class="cam-background" style="background-image:url('{{ $stream_url }}');"></div>
        <div class="cam" style="background-image:url('{{ $stream_url }}');"></div>
        <div class="controls">
            <span title="View saved snapshots on Tumblr" class="open-gallery" role="button">🖼</span>
            <span title="Take snapshot" class="take-snapshot" role="button">📸</span>
        </div>
        <a title="View source code on GitHub" href="https://github.com/JoeAnzalone/shmitcam" target="_blank" class="view-source">💿</a>
    </div>
    <script src="/javascript/main.js"></script>
</body>
</html>
