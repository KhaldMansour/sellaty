<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class CreateChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust this if you have authorization logic
    }

    public function rules(): array
    {
        return [
            'type' => 'required|string|in:Product,WantedProduct',
        ];
    }

    /**
     * Resolve the fully qualified model class name.
     */
    public function resourceModelClass(): ?string
    {
        return match (($this->input('type'))) {
            'Product' => \App\Models\Product::class,
            'WantedProduct' => \App\Models\WantedProduct::class,
            default => null,
        };
    }

    /**
     * Resolve the resource model instance.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function resource(): mixed
    {
        $modelClass = $this->resourceModelClass();

        if (!$modelClass || !class_exists($modelClass)) {
            throw ValidationException::withMessages([
                'type' => 'Invalid resource type.',
            ]);
        }

        // $model = $modelClass::find($this->input('id'));

        // if (!$model) {
        //     throw ValidationException::withMessages([
        //         'id' => 'Resource not found.',
        //     ]);
        // }

        return $modelClass;
    }
}
