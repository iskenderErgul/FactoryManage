<?php

namespace App\Http\Controllers\Production;

use App\Domains\Production\Repositories\ProductionRepository;
use App\DTOs\Production\StoreProductionDTO;
use App\DTOs\Production\UpdateProductionDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Production\StoreByAdminProductionRequest;
use App\Http\Requests\Production\UpdateProductionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class ProductionController extends Controller
{

        protected ProductionRepository $productionRepository;

        public function __construct(ProductionRepository $productionRepository)
        {
            $this->productionRepository = $productionRepository;
        }

        public function getAllProductions(): JsonResponse
        {
            return $this->productionRepository->getAllProductions();
        }
        public function getAllProductionLogs(): JsonResponse
        {
            return $this->productionRepository->getAllProductionLogs();
        }
        public function storeByWorker(Request $request): JsonResponse
        {
            return $this->productionRepository->storeByWorker($request);
        }
        public function storeByAdmin(StoreByAdminProductionRequest $request): JsonResponse
        {
            return $this->productionRepository->storeByAdmin(StoreProductionDTO::buildFromRequest($request));
        }
        public function update(UpdateProductionRequest $request, $id): JsonResponse
        {
            return $this->productionRepository->update(UpdateProductionDTO::buildFromRequest($request), $id);
        }
        public function destroy($id): JsonResponse
        {
            return $this->productionRepository->destroy($id);
        }

}
