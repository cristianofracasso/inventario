@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Leitura da Área</div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('validar.palet') }}" method="POST" class="ms-2"  id="formIniciaColeta">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="codigo_palet">Código da Área</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="codigo_palet" 
                                   name="codigo_palet" 
                                   required 
                                   autofocus>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary" onclick="confirmarArea(event)">Avançar</button>
                        </div>
                    </form>
                </div>
                <div style="display: flex; gap: 20px;">

    <div style="flex: 1;">
                        <table class="table">
                            <thead>
                              <tr>
                                    <th>Áreas Coletadas</th>
                                                                  
                              </tr>
                            </thead>
                            <tbody>
                                    <tr>
                                               @foreach($status['areas_contadas'] as $area)

                                            
                                    <td>{{ $area->id }}</td> 
                                 
                                    </tr>@endforeach
                            
                            </tbody>
                        </table>
                    </div>

    <div style="flex: 1;">
                        <table class="table">
                            <thead>
                              <tr>
                                    <th>Áreas Abertas</th>
                                                                  
                              </tr>
                            </thead>
                            <tbody>
                                    <tr>
                                               @foreach($status['areas_em_andamento'] as $area)

                                            
                                    <td>{{ $area->id }}</td> 
                                 
                                    </tr>@endforeach
                            
                            </tbody>
                        </table>
                    </div>
                    <div style="flex: 1;">
                        <table class="table">
                            <thead>
                              <tr>
                                    <th>Áreas Pendentes</th>
                                                                  
                              </tr>
                            </thead>
                            <tbody>
                                    <tr>
                                               @foreach($status['areas_pendentes'] as $area)

                                            
                                    <td>{{ $area->id }}</td> 
                                 
                                    </tr>@endforeach
                            
                            </tbody>
                        </table>
                    </div>
                    </div>
            </div>
        </div>
    </div>
</div>
@endsection
<script>
function confirmarArea(event) {
    event.preventDefault(); // Stop form from submitting immediately
    Swal.fire({
        title: 'Tem certeza?',
        text: "Confirma a Abertura dessa Área?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('formIniciaColeta').submit();
        }
    });
}

// Mensagens de sucesso/erro
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Sucesso!',
            text: "{{ session('success') }}",
            timer: 1000,
            showConfirmButton: false,
            timerProgressBar: true,
            didOpen: () => {
                setTimeout(() => {
                    document.getElementById('codigo_produto').focus();
                }, 1600);
            }
        });
    @endif

    @if(session('error') || $errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Atenção!',
            text: "{{ session('error') ?? $errors->first() }}",
            showConfirmButton: true,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.getConfirmButton().addEventListener('click', () => {
                    document.getElementById('codigo_produto').focus();
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('codigo_produto').focus();
            }
        });
    @endif

    // Remover alertas padrão do Laravel
    document.addEventListener('DOMContentLoaded', function() {
        const alertDiv = document.querySelector('.alert');
        if (alertDiv) {
            alertDiv.style.display = 'none';
        }
        document.getElementById('codigo_produto').focus();
    });
</script>