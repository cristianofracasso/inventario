<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class RelatorioListaExport implements FromCollection, WithHeadings
{
    protected $coletas;

    public function __construct($coletas)
    {
        $this->coletas = $coletas;
    }

    public function collection()
    {
        // Transforma os dados em uma coleção formatada
        return $this->coletas->map(function ($coleta) {
            return [
                'SKU' => $coleta->sku,
                'Grupo' => $coleta->grupo,
                'Contagem' => $coleta->contagem,
                'Custo' => number_format($coleta->custo, 2, ',', '.'),
                'Data da Coleta' => $coleta->created_at->format('d/m/Y H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Grupo',
            'Contagem',
            'Custo',
            'Data da Coleta',
        ];
    }
}