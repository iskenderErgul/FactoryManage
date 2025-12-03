<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalesReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Middleware'de auth kontrolü yapılıyor
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $action = $this->route()->getActionMethod();
        
        return match($action) {
            'dateRangeReport' => [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ],
            'monthlyReport' => [
                'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
                'month' => 'required|integer|min:1|max:12',
            ],
            'customerReport' => [
                'customer_id' => 'required|exists:customers,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ],
            'customerProductsReport' => [
                'customer_id' => 'required|exists:customers,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'month' => 'nullable|integer|min:1|max:12',
                'year' => 'nullable|integer|min:2000',
            ],
            'customerPaymentsReport' => [
                'customer_id' => 'required|exists:customers,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ],
            'exportPdf', 'exportExcel' => [
                'report_type' => 'required|in:date-range,monthly,customer,customer-products,customer-payments,trends',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'customer_id' => 'nullable|exists:customers,id',
                'year' => 'nullable|integer|min:2000',
                'month' => 'nullable|integer|min:1|max:12',
            ],
            default => [],
        };
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'start_date.required' => 'Başlangıç tarihi zorunludur.',
            'start_date.date' => 'Geçerli bir başlangıç tarihi giriniz.',
            'end_date.required' => 'Bitiş tarihi zorunludur.',
            'end_date.date' => 'Geçerli bir bitiş tarihi giriniz.',
            'end_date.after_or_equal' => 'Bitiş tarihi başlangıç tarihinden önce olamaz.',
            'customer_id.required' => 'Müşteri seçimi zorunludur.',
            'customer_id.exists' => 'Seçilen müşteri bulunamadı.',
            'year.required' => 'Yıl seçimi zorunludur.',
            'year.integer' => 'Geçerli bir yıl giriniz.',
            'month.required' => 'Ay seçimi zorunludur.',
            'month.integer' => 'Geçerli bir ay giriniz.',
            'month.min' => 'Ay 1 ile 12 arasında olmalıdır.',
            'month.max' => 'Ay 1 ile 12 arasında olmalıdır.',
            'report_type.required' => 'Rapor tipi zorunludur.',
            'report_type.in' => 'Geçersiz rapor tipi.',
        ];
    }
}
