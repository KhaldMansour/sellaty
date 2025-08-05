<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class BaseFormRequest extends FormRequest
{
    /**
     * Override the default failed validation behavior
     */
    protected function failedValidation(Validator $validator)
    {
        $errorString = implode(" | ", $validator->errors()->all());

        throw new HttpException(422, $errorString);
    }
}
