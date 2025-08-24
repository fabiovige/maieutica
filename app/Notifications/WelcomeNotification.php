<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $user;

    public $password;

    public function __construct($user, $password = null)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject('Bem-vindo ao ' . config('app.name'))
            ->greeting('Olá ' . $this->user->name)
            ->line('Sua conta foi criada com sucesso!')
            ->line('Use seu email para acessar o sistema.')
            ->line('Sua senha temporária é: ' . ($this->password ?: 'NÃO INFORMADA - ERRO!'))
            ->action('Acessar o sistema', url('/'))
            ->line('Por favor, altere sua senha no primeiro acesso por questões de segurança.');
    }

    /**
     * Determine which queues should be used for each notification channel.
     */
    public function viaQueues()
    {
        return [
            'mail' => 'emails',
        ];
    }
}
