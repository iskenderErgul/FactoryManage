<?php

namespace App\DTOs\Production;

use App\Http\Requests\Production\UpdateProductionRequest;

class UpdateProductionDTO
{
    public int $id;
    public int $productId;
    public int $quantity;
    public int $machineId;
    public int $shiftId;
    public string $productionDate;
    public int $workerId;

    public static function buildFromRequest(UpdateProductionRequest $request): self
    {
        $updateProductionDTO = new self();
        $updateProductionDTO->id = $request->input('id');
        $updateProductionDTO->product_id = $request->input('product_id');
        $updateProductionDTO->quantity = $request->input('quantity');
        $updateProductionDTO->machine_id = $request->input('machine_id');
        $updateProductionDTO->shift_id = $request->input('shift_id');
        $updateProductionDTO->production_date = $request->input('production_date');
        $updateProductionDTO->worker_id = $request->input('worker_id');

        return $updateProductionDTO;
    }
}
