<?php

namespace App\Policies;

use App\Models\MedicalRecord;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MedicalRecordPolicy
{
    use HandlesAuthorization;

    /**
     * Listar prontuários médicos.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('medical-record-list') || $user->can('medical-record-list-all');
    }

    /**
     * Visualizar um prontuário específico.
     * Profissionais podem ver prontuários de TODOS os pacientes atribuídos a eles.
     */
    public function view(User $user, MedicalRecord $medicalRecord): bool
    {
        // Admin sees everything
        if ($user->can('medical-record-show-all')) {
            return true;
        }

        // Professional can view if they created it
        if ($user->can('medical-record-show')) {
            if ($medicalRecord->created_by === $user->id) {
                return true;
            }

            // OR if patient is assigned to them
            $professional = $user->professional->first();
            if ($professional && $medicalRecord->patient) {
                // If Kid, check professionals pivot
                if ($medicalRecord->patient_type === 'App\\Models\\Kid') {
                    if ($medicalRecord->patient->professionals->contains($professional->id)) {
                        return true;
                    }
                }

                // If User (adult patient), check assignment
                // TODO: implement when User->Professional relationship is defined
                if ($medicalRecord->patient_type === 'App\\Models\\User') {
                    // Temporarily allow viewing for any professional
                    // Adjust when User->Professional assignment system is implemented
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Criar novos prontuários.
     */
    public function create(User $user): bool
    {
        return $user->can('medical-record-create') || $user->can('medical-record-create-all');
    }

    /**
     * Determine whether the user can create a new version of the medical record.
     * Only the current version can have a new version created from it.
     * Only the creator of the original record can create new versions.
     */
    public function update(User $user, MedicalRecord $medicalRecord): bool
    {
        // Admin can edit everything
        if ($user->can('medical-record-edit-all')) {
            return true;
        }

        // Professional ONLY if they created it
        if ($user->can('medical-record-edit')) {
            return $medicalRecord->created_by === $user->id;
        }

        return false;
    }

    /**
     * Enviar prontuário para a lixeira (soft delete).
     * Profissionais só podem deletar prontuários que ELES criaram.
     */
    public function delete(User $user, MedicalRecord $medicalRecord): bool
    {
        // Admin can delete everything
        if ($user->can('medical-record-delete-all')) {
            return true;
        }

        // Professional ONLY if they created it
        if ($user->can('medical-record-delete')) {
            return $medicalRecord->created_by === $user->id;
        }

        return false;
    }

    /**
     * Visualizar a lixeira de prontuários.
     */
    public function viewTrash(User $user): bool
    {
        // Only admin can view trash
        return $user->can('medical-record-list-all');
    }

    /**
     * Restaurar um prontuário.
     */
    public function restore(User $user, MedicalRecord $medicalRecord): bool
    {
        // Admin can restore everything
        if ($user->can('medical-record-edit-all')) {
            return true;
        }

        // Professional ONLY if they created it
        if ($user->can('medical-record-edit')) {
            return $medicalRecord->created_by === $user->id;
        }

        return false;
    }

    /**
     * Forçar exclusão permanente.
     */
    public function forceDelete(User $user, MedicalRecord $medicalRecord): bool
    {
        return $user->can('medical-record-delete-all');
    }
}

