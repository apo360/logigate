<?php
namespace Tests\Feature\V1;
use Tests\TestCase;
use Tests\Feature\Processo\ProcessoTestFixtures;
use Tests\Feature\Licenciamento\LicenciamentoTestSupport;
use App\Support\TenantContext;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class ConcurrentOperationsTest extends TestCase
{
    use ProcessoTestFixtures, LicenciamentoTestSupport;
    private function scenario(): array
    {
        self::assertNotFalse(getenv('V1_TEST_DATABASE'), 'Run only in disposable sandbox.');
        [$user,$empresa] = $this->createTenant('CONC' . bin2hex(random_bytes(3)));
        $this->actingAs($user); TenantContext::setEmpresa($user,$empresa);
        foreach (['mercadorias.create','licenciamentos.update','processos.create'] as $permission) { Permission::findOrCreate($permission,'web'); }
        $user->syncPermissions(['mercadorias.create','licenciamentos.update','processos.create']);
        return [$user,$empresa];
    }
    private function race($user,$empresa,string $kind,?int $licenseId=null): array
    {
        $jobs=[]; $start=(string)(microtime(true)+1);
        for ($i=0;$i<2;$i++) {
            $pipes=[];
            $process=proc_open([PHP_BINARY,base_path('tests/v1-concurrency-worker.php'),getenv('V1_TEST_DATABASE'),(string)$user->id,(string)$empresa->id,$kind,$start,(string)$licenseId],[1=>['pipe','w'],2=>['pipe','w']],$pipes,base_path());
            self::assertIsResource($process); $jobs[]=[$process,$pipes];
        }
        $results=[];
        foreach ($jobs as [$process,$pipes]) {
            $out=stream_get_contents($pipes[1]); $err=stream_get_contents($pipes[2]); fclose($pipes[1]);fclose($pipes[2]);
            self::assertSame(0,proc_close($process),$err.' '.$out);
            $results[]=json_decode($out,true,flags:JSON_THROW_ON_ERROR);
        }
        return $results;
    }
    public function test_first_concurrent_emission_reserves_distinct_numbers(): void
    {
        [$user,$empresa]=$this->scenario();
        [$a,$b]=$this->race($user,$empresa,'sequence'); $values=array_merge($a,$b);sort($values);
        self::assertSame(range(1,12),$values);
    }
    public function test_concurrent_conversion_creates_one_permanent_process(): void
    {
        [$user,$empresa]=$this->scenario();
        $license=$this->createLicenciamentoFor($empresa,$user,'CONVERT'.bin2hex(random_bytes(3)));
        \App\Models\PautaAduaneira::firstOrCreate(['codigo'=>'0203.11.00'],['descricao'=>'Carnes','uq'=>'kg','rg'=>0,'sadc'=>0,'ua'=>0,'iva'=>0,'ieq'=>0,'requisitos'=>'0','observacao'=>'0']);
        $data=\App\Application\Mercadoria\DTOs\MercadoriaData::fromLivewire(['codigo_aduaneiro'=>'0203.11.00','descricao'=>'Teste','unidade'=>'Kg','quantidade'=>1,'peso'=>2,'preco_unitario'=>100],'licenciamento',$license->id);
        $item=app(\App\Application\Mercadoria\Actions\CriarMercadoriaAction::class)->execute($data);
        $license->update(['txt_gerado'=>true,'porto_origem'=>'LAD','codigo_banco'=>'0040']);
        [$a,$b]=$this->race($user,$empresa,'convert',$license->id);
        self::assertSame($a,$b);
        self::assertSame(1,DB::table('licenciamento_processos')->where('licenciamento_id',$license->id)->count());
        self::assertSame($a[0],(int)$item->refresh()->Fk_Importacao);
        DB::table('mercadorias')->where('id',$item->id)->delete();
        self::assertSame($a[0],app(\App\Application\Licenciamento\Actions\ConstituirProcessoAction::class)->execute($license)->id);
    }
}
