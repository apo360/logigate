<?php

// Render the actual Blade view with isolated, visibly fictitious plan data.
// No database, mail, payments or other integrations are used.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['app.url' => 'http://127.0.0.1:8123', 'session.driver' => 'array', 'cache.default' => 'array', 'mail.default' => 'array', 'filesystems.default' => 'local']);
Illuminate\Support\Facades\Http::preventStrayRequests();
Illuminate\Support\Facades\Mail::fake();
$request = Illuminate\Http\Request::create('http://127.0.0.1:8123/');
$app->instance('request', $request);
Illuminate\Support\Facades\URL::forceRootUrl('http://127.0.0.1:8123');
require_once __DIR__.'/../tests/Support/LandingFixtures.php';
$directory = storage_path('app/landing-qa');
if (! is_dir($directory)) mkdir($directory, 0777, true);
$html = view('welcome', ['planos' => Tests\Support\LandingFixtures::plans()])->render();
file_put_contents($directory.'/index.html', $html);
file_put_contents($directory.'/empty.html', view('welcome', ['planos' => collect()])->render());
echo "Rendered landing fixtures in storage/app/landing-qa (no database writes).\n";
