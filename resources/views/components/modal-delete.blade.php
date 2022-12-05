
<button class="btn btn-warning"  onclick="event.preventDefault(); document.getElementById('delete-form').submit();">
    <i class="bi bi-trash3"></i> {{ __('Remover') }}
</button>

<form id="delete-form" action="{{ route($name.'.destroy', $id) }}" method="POST" style="display: none;">
    @csrf
    @method('delete')
</form>
