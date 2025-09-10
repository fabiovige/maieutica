<?php

/**
 * Script de verificação pós-migration e seeders
 * Execute: php scripts/verify_migration.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

echo "🔍 Verificando migrations e seeders aplicados...\n\n";

// Verificar se as colunas existem
$hasColumns = [
    'primary_professional' => Schema::hasColumn('kids', 'primary_professional'),
    'months' => Schema::hasColumn('kids', 'months'),
];

foreach ($hasColumns as $column => $exists) {
    echo ($exists ? '✅' : '❌') . " Coluna '$column': " . ($exists ? 'EXISTS' : 'MISSING') . "\n";
}

// Verificar Roles e Permissions (CRÍTICO)
echo "\n🔑 Verificando Roles e Permissions:\n";
$expectedRoles = ['superadmin', 'admin', 'professional', 'pais'];
foreach ($expectedRoles as $roleName) {
    $exists = Role::where('name', $roleName)->exists();
    echo ($exists ? '✅' : '❌') . " Role '$roleName': " . ($exists ? 'EXISTS' : 'MISSING') . "\n";
}

$permissionCount = Permission::count();
echo "📋 Total de Permissions: $permissionCount\n";

// Verificar Especialidades (CRÍTICO)
echo "\n🏥 Especialidades:\n";
$specialtyCount = \App\Models\Specialty::count();
echo ($specialtyCount > 0 ? '✅' : '❌') . " Specialties: $specialtyCount\n";

// Verificar Domínios e Competências (CRÍTICO)
echo "\n🧠 Sistema de Avaliação:\n";
$domainCount = \App\Models\Domain::count();
$levelCount = \App\Models\Level::count();
$competenceCount = \App\Models\Competence::count();

echo ($domainCount > 0 ? '✅' : '❌') . " Domains: $domainCount\n";
echo ($levelCount > 0 ? '✅' : '❌') . " Levels: $levelCount\n";
echo ($competenceCount > 0 ? '✅' : '❌') . " Competences: $competenceCount\n";

// Verificar contagem de registros
$counts = [
    'users' => App\Models\User::count(),
    'kids' => App\Models\Kid::count(),
    'professionals' => App\Models\Professional::count(),
    'responsibles' => \App\Models\Responsible::count(),
];

echo "\n📊 Contagem de registros:\n";
foreach ($counts as $table => $count) {
    echo "   $table: $count\n";
}

// Verificar se algum kid tem months NULL
$kidsWithoutMonths = App\Models\Kid::whereNull('months')->count();
echo "\n👶 Kids sem campo 'months': $kidsWithoutMonths\n";

// Verificar foreign keys
try {
    $kidsWithProfessional = App\Models\Kid::whereNotNull('primary_professional')->count();
    echo "👨‍⚕️ Kids com profissional primário: $kidsWithProfessional\n";
} catch (Exception $e) {
    echo "❌ Erro ao verificar foreign key: " . $e->getMessage() . "\n";
}

// Verificar se sistema está funcional
echo "\n🎯 Status do Sistema:\n";
$criticalOk = $domainCount > 0 && $levelCount > 0 && $competenceCount > 0 && $permissionCount > 0;
echo ($criticalOk ? '✅ SISTEMA FUNCIONAL' : '❌ SISTEMA COM PROBLEMAS') . "\n";

echo "\n✅ Verificação concluída!\n";