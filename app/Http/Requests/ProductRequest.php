<?php

namespace App\Http\Requests;

use App\Traits\Res;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ProductRequest extends FormRequest
{
    use Res;
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
        $rule = [
            'name' => ['string', 'required', 'max:255'],
            'price' => ['required', 'integer'],
            'quantity' => ['required', 'integer'],
            'categorie_id' => ['required', 'integer', 'exists:categories,id'],
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'image.*' => ['image', 'mimes:jpeg,png,jpg', 'max:10240'],
        ];
        return $rule;
    }

    public function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, $this->sendRes("error", false, $validator->errors(), 400));
    }
}
