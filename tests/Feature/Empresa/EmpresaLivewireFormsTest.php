<?php

namespace Tests\Feature\Empresa;

use App\Livewire\Empresa\EmpresaAuditoria;
use App\Livewire\Empresa\EmpresaConfiguracoes;
use App\Livewire\Empresa\EmpresaContasBancarias;
use App\Livewire\Empresa\EmpresaIntegracoes;
use App\Livewire\Empresa\EmpresaLogo;
use App\Livewire\Empresa\EmpresaMigracoes;
use App\Livewire\Empresa\EmpresaProfile;
use App\Livewire\Empresa\EmpresaUserForm;
use App\Livewire\Empresa\EmpresaUserPermissions;
use App\Livewire\Empresa\EmpresaUsers;
use App\Livewire\Empresa\EmpresaUserSecurity;
use App\Models\Empresa;
use App\Models\EmpresaBanco;
use App\Models\EmpresaIntegracao;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EmpresaLivewireFormsTest extends TestCase
{
    use DatabaseTransactions;

    private Empresa $empresa;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $empresa = Empresa::query()->find(1);

        if (! $empresa) {
            $this->fail('A base de testes precisa conter empresas.id = 1. Nenhuma migration, refresh ou seed global foi executado.');
        }

        $this->empresa = $empresa;
        $this->actor = $this->adminForEmpresa($empresa);
        $this->actingAs($this->actor);
    }

    public function test_empresa_profile_loads_and_updates_empresa_one(): void
    {
        $nome = 'Empresa Teste Livewire ' . uniqid();
        $nif = 'NIF-LW-' . uniqid();

        Livewire::test(EmpresaProfile::class, ['empresa' => $this->empresa->fresh()])
            ->assertSet('Empresa', $this->empresa->Empresa)
            ->set('Empresa', $nome)
            ->set('NIF', $nif)
            ->set('Cedula', 'CED-' . uniqid())
            ->set('Designacao', 'Despachante Oficial')
            ->set('Email', 'empresa-' . uniqid() . '@example.test')
            ->set('Contacto_movel', '923000001')
            ->set('Contacto_fixo', '222000001')
            ->set('Slogan', 'Teste transaccional')
            ->set('Endereco_completo', 'Rua de Teste, Luanda')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('empresas', [
            'id' => 1,
            'Empresa' => $nome,
            'NIF' => $nif,
        ]);
    }

    public function test_empresa_logo_uploads_to_s3_using_ddd_path_and_removes_legacy_logo(): void
    {
        Storage::fake('s3');

        $oldPath = 'empresa/1/logotipos/old-logo.png';
        Storage::disk('s3')->put($oldPath, 'old-logo');

        $this->empresa->forceFill([
            'Logotipo' => 'https://s3.test/' . $oldPath,
        ])->save();

        Livewire::test(EmpresaLogo::class, ['empresa' => $this->empresa->fresh()])
            ->set('logotipo', UploadedFile::fake()->image('new-logo.png', 64, 64))
            ->call('save')
            ->assertHasNoErrors();

        Storage::disk('s3')->assertMissing($oldPath);

        $files = Storage::disk('s3')->allFiles('despachantes/1/empresa/logotipos');

        $this->assertCount(1, $files);
        $this->assertStringStartsWith('despachantes/1/empresa/logotipos/', $files[0]);
        $this->assertStringContainsString('despachantes/1/empresa/logotipos/', (string) $this->empresa->fresh()->Logotipo);
        $this->assertStringNotContainsString('/tmp/', (string) $this->empresa->fresh()->Logotipo);
    }

    public function test_empresa_contas_bancarias_creates_lists_and_deletes_account_for_empresa_one(): void
    {
        $iban = 'AO0600400000' . random_int(1000000000, 9999999999);
        $conta = 'CNT-' . uniqid();

        Livewire::test(EmpresaContasBancarias::class, ['empresa' => $this->empresa->fresh()])
            ->set('banco', '0040')
            ->set('iban', $iban)
            ->set('conta', $conta)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('banco', null)
            ->assertSet('iban', null)
            ->assertSet('conta', null);

        $account = EmpresaBanco::query()
            ->where('empresa_id', 1)
            ->where('iban', $iban)
            ->where('conta', $conta)
            ->first();

        $this->assertNotNull($account);

        Livewire::test(EmpresaContasBancarias::class, ['empresa' => $this->empresa->fresh()])
            ->call('delete', $account->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('empresa_banco', [
            'id' => $account->id,
            'empresa_id' => 1,
        ]);
    }

    public function test_empresa_user_form_creates_and_updates_user_for_empresa_one(): void
    {
        $role = $this->role('Operador Empresa Teste');
        $email = 'novo-utilizador-' . uniqid() . '@example.test';

        Livewire::test(EmpresaUserForm::class, ['empresa' => $this->empresa->fresh()])
            ->call('create')
            ->set('form.name', 'Novo Utilizador')
            ->set('form.email', $email)
            ->set('form.password', 'password123')
            ->set('form.password_confirmation', 'password123')
            ->set('form.role', $role->name)
            ->call('save')
            ->assertHasNoErrors();

        $created = User::query()->where('email', $email)->first();

        $this->assertNotNull($created);
        $this->assertTrue($created->empresas()->where('empresas.id', 1)->exists());
        $this->assertTrue($created->hasRole($role->name));

        $this->role('Gestor');
        $updatedEmail = 'utilizador-editado-' . uniqid() . '@example.test';

        Livewire::test(EmpresaUserForm::class, ['empresa' => $this->empresa->fresh()])
            ->call('edit', $created->id)
            ->assertSet('editing', true)
            ->set('form.name', 'Utilizador Editado')
            ->set('form.email', $updatedEmail)
            ->set('form.role', 'Gestor')
            ->call('save')
            ->assertHasNoErrors();

        $created->refresh();

        $this->assertSame('Utilizador Editado', $created->name);
        $this->assertSame($updatedEmail, $created->email);
        $this->assertTrue($created->hasRole('Gestor'));
    }

    public function test_empresa_users_and_security_manage_block_reset_and_remove_access(): void
    {
        $managed = $this->managedUserForEmpresa($this->empresa);

        Livewire::test(EmpresaUserSecurity::class, [
            'empresa' => $this->empresa->fresh(),
            'user' => $managed,
        ])
            ->call('block')
            ->assertHasNoErrors()
            ->call('unblock')
            ->assertHasNoErrors()
            ->call('resetPassword')
            ->assertHasNoErrors()
            ->assertSet('temporaryPassword', fn ($value) => is_string($value) && strlen($value) >= 8);

        $managed->refresh();

        $this->assertFalse((bool) $managed->is_blocked);
        $this->assertTrue((bool) $managed->is_active);
        $this->assertFalse((bool) $managed->password_changed);

        Livewire::test(EmpresaUsers::class, ['empresa' => $this->empresa->fresh()])
            ->call('remove', $managed->id)
            ->assertHasNoErrors();

        $this->assertFalse($managed->fresh()->empresas()->where('empresas.id', 1)->exists());
    }

    public function test_empresa_user_permissions_sync_roles_and_permissions(): void
    {
        $managed = $this->managedUserForEmpresa($this->empresa);
        $role = $this->role('Supervisor Empresa Teste');
        $permission = $this->permission('manage empresa livewire test');

        Livewire::test(EmpresaUserPermissions::class, [
            'empresa' => $this->empresa->fresh(),
            'user' => $managed,
        ])
            ->set('roles', [$role->name])
            ->set('permissions', [$permission->name])
            ->call('save')
            ->assertHasNoErrors();

        $managed->refresh();

        $this->assertTrue($managed->hasRole($role->name));
        $this->assertTrue($managed->hasPermissionTo($permission->name));
    }

    public function test_empresa_integracoes_configures_or_reports_missing_schema(): void
    {
        if (! Schema::hasTable('empresa_integracoes')) {
            Livewire::test(EmpresaIntegracoes::class)
                ->assertSet('schemaReady', false);

            return;
        }

        $token = 'secret-token-' . uniqid();

        Livewire::test(EmpresaIntegracoes::class)
            ->call('openConfigure', 'facturacao', 'hongayetu_facturacao')
            ->set('form.config.api_url', 'https://facturacao.internal')
            ->set('form.config.ambiente', 'teste')
            ->set('form.config.timeout', 5)
            ->set('form.config.retry_attempts', 0)
            ->set('form.config.retry_sleep', 0)
            ->set('form.config.nif', '5000000000')
            ->set('form.credentials.api_token', $token)
            ->call('save')
            ->assertHasNoErrors();

        $integration = EmpresaIntegracao::query()
            ->where('empresa_id', 1)
            ->where('tipo', 'facturacao')
            ->where('provedor', 'hongayetu_facturacao')
            ->firstOrFail();

        $this->assertArrayNotHasKey('credentials_encrypted', $integration->toArray());
        $this->assertStringNotContainsString($token, json_encode($integration->getAttributes(), JSON_THROW_ON_ERROR));

        Livewire::test(EmpresaIntegracoes::class)
            ->call('activate', $integration->id)
            ->assertHasNoErrors();

        $this->assertSame('activo', $integration->fresh()->estado->value);

        Http::fake([
            'https://facturacao.internal/api/integrations/health' => Http::response(['ok' => true], 200),
        ]);

        Livewire::test(EmpresaIntegracoes::class)
            ->call('test', $integration->id)
            ->assertHasNoErrors();

        $this->assertSame('sucesso', $integration->fresh()->ultimo_teste_status);

        Livewire::test(EmpresaIntegracoes::class)
            ->call('deactivate', $integration->id)
            ->assertHasNoErrors();

        $this->assertSame('inactivo', $integration->fresh()->estado->value);
    }

    public function test_non_admin_company_user_cannot_manage_integrations(): void
    {
        $plainUser = User::factory()->create([
            'email' => 'plain-' . uniqid() . '@example.test',
        ]);
        $this->empresa->users()->attach($plainUser->id, ['conta' => $this->empresa->conta]);
        $this->actingAs($plainUser);

        Livewire::test(EmpresaIntegracoes::class)
            ->assertForbidden();
    }

    public function test_empresa_placeholder_components_render(): void
    {
        Livewire::test(EmpresaConfiguracoes::class)->assertOk();
        Livewire::test(EmpresaAuditoria::class)->assertOk();
        Livewire::test(EmpresaMigracoes::class)->assertOk();
    }

    private function adminForEmpresa(Empresa $empresa): User
    {
        $user = User::factory()->create([
            'email' => 'admin-empresa-' . uniqid() . '@example.test',
            'is_active' => true,
            'is_blocked' => false,
        ]);

        $empresa->users()->attach($user->id, ['conta' => $empresa->conta]);
        $user->assignRole($this->role('Administrador'));

        return $user->refresh();
    }

    private function managedUserForEmpresa(Empresa $empresa): User
    {
        $user = User::factory()->create([
            'email' => 'managed-empresa-' . uniqid() . '@example.test',
            'is_active' => true,
            'is_blocked' => false,
            'password_changed' => true,
        ]);

        $empresa->users()->attach($user->id, ['conta' => $empresa->conta]);
        $user->assignRole($this->role('Operador Empresa Teste'));

        return $user->refresh();
    }

    private function role(string $name): Role
    {
        return Role::query()->firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ]);
    }

    private function permission(string $name): Permission
    {
        return Permission::query()->firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ]);
    }
}
