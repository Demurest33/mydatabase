<?php
use Illuminate\Support\Facades\Route;

Route::get('/php-check', function() {
    return "
        <html>
        <head><title>PHP Upload Limits</title><style>body{background:#0b111a;color:white;font-family:sans-serif;padding:50px;} .val{color:#10b981;font-weight:bold;}</style></head>
        <body>
            <h1>Diagnóstico de Subida</h1>
            <p>upload_max_filesize: <span class='val'>" . ini_get('upload_max_filesize') . "</span></p>
            <p>post_max_size: <span class='val'>" . ini_get('post_max_size') . "</span></p>
            <p>memory_limit: <span class='val'>" . ini_get('memory_limit') . "</span></p>
            <p>max_execution_time: <span class='val'>" . ini_get('max_execution_time') . " seg</span></p>
            <br>
            <p><i>Si no ves 512M en los dos primeros, el video de 240MB seguirá fallando.</i></p>
        </body>
        </html>
    ";
});
