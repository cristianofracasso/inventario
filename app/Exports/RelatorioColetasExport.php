<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RelatorioColetasExport implements WithMultipleSheets
{
    protected $relatorioPorArea;
    protected $totaisPorGrupo;
    protected $grupos;

    public function __construct($relatorioPorArea, $totaisPorGrupo, $grupos)
    {
        $this->relatorioPorArea = $relatorioPorArea;
        $this->totaisPorGrupo = $totaisPorGrupo;
        $this->grupos = $grupos;
    }

    /**
     * Retorna várias abas (sheets) para o Excel, uma para cada área.
     */
    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->relatorioPorArea as $area => $dadosArea) {
            $sheets[] = new RelatorioColetasSheet($area, $dadosArea, $this->totaisPorGrupo, $this->grupos);
        }

        return $sheets;
    }
}

class RelatorioColetasSheet implements FromArray, WithHeadings, WithTitle
{
    protected $area;
    protected $dadosArea;
    protected $totaisPorGrupo;
    protected $grupos;

    public function __construct($area, $dadosArea, $totaisPorGrupo, $grupos)
    {
        $this->area = $area;
        $this->dadosArea = $dadosArea;
        $this->totaisPorGrupo = $totaisPorGrupo;
        $this->grupos = $grupos;
    }

    /**
     * Retorna os dados para a aba atual.
     */
    public function array(): array
    {
        $dados = [];

        // Adiciona os dados dos SKUs
        foreach ($this->dadosArea as $dadosSku) {
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

    /**
     * Retorna os cabeçalhos da planilha.
     */
    public function headings(): array
    {
        $cabecalhos = ['SKU'];
        foreach ($this->grupos as $grupo) {
            $cabecalhos[] = $grupo;
        }
        return $cabecalhos;
    }

    /**
     * Define o título da aba.
     */
    public function title(): string
    {
        return $this->area;
    }
}