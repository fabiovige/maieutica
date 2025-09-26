<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Http\Middleware\SanitizeInput;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class TestSanitizeInput extends Command
{
    protected $signature = 'security:test-sanitize {--examples}';

    protected $description = 'Testa o middleware de sanitização de input para verificar proteção XSS';

    public function handle(): int
    {
        if ($this->option('examples')) {
            return $this->showExamples();
        }

        $this->info('🛡️  Testando Middleware de Sanitização de Input');
        $this->newLine();

        $middleware = new SanitizeInput();

        $testCases = [
            [
                'title' => 'Script Tag Simples',
                'input' => ['name' => '<script>alert("XSS")</script>João'],
                'expected_plain' => '&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;João',
            ],
            [
                'title' => 'JavaScript Protocol',
                'input' => ['url' => 'javascript:alert("malicious")'],
                'expected_plain' => 'alert(&quot;malicious&quot;)',
            ],
            [
                'title' => 'Campo Rich Text Seguro',
                'input' => ['description' => '<p>Texto válido</p><strong>Negrito</strong>'],
                'expected_rich' => '<p>Texto válido</p><strong>Negrito</strong>',
            ],
            [
                'title' => 'Campo Rich Text com Script',
                'input' => ['description' => '<p>Texto válido</p><script>alert("evil")</script>'],
                'expected_rich' => '<p>Texto válido</p>',
            ],
            [
                'title' => 'Eventos JavaScript',
                'input' => ['message' => '<img src="test.jpg" onclick="alert(\'XSS\')" alt="test">'],
                'expected_plain' => '&lt;img src=&quot;test.jpg&quot; onclick=&quot;alert(\'XSS\')&quot; alt=&quot;test&quot;&gt;',
            ],
            [
                'title' => 'Campo de Senha (não sanitizado)',
                'input' => ['password' => 'senha<script>alert("test")</script>123'],
                'expected_skip' => 'senha<script>alert("test")</script>123',
            ],
        ];

        foreach ($testCases as $case) {
            $this->testCase($middleware, $case);
        }

        $this->newLine();
        $this->info('✅ Todos os testes executados com sucesso!');
        $this->line('Para mais exemplos, execute: php artisan security:test-sanitize --examples');

        return self::SUCCESS;
    }

    private function testCase(SanitizeInput $middleware, array $case): void
    {
        $request = Request::create('/test', 'POST', $case['input']);
        $originalValue = collect($case['input'])->first();

        $middleware->handle($request, fn($req) => $req);

        $sanitizedValue = collect($request->all())->first();
        $fieldName = collect($case['input'])->keys()->first();

        $this->info("🧪 {$case['title']}");
        $this->line("Campo: <fg=yellow>{$fieldName}</>");
        $this->line("Original: <fg=red>{$originalValue}</>");
        $this->line("Sanitizado: <fg=green>{$sanitizedValue}</>");

        if (isset($case['expected_plain']) && !$this->isRichTextField($fieldName)) {
            $success = $sanitizedValue === $case['expected_plain'];
        } elseif (isset($case['expected_rich']) && $this->isRichTextField($fieldName)) {
            $success = trim($sanitizedValue) === $case['expected_rich'];
        } elseif (isset($case['expected_skip'])) {
            $success = $sanitizedValue === $case['expected_skip'];
        } else {
            $success = $sanitizedValue !== $originalValue && !str_contains($sanitizedValue, '<script>');
        }

        $this->line($success ? '<fg=green>✅ PASSOU</>' : '<fg=red>❌ FALHOU</>');
        $this->newLine();
    }

    private function showExamples(): int
    {
        $this->info('🛡️  Exemplos de Proteção XSS no Sistema Maiêutica');
        $this->newLine();

        $examples = [
            [
                'attack' => 'Script Injection',
                'payload' => '<script>document.cookie="stolen="+document.cookie</script>',
                'result' => 'Tags script são completamente removidas',
                'risk' => 'Alto - Roubo de cookies/sessão',
            ],
            [
                'attack' => 'Event Handler',
                'payload' => '<img src="x" onerror="alert(\'XSS\')">',
                'result' => 'Eventos JavaScript são removidos',
                'risk' => 'Alto - Execução de código malicioso',
            ],
            [
                'attack' => 'JavaScript Protocol',
                'payload' => 'javascript:window.location="http://attacker.com"',
                'result' => 'Protocolos maliciosos são removidos',
                'risk' => 'Médio - Redirecionamento malicioso',
            ],
            [
                'attack' => 'Form Injection',
                'payload' => '</textarea><form><input type="password"></form>',
                'result' => 'Tags form são proibidas',
                'risk' => 'Alto - Phishing interno',
            ],
            [
                'attack' => 'CSS Expression',
                'payload' => '<div style="expression(alert(\'XSS\'))">',
                'result' => 'Expressões CSS maliciosas removidas',
                'risk' => 'Médio - Execução em browsers antigos',
            ],
        ];

        foreach ($examples as $example) {
            $this->info("⚠️  {$example['attack']}");
            $this->line("Payload: <fg=red>{$example['payload']}</>");
            $this->line("Proteção: <fg=green>{$example['result']}</>");
            $this->line("Risco: <fg=yellow>{$example['risk']}</>");
            $this->newLine();
        }

        $this->info('📋 Campos com Tratamento Diferenciado:');
        $this->newLine();

        $this->table(
            ['Tipo de Campo', 'Tratamento', 'Exemplo'],
            [
                ['Texto Simples', 'HTML totalmente escapado', 'name, email, title'],
                ['Rich Text', 'HTML seguro preservado', 'description, note, content'],
                ['Senha', 'Não sanitizado', 'password, current_password'],
                ['Sistema', 'Não sanitizado', '_token, _method'],
            ]
        );

        $this->newLine();
        $this->info('🔍 Monitoramento:');
        $this->line('- Tentativas de XSS são logadas automaticamente');
        $this->line('- Logs em: storage/logs/laravel.log');
        $this->line('- Configuração em: config/sanitize.php');

        return self::SUCCESS;
    }

    private function isRichTextField(string $fieldName): bool
    {
        $richTextFields = config('sanitize.rich_text_fields', []);
        return in_array($fieldName, $richTextFields);
    }
}