<?php

namespace App\Http\Requests;

use App\Utils\ApiResponse;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AssignPermissionToUserRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'permission_id' => 'required|exists:permissions,id',
        ];
    }

    public function messages()
    {
        return [
            // required
            'user_id.required' => __('common.field_is_required', ['field' => 'id del usuario']),
            'permission_id.required' => __('common.field_is_required', ['field' => 'id del permiso']),
            // exists
            'user_id.exists' => __('common.must_exists', ['model' => 'usuario']),
            'permission_id.exists' => __('common.must_exists', ['model' => 'permiso']),
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error(422, 'Error de validación', $validator->errors()));
    }
}
