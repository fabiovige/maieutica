<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Professional;
use App\Models\Specialty;
use App\Models\User;
use App\Services\ProfessionalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfessionalWebInterfaceTest extends TestCase
{
    use RefreshDatabase;

    private ProfessionalService $professionalService;
    private Professional $professional;
    private User $adminUser;
    private User $professionalUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->professionalService = $this->app->make(ProfessionalService::class);
        
        $this->createTestData();
    }

    private function createTestData(): void
    {
        $professionalRole = Role::create(['name' => 'professional']);
        $adminRole = Role::create(['name' => 'admin']);
        
        $specialty = Specialty::create([
            'name' => 'Psicologia',
            'description' => 'Especialidade de teste'
        ]);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $this->professionalUser = User::factory()->create([
            'name' => 'Dr. Interface Test',
            'email' => 'interface@teste.com',
            'allow' => true,
        ]);
        $this->professionalUser->assignRole('professional');

        $this->professional = Professional::create([
            'registration_number' => 'CRP999999',
            'bio' => 'Biografia para teste de interface',
            'specialty_id' => $specialty->id,
            'created_by' => $this->adminUser->id,
        ]);

        $this->professional->user()->attach($this->professionalUser->id);
    }

    /** @test */
    public function edit_page_renders_correct_data_for_active_professional()
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(route('professionals.edit', $this->professional->id));

        $response->assertStatus(200);
        
        $response->assertSee('Dr. Interface Test');
        $response->assertSee('interface@teste.com');
        $response->assertSee('CRP999999');
        
        $response->assertSee('checked', false);
        $response->assertSee('Ativo');
    }

    /** @test */
    public function edit_page_renders_correct_data_for_inactive_professional()
    {
        $this->actingAs($this->adminUser);
        
        $this->professionalService->deactivateProfessional($this->professional->id);

        $response = $this->get(route('professionals.edit', $this->professional->id));

        $response->assertStatus(200);
        
        $response->assertSee('Dr. Interface Test');
        $response->assertSee('interface@teste.com');
        $response->assertSee('CRP999999');
        
        $response->assertDontSee('checked', false);
        $response->assertSee('Inativo');
    }

    /** @test */
    public function deactivate_via_web_interface_works_correctly()
    {
        $this->actingAs($this->adminUser);
        
        echo "\n=== TESTE DE INTERFACE WEB - DESATIVAÇÃO ===\n";
        
        echo "1. Estado inicial - Acessando página de edição:\n";
        $response = $this->get(route('professionals.edit', $this->professional->id));
        $response->assertStatus(200);
        $response->assertSee('checked', false);
        $response->assertSee('Ativo');
        echo "   ✓ Página carregada com profissional ATIVO\n";

        echo "2. Desativando via interface web:\n";
        $response = $this->patch(route('professionals.deactivate', $this->professional->id));
        $response->assertRedirect(route('professionals.index'));
        echo "   ✓ Desativação executada via web\n";

        echo "3. Verificando dados no backend:\n";
        $professional = $this->professionalService->findProfessionalById($this->professional->id);
        $user = $professional->user->first();
        echo "   - Backend allow: " . ($user->allow ? 'true' : 'false') . "\n";
        $this->assertFalse($user->allow);

        echo "4. Acessando página de edição após desativação:\n";
        $response = $this->get(route('professionals.edit', $this->professional->id));
        $response->assertStatus(200);
        
        $responseContent = $response->getContent();
        
        $hasChecked = str_contains($responseContent, 'checked');
        $hasInativo = str_contains($responseContent, 'Inativo');
        
        echo "   - HTML contém 'checked': " . ($hasChecked ? 'SIM' : 'NÃO') . "\n";
        echo "   - HTML contém 'Inativo': " . ($hasInativo ? 'SIM' : 'NÃO') . "\n";
        
        $this->assertFalse($hasChecked, 'HTML não deveria conter checked');
        $this->assertTrue($hasInativo, 'HTML deveria conter Inativo');

        echo "=== TESTE FINALIZADO ===\n\n";
    }

    /** @test */
    public function activate_after_deactivate_via_web_interface_works_correctly()
    {
        $this->actingAs($this->adminUser);
        
        echo "\n=== TESTE COMPLETO DE ATIVAÇÃO/DESATIVAÇÃO WEB ===\n";
        
        echo "1. Desativando profissional:\n";
        $this->patch(route('professionals.deactivate', $this->professional->id));
        
        $professional = $this->professionalService->findProfessionalById($this->professional->id);
        $user = $professional->user->first();
        echo "   - Status após desativar: " . ($user->allow ? 'ATIVO' : 'INATIVO') . "\n";
        $this->assertFalse($user->allow);

        echo "2. Verificando página de edição após desativação:\n";
        $response = $this->get(route('professionals.edit', $this->professional->id));
        $responseContent = $response->getContent();
        
        preg_match('/name="allow"[^>]*checked/', $responseContent, $matches);
        $hasCheckedAttribute = !empty($matches);
        
        echo "   - Input tem atributo 'checked': " . ($hasCheckedAttribute ? 'SIM' : 'NÃO') . "\n";
        $this->assertFalse($hasCheckedAttribute);

        echo "3. Reativando profissional:\n";
        $this->patch(route('professionals.activate', $this->professional->id));
        
        $professional = $this->professionalService->findProfessionalById($this->professional->id);
        $user = $professional->user->first();
        echo "   - Status após reativar: " . ($user->allow ? 'ATIVO' : 'INATIVO') . "\n";
        $this->assertTrue($user->allow);

        echo "4. Verificando página de edição após reativação:\n";
        $response = $this->get(route('professionals.edit', $this->professional->id));
        $responseContent = $response->getContent();
        
        preg_match('/name="allow"[^>]*checked/', $responseContent, $matches);
        $hasCheckedAttributeAfter = !empty($matches);
        
        echo "   - Input tem atributo 'checked': " . ($hasCheckedAttributeAfter ? 'SIM' : 'NÃO') . "\n";
        $this->assertTrue($hasCheckedAttributeAfter);

        echo "=== TESTE FINALIZADO COM SUCESSO ===\n\n";
    }
}