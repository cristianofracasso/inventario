@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Leitura do Produto - Área: {{ session('codigo_palet') }}</div>

                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <form action="{{ route('validar.produto') }}" method="POST" class="flex-grow-1 me-2" id="formProduto">
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

                    <div class="mt-4">
                        <h5>Produtos Coletados nesta Área -> QTD: {{ $totalItens }}</h5>
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
                                            <form action="{{ route('excluir.produto', $coleta->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" 
                                                        class="btn btn-danger" 
                                                        onclick="confirmarExclusao(this)" 
                                                        data-sku="{{ $coleta->sku }}">
                                                    Excluir Produto
                                                </button>
                                            </form>
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
            title: 'Finalizar Coleta',
            text: "Tem certeza que deseja finalizar a coleta desta área?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'Sim, finalizar!',
            cancelButtonText: 'Cancelar',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formFinalizarColeta').submit();
            }
        });
    }

    function confirmarExclusao(button) {
        const sku = button.getAttribute('data-sku');
        
        Swal.fire({
            title: 'Confirmar exclusão',
            text: `Tem certeza que deseja excluir o produto "${sku}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then((result) => {
            if (result.isConfirmed) {
                button.closest('form').submit();
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

    // Auto-submit ao pressionar Enter
    document.getElementById('codigo_produto').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('formProduto').submit();
        }
    });
    
</script>
@endsection