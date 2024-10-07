<?php

namespace App\Http\Requests;

use App\Utils\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Log;

class SendRecoveryPasswordMailRequest extends FormRequest
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
            'email' => 'required|string|email|exists:users,email',
        ];
    }

    /**
     * Get the validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // required
            'email.required' => __('common.field_is_required', ['field' => 'email']),
            // email
            'email.email' => __('auth.email'),
            // exists
            'email.exists' => __('common.must_exists', ['model' => 'usuario']),
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    public function failedValidation(Validator $validator)
    {
        // Log the failed validation details
        Log::error('Error de validación en el formulario de recuperación de contraseña.', [
            'errors' => $validator->errors(),
            'request_data' => $this->all(),
        ]);

        // Throw the validation exception with a detailed error response
        throw new HttpResponseException(
            ApiResponse::error(422, 'Error de validación', $validator->errors())
        );
    }

    /**
     * Debug method to log the request data before validation.
     */
    protected function prepareForValidation()
    {
        // Log the incoming request data for debugging
        Log::info('Datos recibidos para la recuperación de contraseña:', $this->all());
    }
}
