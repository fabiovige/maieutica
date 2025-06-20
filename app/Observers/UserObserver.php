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
        // dd('updated');
        try {
            // Criar a instância do Mailable e depois chamar onQueue()
            $email = (new UserUpdatedMail($user))->onQueue('emails');

            // Enviar o e-mail para a fila
            Mail::to($user->email)->queue($email);

            Log::alert('E-mail de atualização enfileirado para o usuário', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'street' => $user->street,
                'city' => $user->city,
                'state' => $user->state,
                'country' => $user->country,
                'neighborhood' => $user->neighborhood,
                'postal_code' => $user->postal_code,
                'updated_at' => $user->updated_at,
                'updated_by' => auth()->user()->id,
            ]);

        } catch (\Exception $e) {
            // Registrar o erro no log
            Log::error('Falha ao enviar o e-mail de atualização', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Continuar o processo de log, mesmo que o e-mail falhe
        Log::info('Usuário atualizado UserObserver', ['user_id' => $user->id]);
    }

    /**
     * Handle the User "deleted" event.
     *
     * @return void
     */
    public function deleted(User $user)
    {
        // dd('deleted');
        if ($user->trashed()) {

            $admin = User::where('role_id', 1)->first(); // Ajuste a query conforme a role de admin
            if ($admin) {

                // Criar a instância do Mailable e depois chamar onQueue()
                $email = (new UserDeletedMail($user))->onQueue('emails');

                // Enviar o e-mail para a fila
                Mail::to($admin->email)
                    ->cc($user->email)
                    ->cc($user->email)
                    ->queue($email);
            }

            Log::alert('Usuário excluído UserObserver', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'street' => $user->street,
                'city' => $user->city,
                'state' => $user->state,
                'country' => $user->country,
                'neighborhood' => $user->neighborhood,
                'postal_code' => $user->postal_code,
                'deleted_at' => $user->deleted_at,
                'deleted_by' => auth()->user()->id,
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
