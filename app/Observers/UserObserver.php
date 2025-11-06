<?php

namespace App\Observers;

use App\Mail\UserCreatedMail;
use App\Mail\UserDeletedMail;
use App\Mail\UserUpdatedMail;
use App\Models\User;
use App\Services\Logging\UserLogger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserObserver
{
    protected $userLogger;

    /**
     * UserObserver constructor.
     * Inject UserLogger for centralized logging.
     */
    public function __construct(UserLogger $userLogger)
    {
        $this->userLogger = $userLogger;
    }
    /**
     * Handle the User "created" event.
     *
     * @return void
     */
    public function created(User $user)
    {
        // Observer logs at model level - controller logs business operations
        $this->userLogger->created($user, [
            'source' => 'observer',
        ]);

        try {
            Log::info('UserObserver: created event triggered', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            // Criar a instância do Mailable e enfileirar
            $email = (new UserCreatedMail($user, $user->temporaryPassword))->onQueue('emails');

            // Enviar o e-mail para a fila
            Mail::to($user->email)->queue($email);

            Log::info('E-mail de boas-vindas enfileirado', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

        } catch (\Exception $e) {
            Log::error('Falha ao enviar e-mail de boas-vindas', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the User "updated" event.
     *
     * @return void
     */
    public function updated(User $user)
    {
        // Get the changed attributes
        $changes = [];
        foreach ($user->getDirty() as $field => $newValue) {
            $changes[$field] = [
                'old' => $user->getOriginal($field),
                'new' => $newValue,
            ];
        }

        // Only log if there are actual changes
        if (!empty($changes)) {
            $this->userLogger->updated($user, $changes, [
                'source' => 'observer',
            ]);
        }

        try {
            // Criar a instância do Mailable e depois chamar onQueue()
            $email = (new UserUpdatedMail($user))->onQueue('emails');

            // Enviar o e-mail para a fila
            Mail::to($user->email)->queue($email);

            Log::info('E-mail de atualização enfileirado para o usuário', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'updated_at' => $user->updated_at,
            ]);

        } catch (\Exception $e) {
            // Registrar o erro no log
            Log::error('Falha ao enviar o e-mail de atualização', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the User "deleted" event.
     *
     * @return void
     */
    public function deleted(User $user)
    {
        $this->userLogger->deleted($user, [
            'source' => 'observer',
        ]);

        try {
            if ($user->trashed()) {
                // Buscar administradores usando Spatie Permission
                $admins = User::whereHas('roles', function($query) {
                    $query->where('name', 'superadmin');
                })->get();

                if ($admins->isNotEmpty()) {
                    // Criar a instância do Mailable e depois chamar onQueue()
                    $email = (new UserDeletedMail($user))->onQueue('emails');

                    // Enviar para todos os admins e para o próprio usuário
                    $recipients = $admins->pluck('email')->push($user->email)->unique();

                    Mail::to($recipients->first())
                        ->cc($recipients->slice(1)->toArray())
                        ->queue($email);
                }

                Log::alert('Usuário excluído', [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'deleted_at' => $user->deleted_at,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Falha ao enviar e-mail de exclusão', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the User "restored" event.
     *
     * @return void
     */
    public function restored(User $user)
    {
        $this->userLogger->restored($user, [
            'source' => 'observer',
        ]);
    }

    /**
     * Handle the User "force deleted" event.
     * This is a permanent deletion and should be logged carefully.
     *
     * @return void
     */
    public function forceDeleted(User $user)
    {
        // Force delete is a critical operation - use alert level
        // We'll log through the logger but note it's permanent deletion
        $this->userLogger->deleted($user, [
            'source' => 'observer',
            'permanent' => true,
            'warning' => 'User permanently deleted from database',
        ]);
    }
}
