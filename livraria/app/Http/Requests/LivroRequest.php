<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LivroRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $livroId = $this->route('livro') ? $this->route('livro')->id : null;
        
        return [
            'titulo' => 'required|string|max:255',
            'autor' => 'required|string|max:100',
            'isbn' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('livros')->ignore($livroId)
            ],
            'preco' => 'required|numeric|min:0.01|max:99999.99',
            'editora' => 'nullable|string|max:100',
            'ano_publicacao' => 'nullable|integer|min:1000|max:' . (date('Y') + 1),
            'paginas' => 'nullable|integer|min:1|max:99999',
            'categoria' => 'nullable|string|max:50',
            'estoque' => 'required|integer|min:0|max:99999',
            'sinopse' => 'nullable|string|max:2000',
            'imagem' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB
            'ativo' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'titulo.required' => 'O título é obrigatório',
            'titulo.max' => 'O título deve ter no máximo 255 caracteres',
            'autor.required' => 'O autor é obrigatório',
            'autor.max' => 'O nome do autor deve ter no máximo 100 caracteres',
            'isbn.unique' => 'Este ISBN já está cadastrado',
            'isbn.max' => 'O ISBN deve ter no máximo 20 caracteres',
            'preco.required' => 'O preço é obrigatório',
            'preco.numeric' => 'O preço deve ser um número válido',
            'preco.min' => 'O preço deve ser maior que zero',
            'preco.max' => 'O preço deve ser menor que R$ 99.999,99',
            'ano_publicacao.integer' => 'O ano deve ser um número válido',
            'ano_publicacao.min' => 'O ano deve ser maior que 1000',
            'ano_publicacao.max' => 'O ano não pode ser futuro',
            'paginas.integer' => 'O número de páginas deve ser um número inteiro',
            'paginas.min' => 'O livro deve ter pelo menos 1 página',
            'categoria.max' => 'A categoria deve ter no máximo 50 caracteres',
            'estoque.required' => 'O estoque é obrigatório',
            'estoque.integer' => 'O estoque deve ser um número inteiro',
            'estoque.min' => 'O estoque não pode ser negativo',
            'sinopse.max' => 'A sinopse deve ter no máximo 2000 caracteres',
            'imagem.image' => 'O arquivo deve ser uma imagem',
            'imagem.mimes' => 'A imagem deve ser do tipo: jpeg, png, jpg, gif ou webp',
            'imagem.max' => 'A imagem deve ter no máximo 5MB',
        ];
    }
}