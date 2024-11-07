<?php

namespace App\Services\Export;

interface ExportStrategy
{
    public function export(array $data,array $headers);
}
