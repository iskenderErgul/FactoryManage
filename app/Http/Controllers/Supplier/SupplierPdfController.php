<?php

namespace App\Http\Controllers\Supplier;

use App\Common\Services\SupplierPdfService;
use App\Domains\Suppliers\Models\Supplier;
use App\Http\Controllers\Controller;
use App\Http\Requests\Supplier\GenerateSupplierPdfRequest;
use Illuminate\Http\Response;

class SupplierPdfController extends Controller
{
    protected SupplierPdfService $pdfService;

    public function __construct(SupplierPdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function generate(GenerateSupplierPdfRequest $request, int $supplierId): Response
    {
        try {
            $allHistory = $request->boolean('all_history');
            
            $pdf = $this->pdfService->generateSupplierPdf(
                $supplierId,
                $request->start_date,
                $request->end_date,
                $allHistory
            );
            
            $supplier = Supplier::findOrFail($supplierId);
            $fileName = $this->generateFileName($supplier, $request->start_date, $request->end_date, $allHistory);
            
            $displayMode = $request->input('display', 'download');
            
            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => ($displayMode === 'inline' ? 'inline' : 'attachment') . '; filename="' . $fileName . '"',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'PDF oluşturulurken bir hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function generateFileName(Supplier $supplier, ?string $startDate, ?string $endDate, bool $allHistory): string
    {
        $supplierName = \Illuminate\Support\Str::slug($supplier->supplier_name);
        
        if ($allHistory) {
            return "tedarikci_ekstre_{$supplierName}_tum_gecmis.pdf";
        }
        
        $start = \Carbon\Carbon::parse($startDate)->format('d-m-Y');
        $end = \Carbon\Carbon::parse($endDate)->format('d-m-Y');
        
        return "tedarikci_ekstre_{$supplierName}_{$start}_{$end}.pdf";
    }
}
