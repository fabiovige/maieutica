<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateN8nIntegrationToken extends Command
{
    /**
     * Nome do token Sanctum emitido para a conta de serviço.
     */
    private const TOKEN_NAME = 'n8n-integration';

    /**
     * Único ability concedida ao token — precisa bater com o middleware
     * `abilities:integration:professionals-read` em routes/api.php.
     */
    private const TOKEN_ABILITY = 'integration:professionals-read';

    /**
     * Única permission concedida à conta de serviço (sem role).
     */
    private const PERMISSION = 'professional-list-all';

    protected $signature = 'integration:n8n-token
        {--email= : Email da conta de serviço (padrão: integracao.n8n@sistema.local)}
        {--revoke : Apenas revoga os tokens existentes, sem emitir um novo}';

    protected $description = 'Provisiona a conta de serviço do N8N e emite/rotaciona o token restrito de leitura de profissionais';

    public function handle(): int
    {
        $email = $this->option('email') ?: 'integracao.n8n@sistema.local';

        $user = User::withTrashed()->firstOrNew(['email' => $email]);

        if (! $user->exists) {
            $user->name = 'Integração N8N';
            $user->password = bcrypt(Str::random(40));
            $user->type = User::TYPE_E;
            $user->allow = true;
            $user->email_verified_at = now();
            $user->save();

            $this->info("Conta de serviço criada: {$email} (id {$user->id})");
        } elseif ($user->trashed()) {
            $this->error("A conta de serviço {$email} está na lixeira. Restaure-a antes de continuar.");

            return self::FAILURE;
        } else {
            $this->info("Conta de serviço já existe: {$email} (id {$user->id})");
        }

        // Garante que a conta só tenha esta permission — nunca uma role.
        $user->syncPermissions([self::PERMISSION]);
        $user->syncRoles([]);

        $user->tokens()->where('name', self::TOKEN_NAME)->delete();

        if ($this->option('revoke')) {
            $this->info('Tokens existentes revogados. Nenhum token novo foi emitido.');

            return self::SUCCESS;
        }

        $token = $user->createToken(self::TOKEN_NAME, [self::TOKEN_ABILITY])->plainTextToken;

        $this->newLine();
        $this->warn('Guarde este token com segurança — ele não será exibido novamente:');
        $this->line($token);
        $this->newLine();
        $this->info('Uso no N8N: header "Authorization: Bearer '.$token.'" em GET /api/integrations/professionals');

        return self::SUCCESS;
    }
}
