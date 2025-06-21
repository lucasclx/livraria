<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoriaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'nome' => 'required|string|min:3|max:100',
            'descricao' => 'nullable|string|max:500',
            'ativo' => 'boolean',
        ];

        if ($this->isMethod('post')) {
            $rules['imagem'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
        } else {
            $rules['imagem'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'nome.required' => 'O nome é obrigatório',
            'nome.min' => 'O nome deve ter pelo menos 3 caracteres',
            'nome.max' => 'O nome deve ter no máximo 100 caracteres',
            'descricao.max' => 'A descrição deve ter no máximo 500 caracteres',
            'imagem.image' => 'O arquivo deve ser uma imagem',
            'imagem.mimes' => 'A imagem deve ser do tipo: jpeg, png, jpg ou gif',
            'imagem.max' => 'A imagem deve ter no máximo 2MB',
        ];
    }
}