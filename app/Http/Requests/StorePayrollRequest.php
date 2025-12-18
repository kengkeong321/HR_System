<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePayrollRequest extends FormRequest
{
    public function authorize()
    {
        // Centralized Access Control [78] - Delegates to the Policy
        return $this->user()->can('process', \App\Models\PayrollRecord::class);
    }

    public function rules()
    {
        return [
            'staff_id' => 'required|exists:staff,id',
            'month'    => 'required|date_format:Y-m',
            // Input Validation [12] - Validate Data Range
            'bonus'    => 'nullable|numeric|min:0|max:10000', 
        ];
    }
}