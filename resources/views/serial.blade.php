@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    Leitura do Serial - Produto: {{ session('codigo_produto') }}
                </div>

                <div class="card-body">
                    <!-- Exibe mensagens de sucesso -->
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Exibe erros de validação -->
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Formulário de leitura do serial -->
                    <form action="{{ route('registrar.serial') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="serial">Serial do Produto</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="serial"
                                   name="serial" 
                                   required 
                                   autofocus>
                        </div>
                        <div>
                          <!--  <button type="submit" class="btn btn-primary">Registrar Serial</button> -->
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection