<?php

namespace App\Http\Controllers\Customer;

use App\Common\Services\TransactionPdfService;
use App\Domains\Customer\Models\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\GenerateTransactionPdfRequest;
use Illuminate\Http\Response;

class TransactionPdfController extends Controller
{
    protected TransactionPdfService $pdfService;

    public function __construct(TransactionPdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function generate(GenerateTransactionPdfRequest $request, int $customerId): Response
    {
        try {
            $allHistory = $request->boolean('all_history');
            
            $pdf = $this->pdfService->generateTransactionPdf(
                $customerId,
                $request->start_date,
                $request->end_date,
                $allHistory
            );
            
            $customer = Customer::findOrFail($customerId);
            $fileName = $this->generateFileName($customer, $request->start_date, $request->end_date, $allHistory);
            
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

    private function generateFileName(Customer $customer, ?string $startDate, ?string $endDate, bool $allHistory): string
    {
        $customerName = \Illuminate\Support\Str::slug($customer->name);
        
        if ($allHistory) {
            return "ekstre_{$customerName}_tum_gecmis.pdf";
        }
        
        $start = \Carbon\Carbon::parse($startDate)->format('d-m-Y');
        $end = \Carbon\Carbon::parse($endDate)->format('d-m-Y');
        
        return "ekstre_{$customerName}_{$start}_{$end}.pdf";
    }
}
