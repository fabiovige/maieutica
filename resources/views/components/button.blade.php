@if($type=='link')
    <a href="{{ $href }}" class="btn btn-{{ $class }} btn-sm"><i class="bi bi-{{ $icon }}"></i> {{ $name }} </a>
@endif
@if($type=='submit')
    <button type="submit" class="btn btn-{{ $class }} btn-sm"><i class="bi bi-{{ $icon }}"></i> {{ $name  }}</button>
@endif
