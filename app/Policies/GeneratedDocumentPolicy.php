<?php

namespace App\Policies;

use App\Models\GeneratedDocument;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GeneratedDocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any documents.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('document-list') || $user->can('document-list-all');
    }

    /**
     * Determine if the user can view the document.
     */
    public function view(User $user, GeneratedDocument $document): bool
    {
        // Admin vê tudo
        if ($user->can('document-show-all')) {
            return true;
        }

        // Requer permissão base
        if (! $user->can('document-show')) {
            return false;
        }

        // Pode ver se gerou
        if ($document->generated_by === $user->id) {
            return true;
        }

        // Profissional pode ver documentos que assinou
        $professional = $user->professional->first();
        if ($professional && $document->professional_id === $professional->id) {
            return true;
        }

        // Se for documento de Kid, profissional vinculado pode ver
        if ($document->documentable_type === \App\Models\Kid::class) {
            $kid = $document->documentable;
            if ($professional && $kid->professionals->contains($professional->id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the user can delete the document.
     */
    public function delete(User $user, GeneratedDocument $document): bool
    {
        // Admin pode deletar tudo
        if ($user->can('document-delete-all')) {
            return true;
        }

        // Pode deletar apenas próprios documentos
        return $user->can('document-delete') && $document->generated_by === $user->id;
    }

    /**
     * Determine if the user can view trashed documents.
     */
    public function viewTrash(User $user): bool
    {
        return $user->can('document-list-all');
    }

    /**
     * Determine if the user can restore the document.
     */
    public function restore(User $user, GeneratedDocument $document): bool
    {
        return $user->can('document-delete-all');
    }

    /**
     * Determine if the user can permanently delete the document.
     */
    public function forceDelete(User $user, GeneratedDocument $document): bool
    {
        return $user->can('document-delete-all');
    }

    /**
     * Determine if the user can download the document.
     */
    public function download(User $user, GeneratedDocument $document): bool
    {
        // Mesma lógica de view - quem pode ver, pode baixar
        return $this->view($user, $document);
    }
}
