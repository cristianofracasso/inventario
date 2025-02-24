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

    <!-- Removido o d-flex e ajustado para colocar os botões em bloco -->
    <div>
        <!-- Botão de Cadastro de Produtos -->
        <a href="{{ route('cadastrar.produto') }}" class="btn btn-primary mb-2 w-100">
            Cadastrar Produto
        </a>
        
        <!-- Botão de Finalizar Coleta -->
        <form action="{{ route('finalizar.coleta') }}" method="POST" id="formFinalizarColeta">
            @csrf
            <button type="button" class="btn btn-success w-100" onclick="confirmarFinalizacao()">
                Finalizar Coleta
            </button>
        </form>
    </div>
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

<!-- Modal para Cadastro de Produto -->
<div class="modal fade" id="cadastrarProdutoModal" tabindex="-1" aria-labelledby="cadastrarProdutoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cadastrarProdutoModalLabel">Cadastrar Novo Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('salvar.produto') }}" method="POST" id="formCadastrarProduto">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="sku">Código do Produto</label>
                        <input type="text" class="form-control" id="sku" name="sku" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="quantidade">Quantidade</label>
                        <input type="number" class="form-control" id="quantidade" name="quantidade" required min="1" value="1">
                    </div>
                  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarProduto()">Salvar Produto</button>
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

    // Função para abrir modal de cadastro ao clicar no botão
    document.addEventListener('DOMContentLoaded', function() {
        const cadastrarBtn = document.querySelector('a[href="{{ route("cadastrar.produto") }}"]');
        if (cadastrarBtn) {
            cadastrarBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const cadastrarModal = new bootstrap.Modal(document.getElementById('cadastrarProdutoModal'));
                cadastrarModal.show();
            });
        }
    });

    // Função para salvar o produto
    function salvarProduto() {
        Swal.fire({
            title: 'Confirmar cadastro',
            text: "Deseja cadastrar este produto?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'Sim, cadastrar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formCadastrarProduto').submit();
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

    // Função para desabilitar completamente o input
    function disableInput(input) {
        input.disabled = true;  // Desabilita completamente o input
        input.style.backgroundColor = '#e9ecef';
        
        // Previne qualquer evento de teclado
        input.addEventListener('keydown', function(e) {
            e.preventDefault();
            return false;
        }, true);
        
        // Previne colagem de texto
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            return false;
        }, true);
        
        // Previne eventos de input
        input.addEventListener('input', function(e) {
            e.preventDefault();
            return false;
        }, true);
    }

    // Configurar o formulário de produto
    const formProduto = document.getElementById('formProduto');
    const inputProduto = document.getElementById('codigo_produto');

    // Auto-submit ao pressionar Enter
    inputProduto.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const form = this.form;
            // Primeiro submete o formulário
            form.submit();
            // Depois desabilita o input
            setTimeout(() => {
                disableInput(this);
            }, 50);
        }
    });
</script>
@endsection