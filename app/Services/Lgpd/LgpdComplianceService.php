<?php

namespace App\Services\Lgpd;

use App\Models\LgpdConsent;
use App\Models\LgpdDataRequest;
use App\Models\User;
use App\Models\Kid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LgpdComplianceService
{
    public function recordConsent(User $user, string $type, array $purposes, Request $request): LgpdConsent
    {
        $existingConsent = LgpdConsent::where('user_id', $user->id)
            ->where('consent_type', $type)
            ->first();

        if ($existingConsent && $existingConsent->isActive()) {
            return $existingConsent;
        }

        return LgpdConsent::create([
            'user_id' => $user->id,
            'consent_type' => $type,
            'purposes' => $purposes,
            'granted' => true,
            'granted_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'version' => '1.0',
                'source' => 'web_form',
            ],
        ]);
    }

    public function revokeConsent(User $user, string $type): bool
    {
        $consent = LgpdConsent::where('user_id', $user->id)
            ->where('consent_type', $type)
            ->active()
            ->first();

        return $consent ? $consent->revoke() : false;
    }

    public function hasActiveConsent(User $user, string $type): bool
    {
        return LgpdConsent::where('user_id', $user->id)
            ->where('consent_type', $type)
            ->active()
            ->exists();
    }

    public function createDataRequest(User $user, string $type, ?string $description = null): LgpdDataRequest
    {
        return LgpdDataRequest::create([
            'user_id' => $user->id,
            'request_type' => $type,
            'description' => $description,
            'requested_at' => now(),
        ]);
    }

    public function exportUserData(User $user): array
    {
        $data = [
            'personal_info' => [
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'kids' => [],
            'consents' => [],
            'data_requests' => [],
        ];

        if ($user->hasRole('responsible')) {
            $data['kids'] = $user->responsible?->kids()
                ->select('id', 'name', 'birth_date', 'created_at')
                ->get()
                ->toArray();
        }

        $data['consents'] = $user->lgpdConsents()
            ->select('consent_type', 'purposes', 'granted', 'granted_at', 'revoked_at')
            ->get()
            ->toArray();

        $data['data_requests'] = $user->lgpdDataRequests()
            ->select('request_type', 'status', 'requested_at', 'processed_at')
            ->get()
            ->toArray();

        return $data;
    }

    public function processDataDeletion(User $user): array
    {
        $results = [];

        DB::beginTransaction();

        try {
            if ($user->hasRole('responsible')) {
                $kidsCount = $user->responsible?->kids()->count() ?? 0;
                if ($kidsCount > 0) {
                    $results[] = "Não é possível excluir dados: usuário possui {$kidsCount} criança(s) associada(s)";
                    DB::rollBack();
                    return $results;
                }
            }

            $user->lgpdConsents()->delete();
            $results[] = 'Consentimentos removidos';

            $user->lgpdDataRequests()->delete();
            $results[] = 'Solicitações de dados removidas';

            $user->auditLogs()->delete();
            $results[] = 'Logs de auditoria removidos';

            $user->delete();
            $results[] = 'Conta de usuário removida';

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $results[] = 'Erro na exclusão: ' . $e->getMessage();
        }

        return $results;
    }

    public function getConsentReport(): array
    {
        return [
            'total_consents' => LgpdConsent::count(),
            'active_consents' => LgpdConsent::active()->count(),
            'revoked_consents' => LgpdConsent::whereNotNull('revoked_at')->count(),
            'consent_types' => LgpdConsent::select('consent_type')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('consent_type')
                ->pluck('count', 'consent_type'),
            'recent_consents' => LgpdConsent::where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }

    public function getDataRequestsReport(): array
    {
        return [
            'total_requests' => LgpdDataRequest::count(),
            'pending_requests' => LgpdDataRequest::where('status', 'pending')->count(),
            'completed_requests' => LgpdDataRequest::where('status', 'completed')->count(),
            'request_types' => LgpdDataRequest::select('request_type')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('request_type')
                ->pluck('count', 'request_type'),
            'avg_processing_time' => LgpdDataRequest::whereNotNull('processed_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, requested_at, processed_at)) as avg_hours')
                ->value('avg_hours'),
        ];
    }
}