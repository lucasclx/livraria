<?php
// app/Http/Requests/ProdutoRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProdutoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $produtoId = $this->route('produto') ? $this->route('produto')->id : null;
        
        return [
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:5000',
            'preco' => 'required|numeric|min:0.01|max:999999.99',
            'estoque' => 'required|integer|min:0|max:999999',
            'categoria' => 'nullable|string|max:100',
            'marca' => 'nullable|string|max:100',
            'sku' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('produtos')->ignore($produtoId)
            ],
            'peso' => 'nullable|numeric|min:0|max:999999.999',
            'unidade_medida' => 'nullable|string|max:20',
            'desconto_percentual' => 'nullable|numeric|min:0|max:100',
            'ativo' => 'boolean',
            'destaque' => 'boolean',
            'data_lancamento' => 'nullable|date',
            'imagem' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB
            'galeria_imagens.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072', // 3MB cada
            'caracteristicas_json' => 'nullable|json',
        ];
    }

    public function messages()
    {
        return [
            'nome.required' => 'O nome do produto é obrigatório',
            'nome.max' => 'O nome deve ter no máximo 255 caracteres',
            
            'descricao.max' => 'A descrição deve ter no máximo 5000 caracteres',
            
            'preco.required' => 'O preço é obrigatório',
            'preco.numeric' => 'O preço deve ser um número válido',
            'preco.min' => 'O preço deve ser maior que zero',
            'preco.max' => 'O preço deve ser menor que R$ 999.999,99',
            
            'estoque.required' => 'O estoque é obrigatório',
            'estoque.integer' => 'O estoque deve ser um número inteiro',
            'estoque.min' => 'O estoque não pode ser negativo',
            'estoque.max' => 'O estoque deve ser menor que 999.999',
            
            'categoria.max' => 'A categoria deve ter no máximo 100 caracteres',
            'marca.max' => 'A marca deve ter no máximo 100 caracteres',
            
            'sku.unique' => 'Este SKU já está cadastrado',
            'sku.max' => 'O SKU deve ter no máximo 50 caracteres',
            
            'peso.numeric' => 'O peso deve ser um número válido',
            'peso.min' => 'O peso não pode ser negativo',
            'peso.max' => 'O peso deve ser menor que 999.999,999 kg',
            
            'unidade_medida.max' => 'A unidade de medida deve ter no máximo 20 caracteres',
            
            'desconto_percentual.numeric' => 'O desconto deve ser um número válido',
            'desconto_percentual.min' => 'O desconto não pode ser negativo',
            'desconto_percentual.max' => 'O desconto não pode ser maior que 100%',
            
            'data_lancamento.date' => 'A data de lançamento deve ser uma data válida',
            
            'imagem.image' => 'O arquivo deve ser uma imagem',
            'imagem.mimes' => 'A imagem deve ser do tipo: jpeg, png, jpg, gif ou webp',
            'imagem.max' => 'A imagem deve ter no máximo 5MB',
            
            'galeria_imagens.*.image' => 'Todos os arquivos da galeria devem ser imagens',
            'galeria_imagens.*.mimes' => 'As imagens da galeria devem ser do tipo: jpeg, png, jpg, gif ou webp',
            'galeria_imagens.*.max' => 'Cada imagem da galeria deve ter no máximo 3MB',
            
            'caracteristicas_json.json' => 'As características devem estar em formato JSON válido',
        ];
    }

    public function prepareForValidation()
    {
        // Converter strings vazias para null
        $this->merge([
            'categoria' => $this->categoria ?: null,
            'marca' => $this->marca ?: null,
            'sku' => $this->sku ?: null,
            'peso' => $this->peso ?: null,
            'unidade_medida' => $this->unidade_medida ?: 'unidade',
            'desconto_percentual' => $this->desconto_percentual ?: 0,
            'data_lancamento' => $this->data_lancamento ?: null,
            'caracteristicas_json' => $this->caracteristicas_json ?: null,
        ]);

        // Garantir que checkboxes tenham valor boolean
        $this->merge([
            'ativo' => $this->boolean('ativo', true),
            'destaque' => $this->boolean('destaque', false),
        ]);
    }
}