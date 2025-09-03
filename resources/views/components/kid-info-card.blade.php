<div class="card shadow-sm">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-2 text-center">
                @if ($kid->photo)
                    <img src="{{ asset($kid->photo) }}" class="rounded-circle img-fluid"
                        style="width: 120px; height: 120px; object-fit: cover;" alt="{{ $kid->name }}">
                @else
                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto text-white"
                        style="width: 120px; height: 120px; font-size: 2.5em;">
                        {{ substr($kid->name, 0, 2) }}
                    </div>
                @endif
            </div>
            <div class="col-md-5">
                <h4 class="mb-1">{{ $kid->name }}</h4>
                <p class="text-muted mb-2">
                    <i class="bi bi-calendar-event"></i>
                    @if (isset($kid->birth_date))
                        {{ $kid->birth_date_formatted }}
                    @endif
                    ({{ $kid->months }} meses)
                </p>
                @if ($kid->responsible)
                    <p class="mb-1">
                        <i class="bi bi-person"></i>
                        <strong>Responsável:</strong>
                        {{ $kid->responsible->name }}
                    </p>
                @endif
                <p class="mb-0">
                    <i class="bi bi-geo-alt"></i>
                    <strong>Endereço:</strong>
                    @if ($kid->responsible && ($kid->responsible->street || $kid->responsible->city))
                        {{ collect([
                            $kid->responsible->street,
                            $kid->responsible->number,
                            $kid->responsible->complement,
                            $kid->responsible->neighborhood,
                            $kid->responsible->city,
                            $kid->responsible->state,
                        ])->filter()->join(', ') }}
                        @if ($kid->responsible->postal_code)
                            <br><small class="text-muted">CEP: {{ $kid->responsible->postal_code }}</small>
                        @endif
                    @else
                        <span class="text-muted">Endereço não cadastrado</span>
                    @endif
                </p>
            </div>
            <div class="col-md-5">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-people"></i>
                            Profissionais Responsáveis
                        </h5>
                        @if ($kid->professionals->count() > 0)
                            @foreach ($kid->professionals as $professional)
                                <div class="d-flex align-items-center mb-2">
                                    @if ($professional->user->first() && $professional->user->first()->photo)
                                        <img src="{{ asset('images/users/' . $professional->user->first()->photo) }}"
                                            class="rounded-circle me-2"
                                            style="width: 30px; height: 30px; object-fit: cover;"
                                            alt="{{ $professional->user->first()->name }}">
                                    @else
                                        <div class="rounded-circle me-2 d-flex align-items-center justify-content-center bg-secondary text-white"
                                            style="width: 30px; height: 30px; font-size: 12px;">
                                            {{ $professional->user->first() ? substr($professional->user->first()->name, 0, 2) : 'NA' }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="mb-0">
                                            {{ $professional->user->first() ? $professional->user->first()->name : 'Sem nome' }}
                                        </p>
                                        <small class="text-muted">
                                            {{ $professional->specialty ? $professional->specialty->name : 'Sem especialidade' }}
                                            @if ($professional->pivot && $professional->pivot->is_primary)
                                                <span class="badge bg-primary ms-1">Principal</span>
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted mb-0">Nenhum profissional associado</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
