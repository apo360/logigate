<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProtectedLogsRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_route_requires_authenticated_user(): void
    {
        $this->getJson('/api/log-alert')
            ->assertUnauthorized();
    }

    public function test_logs_route_requires_admin_role(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/log-alert')
            ->assertForbidden();
    }

    public function test_logs_route_allows_admins_with_viewlogs_gate(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::findOrCreate('Administrador');
        $user->assignRole($adminRole);

        File::ensureDirectoryExists(storage_path('logs'));
        File::put(storage_path('logs/laravel.log'), "security test log line\n");

        Sanctum::actingAs($user);

        $this->getJson('/api/log-alert')
            ->assertOk()
            ->assertJsonPath('logs.0', 'security test log line');
    }
}
