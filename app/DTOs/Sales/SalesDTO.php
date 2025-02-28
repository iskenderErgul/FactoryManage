<?php

namespace App\DTOs\Sales;

use App\Http\Requests\Sales\SalesRequest;
use Carbon\Carbon;

class SalesDTO
{
    public int $customer_id;
    public string $sale_date;
    public array $products;
    public ?string $paymentType;
    public ?float $partialPayment = null;

    public static function buildFromRequest(SalesRequest $request): self
    {
        $storeSalesDTO = new self();
        $storeSalesDTO->customer_id = $request->customer_id;
        $storeSalesDTO->sale_date =  Carbon::parse($request->sale_date)->setTimezone('Europe/Istanbul')->format('Y-m-d');
        $storeSalesDTO->products = $request->products;
        $storeSalesDTO->paymentType = $request->paymentType;
        $storeSalesDTO->partialPayment = $request->partialPayment;

        return $storeSalesDTO;

    }
}
