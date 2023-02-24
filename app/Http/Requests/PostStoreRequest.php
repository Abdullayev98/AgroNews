<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PostStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title_uz' => 'required|max:255',
            'title_ru' => 'required|max:255',
            'title_crl' => 'required|max:255',
            'description_uz' => 'required',
            'description_ru' => 'required',
            'description_crl' => 'required',
            'content_uz' => 'required',
            'content_ru' => 'required',
            'content_crl' => 'required',

        ];
    }
    public function failedValidation(Validator $validator){
        throw new HttpResponseException(response()->json([

            'success'   => false,

            'message'   => 'Validation errors',

            'data'      => $validator->errors()

        ]));
    }
    public function messages()    {
        return [
            'title_uz.required' => 'Title.uz is required',
            'title_ru.required' => 'Title.ru is required',
            'title_crl.required' => 'Title.crl is required',
            'title_uz.max' => 'Title.uz can consist max 255sign',
            'title_ru.max' => 'Title.ru can consist max 255sign',
            'title_crl.max' => 'Title.crl can consist max 255sign',
            'description_uz.required' => 'description_uz is required',
            'description_ru.required' => 'description_ru is required',
            'description_crl.required' => 'description_crl is required',
            'content_uz.required' => 'content_uz is required',
            'content_ru.required' => 'content_ru is required',
            'content_crl.required' => 'content_crl is required',
        ];
    }
}
