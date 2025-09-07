<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LogOperation;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use App\Notifications\PasswordResetNotification;
use App\Services\Log\LoggingService;
use Exception;

class NotificationService
{
    public function __construct(
        private readonly LoggingService $loggingService
    ) {
    }
    public function sendWelcomeNotification(User $user, string $password): void
    {
        try {
            $user->notify(new WelcomeNotification($user, $password));
            
            $this->loggingService->logUserOperation(
                LogOperation::EMAIL_SEND,
                'Welcome notification sent successfully to new user',
                ['user_id' => $user->id]);
            
        } catch (Exception $e) {
            $this->loggingService->logException(
                $e,
                'Failed to send welcome notification',
                ['user_id' => $user->id]
            );
            
            throw new Exception("Falha ao enviar notificação de boas-vindas", 0, $e);
        }
    }

    public function sendPasswordResetNotification(User $user, string $newPassword): void
    {
        try {
            if (class_exists(PasswordResetNotification::class)) {
                $user->notify(new PasswordResetNotification($user, $newPassword));
                $notificationType = 'password_reset';
            } else {
                $user->notify(new WelcomeNotification($user, $newPassword));
                $notificationType = 'welcome_fallback';
            }
            
            $this->loggingService->logSecurityEvent(
                LogOperation::PASSWORD_RESET,
                'Password reset notification sent successfully',
                [
                    'user_id' => $user->id,
                    'notification_type' => $notificationType
                ]);
            
        } catch (Exception $e) {
            $this->loggingService->logException(
                $e,
                'Failed to send password reset notification',
                ['user_id' => $user->id]
            );
            
            throw new Exception("Falha ao enviar notificação de reset de senha", 0, $e);
        }
    }

    public function sendBulkNotification(array $users, string $notificationClass, array $data = []): void
    {
        $userIds = array_map(fn($user) => $user->id, $users);
        
        $this->loggingService->logUserOperation(
            LogOperation::EMAIL_SEND,
            'Starting bulk notification process',
            [
                'notification_class' => $notificationClass,
                'user_count' => count($users),
                'user_ids' => $userIds
            ]
        );
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($users as $user) {
            try {
                if (class_exists($notificationClass)) {
                    $user->notify(new $notificationClass($user, ...$data));
                    $successCount++;
                }
            } catch (Exception $e) {
                $errorCount++;
                
                $this->loggingService->logException(
                    $e,
                    'Failed to send bulk notification to individual user',
                    [
                        'user_id' => $user->id,
                        'notification_class' => $notificationClass
                    ]
                );
            }
        }
        
        $this->loggingService->logUserOperation(
            LogOperation::EMAIL_SEND,
            'Bulk notification process completed',
            [
                'notification_class' => $notificationClass,
                'total_users' => count($users),
                'success_count' => $successCount,
                'error_count' => $errorCount
            ],
            $errorCount > 0 ? 'warning' : 'info'
        );
    }
}