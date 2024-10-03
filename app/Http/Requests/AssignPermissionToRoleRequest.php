<?php

namespace App\Http\Requests;

use App\Utils\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AssignPermissionToRoleRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'permission_id' => 'required|exists:permissions,id',
            'role_id' => 'required|exists:roles,id',
        ];
    }

    public function messages()
    {
        return [
            // required
            'permission_id.required' => __('common.field_is_required', ['field' => 'id del permiso']),
            'role_id.required' => __('common.field_is_required', ['field' => 'id del rol']),
            // exists
            'permission_id.exists' => __('common.must_exists', ['model' => 'permiso']),
            'role_id.exists' => __('common.must_exists', ['model' => 'rol']),
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error(422, 'Error de validación', $validator->errors()));
    }
}
