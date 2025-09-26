<?php

namespace App\Services\Security;

use App\Enums\LogOperation;
use App\Services\Log\LoggingService;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LoginRateLimiterService
{
    private const IP_LIMIT_PER_MINUTE = 5;
    private const EMAIL_LIMIT_PER_5_MINUTES = 3;
    private const ESCALATION_LEVELS = [1, 5, 15, 60]; // minutes

    public function __construct(
        private readonly RateLimiter $limiter,
        private readonly LoggingService $loggingService
    ) {
    }

    public function canAttemptLogin(Request $request): bool
    {
        return $this->canAttemptByIp($request) && $this->canAttemptByEmail($request);
    }

    public function canAttemptByIp(Request $request): bool
    {
        $ipKey = $this->getIpThrottleKey($request);
        $attempts = $this->limiter->attempts($ipKey);

        return $attempts < self::IP_LIMIT_PER_MINUTE;
    }

    public function canAttemptByEmail(Request $request): bool
    {
        $emailKey = $this->getEmailThrottleKey($request);
        $attempts = $this->getEmailAttempts($emailKey);

        return $attempts < self::EMAIL_LIMIT_PER_5_MINUTES;
    }

    public function recordFailedAttempt(Request $request): void
    {
        $ipKey = $this->getIpThrottleKey($request);
        $emailKey = $this->getEmailThrottleKey($request);

        // Incrementar tentativas por IP
        $ipAttempts = $this->limiter->hit($ipKey, 60);

        // Incrementar tentativas por email
        $emailAttempts = $this->incrementEmailAttempts($emailKey);

        $this->logFailedAttempt($request, $ipAttempts, $emailAttempts);

        // Aplicar bloqueio escalonado se necessário
        $this->applyEscalatedLockout($request, $ipAttempts);
    }

    public function clearAttempts(Request $request): void
    {
        $ipKey = $this->getIpThrottleKey($request);
        $emailKey = $this->getEmailThrottleKey($request);

        $this->limiter->clear($ipKey);
        Cache::forget($emailKey);
        Cache::forget($this->getEscalationKey($request));
    }

    public function getRemainingTime(Request $request): int
    {
        $ipKey = $this->getIpThrottleKey($request);
        $emailKey = $this->getEmailThrottleKey($request);
        $escalationKey = $this->getEscalationKey($request);

        // Verificar bloqueio escalonado primeiro
        if (Cache::has($escalationKey)) {
            return Cache::get($escalationKey)['remaining_time'] ?? 0;
        }

        // Verificar bloqueio por IP
        $ipRemaining = $this->limiter->availableIn($ipKey);

        // Verificar bloqueio por email
        $emailData = Cache::get($emailKey);
        $emailRemaining = $emailData ? max(0, $emailData['expires_at'] - time()) : 0;

        return max($ipRemaining, $emailRemaining);
    }

    public function getThrottleMessage(Request $request): string
    {
        $remainingTime = $this->getRemainingTime($request);

        if ($remainingTime <= 0) {
            return '';
        }

        $minutes = ceil($remainingTime / 60);

        if ($minutes <= 1) {
            return 'Muitas tentativas de login. Tente novamente em alguns segundos.';
        }

        return "Muitas tentativas de login. Tente novamente em {$minutes} minuto(s).";
    }

    public function isUnderBruteForceAttack(Request $request): bool
    {
        $ipKey = $this->getIpThrottleKey($request);
        $attempts = $this->limiter->attempts($ipKey);

        return $attempts >= 10; // 10 ou mais tentativas em 1 minuto
    }

    public function getLoginAttemptsSummary(Request $request): array
    {
        $ipKey = $this->getIpThrottleKey($request);
        $emailKey = $this->getEmailThrottleKey($request);

        $ipAttempts = $this->limiter->attempts($ipKey);
        $emailAttempts = $this->getEmailAttempts($emailKey);

        return [
            'ip_attempts' => $ipAttempts,
            'ip_remaining' => max(0, self::IP_LIMIT_PER_MINUTE - $ipAttempts),
            'email_attempts' => $emailAttempts,
            'email_remaining' => max(0, self::EMAIL_LIMIT_PER_5_MINUTES - $emailAttempts),
            'remaining_time' => $this->getRemainingTime($request),
            'throttle_message' => $this->getThrottleMessage($request),
        ];
    }

    private function getIpThrottleKey(Request $request): string
    {
        return 'login_throttle_ip:' . $request->ip();
    }

    private function getEmailThrottleKey(Request $request): string
    {
        return 'login_throttle_email:' . sha1(strtolower($request->input('email', '')));
    }

    private function getEscalationKey(Request $request): string
    {
        return 'login_escalation:' . $request->ip();
    }

    private function getEmailAttempts(string $emailKey): int
    {
        $data = Cache::get($emailKey);

        if (!$data || $data['expires_at'] < time()) {
            return 0;
        }

        return $data['attempts'];
    }

    private function incrementEmailAttempts(string $emailKey): int
    {
        $data = Cache::get($emailKey, ['attempts' => 0, 'expires_at' => 0]);

        if ($data['expires_at'] < time()) {
            $data = ['attempts' => 0, 'expires_at' => 0];
        }

        $data['attempts']++;
        $data['expires_at'] = time() + (5 * 60); // 5 minutos

        Cache::put($emailKey, $data, now()->addMinutes(5));

        return $data['attempts'];
    }

    private function applyEscalatedLockout(Request $request, int $attempts): void
    {
        if ($attempts < 5) {
            return;
        }

        $escalationKey = $this->getEscalationKey($request);
        $escalationData = Cache::get($escalationKey, ['level' => 0, 'total_attempts' => 0]);

        $escalationData['total_attempts'] += 1;

        // Determinar nível de escalada baseado no total de tentativas
        $escalationLevel = min(
            count(self::ESCALATION_LEVELS) - 1,
            intval($escalationData['total_attempts'] / 5)
        );

        $lockoutMinutes = self::ESCALATION_LEVELS[$escalationLevel];
        $escalationData['level'] = $escalationLevel;
        $escalationData['remaining_time'] = $lockoutMinutes * 60;
        $escalationData['expires_at'] = time() + ($lockoutMinutes * 60);

        Cache::put($escalationKey, $escalationData, now()->addMinutes($lockoutMinutes));

        $this->loggingService->logSecurityEvent(
            LogOperation::ACCESS_DENIED,
            'Escalated login lockout applied',
            [
                'ip_address' => $request->ip(),
                'lockout_minutes' => $lockoutMinutes,
                'escalation_level' => $escalationLevel,
                'total_attempts' => $escalationData['total_attempts'],
            ],
            'critical'
        );
    }

    private function logFailedAttempt(Request $request, int $ipAttempts, int $emailAttempts): void
    {
        $severity = 'warning';
        $message = 'Failed login attempt recorded';

        if ($this->isUnderBruteForceAttack($request)) {
            $severity = 'critical';
            $message = 'Potential brute force attack detected';
        }

        $this->loggingService->logSecurityEvent(
            LogOperation::VALIDATION_FAILED,
            $message,
            [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'email_attempted' => $request->input('email'),
                'ip_attempts' => $ipAttempts,
                'email_attempts' => $emailAttempts,
                'is_brute_force' => $this->isUnderBruteForceAttack($request),
            ],
            $severity
        );
    }
}