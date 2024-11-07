<?php

namespace App\Services\Export;

use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExcelExportStrategy implements ExportStrategy
{
    // export fonksiyonu iki argüman almalıdır
    public function export(array $data, array $headers): BinaryFileResponse
    {
        // Excel dosyasını oluşturuyoruz ve başlıkları + verileri kullanıyoruz
        return Excel::download(new DataExport($data, $headers), 'veriler.xlsx');
    }

}
