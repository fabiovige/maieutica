<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Professional;
use App\Models\Specialty;
use App\Models\User;
use App\Services\ProfessionalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfessionalActivationTest extends TestCase
{
    use RefreshDatabase;

    private ProfessionalService $professionalService;
    private Professional $professional;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->professionalService = $this->app->make(ProfessionalService::class);
        
        $this->createTestData();
    }

    private function createTestData(): void
    {
        $role = Role::create(['name' => 'professional']);
        $adminRole = Role::create(['name' => 'admin']);
        
        $specialty = Specialty::create([
            'name' => 'Psicologia',
            'description' => 'Especialidade de teste'
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $this->user = User::factory()->create([
            'name' => 'Dr. Teste',
            'email' => 'teste@exemplo.com',
            'allow' => true,
        ]);
        $this->user->assignRole('professional');

        $this->professional = Professional::create([
            'registration_number' => 'CRP123456',
            'bio' => 'Biografia de teste',
            'specialty_id' => $specialty->id,
            'created_by' => $admin->id,
        ]);

        $this->professional->user()->attach($this->user->id);
    }

    /** @test */
    public function pode_desativar_profissional_com_sucesso()
    {
        $this->assertTrue($this->user->allow, 'Usuario deve iniciar ativo');

        $resultado = $this->professionalService->deactivateProfessional($this->professional->id);

        $this->assertTrue($resultado, 'Desativacao deve retornar true');

        $this->user->refresh();
        $this->assertFalse($this->user->allow, 'Usuario deve estar desativado apos desativacao');
    }

    /** @test */
    public function pode_ativar_profissional_com_sucesso()
    {
        $this->user->update(['allow' => false]);
        $this->assertFalse($this->user->allow, 'Usuario deve iniciar desativado');

        $resultado = $this->professionalService->activateProfessional($this->professional->id);

        $this->assertTrue($resultado, 'Ativacao deve retornar true');

        $this->user->refresh();
        $this->assertTrue($this->user->allow, 'Usuario deve estar ativo apos ativacao');
    }

    /** @test */
    public function dados_permanecem_consistentes_apos_multiplas_mudancas()
    {
        $this->assertTrue($this->user->allow, 'Usuario deve iniciar ativo');

        $this->professionalService->deactivateProfessional($this->professional->id);
        $this->user->refresh();
        $this->assertFalse($this->user->allow, 'Usuario deve estar desativado');

        $this->professionalService->activateProfessional($this->professional->id);
        $this->user->refresh();
        $this->assertTrue($this->user->allow, 'Usuario deve estar ativo novamente');

        $this->professionalService->deactivateProfessional($this->professional->id);
        $this->user->refresh();
        $this->assertFalse($this->user->allow, 'Usuario deve estar desativado novamente');
    }

    /** @test */
    public function findProfessionalById_carrega_usuario_corretamente_quando_ativo()
    {
        $professional = $this->professionalService->findProfessionalById($this->professional->id);
        
        $this->assertNotNull($professional);
        $this->assertCount(1, $professional->user);
        
        $user = $professional->user->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->allow);
        $this->assertEquals($this->user->id, $user->id);
    }

    /** @test */
    public function findProfessionalById_carrega_usuario_corretamente_quando_desativado()
    {
        $this->professionalService->deactivateProfessional($this->professional->id);

        $professional = $this->professionalService->findProfessionalById($this->professional->id);
        
        $this->assertNotNull($professional);
        $this->assertCount(1, $professional->user);
        
        $user = $professional->user->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->allow, 'Usuario deve estar desativado');
        $this->assertEquals($this->user->id, $user->id);
    }

    /** @test */
    public function view_edit_renderiza_valores_corretos_para_usuario_ativo()
    {
        $professional = $this->professionalService->findProfessionalById($this->professional->id);
        $user = $professional->user->first();
        
        $allowValue = old('allow', $user->allow ?? false);
        
        $this->assertTrue($allowValue);
        $this->assertEquals('Ativo', $allowValue ? 'Ativo' : 'Inativo');
        $this->assertEquals('checked', $allowValue ? 'checked' : '');
    }

    /** @test */
    public function view_edit_renderiza_valores_corretos_para_usuario_desativado()
    {
        $this->professionalService->deactivateProfessional($this->professional->id);
        
        $professional = $this->professionalService->findProfessionalById($this->professional->id);
        $user = $professional->user->first();
        
        $allowValue = old('allow', $user->allow ?? false);
        
        $this->assertFalse($allowValue);
        $this->assertEquals('Inativo', $allowValue ? 'Ativo' : 'Inativo');
        $this->assertEquals('', $allowValue ? 'checked' : '');
    }

    /** @test */
    public function ciclo_completo_ativacao_desativacao_mantem_consistencia()
    {
        echo "\n=== TESTE DE CICLO COMPLETO ===\n";
        
        echo "1. Estado inicial:\n";
        $professional = $this->professionalService->findProfessionalById($this->professional->id);
        $user = $professional->user->first();
        echo "   - Allow: " . ($user->allow ? 'true' : 'false') . "\n";
        $this->assertTrue($user->allow);

        echo "2. Desativando profissional:\n";
        $resultado = $this->professionalService->deactivateProfessional($this->professional->id);
        $this->assertTrue($resultado);
        
        $professional = $this->professionalService->findProfessionalById($this->professional->id);
        $user = $professional->user->first();
        echo "   - Allow após desativação: " . ($user->allow ? 'true' : 'false') . "\n";
        $this->assertFalse($user->allow);

        echo "3. Simulando renderização da view de edição:\n";
        $allowValue = old('allow', $user->allow ?? false);
        echo "   - Valor para view: " . ($allowValue ? 'true' : 'false') . "\n";
        echo "   - Checkbox seria: " . ($allowValue ? 'checked' : 'unchecked') . "\n";
        echo "   - Texto seria: " . ($allowValue ? 'Ativo' : 'Inativo') . "\n";
        $this->assertFalse($allowValue);

        echo "4. Reativando profissional:\n";
        $resultado = $this->professionalService->activateProfessional($this->professional->id);
        $this->assertTrue($resultado);
        
        $professional = $this->professionalService->findProfessionalById($this->professional->id);
        $user = $professional->user->first();
        echo "   - Allow após reativação: " . ($user->allow ? 'true' : 'false') . "\n";
        $this->assertTrue($user->allow);

        echo "5. Simulando renderização da view de edição após reativação:\n";
        $allowValue = old('allow', $user->allow ?? false);
        echo "   - Valor para view: " . ($allowValue ? 'true' : 'false') . "\n";
        echo "   - Checkbox seria: " . ($allowValue ? 'checked' : 'unchecked') . "\n";
        echo "   - Texto seria: " . ($allowValue ? 'Ativo' : 'Inativo') . "\n";
        $this->assertTrue($allowValue);

        echo "=== TESTE FINALIZADO COM SUCESSO ===\n\n";
    }
}