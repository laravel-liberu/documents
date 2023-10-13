<?php

namespace LaravelLiberu\Documents\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use LaravelLiberu\Helpers\Traits\TransformMorphMap;

class ValidateDocument extends FormRequest
{
    use TransformMorphMap;

    public function morphType(): string
    {
        return 'documentable_type';
    }

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'documentable_id' => 'required',
            'documentable_type' => 'required',
        ];
    }
}
