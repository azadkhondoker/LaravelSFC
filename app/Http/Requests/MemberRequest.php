<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
     public function rules(): array
    {
        $id = $this->route('member')?->id;

        return [
            'member_code' => 'sometimes|required|unique:members,member_code,' . $id,
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'gender' => 'required|in:0,1',
            'nid' => 'nullable|integer',
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s().-]+$/'],
            'address' => 'nullable|string',
            'joining_date' => 'required|date',
            'installment_amount' => 'required|numeric|decimal:0,2|min:0.01|max:9999999999.99',
            'status' => 'required|in:active,inactive',
        ];
    }
}

