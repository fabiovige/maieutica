<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppointmentPolicy
{
    use HandlesAuthorization;

    /**
     * Acessar a tela de agendamentos.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('appointment-list') || $user->can('appointment-list-all');
    }

    /**
     * Visualizar um agendamento especifico.
     */
    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->can('appointment-list-all')) {
            return true;
        }

        if (! $user->can('appointment-list')) {
            return false;
        }

        // Profissional so enxerga o proprio agendamento, e apenas depois da
        // confirmacao da clinica.
        $professional = $user->professional->first();

        return $appointment->isConfirmed()
            && $professional
            && $appointment->professional_id === $professional->id;
    }

    /**
     * Confirmar ou recusar um agendamento (recepcao da clinica).
     */
    public function confirm(User $user, Appointment $appointment): bool
    {
        return $user->can('appointment-confirm');
    }
}
