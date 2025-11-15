<?php

namespace App\Policies;

use App\Models\GeneratedDocument;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GeneratedDocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode visualizar qualquer documento (listagem).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('document-list') || $user->can('document-list-all');
    }

    /**
     * Determina se o usuário pode visualizar um documento específico.
     */
    public function view(User $user, GeneratedDocument $generatedDocument): bool
    {
        return $user->can('document-show') || $user->can('document-show-all');
    }

    /**
     * Determina se o usuário pode gerar novos documentos.
     */
    public function generate(User $user): bool
    {
        return $user->can('document-generate');
    }

    /**
     * Determina se o usuário pode baixar um documento específico.
     */
    public function download(User $user, GeneratedDocument $generatedDocument): bool
    {
        return $user->can('document-download');
    }

    /**
     * Determina se o usuário pode remover um documento específico.
     */
    public function delete(User $user, GeneratedDocument $generatedDocument): bool
    {
        return $user->can('document-delete') || $user->can('document-delete-all');
    }

    /**
     * Determina se o usuário pode visualizar a lixeira de documentos.
     */
    public function viewTrash(User $user): bool
    {
        return $user->can('document-list-all');
    }

    /**
     * Determina se o usuário pode restaurar um documento específico.
     */
    public function restore(User $user, GeneratedDocument $generatedDocument): bool
    {
        return $user->can('document-delete-all');
    }

    /**
     * Determina se o usuário pode forçar a remoção de um documento específico.
     */
    public function forceDelete(User $user, GeneratedDocument $generatedDocument): bool
    {
        return $user->can('document-delete-all');
    }
}
