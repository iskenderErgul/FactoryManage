<?php

namespace App\DTOs\Production;



use App\Http\Requests\Production\StoreByAdminProductionRequest;

class StoreProductionDTO
{
    public int $productId;
    public int $quantity;
    public int $machineId;
    public int $shiftId;
    public string $productionDate;
    public int $workerId;

    public static function buildFromRequest(StoreByAdminProductionRequest $request): self
    {
        $storeProductionDTO = new self();
        $storeProductionDTO->productId = $request->input('product_id');
        $storeProductionDTO->quantity = $request->input('quantity');
        $storeProductionDTO->machineId = $request->input('machine_id');
        $storeProductionDTO->shiftId = $request->input('shift_id');
        $storeProductionDTO->productionDate = $request->input('production_date');
        $storeProductionDTO->workerId = $request->input('worker_id');

        return $storeProductionDTO;
    }
}
