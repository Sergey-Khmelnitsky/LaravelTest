<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecipeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by Policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'cuisine_id' => ['sometimes', 'required', 'exists:cuisines,id'],
            'description' => ['nullable', 'string'],
            'prep_time' => ['nullable', 'integer', 'min:0'],
            'cook_time' => ['nullable', 'integer', 'min:0'],
            'servings' => ['nullable', 'integer', 'min:1'],
            'steps' => ['sometimes', 'required', 'array', 'min:1'],
            'steps.*.step_number' => ['required_with:steps', 'integer', 'min:1'],
            'steps.*.description' => ['required_with:steps', 'string'],
            'steps.*.order' => ['nullable', 'integer', 'min:0'],
            'steps.*.ingredients' => ['nullable', 'array'],
            'steps.*.ingredients.*.ingredient_id' => ['required_with:steps.*.ingredients', 'exists:ingredients,id'],
            'steps.*.ingredients.*.amount' => ['nullable', 'numeric', 'min:0'],
            'steps.*.ingredients.*.unit' => ['nullable', 'string', 'max:50'],
            'images' => ['nullable', 'array'],
            'images.*' => ['integer', 'exists:attachments,id'],
        ];
    }
}
