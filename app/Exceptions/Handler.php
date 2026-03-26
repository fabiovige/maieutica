<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            // Notificar admin por email em erros criticos (producao)
            if (app()->isProduction() && $this->shouldNotifyAdmin($e)) {
                $this->notifyAdmin($e);
            }
        });
    }

    private function shouldNotifyAdmin(Throwable $e): bool
    {
        return !($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException ||
                 $e instanceof \Illuminate\Session\TokenMismatchException ||
                 $e instanceof \Illuminate\Validation\ValidationException ||
                 $e instanceof \Illuminate\Auth\AuthenticationException);
    }

    private function notifyAdmin(Throwable $e): void
    {
        try {
            $adminEmail = config('mail.admin_email');
            if (!$adminEmail) {
                return;
            }

            Mail::raw(
                "Erro em producao:\n\n" .
                get_class($e) . ": " . $e->getMessage() . "\n\n" .
                "Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n\n" .
                "URL: " . (request() ? request()->fullUrl() : 'N/A') . "\n" .
                "User: " . (auth()->id() ?? 'Guest') . "\n" .
                "IP: " . (request() ? request()->ip() : 'N/A') . "\n" .
                "Data: " . now()->format('d/m/Y H:i:s'),
                function ($message) use ($adminEmail) {
                    $message->to($adminEmail)
                            ->subject('[Maieutica] Erro em Producao');
                }
            );
        } catch (\Exception $mailError) {
            Log::error('Falha ao notificar admin sobre erro', [
                'original_error' => $e->getMessage(),
                'mail_error' => $mailError->getMessage(),
            ]);
        }
    }
}
