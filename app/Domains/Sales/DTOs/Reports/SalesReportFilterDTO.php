<?php

namespace App\Domains\Sales\DTOs\Reports;

/**
 * SalesReportFilterDTO - Satış rapor filtreleri için DTO
 */
class SalesReportFilterDTO
{
    public function __construct(
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly ?int $customerId = null,
        public readonly ?string $paymentMethod = null,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            startDate: $request->input('start_date'),
            endDate: $request->input('end_date'),
            customerId: $request->input('customer_id'),
            paymentMethod: $request->input('payment_method'),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'customer_id' => $this->customerId,
            'payment_method' => $this->paymentMethod,
        ], fn($value) => $value !== null);
    }
}
