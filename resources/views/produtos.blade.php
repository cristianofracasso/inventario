@extends('layouts.app')

@section('title', 'Relatório de SKUs por Grupo')

@section('content')
    <h1>Relatório de SKUs por Grupo (Contagem: {{ $contagem }})</h1>
<div class="mb-4">
    <a href="{{ route('relatorio.exportar', ['contagem' => $contagem]) }}" class="btn btn-success">
        <i class="fas fa-file-excel"></i> Exportar para Excel
    </a>
</div>
    <!-- Resumo das contagens distintas por grupo -->
    <div class="mb-4">
        <h3>Resumo das Contagens Distintas por Grupo:</h3>
        <ul>
            @foreach($resumoContagensPorGrupo as $grupo => $total)
                <li><strong>{{ $grupo }}:</strong> {{ $total }} contagens distintas</li>
            @endforeach
        </ul>
    </div>

    <!-- Filtro por contagem -->
    <form method="GET" action="{{ route('produtos') }}" class="mb-4">
        <label for="contagem">Filtrar por Contagem:</label>
        <input type="number" name="contagem" id="contagem" value="{{ $contagem }}" min="1">
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>

    <!-- Tabela de Relatório -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>SKU</th>
                @foreach($grupos as $grupo)
                    <th>{{ $grupo }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($relatorio as $dadosSku)
                <tr>
                    <td>{{ $dadosSku['sku'] }}</td>
                    @foreach($grupos as $grupo)
                        <td>{{ $dadosSku[$grupo] ?? 0 }}</td>
                    @endforeach
                </tr>
            @endforeach
            <!-- Linha de totais -->
            <tr>
                <td><strong>Total</strong></td>
                @foreach($grupos as $grupo)
                    <td><strong>{{ $totaisPorGrupo[$grupo] ?? 0 }}</strong></td>
                @endforeach
            </tr>
        </tbody>
    </table>
@endsection