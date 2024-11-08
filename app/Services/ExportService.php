<?php

namespace App\Services;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportService implements FromArray, WithHeadings
{

    protected $data;
    protected $columns;

    public function __construct($data, $columns)
    {
        $this->data = $data;
        $this->columns = $columns;
    }

    public function array(): array
    {
        return $this->data->map(function ($item) {
            return collect($item)->only($this->columns)->toArray();
        })->toArray();
    }

    public function headings(): array
    {
        return $this->columns;
    }

    public function export(string $fileName): BinaryFileResponse
    {
        return Excel::download($this, $fileName);
    }
}
