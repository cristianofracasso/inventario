@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Leitura do Produto - Área: {{ session('codigo_palet') }}</div>

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
                        <form action="{{ route('validar.produto') }}" method="POST" class="flex-grow-1 me-2">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="codigo_produto">Código do Produto</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="codigo_produto" 
                                       name="codigo_produto" 
                                       required 
                                       autofocus>
                            </div>
                        </form>

                        <form action="{{ route('finalizar.coleta') }}" method="POST" class="ms-2" id="formFinalizarColeta">
                            @csrf
                            <button type="button" class="btn btn-success" onclick="confirmarFinalizacao()">
                                Finalizar Coleta
                            </button>
                        </form>
                    </div>

                    <!-- Lista de produtos coletados no palet atual -->
                    <div class="mt-4">
                        <h5>Produtos Coletados nesta Área</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Código Produto</th>
                                    <th>Serial</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($itens as $coleta)
                                <tr>
                                    <td>{{ $coleta->sku }}</td>
                                    <td>{{ $coleta->serial }}</td>
                                    <td>
                                        @if($loop->first)
                                            <button type="button" 
                                                    class="btn btn-danger"
                                                    onclick="confirmarExclusao({{ $coleta->id }})">
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
    function confirmarFinalizacao() {
        Swal.fire({
            title: 'Tem certeza?',
            text: "Você deseja finalizar a coleta?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, finalizar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formFinalizarColeta').submit();
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
                form.action = '/coleta/' + id;
                
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

    // Mensagens de sucesso/erro
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Sucesso!',
            text: "{{ session('success') }}",
            timer: 800,
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

    // Auto-focus no input de código do produto
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('codigo_produto').focus();
    });
</script>
@endsection