<?php

namespace App\Http\Requests;

use App\Utils\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'id' => 'required|exists:roles,id',
            'name' => 'required|string|regex:/^[\pL]+(?:[\pL\s]*[\pL])?$/u',
        ];
    }
    public function messages()
    {
        return [
            // required
            'id.required' => __('common.field_is_required', ['field' => 'id']),
            'name.required' => __('common.field_is_required', ['field' => 'nombre']),
            // regex
            'name.regex' => __('common.only_letters', ['field' => 'nombre']),
            //exists
            'id.exists' => __('common.must_exists', ['model' => 'rol']),

        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error(422, 'Error de validación', $validator->errors()));
    }

}
