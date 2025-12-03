<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Requests;

use App\Modules\Auth\Enums\CompanySize;
use App\Modules\Auth\Enums\IndustryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => [
                'required',
                'string',
                'max:255',
            ],
            'company_size' => [
                'required',
                'string',
                Rule::enum(CompanySize::class),
            ],
            'website_protocol' => [
                'nullable',
                'string',
                Rule::in(['http', 'https']),
            ],
            'website_url' => [
                'nullable',
                'string',
                'max:255',
                // Custom validation: should not include protocol
                'regex:/^(?!https?:\/\/)[a-zA-Z0-9][a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(\/.*)?$/',
            ],
            'industry_type' => [
                'required',
                'string',
                Rule::enum(IndustryType::class),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'company_name.required' => 'Please enter your company name.',
            'company_size.required' => 'Please select your company size.',
            'company_size.enum' => 'Please select a valid company size.',
            'website_url.regex' => 'Please enter a valid website URL without http:// or https://.',
            'industry_type.required' => 'Please select your industry type.',
            'industry_type.enum' => 'Please select a valid industry type.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Clean up website URL if provided
        if ($this->website_url) {
            $url = $this->website_url;
            // Remove protocol if accidentally included
            $url = preg_replace('/^https?:\/\//', '', $url);
            // Remove trailing slash
            $url = rtrim($url, '/');

            $this->merge(['website_url' => $url]);
        }

        // Default protocol to https
        if (! $this->website_protocol) {
            $this->merge(['website_protocol' => 'https']);
        }
    }
}
