<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$env = Dotenv\Dotenv::parse(file_get_contents(dirname(__DIR__) . '/.env'));
$name = $argv[1] ?? '';
if (! preg_match('/^logigate_testing_v1_[a-f0-9]{10}$/', $name) || ($env['DB_DATABASE'] ?? '') !== 'logigate_testing') { throw new RuntimeException('Unsafe sandbox.'); }
foreach ($env as $key => $value) { if (str_starts_with($key, 'DB_') || $key === 'APP_ENV') { $value = $key === 'DB_DATABASE' ? $name : $value; putenv($key . '=' . $value); $_ENV[$key] = $_SERVER[$key] = $value; } }
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (Illuminate\Support\Facades\DB::connection()->getDatabaseName() !== $name) { throw new RuntimeException('Target mismatch.'); }
$user = App\Models\User::findOrFail((int)$argv[2]);
Illuminate\Support\Facades\Auth::guard('web')->setUser($user);
session()->put('empresa_id',(int)$argv[3]);
App\Support\CompanyRbac::activate();
Illuminate\Support\Facades\Http::preventStrayRequests();
// Child processes are test-only and must never reach S3.
$app->bind(App\Application\Arquivo\Services\FileStorageService::class, fn () => new class extends App\Application\Arquivo\Services\FileStorageService { public function createDirectory(\App\Domains\Arquivo\ValueObjects\S3Path $path): void {} });
$start = (float)$argv[5];
while (microtime(true) < $start) { usleep(1000); }
$values = [];
if ($argv[4] === 'sequence') {
    for ($i=0; $i<6; $i++) { $values[] = app(App\Domains\Processo\Services\OperationalSequence::class)->reserve((int)$argv[3], 'processo', 2026); }
} else {
    $license = App\Models\Licenciamento::where('empresa_id',(int)$argv[3])->findOrFail((int)$argv[6]);
    $values[] = app(App\Application\Licenciamento\Actions\ConstituirProcessoAction::class)->execute($license)->id;
}
echo json_encode($values, JSON_THROW_ON_ERROR);
