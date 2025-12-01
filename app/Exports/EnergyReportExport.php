<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EnergyReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Sr No',
            'Date',
            'kWh',
            'Today m_recharge_amount',
            'Amount Cut',
            'Recharge Amount'
        ];
    }

    public function map($row): array
    {
        static $i = 1;

        return [
            $i++,
            \Carbon\Carbon::parse($row->setting_created_at)->format('d-m-Y'),
            $row->kwh,
            $row->m_recharge_amount,
            number_format($row->amount_cut, 2),
            $row->recharge_amount
        ];
    }
}
