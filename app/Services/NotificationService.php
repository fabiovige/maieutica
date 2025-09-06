<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Notifications\WelcomeNotification;
use App\Notifications\PasswordResetNotification;
use Illuminate\Support\Facades\Log;
use Exception;

class NotificationService
{
    public function sendWelcomeNotification(User $user, string $password): void
    {
        try {
            $user->notify(new WelcomeNotification($user, $password));
            Log::info("Welcome notification sent to user {$user->id} with email {$user->email}");
        } catch (Exception $e) {
            Log::error("Failed to send welcome notification to user {$user->id}: " . $e->getMessage());
            throw new Exception("Falha ao enviar notificação de boas-vindas", 0, $e);
        }
    }

    public function sendPasswordResetNotification(User $user, string $newPassword): void
    {
        try {
            if (class_exists(PasswordResetNotification::class)) {
                $user->notify(new PasswordResetNotification($user, $newPassword));
            } else {
                $user->notify(new WelcomeNotification($user, $newPassword));
            }
            Log::info("Password reset notification sent to user {$user->id} with email {$user->email}");
        } catch (Exception $e) {
            Log::error("Failed to send password reset notification to user {$user->id}: " . $e->getMessage());
            throw new Exception("Falha ao enviar notificação de reset de senha", 0, $e);
        }
    }

    public function sendBulkNotification(array $users, string $notificationClass, array $data = []): void
    {
        foreach ($users as $user) {
            try {
                if (class_exists($notificationClass)) {
                    $user->notify(new $notificationClass($user, ...$data));
                }
            } catch (Exception $e) {
                Log::error("Failed to send notification to user {$user->id}: " . $e->getMessage());
            }
        }
    }
}