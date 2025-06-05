{{-- resources/views/produtos/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Cadastrar Novo Produto')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-plus"></i> Cadastrar Novo Produto</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('produtos.store') }}" method="POST" enctype="multipart/form-data" id="form-produto">
                    @csrf
                    
                    <!-- Informações Básicas -->
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Nome do Produto *</label>
                            <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror" 
                                   value="{{ old('nome') }}" required>
                            @error('nome')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">SKU (Código)</label>
                            <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" 
                                   value="{{ old('sku') }}" placeholder="Deixe vazio para gerar automaticamente">
                            @error('sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Categoria</label>
                            <input type="text" name="categoria" class="form-control @error('categoria') is-invalid @enderror" 
                                   value="{{ old('categoria') }}" placeholder="Ex: Eletrônicos, Roupas, Casa">
                            @error('categoria')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Marca</label>
                            <input type="text" name="marca" class="form-control @error('marca') is-invalid @enderror" 
                                   value="{{ old('marca') }}">
                            @error('marca')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Unidade de Medida</label>
                            <select name="unidade_medida" class="form-select @error('unidade_medida') is-invalid @enderror">
                                <option value="unidade" {{ old('unidade_medida') == 'unidade' ? 'selected' : '' }}>Unidade</option>
                                <option value="kg" {{ old('unidade_medida') == 'kg' ? 'selected' : '' }}>Quilograma (kg)</option>
                                <option value="g" {{ old('unidade_medida') == 'g' ? 'selected' : '' }}>Grama (g)</option>
                                <option value="litro" {{ old('unidade_medida') == 'litro' ? 'selected' : '' }}>Litro</option>
                                <option value="ml" {{ old('unidade_medida') == 'ml' ? 'selected' : '' }}>Mililitro (ml)</option>
                                <option value="metro" {{ old('unidade_medida') == 'metro' ? 'selected' : '' }}>Metro</option>
                                <option value="cm" {{ old('unidade_medida') == 'cm' ? 'selected' : '' }}>Centímetro</option>
                                <option value="par" {{ old('unidade_medida') == 'par' ? 'selected' : '' }}>Par</option>
                                <option value="pacote" {{ old('unidade_medida') == 'pacote' ? 'selected' : '' }}>Pacote</option>
                            </select>
                            @error('unidade_medida')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Preço e Estoque -->
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Preço *</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" step="0.01" name="preco" class="form-control @error('preco') is-invalid @enderror" 
                                       value="{{ old('preco') }}" required min="0">
                                @error('preco')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Desconto (%)</label>
                            <input type="number" step="0.01" name="desconto_percentual" 
                                   class="form-control @error('desconto_percentual') is-invalid @enderror" 
                                   value="{{ old('desconto_percentual', 0) }}" min="0" max="100">
                            @error('desconto_percentual')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Estoque *</label>
                            <input type="number" name="estoque" class="form-control @error('estoque') is-invalid @enderror" 
                                   value="{{ old('estoque', 0) }}" required min="0">
                            @error('estoque')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Peso (kg)</label>
                            <input type="number" step="0.001" name="peso" class="form-control @error('peso') is-invalid @enderror" 
                                   value="{{ old('peso') }}" min="0" placeholder="0.000">
                            @error('peso')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Descrição -->
                    <div class="mb-3">
                        <label class="form-label">Descrição do Produto</label>
                        <textarea name="descricao" class="form-control @error('descricao') is-invalid @enderror" rows="4" 
                                  placeholder="Descreva as características e benefícios do produto...">{{ old('descricao') }}</textarea>
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Características Técnicas -->
                    <div class="mb-3">
                        <label class="form-label">Características Técnicas</label>
                        <div class="border rounded p-3">
                            <div id="caracteristicas-container">
                                <div class="row caracteristica-item mb-2">
                                    <div class="col-md-5">
                                        <input type="text" class="form-control caracteristica-valor" placeholder="Ex: Azul">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger btn-sm remover-caracteristica">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="adicionar-caracteristica">
                                <i class="fas fa-plus me-1"></i> Adicionar Característica
                            </button>
                        </div>
                        <input type="hidden" name="caracteristicas_json" id="caracteristicas-json">
                    </div>

                    <!-- Data de Lançamento -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data de Lançamento</label>
                            <input type="date" name="data_lancamento" class="form-control @error('data_lancamento') is-invalid @enderror" 
                                   value="{{ old('data_lancamento') }}">
                            @error('data_lancamento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Configurações</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input type="checkbox" name="ativo" id="ativo" class="form-check-input" 
                                           {{ old('ativo', true) ? 'checked' : '' }}>
                                    <label for="ativo" class="form-check-label">Produto Ativo</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="destaque" id="destaque" class="form-check-input" 
                                           {{ old('destaque') ? 'checked' : '' }}>
                                    <label for="destaque" class="form-check-label">Produto em Destaque</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upload de Imagens -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Imagem Principal</label>
                            <input type="file" name="imagem" class="form-control @error('imagem') is-invalid @enderror" 
                                   accept="image/*" onchange="previewImage(this, 'preview-image-principal')">
                            @error('imagem')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Formatos aceitos: JPG, PNG, GIF, WebP. Tamanho máximo: 5MB
                            </small>
                            
                            <div class="mt-2">
                                <img id="preview-image-principal" src="#" alt="Preview" 
                                     style="display: none; max-width: 200px; max-height: 200px; border-radius: 5px; border: 1px solid #ddd;">
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Galeria de Imagens</label>
                            <input type="file" name="galeria_imagens[]" class="form-control @error('galeria_imagens.*') is-invalid @enderror" 
                                   accept="image/*" multiple onchange="previewGallery(this)">
                            @error('galeria_imagens.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Selecione múltiplas imagens. Máximo 3MB cada.
                            </small>
                            
                            <div class="mt-2" id="gallery-preview">
                                <!-- Preview das imagens da galeria -->
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('produtos.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Cadastrar Produto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Preview da imagem principal
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
            document.getElementById(previewId).style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Preview da galeria de imagens
function previewGallery(input) {
    const galleryPreview = document.getElementById('gallery-preview');
    galleryPreview.innerHTML = '';
    
    if (input.files) {
        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'col-md-3 mb-2';
                div.innerHTML = `
                    <img src="${e.target.result}" class="img-thumbnail" style="max-height: 100px;">
                    <small class="d-block text-muted">${file.name}</small>
                `;
                galleryPreview.appendChild(div);
            }
            reader.readAsDataURL(file);
        });
    }
}

// Gerenciamento de características
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('caracteristicas-container');
    const btnAdicionar = document.getElementById('adicionar-caracteristica');
    
    // Adicionar nova característica
    btnAdicionar.addEventListener('click', function() {
        const newItem = document.createElement('div');
        newItem.className = 'row caracteristica-item mb-2';
        newItem.innerHTML = `
            <div class="col-md-5">
                <input type="text" class="form-control caracteristica-nome" placeholder="Ex: Material">
            </div>
            <div class="col-md-5">
                <input type="text" class="form-control caracteristica-valor" placeholder="Ex: Algodão">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm remover-caracteristica">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        container.appendChild(newItem);
    });
    
    // Remover característica
    container.addEventListener('click', function(e) {
        if (e.target.closest('.remover-caracteristica')) {
            e.target.closest('.caracteristica-item').remove();
        }
    });
    
    // Processar características antes do submit
    document.getElementById('form-produto').addEventListener('submit', function() {
        const caracteristicas = {};
        const items = document.querySelectorAll('.caracteristica-item');
        
        items.forEach(item => {
            const nome = item.querySelector('.caracteristica-nome').value.trim();
            const valor = item.querySelector('.caracteristica-valor').value.trim();
            
            if (nome && valor) {
                caracteristicas[nome] = valor;
            }
        });
        
        document.getElementById('caracteristicas-json').value = JSON.stringify(caracteristicas);
    });
});

// Cálculo automático do preço com desconto
document.addEventListener('DOMContentLoaded', function() {
    const precoInput = document.querySelector('[name="preco"]');
    const descontoInput = document.querySelector('[name="desconto_percentual"]');
    
    function calcularPrecoFinal() {
        const preco = parseFloat(precoInput.value) || 0;
        const desconto = parseFloat(descontoInput.value) || 0;
        const precoFinal = preco - (preco * desconto / 100);
        
        // Mostrar preview do preço final
        let previewDiv = document.getElementById('preco-preview');
        if (!previewDiv) {
            previewDiv = document.createElement('div');
            previewDiv.id = 'preco-preview';
            previewDiv.className = 'mt-2';
            descontoInput.parentNode.appendChild(previewDiv);
        }
        
        if (desconto > 0 && preco > 0) {
            previewDiv.innerHTML = `
                <small class="text-success">
                    <strong>Preço final: R$ ${precoFinal.toFixed(2).replace('.', ',')}</strong>
                    <br>Economia: R$ ${(preco - precoFinal).toFixed(2).replace('.', ',')}
                </small>
            `;
        } else {
            previewDiv.innerHTML = '';
        }
    }
    
    precoInput.addEventListener('input', calcularPrecoFinal);
    descontoInput.addEventListener('input', calcularPrecoFinal);
});

// Validação de arquivos
document.addEventListener('DOMContentLoaded', function() {
    const imagemInput = document.querySelector('[name="imagem"]');
    const galeriaInput = document.querySelector('[name="galeria_imagens[]"]');
    
    function validarImagem(file, maxSize = 5) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        const maxSizeBytes = maxSize * 1024 * 1024; // MB para bytes
        
        if (!allowedTypes.includes(file.type)) {
            alert('Formato de arquivo não suportado. Use JPG, PNG, GIF ou WebP.');
            return false;
        }
        
        if (file.size > maxSizeBytes) {
            alert(`Arquivo muito grande. Máximo permitido: ${maxSize}MB`);
            return false;
        }
        
        return true;
    }
    
    imagemInput.addEventListener('change', function() {
        if (this.files[0] && !validarImagem(this.files[0], 5)) {
            this.value = '';
            document.getElementById('preview-image-principal').style.display = 'none';
        }
    });
    
    galeriaInput.addEventListener('change', function() {
        for (let file of this.files) {
            if (!validarImagem(file, 3)) {
                this.value = '';
                document.getElementById('gallery-preview').innerHTML = '';
                break;
            }
        }
    });
});
</script>

<style>
.caracteristica-item {
    transition: all 0.3s ease;
}

.caracteristica-item:hover {
    background-color: #f8f9fa;
    border-radius: 5px;
    padding: 5px;
}

#gallery-preview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 10px;
}

.img-thumbnail {
    transition: transform 0.2s ease;
}

.img-thumbnail:hover {
    transform: scale(1.05);
}

.form-check {
    padding: 10px;
    border-radius: 5px;
    transition: background-color 0.2s ease;
}

.form-check:hover {
    background-color: #f8f9fa;
}
</style>
@endsectionistica-nome" placeholder="Ex: Cor">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" class="form-control caracter