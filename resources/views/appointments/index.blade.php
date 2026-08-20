@extends('layouts.app')

@section('title')
    Agendamentos
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-calendar-check"></i> Agendamentos
    </li>
@endsection

@section('content')

    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('appointments.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">
                        <i class="bi bi-search"></i> Buscar
                    </label>
                    <input type="text"
                           class="form-control"
                           id="search"
                           name="search"
                           placeholder="Paciente, contato, profissional ou especialidade..."
                           value="{{ request('search') }}">
                </div>

                @can('appointment-list-all')
                    <div class="col-md-2">
                        <label for="situation" class="form-label">Situação</label>
                        <select class="form-select" id="situation" name="situation">
                            <option value="">Todas</option>
                            @foreach(\App\Models\Appointment::SITUATION as $key => $label)
                                <option value="{{ $key }}" @selected(request('situation') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endcan

                <div class="col-md-2">
                    <label for="from" class="form-label">De</label>
                    <input type="date" class="form-control" id="from" name="from" value="{{ request('from') }}">
                </div>

                <div class="col-md-2">
                    <label for="to" class="form-label">Até</label>
                    <input type="date" class="form-control" id="to" name="to" value="{{ request('to') }}">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        @if(request()->hasAny(['search', 'situation', 'from', 'to']))
                            <a href="{{ route('appointments.index') }}" class="btn btn-secondary" title="Limpar filtros">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @cannot('appointment-list-all')
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Estes são os seus agendamentos já confirmados pela clínica.
        </div>
    @endcannot

    @if ($appointments->isEmpty())
        <div class="alert alert-warning">
            <i class="bi bi-calendar-x"></i> Nenhum agendamento encontrado.
        </div>
    @else
        <div class="card">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>DATA / HORA</th>
                            <th>PACIENTE</th>
                            <th>PROFISSIONAL</th>
                            <th>ESPECIALIDADE</th>
                            @can('appointment-list-all')
                                <th>CONTATO</th>
                                <th>SITUAÇÃO</th>
                                <th class="text-center">AÇÕES</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($appointments as $appointment)
                            <tr>
                                <td>
                                    <strong>{{ $appointment->starts_at?->format('d/m/Y') }}</strong>
                                    <br>
                                    <span class="text-muted">{{ $appointment->starts_at?->format('H:i') }}</span>
                                </td>
                                <td>
                                    {{ $appointment->patient_name ?? '—' }}
                                    @if($appointment->reason)
                                        <br><small class="text-muted">{{ $appointment->reason }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($appointment->professional)
                                        {{ $appointment->professional->user->first()?->name ?? $appointment->professional_raw }}
                                    @elseif($appointment->professional_raw)
                                        <span class="text-muted" title="Nome informado no agendamento, ainda não vinculado ao cadastro">
                                            {{ $appointment->professional_raw }} <i class="bi bi-question-circle"></i>
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $appointment->specialty_raw ?? '—' }}</td>

                                @can('appointment-list-all')
                                    <td>
                                        @if($appointment->patient_phone)
                                            <i class="bi bi-telephone"></i> {{ $appointment->patient_phone }}<br>
                                        @endif
                                        @if($appointment->patient_email)
                                            <small class="text-muted">{{ $appointment->patient_email }}</small>
                                        @endif
                                        @unless($appointment->patient_phone || $appointment->patient_email)
                                            —
                                        @endunless
                                    </td>
                                    <td>
                                        @php
                                            $badge = [
                                                'p' => 'bg-warning text-dark',
                                                'c' => 'bg-success',
                                                'r' => 'bg-secondary',
                                                'x' => 'bg-danger',
                                            ][$appointment->situation] ?? 'bg-secondary';
                                        @endphp
                                        <span class="badge {{ $badge }}">{{ $appointment->situation_label }}</span>
                                        @if($appointment->confirmed_at)
                                            <br><small class="text-muted">
                                                {{ $appointment->confirmedBy?->name }}
                                                em {{ $appointment->confirmed_at->format('d/m/Y H:i') }}
                                            </small>
                                        @endif
                                        @if($appointment->canceled_at)
                                            <br><small class="text-muted">
                                                {{ $appointment->canceledBy?->name }}
                                                em {{ $appointment->canceled_at->format('d/m/Y H:i') }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($appointment->situation === \App\Models\Appointment::SITUATION_PENDING)
                                            @can('appointment-confirm')
                                                <form method="POST"
                                                      action="{{ route('appointments.confirm', $appointment) }}"
                                                      class="d-flex gap-1 justify-content-center align-items-center">
                                                    @csrf
                                                    @foreach(request()->only(['search', 'situation', 'from', 'to', 'page']) as $key => $value)
                                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                                    @endforeach
                                                    <select name="professional_id" class="form-select form-select-sm" required style="min-width: 180px;">
                                                        <option value="">Profissional...</option>
                                                        @foreach($professionals as $professional)
                                                            <option value="{{ $professional->id }}"
                                                                @selected(
                                                                    $appointment->professional_raw &&
                                                                    $professional->user->first() &&
                                                                    mb_strtolower($professional->user->first()->name) === mb_strtolower($appointment->professional_raw)
                                                                )>
                                                                {{ $professional->user->first()?->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="btn btn-sm btn-success" title="Confirmar">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                </form>
                                                <form method="POST"
                                                      action="{{ route('appointments.refuse', $appointment) }}"
                                                      class="mt-1">
                                                    @csrf
                                                    @foreach(request()->only(['search', 'situation', 'from', 'to', 'page']) as $key => $value)
                                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                                    @endforeach
                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-secondary"
                                                            title="Recusar"
                                                            onclick="return confirm('Deseja realmente recusar este agendamento?')">
                                                        <i class="bi bi-x-lg"></i> Recusar
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endcan
                                        @elseif($appointment->isConfirmed())
                                            <div class="d-flex flex-column gap-1">
                                                @can('appointment-replace')
                                                    <button type="button"
                                                            class="btn btn-sm btn-primary"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#replaceAppointment{{ $appointment->id }}">
                                                        <i class="bi bi-person-plus"></i> Encaixar paciente
                                                    </button>
                                                @endcan
                                                @can('appointment-cancel')
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#cancelAppointment{{ $appointment->id }}">
                                                        <i class="bi bi-calendar-x"></i> Registrar desistência
                                                    </button>
                                                @endcan
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                @endcan
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $appointments->onEachSide(1)->appends(request()->query())->links() }}
        </div>

        @foreach($appointments->where('situation', \App\Models\Appointment::SITUATION_CONFIRMED) as $appointment)
            @can('appointment-cancel')
                <div class="modal fade" id="cancelAppointment{{ $appointment->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('appointments.cancel', $appointment) }}">
                                @csrf
                                @foreach(request()->only(['search', 'situation', 'from', 'to', 'page']) as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endforeach
                                <div class="modal-header">
                                    <h5 class="modal-title">Registrar desistência</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                </div>
                                <div class="modal-body">
                                    <p>
                                        O evento de <strong>{{ $appointment->patient_name }}</strong>, em
                                        <strong>{{ $appointment->starts_at?->format('d/m/Y') }} às {{ $appointment->starts_at?->format('H:i') }}</strong>,
                                        será removido do Google Calendar e o horário ficará livre.
                                    </p>
                                    <label for="cancel-reason-{{ $appointment->id }}" class="form-label">Motivo da desistência</label>
                                    <textarea id="cancel-reason-{{ $appointment->id }}"
                                              name="cancellation_reason"
                                              class="form-control"
                                              rows="3"
                                              maxlength="1000"></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                                    <button type="submit" class="btn btn-danger">Confirmar desistência</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endcan

            @can('appointment-replace')
                <div class="modal fade" id="replaceAppointment{{ $appointment->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('appointments.replace', $appointment) }}">
                                @csrf
                                @foreach(request()->only(['search', 'situation', 'from', 'to', 'page']) as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endforeach
                                <div class="modal-header">
                                    <h5 class="modal-title">Encaixar outro paciente</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-info">
                                        O evento atual será atualizado no Google Calendar, mantendo
                                        {{ $appointment->starts_at?->format('d/m/Y') }} às {{ $appointment->starts_at?->format('H:i') }}.
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Nome do novo paciente</label>
                                            <input type="text" name="patient_name" class="form-control" maxlength="255" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">WhatsApp</label>
                                            <input type="text" name="patient_phone" class="form-control" maxlength="30" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">E-mail</label>
                                            <input type="email" name="patient_email" class="form-control" maxlength="255">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Profissional</label>
                                            <select name="professional_id" class="form-select" required>
                                                <option value="">Selecione...</option>
                                                @foreach($professionals as $professional)
                                                    <option value="{{ $professional->id }}" @selected($professional->id === $appointment->professional_id)>
                                                        {{ $professional->user->first()?->name }}
                                                        @if($professional->specialty) — {{ $professional->specialty->name }} @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Motivo da consulta</label>
                                            <input type="text" name="reason" class="form-control" maxlength="2000">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Motivo da desistência do paciente anterior</label>
                                            <textarea name="cancellation_reason" class="form-control" rows="2" maxlength="1000"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                                    <button type="submit" class="btn btn-primary">Confirmar encaixe</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endcan
        @endforeach
    @endif

@endsection
