<?php

namespace App\Http\Requests;

use App\Utils\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ResetUserPasswordRequest extends FormRequest
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
            'email' => 'required|string|email',
            'token' => 'required',
            'password' => 'required|confirmed|min:6',
        ];
    }

    public function messages()
    {
        return [
            // required
            'email.required' => __('common.field_is_required', ['field' => 'email']),
            'token.required' => __('common.field_is_required', ['field' => 'token']),
            'password.required' => __('common.field_is_required', ['field' => 'contraseña']),
            // email
            'email.email' => __('auth.email'),

            // confirmed
            'password.confirmed' => __('auth.password_confirmed'),
            // min
            'password.min' => __('auth.password_min', ['min' => '6'])
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error(422, 'Error de validación', $validator->errors()));
    }
}
