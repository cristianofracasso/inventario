@extends('layouts.app')

@section('title', 'Relatório de Coletas')

@section('content')
    <h1>Relatório de Coletas</h1>

    <!-- Filtro de contagem e grupo -->
    <form method="GET" action="{{ route('relatorio.lista') }}" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="contagem">Contagem:</label>
                <input type="number" name="contagem" id="contagem" value="{{ request('contagem') }}" min="1" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="grupo">Grupo:</label>
                <select name="grupo" id="grupo" class="form-control">
                    <option value="">Todos os Grupos</option>
                    @foreach($grupos as $grupo)
                        <option value="{{ $grupo }}" {{ request('grupo') == $grupo ? 'selected' : '' }}>{{ $grupo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 align-self-end">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="{{ route('relatorio.lista') }}" class="btn btn-secondary">Limpar Filtros</a>
            </div>
        </div>
    </form>

    <!-- Tabela de Coletas -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Grupo</th>
                <th>Contagem</th>
                <th>Custo</th>
                <th>Data da Coleta</th>
            </tr>
        </thead>
        <tbody>
            @foreach($coletas as $coleta)
                <tr>
                    <td>{{ $coleta->sku }}</td>
                    <td>{{ $coleta->grupo }}</td>
                    <td>{{ $coleta->contagem }}</td>
                    <td>{{ number_format($coleta->custo, 2, ',', '.') }}</td>
                    <td>{{ $coleta->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
            <!-- Linha de totais -->
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td><strong>{{ number_format($totalCusto, 2, ',', '.') }}</strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <!-- Botão de exportação -->
   <a href="{{ route('relatorio.exportar.lista', [
    'contagem' => request('contagem') ?: 'todos',
    'grupo' => request('grupo') ?: 'todos'
]) }}" class="btn btn-success">
    <i class="fas fa-file-excel"></i> Exportar para Excel
</a>
@endsection