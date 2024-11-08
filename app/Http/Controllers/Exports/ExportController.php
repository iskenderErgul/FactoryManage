<?php

namespace App\Http\Controllers\Exports;

use App\Domains\Export\Repositories\ExportRepository;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    protected ExportRepository $exportRepository;


    public function __construct(ExportRepository $exportRepository)
    {
        $this->exportRepository = $exportRepository;
    }

    public function costsExport(): BinaryFileResponse
    {
        return $this->exportRepository->costsExport();
    }

    /**
     * Production verileri için export işlemi.
     *
     * @return BinaryFileResponse
     */
    public function productionExport(): BinaryFileResponse
    {
        return $this->exportRepository->productionExport();

    }

    /**
     * Sales verileri için export işlemi.
     *
     * @return BinaryFileResponse
     */
    public function salesExport(): BinaryFileResponse
    {
       return $this->exportRepository->salesExport();
    }

    /**
     * SalesProduct verileri için export işlemi.
     *
     * @return BinaryFileResponse
     */
    public function salesProductExport(): BinaryFileResponse
    {
        return $this->exportRepository->salesProductExport();
    }

    /**
     * Pacs verileri için export işlemi.
     *
     * @return BinaryFileResponse
     */
    public function pacsExport(): BinaryFileResponse
    {
       return $this->exportRepository->pacsExport();
    }

    /**
     * Stock Movement verileri için export işlemi.
     *
     * @return BinaryFileResponse
     */
    public function stockMovementExport(): BinaryFileResponse
    {
        return $this->exportRepository->stockMovementExport();
    }


    /**
     * Pacs Log verileri için export işlemi.
     *
     * @return BinaryFileResponse
     */

    public function pacsLogExport(): BinaryFileResponse
    {
        return $this->exportRepository->pacsLogExport();
    }

    /**
     * Production Log verileri için export işlemi.
     *
     * @return BinaryFileResponse
     */
    public function productionLogExport(): BinaryFileResponse
    {
        return $this->exportRepository->productionLogExport();
    }

    /**
     * Sales Log verileri için export işlemi.
     *
     * @return BinaryFileResponse
     */
    public function salesLogExport(): BinaryFileResponse
    {
        return $this->exportRepository->salesLogExport();
    }


    /**
     * Stock Movement Log verileri için export işlemi.
     *
     * @return BinaryFileResponse
     */
    public function stockMovementLogExport(): BinaryFileResponse
    {
        return $this->exportRepository->stockMovementLogExport();
    }

}
