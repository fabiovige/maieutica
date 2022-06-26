@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Papéis</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cadastrar</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="row">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3>Cadastrar</h3>
        </div>
    </div>

    <div class="row mt-2">
       <div class="col-12">
           <form action="{{route('roles.store')}}"  method="post">
               @csrf
               @method('POST')

               <div class="card">
                   <div class="card-header">
                       Cadasrar novo papél
                   </div>
                   <div class="card-body">
                       <div class="form-group">
                           <label>Nome do Papél</label>
                           <input type="text" class="form-control @error('name') is-invalid @enderror"
                                  name="name"
                                  value="{{old('name')}}">

                           @error('name')
                           <div class="invalid-feedback">{{$message}}</div>
                           @enderror
                       </div>
                   </div>
                   <div class="card-footer">
                       <button class="btn btn-success">
                           <i class="bi bi-plus-circle"></i> Cadastrar</button>
                   </div>
               </div>

           </form>
       </div>
    </div>

@endsection
