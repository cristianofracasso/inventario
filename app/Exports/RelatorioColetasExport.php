<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RelatorioColetasExport implements FromArray, WithHeadings
{
    protected $relatorio;
    protected $totaisPorGrupo;
    protected $grupos;

    public function __construct($relatorio, $totaisPorGrupo, $grupos)
    {
        $this->relatorio = $relatorio;
        $this->totaisPorGrupo = $totaisPorGrupo;
        $this->grupos = $grupos;
    }

    public function array(): array
    {
        // Prepara os dados para exportação
        $dados = [];
        foreach ($this->relatorio as $dadosSku) {
            $linha = ['SKU' => $dadosSku['sku']];
            foreach ($this->grupos as $grupo) {
                $linha[$grupo] = $dadosSku[$grupo] ?? 0;
            }
            $dados[] = $linha;
        }

        // Adiciona a linha de totais
        $linhaTotais = ['SKU' => 'Total'];
        foreach ($this->grupos as $grupo) {
            $linhaTotais[$grupo] = $this->totaisPorGrupo[$grupo] ?? 0;
        }
        $dados[] = $linhaTotais;

        return $dados;
    }

    public function headings(): array
    {
        // Define os cabeçalhos da planilha
        $cabecalhos = ['SKU'];
        foreach ($this->grupos as $grupo) {
            $cabecalhos[] = $grupo;
        }
        return $cabecalhos;
    }
}