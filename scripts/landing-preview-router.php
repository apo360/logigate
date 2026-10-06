<?php

// Deliberately does not dispatch application endpoints or submit forms.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (in_array($path, ['/', '/empty', '/live'], true)) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile(__DIR__.'/../storage/app/landing-qa/'.match ($path) { '/empty' => 'empty.html', '/live' => 'live.html', default => 'index.html' });
    return;
}
if (str_starts_with($path, '/build/') || str_starts_with($path, '/dist/img/LandingPage/') || $path === '/favicon.ico') {
    return false;
}
http_response_code(404);
echo 'Isolated landing preview: application endpoints are disabled.';
