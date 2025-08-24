@if(session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        
        @if(session()->has('user_password') && session()->has('user_email'))
            <hr>
            <div class="mt-3">
                <h6><i class="bi bi-key"></i> Dados de Acesso:</h6>
                <p class="mb-1"><strong>Email:</strong> {{ session('user_email') }}</p>
                <p class="mb-0">
                    <strong>Senha Temporária:</strong> 
                    <code id="tempPassword">{{ session('user_password') }}</code>
                    <button type="button" class="btn btn-sm btn-outline-success ms-2" onclick="copyToClipboard()">
                        <i class="bi bi-clipboard"></i> Copiar
                    </button>
                </p>
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i> 
                    Esta senha deve ser alterada no primeiro acesso.
                </small>
            </div>
        @endif
        
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    
    @if(session()->has('user_password'))
        <script>
            function copyToClipboard() {
                const tempPassword = document.getElementById('tempPassword').textContent;
                navigator.clipboard.writeText(tempPassword).then(() => {
                    alert('Senha copiada para a área de transferência!');
                });
            }
        </script>
    @endif
@endif

@if(session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session()->has('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session()->has('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@foreach(session('flash_notification', collect())->toArray() as $message)
    <div class="alert alert-{{ $message['level'] }} alert-dismissible fade show" role="alert">
        {!! $message['message'] !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endforeach
