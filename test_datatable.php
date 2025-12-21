<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== TESTE DO ENDPOINT DATATABLE ===\n\n";

try {
    // Simular autenticação com o primeiro usuário admin
    $user = \App\Models\User::whereHas('roles', function($q) {
        $q->where('name', 'admin');
    })->first();

    if (!$user) {
        echo "❌ Nenhum usuário admin encontrado!\n";
        echo "Tentando com primeiro usuário...\n";
        $user = \App\Models\User::first();
    }

    if (!$user) {
        echo "❌ Nenhum usuário encontrado no banco!\n";
        exit(1);
    }

    auth()->login($user);
    echo "✅ Autenticado como: {$user->name} (ID: {$user->id})\n\n";

    // Verificar permissões
    echo "Permissões do usuário:\n";
    $hasDocList = $user->can('document-list');
    $hasDocListAll = $user->can('document-list-all');
    echo "  - document-list: " . ($hasDocList ? 'SIM' : 'NÃO') . "\n";
    echo "  - document-list-all: " . ($hasDocListAll ? 'SIM' : 'NÃO') . "\n\n";

    // Buscar documentos
    echo "=== DOCUMENTOS NO BANCO ===\n";
    $allDocs = \App\Models\GeneratedDocument::all();
    echo "Total de documentos (sem filtro): {$allDocs->count()}\n";

    if ($allDocs->count() > 0) {
        foreach ($allDocs as $doc) {
            echo "\nDocumento ID {$doc->id}:\n";
            echo "  - model_type: {$doc->model_type}\n";
            echo "  - documentable_type: {$doc->documentable_type}\n";
            echo "  - documentable_id: {$doc->documentable_id}\n";
            echo "  - professional_id: {$doc->professional_id}\n";
            echo "  - generated_by: {$doc->generated_by}\n";
            echo "  - generated_at: {$doc->generated_at}\n";
        }
    }

    echo "\n=== TESTE SCOPE forAuthUser ===\n";
    $filteredDocs = \App\Models\GeneratedDocument::forAuthUser()->get();
    echo "Total de documentos (com scope forAuthUser): {$filteredDocs->count()}\n";

    echo "\n=== TESTE QUERY COM RELATIONSHIPS ===\n";
    $query = \App\Models\GeneratedDocument::with(['documentable', 'professional.user', 'generatedBy'])
        ->forAuthUser();

    $docs = $query->get();
    echo "Total de documentos (query completa): {$docs->count()}\n";

    if ($docs->count() > 0) {
        foreach ($docs as $doc) {
            echo "\nDocumento ID {$doc->id}:\n";
            echo "  - model_type_name: {$doc->model_type_name}\n";
            echo "  - documentable_name: " . ($doc->documentable ? $doc->documentable->name : 'NULL') . "\n";
            echo "  - professional: " . ($doc->professional ? 'ID ' . $doc->professional->id : 'NULL') . "\n";
            echo "  - generatedBy: " . ($doc->generatedBy ? $doc->generatedBy->name : 'NULL') . "\n";
            echo "  - generated_at: {$doc->generated_at}\n";
        }
    }

    echo "\n=== TESTE DATATABLE ===\n";
    $datatableResult = datatables()->eloquent($query)
        ->addColumn('documentable_name', function ($doc) {
            return $doc->documentable ? $doc->documentable->name : 'N/A';
        })
        ->addColumn('professional_name', function ($doc) {
            $user = $doc->professional?->user->first();
            return $user ? $user->name : 'N/A';
        })
        ->addColumn('action', function ($doc) {
            return '<button>Download</button>';
        })
        ->rawColumns(['action'])
        ->toJson();

    echo "JSON Result:\n";
    $json = json_decode($datatableResult, true);
    echo "  - draw: " . ($json['draw'] ?? 'N/A') . "\n";
    echo "  - recordsTotal: " . ($json['recordsTotal'] ?? 'N/A') . "\n";
    echo "  - recordsFiltered: " . ($json['recordsFiltered'] ?? 'N/A') . "\n";
    echo "  - data count: " . (isset($json['data']) ? count($json['data']) : 'N/A') . "\n";

    if (isset($json['data']) && count($json['data']) > 0) {
        echo "\nPrimeiro registro:\n";
        print_r($json['data'][0]);
    }

    echo "\n✅ Teste concluído!\n";

} catch (\Exception $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}
