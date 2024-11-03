<?php

namespace App\DTOs\Sales;

use App\Http\Requests\Sales\SalesRequest;

class SalesDTO
{
    public int $customer_id;
    public string $sale_date;
    public array $products;

    public static function buildFromRequest(SalesRequest $request): self
    {
        $storeSalesDTO = new self();
        $storeSalesDTO->customer_id = $request->customer_id;
        $storeSalesDTO->sale_date = $request->sale_date;
        $storeSalesDTO->products = $request->products;
        return $storeSalesDTO;
    }
}
