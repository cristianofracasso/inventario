@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center">Sistema de inventário</div>

                <div class="card-body text-center">
                 
                    <a href="{{ route('iniciar.coleta') }}" class="btn btn-primary btn-lg">
                        Iniciar Coleta  
                    </a>
<br><br><br><br>
                                <a href="{{ route('produtos') }}" class="btn btn-danger btn-lg">
                                        Relatório
                                    </a>

                                    <a href="{{ route('relatorio.lista') }}" class="btn btn-danger btn-lg">
                                        Relatório2
                                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
