<?php

namespace App\Traits;

use App\Enums\LogOperation;
use App\Services\Log\LoggingService;
use Illuminate\Http\Request;

trait HasControllerLogging
{
    protected function logControllerAction(
        string $action,
        array $context = [],
        ?string $message = null
    ): void {
        $controllerName = class_basename(static::class);
        $logMessage = $message ?? "Ação executada: {$controllerName}@{$action}";

        $controllerContext = array_merge($context, [
            'controller' => $controllerName,
            'action' => $action,
            'route_name' => request()->route()?->getName(),
        ]);

        LoggingService::logSystemOperation(
            LogOperation::READ,
            $logMessage,
            $controllerContext
        );
    }

    protected function logResourceAction(
        string $action,
        $resource = null,
        array $context = []
    ): void {
        $controllerName = class_basename(static::class);
        $resourceName = $resource ? class_basename($resource) : 'resource';
        $resourceId = $resource?->getKey();

        $message = match ($action) {
            'index' => "Listagem de {$resourceName} acessada",
            'show' => "Visualização de {$resourceName} #{$resourceId}",
            'create' => "Formulário de criação de {$resourceName} acessado",
            'store' => "Novo {$resourceName} criado",
            'edit' => "Formulário de edição de {$resourceName} #{$resourceId} acessado",
            'update' => "{$resourceName} #{$resourceId} atualizado",
            'destroy' => "{$resourceName} #{$resourceId} excluído",
            default => "Ação {$action} executada em {$resourceName}"
        };

        $resourceContext = array_merge($context, [
            'controller' => $controllerName,
            'action' => $action,
            'resource_type' => $resourceName,
            'resource_id' => $resourceId,
        ]);

        $operation = match ($action) {
            'index', 'show' => LogOperation::READ,
            'store' => LogOperation::CREATE,
            'update' => LogOperation::UPDATE,
            'destroy' => LogOperation::DELETE,
            default => LogOperation::READ
        };

        LoggingService::logSystemOperation($operation, $message, $resourceContext);
    }

    protected function logFormValidation(Request $request, array $rules, bool $passed = true): void
    {
        $controllerName = class_basename(static::class);

        if ($passed) {
            LoggingService::logDataValidation(
                LogOperation::READ,
                "Validação de formulário passou: {$controllerName}",
                [
                    'controller' => $controllerName,
                    'validation_rules' => array_keys($rules),
                    'validated_fields' => array_keys($request->all()),
                ],
                'info'
            );
        } else {
            LoggingService::logDataValidation(
                LogOperation::VALIDATION_FAILED,
                "Validação de formulário falhou: {$controllerName}",
                [
                    'controller' => $controllerName,
                    'validation_rules' => array_keys($rules),
                    'submitted_fields' => array_keys($request->all()),
                ],
                'warning'
            );
        }
    }

    protected function logUnauthorizedAccess(string $action, $resource = null): void
    {
        $controllerName = class_basename(static::class);
        $resourceName = $resource ? class_basename($resource) : 'resource';

        LoggingService::logSecurityEvent(
            LogOperation::ACCESS_DENIED,
            "Acesso negado: {$controllerName}@{$action}",
            [
                'controller' => $controllerName,
                'action' => $action,
                'resource_type' => $resourceName,
                'resource_id' => $resource?->getKey(),
                'attempted_by_user' => auth()->id(),
                'user_roles' => auth()->user()?->getRoleNames()?->toArray(),
            ]
        );
    }

    protected function logBulkOperation(string $operation, array $ids, string $resourceType): void
    {
        $controllerName = class_basename(static::class);

        LoggingService::logSystemOperation(
            LogOperation::UPDATE,
            "Operação em lote executada: {$operation} em {$resourceType}",
            [
                'controller' => $controllerName,
                'bulk_operation' => $operation,
                'resource_type' => $resourceType,
                'affected_ids' => $ids,
                'affected_count' => count($ids),
            ]
        );
    }

    protected function logFileUpload(string $fileName, string $filePath, int $fileSize): void
    {
        $controllerName = class_basename(static::class);

        LoggingService::logFileOperation(
            LogOperation::UPLOAD,
            "Arquivo enviado: {$fileName}",
            [
                'controller' => $controllerName,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'file_size_mb' => round($fileSize / 1024 / 1024, 2),
            ]
        );
    }

    protected function logSearch(Request $request, int $resultsCount): void
    {
        $controllerName = class_basename(static::class);

        LoggingService::logSystemOperation(
            LogOperation::READ,
            "Busca realizada: {$controllerName}",
            [
                'controller' => $controllerName,
                'search_query' => $request->get('search'),
                'search_filters' => $request->except(['search', 'page', '_token']),
                'results_count' => $resultsCount,
                'page' => $request->get('page', 1),
            ]
        );
    }
}
