@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    Coleta de Seriais - Área: {{ session('codigo_palet') }} - Produto: {{ session('codigo_produto') }}
                </div>

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

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <form action="{{ route('registrar.serial.produto') }}" method="POST" class="flex-grow-1 me-2">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="serial">Número de Série6</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="serial" 
                                       name="serial" 
                                       required 
                                       autofocus>
                            </div>
                        </form>

                        <form action="{{ route('encerrar.produto') }}" method="POST" class="ms-2" id="formEncerrarProduto">
                            @csrf
                            <button type="button" class="btn btn-success" onclick="confirmarEncerramento()">
                                Encerrar Produto
                            </button>
                        </form>
                    </div>

                    <div class="mt-4">
                        <h5>Seriais Coletados</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Serial</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($seriais as $serial)
                                <tr>
                                    <td>{{ $serial->serial }}</td>
                                    <td>
                                        @if($loop->first)
                                            <button type="button" 
                                                    class="btn btn-danger"
                                                    onclick="confirmarExclusao({{ $serial->id }})">
                                                Excluir
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmarEncerramento() {
        Swal.fire({
            title: 'Tem certeza?',
            text: "Você deseja encerrar a coleta deste produto?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, encerrar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formEncerrarProduto').submit();
            }
        });
    }

    function confirmarExclusao(id) {
    Swal.fire({
        title: 'Tem certeza?',
        text: "Você não poderá reverter isso!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            
            // Usando uma variável JavaScript com a rota do Laravel
            const deleteUrl = @json(route('coleta.destroy', ['coleta' => ':id']));
            form.action = deleteUrl.replace(':id', id);
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
}


    // Mensagens de sucesso/erro
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Sucesso!',
            text: "{{ session('success') }}",
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: "{{ session('error') }}",
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    // Auto-focus no input de serial
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('serial').focus();
    });
</script>
@endsection