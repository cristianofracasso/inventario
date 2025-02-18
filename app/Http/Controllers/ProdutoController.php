<?php
namespace App\Http\Controllers;

use App\Exports\RelatorioColetasExport;
use App\Models\Coleta;
use App\Models\Grupo;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ProdutoController extends Controller
{
    public function index(Request $request)
{
    // Filtro inicial por contagem
    $contagem = $request->input('contagem', 1);

    // Recupera todos os grupos distintos
    $grupos = Coleta::distinct('grupo')->pluck('grupo');

    // Calcula a quantidade de contagens distintas por grupo
    $resumoContagensPorGrupo = [];
    foreach ($grupos as $grupo) {
        $resumoContagensPorGrupo[$grupo] = Coleta::where('grupo', $grupo)
            ->distinct('contagem')
            ->count('contagem');
    }

    // Recupera todos os SKUs únicos para a contagem especificada
    $skus = Coleta::where('contagem', $contagem)
        ->distinct('sku')
        ->pluck('sku');

    // Organiza os dados para o relatório (com filtro de contagem)
    $relatorio = [];
    foreach ($skus as $sku) {
        $dadosSku = ['sku' => $sku];
        foreach ($grupos as $grupo) {
            $dadosSku[$grupo] = Coleta::where('contagem', $contagem)
                ->where('sku', $sku)
                ->where('grupo', $grupo)
                ->count();
        }
        $relatorio[] = $dadosSku;
    }

    // Calcula os totais de coletas por grupo
    $totaisPorGrupo = [];
    foreach ($grupos as $grupo) {
        $totaisPorGrupo[$grupo] = Coleta::where('contagem', $contagem)
            ->where('grupo', $grupo)
            ->count();
    }

    return view('produtos', compact('relatorio', 'contagem', 'grupos', 'resumoContagensPorGrupo', 'totaisPorGrupo'));
}

public function exportarExcel($contagem)
{
    // Recupera os dados do relatório
    $grupos = Coleta::distinct('grupo')->pluck('grupo');
    $relatorio = [];
    $skus = Coleta::where('contagem', $contagem)->distinct('sku')->pluck('sku');
    foreach ($skus as $sku) {
        $dadosSku = ['sku' => $sku];
        foreach ($grupos as $grupo) {
            $dadosSku[$grupo] = Coleta::where('contagem', $contagem)
                ->where('sku', $sku)
                ->where('grupo', $grupo)
                ->count();
        }
        $relatorio[] = $dadosSku;
    }

    // Calcula os totais
    $totaisPorGrupo = [];
    foreach ($grupos as $grupo) {
        $totaisPorGrupo[$grupo] = Coleta::where('contagem', $contagem)
            ->where('grupo', $grupo)
            ->count();
    }

    return Excel::download(new RelatorioColetasExport($relatorio, $totaisPorGrupo, $grupos), 'relatorio_coletas.xlsx');
}
}