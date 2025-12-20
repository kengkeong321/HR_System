<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Payroll;

class StorePayrollRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('process', Payroll::class);
    }

    public function rules()
    {
        return [
            'staff_id' => 'required|exists:staff,staff_id', 
            'month'    => 'required|date_format:Y-m',
            'bonus'    => 'nullable|numeric|min:0|max:10000', 
            'allowances' => 'nullable|numeric|min:0|max:5000', 
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation Error: Please check your input ranges.',
            'errors'  => $validator->errors() 
        ], 422));
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Unauthorized: You do not have the required role to process payroll.'
        ], 403));
    }
}