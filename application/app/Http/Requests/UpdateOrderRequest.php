<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'required|numeric',
            'ticket_number' => 'required|numeric', 
            'status' => 'required|in:P,R,D,C',
            'customer_id' => 'nullable|numeric',
            'total_price' => 'required|numeric',
            'delivered_by' => 'nullable|numeric'
        ];

        
    }
}
