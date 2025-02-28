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
    
        // Recupera todas as áreas distintas
        $areas = Coleta::distinct('codigo_palet')->pluck('codigo_palet');
    
        // Calcula a quantidade de contagens distintas por grupo
        $resumoContagensPorGrupo = [];
        foreach ($grupos as $grupo) {
            $resumoContagensPorGrupo[$grupo] = Coleta::where('grupo', $grupo)
                ->distinct('contagem')
                ->count('contagem');
        }
    
        // Organiza os dados para o relatório (agrupados por área)
        $relatorioPorArea = [];
        foreach ($areas as $area) {
            // Recupera todos os SKUs únicos para a área e contagem especificada
            $skus = Coleta::where('contagem', $contagem)
                ->where('codigo_palet', $area)
                ->distinct('sku')
                ->pluck('sku');
    
            // Preenche os dados para cada SKU na área atual
            $dadosArea = [];
            foreach ($skus as $sku) {
                $dadosSku = ['sku' => $sku];
                foreach ($grupos as $grupo) {
                    $dadosSku[$grupo] = Coleta::where('contagem', $contagem)
                        ->where('codigo_palet', $area)
                        ->where('sku', $sku)
                        ->where('grupo', $grupo)
                        ->count();
                }
                $dadosArea[] = $dadosSku;
            }
    
            // Adiciona os dados da área ao relatório
            $relatorioPorArea[$area] = $dadosArea;
        }
    
        // Calcula os totais de coletas por grupo
        $totaisPorGrupo = [];
        foreach ($grupos as $grupo) {
            $totaisPorGrupo[$grupo] = Coleta::where('contagem', $contagem)
                ->where('grupo', $grupo)
                ->count();
        }
    
        return view('produtos', compact('relatorioPorArea', 'contagem', 'grupos', 'resumoContagensPorGrupo', 'totaisPorGrupo', 'areas'));
    }

    public function divergencias(Request $request)
    {
        // Filtro inicial por contagem
        $contagem = $request->input('contagem', 1);
    
        // Recupera todos os grupos distintos
        $todosGrupos = Coleta::distinct('grupo')->pluck('grupo');
        
        // Filtro dos grupos selecionados
        $gruposSelecionados = $request->input('grupos', $todosGrupos->toArray());
        // Verifica se é um array, caso contrário, converte
        if (!is_array($gruposSelecionados)) {
            $gruposSelecionados = [$gruposSelecionados];
        }
        
        // Filtra apenas os grupos selecionados
        $grupos = $todosGrupos->filter(function($grupo) use ($gruposSelecionados) {
            return in_array($grupo, $gruposSelecionados);
        });
    
        // Recupera todas as áreas distintas
        $areas = Coleta::distinct('codigo_palet')->pluck('codigo_palet');
    
        // Calcula a quantidade de contagens distintas por grupo
        $resumoContagensPorGrupo = [];
        foreach ($grupos as $grupo) {
            $resumoContagensPorGrupo[$grupo] = Coleta::where('grupo', $grupo)
                ->distinct('contagem')
                ->count('contagem');
        }
    
        // Verifica se há pelo menos 2 grupos selecionados para comparar
        $relatorioDivergencias = [];
        if (count($grupos) >= 2) {
            // Organiza os dados para o relatório (agrupados por área)
            foreach ($areas as $area) {
                // Recupera todos os SKUs únicos para a área e contagem especificada
                $skus = Coleta::where('contagem', $contagem)
                    ->where('codigo_palet', $area)
                    ->whereIn('grupo', $grupos)
                    ->distinct('sku')
                    ->pluck('sku');
        
                // Preenche os dados para cada SKU na área atual
                $divergenciasArea = [];
                foreach ($skus as $sku) {
                    $dadosSku = ['sku' => $sku];
                    
                    // Coleta os dados de cada grupo
                    foreach ($grupos as $grupo) {
                        $dadosSku[$grupo] = Coleta::where('contagem', $contagem)
                            ->where('codigo_palet', $area)
                            ->where('sku', $sku)
                            ->where('grupo', $grupo)
                            ->count();
                    }
                    
                    // Verifica se há divergência (pelo menos um grupo tem contagem diferente)
                    $valores = array_filter($dadosSku, function($key) {
                        return $key !== 'sku';
                    }, ARRAY_FILTER_USE_KEY);
                    
                    if (count(array_unique($valores)) > 1) {
                        $divergenciasArea[] = $dadosSku;
                    }
                }
        
                // Adiciona os dados da área ao relatório apenas se houver divergências
                if (!empty($divergenciasArea)) {
                    $relatorioDivergencias[$area] = $divergenciasArea;
                }
            }
        }
    
        // Calcula os totais de coletas por grupo
        $totaisPorGrupo = [];
        foreach ($grupos as $grupo) {
            $totaisPorGrupo[$grupo] = Coleta::where('contagem', $contagem)
                ->where('grupo', $grupo)
                ->count();
        }
    
        return view('divergencias', compact('relatorioDivergencias', 'contagem', 'grupos', 'resumoContagensPorGrupo', 'totaisPorGrupo', 'areas', 'todosGrupos', 'gruposSelecionados'));
    }

public function exportarExcel($contagem)
{
    // Recupera todos os grupos e áreas distintos
    $grupos = Coleta::distinct('grupo')->pluck('grupo');
    $areas = Coleta::distinct('codigo_palet')->pluck('codigo_palet');

    // Organiza os dados para o relatório (agrupados por área)
    $relatorioPorArea = [];
    foreach ($areas as $area) {
        // Recupera todos os SKUs únicos para a área e contagem especificada
        $skus = Coleta::where('contagem', $contagem)
            ->where('codigo_palet', $area)
            ->distinct('sku')
            ->pluck('sku');

        // Preenche os dados para cada SKU na área atual
        $dadosArea = [];
        foreach ($skus as $sku) {
            $dadosSku = ['sku' => $sku];
            foreach ($grupos as $grupo) {
                $dadosSku[$grupo] = Coleta::where('contagem', $contagem)
                    ->where('codigo_palet', $area)
                    ->where('sku', $sku)
                    ->where('grupo', $grupo)
                    ->count();
            }
            $dadosArea[] = $dadosSku;
        }

        // Adiciona os dados da área ao relatório
        $relatorioPorArea[$area] = $dadosArea;
    }

    // Calcula os totais de coletas por grupo
    $totaisPorGrupo = [];
    foreach ($grupos as $grupo) {
        $totaisPorGrupo[$grupo] = Coleta::where('contagem', $contagem)
            ->where('grupo', $grupo)
            ->count();
    }

    return Excel::download(new RelatorioColetasExport($relatorioPorArea, $totaisPorGrupo, $grupos, $areas), 'relatorio_coletas.xlsx');
}
}