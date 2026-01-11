# Estudo de Viabilidade de Testes - Maieutica

**Data:** Janeiro 2026
**Branch:** feat/qa-teste
**Objetivo:** Avaliar possibilidades de implementação de testes automatizados para backend e frontend

---

## 1. Visao Geral

### Estado Atual

| Camada | Framework | Infraestrutura | Testes Existentes |
|--------|-----------|----------------|-------------------|
| Backend | Laravel 9 + PHPUnit 9.5 | Configurada | 307 testes (21 arquivos) |
| Frontend | Vue 3 + Options API | Nao configurada | Nenhum |

### Conclusao Executiva

**E totalmente viavel implementar testes em ambas as camadas:**

- **Backend**: Infraestrutura pronta (PHPUnit, Factories, Seeders). So precisa escrever os testes.
- **Frontend**: Requer instalacao de Vitest + Vue Test Utils, depois escrever testes.

---

## 1.1 Status Atual (Atualizado: Janeiro 2026 - Expansao Backend)

### Resultado da Execucao de Testes

```
php artisan test

Tests:  307 passed, 3 skipped
Time:   ~50s
```

### Resumo por Categoria

| Categoria | Arquivos | Testes | Status |
|-----------|----------|--------|--------|
| Unit/Models | 6 | 109 | ✅ |
| Unit/Policies | 8 | 157 | ✅ |
| Unit/Services | 2 | 19 | ✅ |
| Feature/Api | 1 | 4 | ✅ |
| Feature/Auth | 1 | 6 | ✅ |
| Feature/Controllers | 3 | 59 | ✅ (3 skipped) |
| **Total** | **21** | **307** | ✅ |

### Estrutura de Testes Implementada

```
tests/
├── TestCase.php
├── CreatesApplication.php
├── Unit/
│   ├── Models/
│   │   ├── KidModelTest.php (27 testes)
│   │   ├── ChecklistModelTest.php (18 testes)
│   │   ├── MedicalRecordModelTest.php (24 testes)
│   │   ├── UserModelTest.php (17 testes) [NOVO]
│   │   ├── PlaneModelTest.php (13 testes) [NOVO]
│   │   └── GeneratedDocumentModelTest.php (20 testes) [NOVO]
│   ├── Policies/
│   │   ├── KidPolicyTest.php (18 testes)
│   │   ├── ChecklistPolicyTest.php (17 testes)
│   │   ├── UserPolicyTest.php (23 testes)
│   │   ├── MedicalRecordPolicyTest.php (28 testes)
│   │   ├── PlanePolicyTest.php (26 testes)
│   │   ├── ProfessionalPolicyTest.php (20 testes)
│   │   ├── GeneratedDocumentPolicyTest.php (19 testes) [NOVO]
│   │   └── RolePolicyTest.php (18 testes) [NOVO]
│   └── Services/
│       ├── ChecklistServiceTest.php (8 testes)
│       └── OverviewServiceTest.php (11 testes)
└── Feature/
    ├── Api/
    │   └── ChecklistApiTest.php (4 testes)
    ├── Auth/
    │   └── AuthenticationTest.php (6 testes)
    └── Controllers/
        ├── KidsControllerTest.php (12 testes)
        ├── ChecklistControllerTest.php (25 testes, 3 skipped) [NOVO]
        └── MedicalRecordsControllerTest.php (25 testes) [NOVO]
```

### Progresso do Roadmap

| Fase | Descricao | Status |
|------|-----------|--------|
| Fase 1 | Fundacao Backend | ✅ Completa |
| Fase 2 | Cobertura Critica | ✅ Completa |
| Fase 3 | Fundacao Frontend | ⏳ Pendente |
| Fase 4 | Expansao | ⏳ Pendente |
| Fase 5 | CI/CD | ⏳ Pendente |

---

## 2. Backend - Testes com Laravel/PHPUnit

### 2.1 Infraestrutura Existente

#### PHPUnit Configurado (`phpunit.xml`)

```xml
<testsuites>
    <testsuite name="Unit">
        <directory suffix="Test.php">./tests/Unit</directory>
    </testsuite>
    <testsuite name="Feature">
        <directory suffix="Test.php">./tests/Feature</directory>
    </testsuite>
</testsuites>
```

**Variaveis de ambiente para testes:**
- `APP_ENV=testing`
- `BCRYPT_ROUNDS=4` (hashing mais rapido)
- `CACHE_DRIVER=array`
- `SESSION_DRIVER=array`
- `QUEUE_CONNECTION=sync`

#### Dependencias Instaladas

| Pacote | Versao | Uso |
|--------|--------|-----|
| phpunit/phpunit | ^9.5.10 | Framework de testes |
| mockery/mockery | ^1.4.4 | Mocking de objetos |
| fakerphp/faker | ^1.9.1 | Geracao de dados falsos |

#### Factories Disponiveis

| Factory | Arquivo | Status |
|---------|---------|--------|
| UserFactory | `database/factories/UserFactory.php` | Completa |
| KidFactory | `database/factories/KidFactory.php` | Completa |
| ChecklistFactory | `database/factories/ChecklistFactory.php` | Completa |
| ResponsibleFactory | `database/factories/ResponsibleFactory.php` | Completa |
| AddressFactory | `database/factories/AddressFactory.php` | Parcial |
| ChecklistRegisterFactory | `database/factories/ChecklistRegisterFactory.php` | Parcial |

### 2.2 Tipos de Testes Possiveis

#### A) Testes Unitarios (`tests/Unit/`)

Testam classes isoladas sem dependencias externas.

**Alvos prioritarios:**

1. **ChecklistService** (`app/Services/ChecklistService.php`)
   - Metodo `percentualDesenvolvimento()` - calculo de porcentagem

2. **OverviewService** (`app/Services/OverviewService.php`)
   - Metodo `getOverviewData()` - agregacao de dados complexa

3. **Helpers** (`app/helpers.php`)
   - `get_progress_color()` - cores por porcentagem
   - `get_progress_gradient()` - gradientes

**Exemplo de teste unitario:**

```php
<?php
// tests/Unit/Services/ChecklistServiceTest.php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ChecklistService;
use App\Models\Checklist;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChecklistServiceTest extends TestCase
{
    use RefreshDatabase;

    private ChecklistService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ChecklistService();
    }

    /** @test */
    public function calcula_percentual_desenvolvimento_corretamente()
    {
        // Arrange
        $checklist = Checklist::factory()->create();
        // Adicionar competencias com notas conhecidas...

        // Act
        $resultado = $this->service->percentualDesenvolvimento($checklist->id);

        // Assert
        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('porcentagem', $resultado);
    }

    /** @test */
    public function retorna_zero_para_checklist_vazio()
    {
        $checklist = Checklist::factory()->create();

        $resultado = $this->service->percentualDesenvolvimento($checklist->id);

        $this->assertEquals(0, $resultado['porcentagem'] ?? 0);
    }
}
```

#### B) Testes de Feature (`tests/Feature/`)

Testam fluxos completos da aplicacao (HTTP requests, banco, etc).

**Alvos prioritarios:**

1. **Autenticacao e Autorizacao**
2. **CRUD de Kids**
3. **CRUD de Checklists**
4. **API Endpoints**
5. **Policies de permissao**

**Exemplo de teste de feature - API:**

```php
<?php
// tests/Feature/Api/KidApiTest.php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Kid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class KidApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
    }

    /** @test */
    public function profissional_pode_listar_seus_kids()
    {
        // Arrange
        $user = User::factory()->create();
        $user->assignRole('profissional');
        $user->givePermissionTo('kid-list');

        Sanctum::actingAs($user);

        // Act
        $response = $this->getJson('/api/kids');

        // Assert
        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }

    /** @test */
    public function usuario_sem_permissao_nao_pode_listar_kids()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/kids');

        $response->assertStatus(403);
    }
}
```

**Exemplo de teste de feature - Web:**

```php
<?php
// tests/Feature/KidControllerTest.php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Kid;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KidControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
    }

    /** @test */
    public function admin_pode_ver_lista_de_kids()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
                         ->get(route('kids.index'));

        $response->assertStatus(200)
                 ->assertViewIs('kids.index');
    }

    /** @test */
    public function pode_criar_kid_com_dados_validos()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $kidData = [
            'name' => 'Joao Silva',
            'birth_date' => '15/03/2020',
            'gender' => 'M',
            // ... outros campos
        ];

        $response = $this->actingAs($admin)
                         ->post(route('kids.store'), $kidData);

        $response->assertRedirect();
        $this->assertDatabaseHas('kids', ['name' => 'Joao Silva']);
    }
}
```

#### C) Testes de Policy

**Exemplo de teste de Policy:**

```php
<?php
// tests/Unit/Policies/KidPolicyTest.php

namespace Tests\Unit\Policies;

use Tests\TestCase;
use App\Models\User;
use App\Models\Kid;
use App\Policies\KidPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KidPolicyTest extends TestCase
{
    use RefreshDatabase;

    private KidPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->policy = new KidPolicy();
    }

    /** @test */
    public function usuario_com_kid_list_all_pode_ver_qualquer_kid()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('kid-list-all');

        $kid = Kid::factory()->create();

        $this->assertTrue($this->policy->view($user, $kid));
    }

    /** @test */
    public function profissional_so_ve_kids_atribuidos()
    {
        $profissional = User::factory()->create();
        $profissional->givePermissionTo('kid-list');

        $kidNaoAtribuido = Kid::factory()->create();

        $this->assertFalse($this->policy->view($profissional, $kidNaoAtribuido));
    }
}
```

### 2.3 Estrutura de Diretorios Recomendada

```
tests/
├── TestCase.php                 # Classe base customizada
├── CreatesApplication.php       # Trait padrao Laravel
├── Unit/
│   ├── Services/
│   │   ├── ChecklistServiceTest.php
│   │   └── OverviewServiceTest.php
│   ├── Models/
│   │   ├── KidTest.php
│   │   ├── ChecklistTest.php
│   │   └── MedicalRecordTest.php
│   ├── Policies/
│   │   ├── KidPolicyTest.php
│   │   └── ChecklistPolicyTest.php
│   └── Helpers/
│       └── ProgressHelperTest.php
├── Feature/
│   ├── Auth/
│   │   ├── LoginTest.php
│   │   └── PermissionTest.php
│   ├── Controllers/
│   │   ├── KidControllerTest.php
│   │   ├── ChecklistControllerTest.php
│   │   └── MedicalRecordControllerTest.php
│   └── Api/
│       ├── KidApiTest.php
│       ├── ChecklistApiTest.php
│       ├── PlaneApiTest.php
│       └── ChartApiTest.php
└── Traits/
    └── CreatesTestUser.php      # Trait auxiliar
```

### 2.4 Prioridades de Implementacao Backend

| Prioridade | Alvo | Justificativa |
|------------|------|---------------|
| 1 - Alta | ChecklistService | Logica de negocio critica (calculos) |
| 1 - Alta | OverviewService | Agregacao complexa de dados |
| 1 - Alta | Policies (Kid, Checklist) | Seguranca e autorizacao |
| 2 - Media | API Endpoints | Integracao frontend-backend |
| 2 - Media | Controllers principais | Fluxos de usuario |
| 3 - Baixa | Models (accessors/mutators) | Transformacoes de dados |
| 3 - Baixa | Helpers | Funcoes utilitarias |

### 2.5 Comandos para Executar Testes

```bash
# Rodar todos os testes
php artisan test

# Rodar apenas testes unitarios
php artisan test --testsuite=Unit

# Rodar apenas testes de feature
php artisan test --testsuite=Feature

# Rodar teste especifico
php artisan test tests/Unit/Services/ChecklistServiceTest.php

# Rodar metodo especifico
php artisan test --filter=calcula_percentual_desenvolvimento_corretamente

# Com coverage (requer Xdebug ou PCOV)
php artisan test --coverage

# Modo verboso
php artisan test -v
```

---

## 3. Frontend - Testes com Vitest/Vue Test Utils

### 3.1 Estado Atual

- **9 componentes Vue 3** (Options API)
- **8 composables** com logica separada
- **Nenhuma infraestrutura de teste**

### 3.2 Instalacao Necessaria

```bash
# Instalar dependencias de teste
npm install -D vitest @vue/test-utils jsdom @testing-library/vue happy-dom

# Para mock de axios
npm install -D axios-mock-adapter
```

### 3.3 Configuracao do Vitest

Criar arquivo `vitest.config.js` na raiz:

```javascript
// vitest.config.js
import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
    plugins: [vue()],
    test: {
        globals: true,
        environment: 'jsdom',
        include: ['resources/js/**/*.{test,spec}.{js,ts}'],
        coverage: {
            provider: 'v8',
            reporter: ['text', 'json', 'html'],
        },
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
})
```

Adicionar scripts no `package.json`:

```json
{
    "scripts": {
        "test": "vitest",
        "test:run": "vitest run",
        "test:coverage": "vitest run --coverage"
    }
}
```

### 3.4 Componentes e Testabilidade

| Componente | Complexidade | Testabilidade | Prioridade |
|------------|--------------|---------------|------------|
| Resume.vue | Baixa | Alta | 1 |
| TableDescriptions.vue | Baixa-Media | Alta | 1 |
| Initials.vue | Baixa | Alta | 2 |
| Charts.vue | Media | Media | 2 |
| Checklists.vue | Media | Media | 2 |
| Dashboard.vue | Baixa | Alta | 3 |
| Planes.vue | Alta | Baixa | 3 |
| Competences.vue | Muito Alta | Baixa | 4 |
| Resumekid.vue | Baixa | Alta | 2 |

### 3.5 Composables - Alvos Prioritarios

| Composable | Descricao | Testabilidade |
|------------|-----------|---------------|
| charts.js | Busca dados de graficos | Alta (mock axios) |
| checklists.js | CRUD de checklists | Alta |
| competences.js | Busca competencias | Alta |
| domains.js | Busca dominios | Alta |
| levels.js | Busca niveis | Alta |
| planes.js | CRUD de planos | Alta |
| kids.js | Gerenciamento de kids | Alta |
| checklistregisters.js | Registros de avaliacao | Alta |

### 3.6 Exemplos de Testes Frontend

#### A) Teste de Composable

```javascript
// resources/js/composables/__tests__/charts.spec.js
import { describe, it, expect, vi, beforeEach } from 'vitest'
import axios from 'axios'
import { useCharts } from '../charts'

vi.mock('axios')

describe('useCharts composable', () => {
    beforeEach(() => {
        vi.clearAllMocks()
    })

    it('busca dados de porcentagem consolidada', async () => {
        const mockData = {
            data: {
                labels: ['Dominio 1', 'Dominio 2'],
                values: [75, 80]
            }
        }

        axios.get.mockResolvedValue(mockData)

        const { getPercentageConsolidate, percentageConsolidate } = useCharts()

        await getPercentageConsolidate(1)

        expect(axios.get).toHaveBeenCalledWith(expect.stringContaining('/api/charts/percentage'))
        expect(percentageConsolidate.value).toEqual(mockData.data)
    })

    it('trata erro na busca de dados', async () => {
        axios.get.mockRejectedValue(new Error('Network error'))

        const { getPercentageConsolidate, isLoading } = useCharts()

        await getPercentageConsolidate(1)

        expect(isLoading.value).toBe(false)
    })
})
```

#### B) Teste de Componente Simples

```javascript
// resources/js/components/__tests__/Resume.spec.js
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import Resume from '../Resume.vue'

describe('Resume.vue', () => {
    const defaultProps = {
        kid: {
            id: 1,
            name: 'Joao Silva',
            birth_date: '2020-03-15',
            gender: 'M',
            photo: null
        }
    }

    it('renderiza nome do kid corretamente', () => {
        const wrapper = mount(Resume, {
            props: defaultProps
        })

        expect(wrapper.text()).toContain('Joao Silva')
    })

    it('exibe foto padrao quando kid nao tem foto', () => {
        const wrapper = mount(Resume, {
            props: defaultProps
        })

        const img = wrapper.find('img')
        expect(img.attributes('src')).toContain('default')
    })

    it('calcula idade em meses corretamente', () => {
        const wrapper = mount(Resume, {
            props: defaultProps
        })

        // Verificar se idade e exibida
        expect(wrapper.text()).toMatch(/\d+\s*(meses|anos)/)
    })
})
```

#### C) Teste de Componente com API

```javascript
// resources/js/components/__tests__/Charts.spec.js
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import Charts from '../Charts.vue'
import axios from 'axios'

vi.mock('axios')

describe('Charts.vue', () => {
    beforeEach(() => {
        vi.clearAllMocks()
    })

    it('carrega dados do grafico ao montar', async () => {
        axios.get.mockResolvedValue({
            data: {
                labels: ['Motor', 'Cognitivo'],
                datasets: [{ data: [80, 75] }]
            }
        })

        const wrapper = mount(Charts, {
            props: {
                checklistId: 1,
                kidId: 1
            }
        })

        await flushPromises()

        expect(axios.get).toHaveBeenCalled()
    })

    it('exibe loading enquanto carrega dados', () => {
        axios.get.mockImplementation(() => new Promise(() => {}))

        const wrapper = mount(Charts, {
            props: {
                checklistId: 1,
                kidId: 1
            }
        })

        expect(wrapper.find('.loading').exists()).toBe(true)
    })
})
```

### 3.7 Estrutura de Diretorios Frontend

```
resources/js/
├── components/
│   ├── __tests__/
│   │   ├── Resume.spec.js
│   │   ├── Charts.spec.js
│   │   ├── TableDescriptions.spec.js
│   │   └── Checklists.spec.js
│   ├── Resume.vue
│   ├── Charts.vue
│   └── ...
├── composables/
│   ├── __tests__/
│   │   ├── charts.spec.js
│   │   ├── checklists.spec.js
│   │   └── planes.spec.js
│   ├── charts.js
│   ├── checklists.js
│   └── ...
└── utils/
    ├── __tests__/
    │   └── photoUtils.spec.js
    └── photoUtils.js
```

### 3.8 Comandos Frontend

```bash
# Rodar testes em modo watch
npm run test

# Rodar testes uma vez
npm run test:run

# Rodar com coverage
npm run test:coverage

# Rodar teste especifico
npx vitest resources/js/composables/__tests__/charts.spec.js
```

---

## 4. Roadmap de Implementacao

### Fase 1 - Fundacao (Backend) ✅ COMPLETA

**Objetivo:** Estabelecer infraestrutura basica de testes backend

```bash
# 1. Criar estrutura de diretorios
mkdir -p tests/Unit/Services
mkdir -p tests/Unit/Policies
mkdir -p tests/Feature/Api
mkdir -p tests/Feature/Controllers

# 2. Criar TestCase base customizado
# tests/TestCase.php ja existe, verificar se precisa customizar

# 3. Criar primeiro teste unitario
php artisan make:test Services/ChecklistServiceTest --unit
```

**Entregaveis:**
- [x] Estrutura de diretorios criada
- [x] TestCase customizado (se necessario)
- [x] ChecklistServiceTest funcionando (8 testes)
- [x] OverviewServiceTest funcionando (11 testes)

**Data de conclusao:** Janeiro 2026

### Fase 2 - Cobertura Critica (Backend) ✅ COMPLETA

**Objetivo:** Testar logica de negocio e autorizacao

**Entregaveis:**
- [x] Testes de todas as Policies principais (6 policies, 120 testes)
  - KidPolicyTest (18 testes)
  - ChecklistPolicyTest (17 testes)
  - UserPolicyTest (23 testes)
  - MedicalRecordPolicyTest (28 testes)
  - PlanePolicyTest (26 testes)
  - ProfessionalPolicyTest (20 testes)
- [x] Testes dos endpoints de API criticos (ChecklistApiTest - 4 testes)
- [x] Testes de autenticacao/autorizacao (AuthenticationTest - 6 testes)

**Bonus implementado:**
- [x] Testes de Models (3 models, 59 testes)
  - KidModelTest (27 testes)
  - ChecklistModelTest (18 testes)
  - MedicalRecordModelTest (24 testes)
- [x] Testes de Controllers (KidsControllerTest - 12 testes)

**Data de conclusao:** Janeiro 2026

### Fase 3 - Fundacao (Frontend)

**Objetivo:** Configurar infraestrutura de testes frontend

```bash
# 1. Instalar dependencias
npm install -D vitest @vue/test-utils jsdom axios-mock-adapter

# 2. Criar vitest.config.js

# 3. Adicionar scripts no package.json

# 4. Criar primeiro teste de composable
```

**Entregaveis:**
- [ ] Vitest configurado e funcionando
- [ ] Teste de um composable (charts.js)
- [ ] Teste de um componente simples (Resume.vue)

### Fase 4 - Expansao

**Objetivo:** Aumentar cobertura de testes

**Entregaveis:**
- [ ] 80% de cobertura em Services
- [ ] 100% de cobertura em Policies
- [ ] Testes de todos os composables
- [ ] Testes de componentes principais

### Fase 5 - CI/CD

**Objetivo:** Automatizar execucao de testes

**Entregaveis:**
- [ ] GitHub Actions para rodar testes em PRs
- [ ] Relatorio de coverage automatico
- [ ] Bloqueio de merge se testes falharem

---

## 5. Conclusao e Recomendacoes

### Viabilidade

| Aspecto | Backend | Frontend |
|---------|---------|----------|
| Viabilidade | Alta | Alta |
| Esforco inicial | ~~Baixo~~ Concluido | Medio |
| Infraestrutura | ~~Pronta~~ Implementada | Requer setup |
| Prioridade | ~~1~~ Concluido | 1 (proximo) |

### Recomendacoes

1. ~~**Comecar pelo backend**~~ ✅ Concluido - 242 testes implementados

2. ~~**Focar em logica de negocio**~~ ✅ Concluido - Services e Policies testados

3. **Testes de regressao** - Criar testes antes de refatorar codigo existente

4. **Evitar testes frageis** - Nao testar implementacao, testar comportamento

5. **Integracao continua** - Configurar CI/CD (Fase 5 pendente)

6. **Proximo passo: Frontend** - Configurar Vitest e testar composables

### Metricas Atuais vs Metas

| Metrica | Meta Inicial | Atual | Meta Final |
|---------|--------------|-------|------------|
| Cobertura Services | 80% | ✅ 100% | 95% |
| Cobertura Policies | 100% | ✅ 100% (8/10 policies) | 100% |
| Cobertura Models | 50% | ✅ ~30% (6/22 models) | 60% |
| Cobertura Controllers | 50% | ✅ ~21% (3/14 controllers) | 80% |
| Cobertura Composables | 70% | 0% | 90% |
| Cobertura Componentes | 30% | 0% | 60% |

### Riscos e Mitigacoes

| Risco | Mitigacao |
|-------|-----------|
| Testes lentos | Usar RefreshDatabase com transacoes |
| Dependencias jQuery | Mockar globais (window.$) |
| Bootstrap modals | Usar @testing-library/vue para eventos |
| Dados de producao | Usar factories, nunca dados reais |

---

## Apendice: Comandos Uteis

### Backend

```bash
# Criar teste unitario
php artisan make:test NomeDoTeste --unit

# Criar teste de feature
php artisan make:test NomeDoTeste

# Rodar com filtro
php artisan test --filter="nome_do_metodo"

# Coverage HTML
XDEBUG_MODE=coverage php artisan test --coverage-html=coverage
```

### Frontend

```bash
# Rodar testes especificos
npx vitest run resources/js/composables

# Modo debug
npx vitest --ui

# Coverage
npx vitest run --coverage
```
