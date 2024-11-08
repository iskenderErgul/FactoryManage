<?php

namespace App\Domains\Export\Interfaces;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

interface ExportRepositoryInterface
{
    /**
     * Export Costs verilerini dışa aktarır.
     * @return BinaryFileResponse
     */
    public function costsExport(): BinaryFileResponse;

    /**
     * Export Production verilerini dışa aktarır.
     * @return BinaryFileResponse
     */
    public function productionExport(): BinaryFileResponse;

    /**
     * Export Sales verilerini dışa aktarır.
     * @return BinaryFileResponse
     */
    public function salesExport(): BinaryFileResponse;

    /**
     * Export SalesProduct verilerini dışa aktarır.
     * @return BinaryFileResponse
     */
    public function salesProductExport(): BinaryFileResponse;

    /**
     * Export Pacs verilerini dışa aktarır.
     * @return BinaryFileResponse
     */
    public function pacsExport(): BinaryFileResponse;

    /**
     * Export Stock Movement verilerini dışa aktarır.
     * @return BinaryFileResponse
     */
    public function stockMovementExport(): BinaryFileResponse;

    /**
     * Export Pacs Log verilerini dışa aktarır.
     * @return BinaryFileResponse
     */
    public function pacsLogExport(): BinaryFileResponse;

    /**
     * Export Production Log verilerini dışa aktarır.
     * @return BinaryFileResponse
     */
    public function productionLogExport(): BinaryFileResponse;

    /**
     * Export Sales Log verilerini dışa aktarır.
     * @return BinaryFileResponse
 */
    public function salesLogExport(): BinaryFileResponse;

    /**
     * Export Stock Movement Log verilerini dışa aktarır.
     * @return BinaryFileResponse
     */
    public function stockMovementLogExport(): BinaryFileResponse;





}
