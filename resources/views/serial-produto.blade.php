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
                                <label for="serial">Número de Série</label>
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
                        <h5>Seriais Coletados - > QTD: {{ $totalSerie }}</h5>
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
                                        <form action="{{ route('excluir.ultimo.serial') }}" method="POST" class="d-inline" id="formExcluirSerial">
                                            @csrf
                                                <input type="hidden" name="serial_id" value="{{ $serial->id }}">
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmarExclusao(this)" data-serial="{{ $serial->serial }}">
                                                Excluir Serial
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

    function confirmarExclusao(button) {
        const serial = button.getAttribute('data-serial');
        
        Swal.fire({
            title: 'Confirmar exclusão',
            text: `Tem certeza que deseja excluir o serial "${serial}"?`,
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
                    document.getElementById('serial').focus();
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
                    document.getElementById('serial').focus();
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('serial').focus();
            }
        });
    @endif

    // Configuração inicial ao carregar a página
    document.addEventListener('DOMContentLoaded', function() {
        const alertDiv = document.querySelector('.alert');
        if (alertDiv) {
            alertDiv.style.display = 'none';
        }
        document.getElementById('serial').focus();
    });

    // Configurar o formulário de serial
    const serialForm = document.querySelector('form[action*="registrar.serial.produto"]');
    const serialInput = document.getElementById('serial');

    // Auto-submit ao pressionar Enter e prevenir input após submit
    serialInput.addEventListener('keypress', function(e) {
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