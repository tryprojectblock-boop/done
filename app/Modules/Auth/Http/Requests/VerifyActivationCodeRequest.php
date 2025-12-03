<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyActivationCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'code' => ['required', 'string', 'size:6', 'alpha_num'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Please enter the activation code.',
            'code.size' => 'The activation code must be 6 characters.',
            'code.alpha_num' => 'The activation code must only contain letters and numbers.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper($this->code ?? ''),
        ]);
    }
}
