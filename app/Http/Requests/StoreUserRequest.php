<?php

namespace App\Http\Requests;

use App\Utils\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreUserRequest extends FormRequest
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
            'name' => 'required|string|regex:/^[\p{L}\s]+$/u',
            'lastname' => 'required|string|regex:/^[\p{L}\s]+$/u',
            'email' => 'required|string|email',
            'password' => 'required|string|min:6'
        ];

    }

    public function messages()
    {
        return [
            // required
            'name.required' => __('common.field_is_required', ['field' => 'nombre']),
            'lastname.required' => __('common.field_is_required', ['field' => 'apellido']),
            'email.required' => __('common.field_is_required', ['field' => 'email']),
            'password.required' => __('common.field_is_required', ['field' => 'contraseña']),
            // regex
            'name.regex' => __('common.no_special_chars_and_numbers', ['field' => 'nombre']),
            'lastname.regex' => __('common.no_special_chars_and_numbers', ['field' => 'apellido']),
            // email
            'email.email' => __('auth.email'),
            // min
            'password.min' => __('auth.password_min', ['min' => '6'])
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error(422, 'Error de validación', $validator->errors()));
    }

}
