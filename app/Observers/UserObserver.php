<?php

namespace App\Observers;

use App\Mail\UserDeletedMail;
use App\Mail\UserUpdatedMail;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @return void
     */
    public function created(User $user)
    {
        try {
            \Log::info('UserObserver: created event triggered', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            // O Observer é responsável por enviar o email de boas-vindas
            $notification = new WelcomeNotification($user, $user->temporaryPassword);
            $user->notify($notification);

        } catch (\Exception $e) {
            Log::error('Erro no UserObserver: '.$e->getMessage());
        }
    }

    /**
     * Handle the User "updated" event.
     *
     * @return void
     */
    public function updated(User $user)
    {
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
        try {
            if ($user->trashed()) {
                // Buscar administradores usando Spatie Permission
                $admins = User::role('superadmin')->get();

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
        //
    }

    /**
     * Handle the User "force deleted" event.
     *
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
