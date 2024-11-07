<?php

namespace App\Services\Export;

class ExportManager
{
    protected $strategy;

    public function __construct(ExportStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public function export(array $data, array $headers)
    {
        return $this->strategy->export($data,$headers);
    }
}
