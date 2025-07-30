<?php

namespace App\Http\Controllers\Production;

use App\Domains\Production\Repositories\ProductionRepository;
use App\DTOs\Production\StoreProductionDTO;
use App\DTOs\Production\UpdateProductionDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Production\StoreByAdminProductionRequest;
use App\Http\Requests\Production\StoreByWorkerProductionRequest;
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
        /**
         * Tüm üretimleri döner.
         *
         * @return JsonResponse
         */
        public function getAllProductions(): JsonResponse
        {
            return $this->productionRepository->getAllProductions();
        }
        /**
         * Tüm üretim loglarını döner.
         *
         * @return JsonResponse
         */
        public function getAllProductionLogs(): JsonResponse
        {
            return $this->productionRepository->getAllProductionLogs();
        }

        /**
         * İşçi tarafından yeni bir üretim kaydeder.
         *
         * @param StoreByWorkerProductionRequest $request
         * @return JsonResponse
         */
        public function storeByWorker(StoreByWorkerProductionRequest $request): JsonResponse
        {
            return $this->productionRepository->storeByWorker($request);
        }

        /**
         * Admin tarafından yeni bir üretim kaydeder.
         *
         * @param StoreByAdminProductionRequest $request
         * @return JsonResponse
         */
        public function storeByAdmin(StoreByAdminProductionRequest $request): JsonResponse
        {
            return $this->productionRepository->storeByAdmin(StoreProductionDTO::buildFromRequest($request));
        }

        /**
         * Belirtilen üretimi günceller.
         *
         * @param UpdateProductionRequest $request
         * @param int $id
         * @return JsonResponse
         */
        public function update(UpdateProductionRequest $request, $id): JsonResponse
        {
            return $this->productionRepository->update(UpdateProductionDTO::buildFromRequest($request), $id);
        }

        /**
         * Belirtilen üretimi siler.
         *
         * @param int $id
         * @return JsonResponse
         */
        public function destroy($id): JsonResponse
        {
            return $this->productionRepository->destroy($id);
        }

        public function getCurrentShift(Request $request): JsonResponse
        {
            return $this->productionRepository->getCurrentShift($request);
        }

}
