<?php

namespace App\Services\Export;

use Maatwebsite\Excel\Concerns\FromArray;

class DataExport implements FromArray
{
    protected $data;
    protected $headers;

    public function __construct(array $data, array $headers)
    {
        $this->data = $data;
        $this->headers = $headers;
    }

    public function array(): array
    {
        // Başlıkları ve veriyi birleştiriyoruz
        return array_merge([$this->headers], $this->data);
    }
}
