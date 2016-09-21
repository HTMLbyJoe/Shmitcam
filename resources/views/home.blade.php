<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>The Shmitcam</title>
    <link rel="stylesheet" href="/styles/main.css">
    <script>
    var SHMITCAM = {!! json_encode($shmitcam_js_global) !!};
    </script>
</head>
<body>
    <div class="page">
        <div class="cam-background" style="background-image:url('{{ $stream_url }}');"></div>
        <div class="cam" style="background-image:url('{{ $stream_url }}');"></div>
    </div>
    <script src="/javascript/main.js"></script>
</body>
</html>
