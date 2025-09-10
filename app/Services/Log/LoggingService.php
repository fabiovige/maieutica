<?php

namespace App\Services\Log;

use App\Enums\LogCategory;
use App\Enums\LogOperation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class LoggingService
{
    private static ?string $traceId = null;
    private static array $contextStack = [];

    public static function logUserOperation(
        LogOperation $operation,
        string $message,
        array $context = [],
        ?string $level = null
    ): void {
        self::log(
            LogCategory::USER_OPERATIONS,
            $operation,
            $message,
            $context,
            $level
        );
    }

    public static function logProfessionalOperation(
        LogOperation $operation,
        string $message,
        array $context = [],
        ?string $level = null
    ): void {
        self::log(
            LogCategory::PROFESSIONAL_OPERATIONS,
            $operation,
            $message,
            $context,
            $level
        );
    }

    public static function logKidOperation(
        LogOperation $operation,
        string $message,
        array $context = [],
        ?string $level = null
    ): void {
        self::log(
            LogCategory::KID_OPERATIONS,
            $operation,
            $message,
            $context,
            $level
        );
    }

    public static function logChecklistOperation(
        LogOperation $operation,
        string $message,
        array $context = [],
        ?string $level = null
    ): void {
        self::log(
            LogCategory::CHECKLIST_OPERATIONS,
            $operation,
            $message,
            $context,
            $level
        );
    }

    public static function logCompetenceOperation(
        LogOperation $operation,
        string $message,
        array $context = [],
        ?string $level = null
    ): void {
        self::log(
            LogCategory::COMPETENCE_OPERATIONS,
            $operation,
            $message,
            $context,
            $level
        );
    }

    public static function logAuthentication(
        LogOperation $operation,
        string $message,
        array $context = [],
        ?string $level = null
    ): void {
        self::log(
            LogCategory::AUTHENTICATION,
            $operation,
            $message,
            $context,
            $level ?? $operation->getLogLevel()
        );
    }

    public static function logAuthorization(
        LogOperation $operation,
        string $message,
        array $context = [],
        ?string $level = null
    ): void {
        self::log(
            LogCategory::AUTHORIZATION,
            $operation,
            $message,
            $context,
            $level ?? $operation->getLogLevel()
        );
    }

    public static function logSystemOperation(
        LogOperation $operation,
        string $message,
        array $context = [],
        ?string $level = null
    ): void {
        self::log(
            LogCategory::SYSTEM_OPERATIONS,
            $operation,
            $message,
            $context,
            $level
        );
    }

    public static function logSecurityEvent(
        LogOperation $operation,
        string $message,
        array $context = [],
        ?string $level = null
    ): void {
        self::log(
            LogCategory::SECURITY_EVENTS,
            $operation,
            $message,
            $context,
            $level ?? 'warning'
        );
    }

    public static function logPerformance(
        string $message,
        array $context = [],
        ?float $executionTime = null
    ): void {
        $performanceContext = $context;

        if ($executionTime !== null) {
            $performanceContext['execution_time'] = $executionTime;
            $performanceContext['performance_category'] = self::categorizePerformance($executionTime);
        }

        self::log(
            LogCategory::PERFORMANCE_MONITORING,
            LogOperation::READ,
            $message,
            $performanceContext,
            'info'
        );
    }

    public static function logDataValidation(
        LogOperation $operation,
        string $message,
        array $context = [],
        ?string $level = null
    ): void {
        self::log(
            LogCategory::DATA_VALIDATION,
            $operation,
            $message,
            $context,
            $level ?? 'error'
        );
    }

    public static function logFileOperation(
        LogOperation $operation,
        string $message,
        array $context = [],
        ?string $level = null
    ): void {
        self::log(
            LogCategory::FILE_OPERATIONS,
            $operation,
            $message,
            $context,
            $level
        );
    }

    public static function logEmailOperation(
        LogOperation $operation,
        string $message,
        array $context = [],
        ?string $level = null
    ): void {
        self::log(
            LogCategory::EMAIL_OPERATIONS,
            $operation,
            $message,
            $context,
            $level
        );
    }

    public static function logException(
        Throwable $exception,
        string $message = null,
        array $context = []
    ): void {
        $exceptionContext = array_merge($context, [
            'exception_class' => get_class($exception),
            'exception_message' => $exception->getMessage(),
            'exception_code' => $exception->getCode(),
            'exception_file' => $exception->getFile(),
            'exception_line' => $exception->getLine(),
            'stack_trace' => $exception->getTraceAsString(),
        ]);

        self::log(
            LogCategory::ERROR_HANDLING,
            LogOperation::EXCEPTION_OCCURRED,
            $message ?? "Exceção capturada: {$exception->getMessage()}",
            $exceptionContext,
            'critical'
        );
    }

    public static function startTrace(string $operation = null): string
    {
        self::$traceId = Str::uuid()->toString();

        if ($operation) {
            self::pushContext(['operation' => $operation]);
        }

        return self::$traceId;
    }

    public static function endTrace(): void
    {
        self::$traceId = null;
        self::$contextStack = [];
    }

    public static function pushContext(array $context): void
    {
        self::$contextStack[] = $context;
    }

    public static function popContext(): array
    {
        return array_pop(self::$contextStack) ?? [];
    }

    public static function getTraceId(): ?string
    {
        return self::$traceId;
    }

    private static function log(
        LogCategory $category,
        LogOperation $operation,
        string $message,
        array $context = [],
        ?string $level = null
    ): void {
        $logLevel = $level ?? $operation->getLogLevel();

        // Usar apenas o canal 'daily' que já funciona com permissões corretas
        $channel = 'daily';

        $enrichedContext = self::enrichContext($category, $operation, $context);

        $logData = DataSanitizer::createLogContext($message, $enrichedContext, [
            'category' => $category->value,
            'category_display' => $category->getDisplayName(),
            'operation' => $operation->value,
            'operation_display' => $operation->getDisplayName(),
            'level' => $logLevel,
            'channel' => $channel,
        ]);

        Log::channel($channel)->log($logLevel, $message, $logData);
    }

    private static function enrichContext(
        LogCategory $category,
        LogOperation $operation,
        array $context
    ): array {
        $enriched = array_merge($context, [
            'trace_id' => self::$traceId ?? Str::uuid()->toString(),
            'category' => $category->value,
            'operation' => $operation->value,
            'timestamp' => now()->toISOString(),
        ]);

        if (auth()->check()) {
            $enriched['authenticated_user'] = [
                'id' => auth()->id(),
                'email' => auth()->user()->email,
                'roles' => auth()->user()->getRoleNames()->toArray(),
            ];
        }

        if (request()) {
            $enriched['request_info'] = [
                'method' => request()->method(),
                'url' => request()->fullUrl(),
                'route' => request()->route()?->getName(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ];
        }

        foreach (self::$contextStack as $stackContext) {
            $enriched = array_merge($enriched, $stackContext);
        }

        return $enriched;
    }

    private static function categorizePerformance(float $executionTime): string
    {
        return match (true) {
            $executionTime < 0.1 => 'excellent',
            $executionTime < 0.5 => 'good',
            $executionTime < 1.0 => 'acceptable',
            $executionTime < 2.0 => 'slow',
            default => 'critical'
        };
    }

    public static function measureExecution(callable $callback, string $operationName = null): mixed
    {
        $startTime = microtime(true);
        $traceId = self::startTrace($operationName);

        try {
            $result = $callback();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            self::logPerformance(
                'Operação executada: ' . ($operationName ?? 'operação anônima'),
                ['operation_name' => $operationName],
                $executionTime
            );

            return $result;
        } catch (Throwable $e) {
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            self::logPerformance(
                'Operação falhou: ' . ($operationName ?? 'operação anônima'),
                [
                    'operation_name' => $operationName,
                    'error' => $e->getMessage(),
                ],
                $executionTime
            );

            self::logException($e, 'Erro durante execução da operação: ' . $operationName);

            throw $e;
        } finally {
            self::endTrace();
        }
    }

    public static function createLogEntryForModel($model, LogOperation $operation, array $context = []): void
    {
        $modelClass = get_class($model);
        $modelName = class_basename($modelClass);

        $category = self::getCategoryForModel($modelName);
        $message = self::getMessageForModelOperation($modelName, $operation, $model);

        $modelContext = array_merge($context, [
            'model_class' => $modelClass,
            'model_name' => $modelName,
            'model_id' => $model->getKey(),
            'model_attributes' => $model->getAttributes(),
        ]);

        self::log($category, $operation, $message, $modelContext);
    }

    private static function getCategoryForModel(string $modelName): LogCategory
    {
        return match (strtolower($modelName)) {
            'user' => LogCategory::USER_OPERATIONS,
            'professional' => LogCategory::PROFESSIONAL_OPERATIONS,
            'kid' => LogCategory::KID_OPERATIONS,
            'checklist' => LogCategory::CHECKLIST_OPERATIONS,
            'competence', 'domain', 'level', 'plane' => LogCategory::COMPETENCE_OPERATIONS,
            default => LogCategory::SYSTEM_OPERATIONS,
        };
    }

    private static function getMessageForModelOperation(string $modelName, LogOperation $operation, $model): string
    {
        $operationName = $operation->getDisplayName();
        $identifier = method_exists($model, 'getLogIdentifier')
            ? $model->getLogIdentifier()
            : ($model->name ?? $model->getKey());

        return match ($operation) {
            LogOperation::CREATE => "{$modelName} criado: {$identifier}",
            LogOperation::UPDATE => "{$modelName} atualizado: {$identifier}",
            LogOperation::DELETE => "{$modelName} excluído: {$identifier}",
            LogOperation::READ => "{$modelName} visualizado: {$identifier}",
            default => "{$modelName} - {$operationName}: {$identifier}",
        };
    }
}
