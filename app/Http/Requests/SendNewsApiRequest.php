<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
class SendNewsApiRequest extends FormRequest
{
    public function rules()
    {
        return [
            'term' => 'required',
            'page'=> 'interger',
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ]));
    }
    public function messages() //OPTIONAL
    {
        return [
            'term.required' => 'Search Term is required',
            'page.page' => 'Page is not correct'
        ];
    }
}
