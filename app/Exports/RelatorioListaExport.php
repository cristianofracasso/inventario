<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RelatorioListaExport implements FromCollection, WithHeadings
{
    protected $coletas;

    public function __construct($coletas)
    {
        $this->coletas = $coletas;
    }

    public function collection()
    {
        return $this->coletas;
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