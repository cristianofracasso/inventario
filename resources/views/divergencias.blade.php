@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Relatório de Divergências - Contagem {{ $contagem }}</h4>
                        </div>
                        <div class="col-md-6 text-right">
                            <form id="exportForm" action="{{ route('divergencias.exportar', $contagem) }}" method="GET" style="display: inline;">
                                <input type="hidden" name="grupos" id="gruposExport" value="{{ json_encode($gruposSelecionados) }}">
                                <button type="submit" class="btn btn-success">Exportar Excel</button>
                            </form>
                            <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#resumoContagensCollapse">
                                Ver Resumo
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filtros -->
                    <div class="mb-4">
                        <form method="GET" action="{{ route('divergencias') }}" id="filtroForm">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="contagem">Contagem:</label>
                                        <select name="contagem" id="contagem" class="form-control">
                                            @for ($i = 1; $i <= 10; $i++)
                                                <option value="{{ $i }}" {{ $contagem == $i ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label>Grupos para comparação:</label>
                                        <div class="d-flex flex-wrap">
                                            @foreach($todosGrupos as $grupo)
                                                <div class="custom-control custom-checkbox mr-3 mb-2">
                                                    <input type="checkbox" class="custom-control-input grupo-checkbox" 
                                                           name="grupos[]" id="grupo_{{ $grupo }}" 
                                                           value="{{ $grupo }}" 
                                                           {{ in_array($grupo, $gruposSelecionados) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="grupo_{{ $grupo }}">{{ $grupo }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary mb-3">Filtrar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Alerta se menos de 2 grupos selecionados -->
                    @if(count($grupos) < 2)
                        <div class="alert alert-warning">
                            <h5 class="alert-heading">Selecione pelo menos 2 grupos para comparação</h5>
                            <p>É necessário escolher no mínimo 2 grupos diferentes para identificar divergências.</p>
                        </div>
                    @else
                        <!-- Resumo de Contagens -->
                        <div class="collapse mb-4" id="resumoContagensCollapse">
                            <div class="card">
                                <div class="card-header">Resumo de Contagens por Grupo</div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($resumoContagensPorGrupo as $grupo => $qtdContagens)
                                            <div class="col-md-4 mb-3">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h5 class="card-title">{{ $grupo }}</h5>
                                                        <p class="card-text">{{ $qtdContagens }} contagem(ns) realizadas</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total de Coletas por Grupo -->
                        <div class="mb-4">
                            <h5>Total de Coletas na Contagem {{ $contagem }}</h5>
                            <div class="row">
                                @foreach($totaisPorGrupo as $grupo => $total)
                                    <div class="col-md-3 mb-3">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <h5 class="card-title">{{ $grupo }}</h5>
                                                <p class="card-text display-4">{{ $total }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Verificação se existem divergências -->
                        @if(count($relatorioDivergencias) == 0)
                            <div class="alert alert-success mt-4">
                                <h4 class="alert-heading">Nenhuma divergência encontrada!</h4>
                                <p>Todas as contagens estão consistentes entre os grupos selecionados para a contagem {{ $contagem }}.</p>
                            </div>
                        @else
                            <!-- Relatório de Divergências por Área -->
                            @foreach($relatorioDivergencias as $area => $dadosArea)
                                <div class="mb-4">
                                    <h5 class="bg-light p-2">Área: {{ $area }}</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>SKU</th>
                                                    @foreach($grupos as $grupo)
                                                        <th>{{ $grupo }}</th>
                                                    @endforeach
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($dadosArea as $dadosSku)
                                                    <tr>
                                                        <td>{{ $dadosSku['sku'] }}</td>
                                                        @foreach($grupos as $grupo)
                                                            <td class="text-center">{{ $dadosSku[$grupo] }}</td>
                                                        @endforeach
                                                        <td class="bg-danger text-white text-center">DIVERGÊNCIA</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Script para garantir que pelo menos 2 grupos estejam selecionados
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.grupo-checkbox');
        const form = document.getElementById('filtroForm');
        
        form.addEventListener('submit', function(event) {
            const checked = document.querySelectorAll('.grupo-checkbox:checked');
            if (checked.length < 2) {
                event.preventDefault();
                alert('Selecione pelo menos 2 grupos para comparação!');
            }
        });
        
        // Atualiza o campo oculto no formulário de exportação quando o filtro mudar
        checkboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                updateExportFormGroups();
            });
        });
        
        function updateExportFormGroups() {
            const selectedGroups = [];
            document.querySelectorAll('.grupo-checkbox:checked').forEach(function(checkbox) {
                selectedGroups.push(checkbox.value);
            });
            document.getElementById('gruposExport').value = JSON.stringify(selectedGroups);
        }
    });
</script>
@endsection