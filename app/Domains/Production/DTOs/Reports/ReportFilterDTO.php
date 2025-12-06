<?php

namespace App\Domains\Production\DTOs\Reports;

/**
 * ReportFilterDTO - Rapor filtreleme için DTO
 * 
 * Bu DTO, rapor sorgularında kullanılan filtreleri merkezi hale getirir.
 * Immutable yapıda readonly properties kullanır.
 */
class ReportFilterDTO
{
    public function __construct(
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly ?int $productId = null,
        public readonly ?int $userId = null,
        public readonly ?int $machineId = null,
        public readonly ?int $shiftId = null,
    ) {}

    /**
     * Request'ten DTO oluştur
     * 
     * @param \Illuminate\Http\Request $request
     * @return self
     */
    public static function fromRequest($request): self
    {
        return new self(
            startDate: $request->input('start_date'),
            endDate: $request->input('end_date'),
            productId: $request->input('product_id'),
            userId: $request->input('user_id'),
            machineId: $request->input('machine_id'),
            shiftId: $request->input('shift_id'),
        );
    }

    /**
     * Array'den DTO oluştur
     * 
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            startDate: $data['start_date'],
            endDate: $data['end_date'],
            productId: $data['product_id'] ?? null,
            userId: $data['user_id'] ?? null,
            machineId: $data['machine_id'] ?? null,
            shiftId: $data['shift_id'] ?? null,
        );
    }

    /**
     * Filtreleri array olarak döner (null değerler hariç)
     * 
     * @return array
     */
    public function toArray(): array
    {
        return array_filter([
            'product_id' => $this->productId,
            'user_id' => $this->userId,
            'machine_id' => $this->machineId,
            'shift_id' => $this->shiftId,
        ], fn($value) => $value !== null);
    }

    /**
     * Tüm değerleri array olarak döner (null değerler dahil)
     * 
     * @return array
     */
    public function toFullArray(): array
    {
        return [
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'product_id' => $this->productId,
            'user_id' => $this->userId,
            'machine_id' => $this->machineId,
            'shift_id' => $this->shiftId,
        ];
    }

    /**
     * Herhangi bir filtre var mı?
     * 
     * @return bool
     */
    public function hasFilters(): bool
    {
        return !empty($this->toArray());
    }
}
