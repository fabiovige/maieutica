<?php

namespace App\Http\Controllers;

use App\Services\Lgpd\LgpdComplianceService;
use App\Models\LgpdDataRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LgpdController extends Controller
{
    public function __construct(
        private readonly LgpdComplianceService $lgpdService
    ) {
        $this->middleware('auth');
    }

    public function consentForm()
    {
        $user = Auth::user();
        $consents = $user->lgpdConsents()->get()->keyBy('consent_type');

        return view('lgpd.consent', compact('consents'));
    }

    public function grantConsent(Request $request)
    {
        $request->validate([
            'consent_type' => 'required|string|in:data_processing,marketing,analytics',
            'purposes' => 'required|array',
        ]);

        $this->lgpdService->recordConsent(
            Auth::user(),
            $request->consent_type,
            $request->purposes,
            $request
        );

        return back()->with('success', 'Consentimento registrado com sucesso.');
    }

    public function revokeConsent(Request $request)
    {
        $request->validate([
            'consent_type' => 'required|string',
        ]);

        $this->lgpdService->revokeConsent(Auth::user(), $request->consent_type);

        return back()->with('success', 'Consentimento revogado com sucesso.');
    }

    public function dataRequestForm()
    {
        $user = Auth::user();
        $pendingRequests = $user->lgpdDataRequests()->pending()->get();

        return view('lgpd.data-request', compact('pendingRequests'));
    }

    public function submitDataRequest(Request $request)
    {
        $request->validate([
            'request_type' => 'required|string|in:access,correction,deletion,portability,restriction',
            'description' => 'nullable|string|max:1000',
        ]);

        $this->lgpdService->createDataRequest(
            Auth::user(),
            $request->request_type,
            $request->description
        );

        return back()->with('success', 'Solicitação enviada com sucesso. Você será notificado sobre o andamento.');
    }

    public function exportData()
    {
        $user = Auth::user();
        $data = $this->lgpdService->exportUserData($user);

        $fileName = 'meus_dados_' . date('Y-m-d_H-i-s') . '.json';

        return response()->json($data)
            ->header('Content-Disposition', "attachment; filename={$fileName}")
            ->header('Content-Type', 'application/json');
    }

    public function adminDashboard()
    {
        $this->authorize('view-lgpd-dashboard');

        $consentReport = $this->lgpdService->getConsentReport();
        $dataRequestsReport = $this->lgpdService->getDataRequestsReport();

        $pendingRequests = LgpdDataRequest::with('user')
            ->pending()
            ->orderBy('requested_at', 'desc')
            ->paginate(20);

        return view('admin.lgpd.dashboard', compact(
            'consentReport',
            'dataRequestsReport',
            'pendingRequests'
        ));
    }

    public function processDataRequest(Request $request, LgpdDataRequest $dataRequest)
    {
        $this->authorize('process-lgpd-requests');

        $request->validate([
            'action' => 'required|string|in:approve,reject',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($request->action === 'approve') {
            if ($dataRequest->request_type === 'deletion') {
                $results = $this->lgpdService->processDataDeletion($dataRequest->user);
                $dataRequest->complete($results, $request->notes);
            } elseif ($dataRequest->request_type === 'access') {
                $userData = $this->lgpdService->exportUserData($dataRequest->user);
                $dataRequest->complete($userData, $request->notes);
            } else {
                $dataRequest->complete(['message' => 'Solicitação processada manualmente'], $request->notes);
            }

            return back()->with('success', 'Solicitação aprovada e processada.');
        }

        $dataRequest->reject($request->notes);
        return back()->with('success', 'Solicitação rejeitada.');
    }
}