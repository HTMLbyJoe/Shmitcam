<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>The Shmitcam</title>
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
            <span class="open-gallery" role="button">🖼</span>
            <span class="take-snapshot" role="button">📸</span>
        </div>
        <a href="https://github.com/JoeAnzalone/shmitcam" target="_blank" class="view-source">💿</a>
    </div>
    <script src="/javascript/main.js"></script>
</body>
</html>
