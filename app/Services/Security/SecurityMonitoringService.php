<?php

namespace App\Services\Security;

use App\Models\User;
use App\Services\Log\LoggingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SecurityMonitoringService
{
    public function __construct(
        private readonly LoggingService $loggingService
    ) {}

    public function checkSecurityThreats(): array
    {
        $alerts = [];
        $now = Carbon::now();

        $alerts = array_merge($alerts, $this->checkMultipleFailedLogins());
        $alerts = array_merge($alerts, $this->checkSuspiciousIPs());
        $alerts = array_merge($alerts, $this->checkUnusualUserActivity());
        $alerts = array_merge($alerts, $this->checkSystemHealth());
        $alerts = array_merge($alerts, $this->checkDataIntegrity());

        if (!empty($alerts)) {
            $this->processAlerts($alerts);
        }

        return [
            'timestamp' => $now->toDateTimeString(),
            'alerts_count' => count($alerts),
            'alerts' => $alerts,
            'system_status' => empty($alerts) ? 'healthy' : 'attention_required'
        ];
    }

    private function checkMultipleFailedLogins(): array
    {
        $alerts = [];
        $threshold = 10;
        $timeWindow = 15; // minutos

        $failedLogins = DB::table('audit_logs')
            ->where('action', 'login_failed')
            ->where('created_at', '>=', Carbon::now()->subMinutes($timeWindow))
            ->select('ip_address as ip', DB::raw('count(*) as attempts'))
            ->groupBy('ip_address')
            ->havingRaw('count(*) >= ?', [$threshold])
            ->get();

        foreach ($failedLogins as $login) {
            $alerts[] = [
                'type' => 'security_threat',
                'severity' => 'high',
                'title' => 'Múltiplas tentativas de login falharam',
                'message' => "IP {$login->ip} teve {$login->attempts} tentativas de login falharam nos últimos {$timeWindow} minutos",
                'data' => [
                    'ip_address' => $login->ip,
                    'attempts' => $login->attempts,
                    'time_window' => $timeWindow
                ]
            ];
        }

        return $alerts;
    }

    private function checkSuspiciousIPs(): array
    {
        $alerts = [];
        $suspiciousIPs = Cache::get('suspicious_ips', []);
        $timeWindow = 60; // minutos

        foreach ($suspiciousIPs as $ip => $data) {
            if ($data['last_activity'] > Carbon::now()->subMinutes($timeWindow)) {
                $alerts[] = [
                    'type' => 'suspicious_activity',
                    'severity' => 'medium',
                    'title' => 'IP suspeito detectado',
                    'message' => "IP {$ip} apresenta comportamento suspeito: {$data['reason']}",
                    'data' => [
                        'ip_address' => $ip,
                        'reason' => $data['reason'],
                        'last_activity' => $data['last_activity']->toDateTimeString()
                    ]
                ];
            }
        }

        return $alerts;
    }

    private function checkUnusualUserActivity(): array
    {
        $alerts = [];
        $timeWindow = 24; // horas

        $unusualActivity = DB::table('audit_logs')
            ->select('user_id', DB::raw('count(*) as activity_count'))
            ->where('created_at', '>=', Carbon::now()->subHours($timeWindow))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->havingRaw('count(*) > ?', [100]) // Mais de 100 ações por dia
            ->get();

        foreach ($unusualActivity as $activity) {
            $user = User::find($activity->user_id);
            if ($user) {
                $alerts[] = [
                    'type' => 'unusual_activity',
                    'severity' => 'medium',
                    'title' => 'Atividade incomum detectada',
                    'message' => "Usuário {$user->name} ({$user->email}) teve {$activity->activity_count} ações nas últimas {$timeWindow} horas",
                    'data' => [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'user_email' => $user->email,
                        'activity_count' => $activity->activity_count,
                        'time_window' => $timeWindow
                    ]
                ];
            }
        }

        return $alerts;
    }

    private function checkSystemHealth(): array
    {
        $alerts = [];

        $diskUsage = $this->getDiskUsage();
        if ($diskUsage > 90) {
            $alerts[] = [
                'type' => 'system_health',
                'severity' => 'high',
                'title' => 'Espaço em disco baixo',
                'message' => "Uso do disco está em {$diskUsage}%",
                'data' => ['disk_usage' => $diskUsage]
            ];
        }

        $errorRate = $this->getErrorRate();
        if ($errorRate > 5) {
            $alerts[] = [
                'type' => 'system_health',
                'severity' => 'medium',
                'title' => 'Alta taxa de erros',
                'message' => "Taxa de erros está em {$errorRate}% na última hora",
                'data' => ['error_rate' => $errorRate]
            ];
        }

        return $alerts;
    }

    private function checkDataIntegrity(): array
    {
        $alerts = [];

        $orphanedRecords = DB::table('kids')
            ->leftJoin('responsibles', 'kids.responsible_id', '=', 'responsibles.id')
            ->whereNull('responsibles.id')
            ->count();

        if ($orphanedRecords > 0) {
            $alerts[] = [
                'type' => 'data_integrity',
                'severity' => 'medium',
                'title' => 'Registros órfãos encontrados',
                'message' => "Encontradas {$orphanedRecords} crianças sem responsável associado",
                'data' => ['orphaned_count' => $orphanedRecords]
            ];
        }

        return $alerts;
    }

    private function getDiskUsage(): float
    {
        $bytes = disk_total_space('/');
        $free = disk_free_space('/');
        return $bytes > 0 ? round(($bytes - $free) / $bytes * 100, 2) : 0;
    }

    private function getErrorRate(): float
    {
        $total = DB::table('audit_logs')
            ->where('created_at', '>=', Carbon::now()->subHour())
            ->count();

        $errors = DB::table('audit_logs')
            ->where('created_at', '>=', Carbon::now()->subHour())
            ->where('action', 'like', '%error%')
            ->count();

        return $total > 0 ? round($errors / $total * 100, 2) : 0;
    }

    private function processAlerts(array $alerts): void
    {
        $highSeverityAlerts = array_filter($alerts, fn($alert) => $alert['severity'] === 'high');

        if (!empty($highSeverityAlerts)) {
            $this->sendHighSeverityAlert($highSeverityAlerts);
        }

        foreach ($alerts as $alert) {
            $this->loggingService->logSecurityEvent(
                'MONITORING_ALERT',
                $alert['title'],
                $alert['data'],
                $alert['severity']
            );
        }

        Cache::put('security_alerts', $alerts, Carbon::now()->addHours(24));
    }

    private function sendHighSeverityAlert(array $alerts): void
    {
        $adminEmails = User::role(['superadmin', 'admin'])->pluck('email')->toArray();

        if (empty($adminEmails)) {
            return;
        }

        $subject = 'Alerta de Segurança Crítico - Maiêutica';
        $alertCount = count($alerts);

        try {
            Mail::send('emails.security-alert', [
                'alerts' => $alerts,
                'alert_count' => $alertCount,
                'timestamp' => Carbon::now()->format('d/m/Y H:i:s')
            ], function ($message) use ($adminEmails, $subject) {
                $message->to($adminEmails)
                    ->subject($subject)
                    ->priority(1); // High priority
            });
        } catch (\Exception $e) {
            $this->loggingService->logSecurityEvent(
                'ALERT_SEND_FAILED',
                'Falha ao enviar alerta de segurança',
                ['error' => $e->getMessage()],
                'error'
            );
        }
    }

    public function getMetrics(): array
    {
        $today = Carbon::now();
        $yesterday = Carbon::now()->subDay();
        $lastWeek = Carbon::now()->subWeek();

        return [
            'daily_metrics' => [
                'total_users' => User::whereDate('created_at', $today)->count(),
                'active_sessions' => $this->getActiveSessions(),
                'failed_logins' => $this->getFailedLoginsCount($today),
                'security_events' => $this->getSecurityEventsCount($today)
            ],
            'weekly_metrics' => [
                'new_users' => User::where('created_at', '>=', $lastWeek)->count(),
                'total_logins' => $this->getLoginsCount($lastWeek),
                'failed_logins' => $this->getFailedLoginsCount($lastWeek),
                'security_alerts' => $this->getSecurityEventsCount($lastWeek)
            ],
            'system_health' => [
                'disk_usage' => $this->getDiskUsage(),
                'error_rate' => $this->getErrorRate(),
                'average_response_time' => $this->getAverageResponseTime()
            ]
        ];
    }

    private function getActiveSessions(): int
    {
        return DB::table('sessions')->where('last_activity', '>', Carbon::now()->subMinutes(30)->timestamp)->count();
    }

    private function getFailedLoginsCount(Carbon $since): int
    {
        return DB::table('audit_logs')
            ->where('action', 'login_failed')
            ->where('created_at', '>=', $since)
            ->count();
    }

    private function getLoginsCount(Carbon $since): int
    {
        return DB::table('audit_logs')
            ->where('action', 'login_success')
            ->where('created_at', '>=', $since)
            ->count();
    }

    private function getSecurityEventsCount(Carbon $since): int
    {
        return DB::table('audit_logs')
            ->where('created_at', '>=', $since)
            ->where(function ($query) {
                $query->where('action', 'like', '%failed%')
                      ->orWhere('action', 'like', '%error%')
                      ->orWhere('action', 'like', '%blocked%');
            })
            ->count();
    }

    private function getAverageResponseTime(): float
    {
        return 0.25; // Placeholder - seria integrado com APM real
    }

    public function blockSuspiciousIP(string $ip, string $reason): void
    {
        $suspiciousIPs = Cache::get('suspicious_ips', []);
        $suspiciousIPs[$ip] = [
            'reason' => $reason,
            'blocked_at' => Carbon::now(),
            'last_activity' => Carbon::now()
        ];

        Cache::put('suspicious_ips', $suspiciousIPs, Carbon::now()->addDays(7));

        $this->loggingService->logSecurityEvent(
            'IP_BLOCKED',
            'IP bloqueado por atividade suspeita',
            ['ip_address' => $ip, 'reason' => $reason],
            'warning'
        );
    }

    public function unblockIP(string $ip): void
    {
        $suspiciousIPs = Cache::get('suspicious_ips', []);
        unset($suspiciousIPs[$ip]);
        Cache::put('suspicious_ips', $suspiciousIPs, Carbon::now()->addDays(7));

        $this->loggingService->logSecurityEvent(
            'IP_UNBLOCKED',
            'IP desbloqueado',
            ['ip_address' => $ip],
            'info'
        );
    }
}