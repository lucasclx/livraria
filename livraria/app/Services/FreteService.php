<?php
// app/Services/FreteService.php - Serviço melhorado para cálculo de frete

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FreteService
{
    private $client;
    private $cepOrigem;
    
    public function __construct()
    {
        $this->client = new Client();
        $this->cepOrigem = config('app.cep_origem', '01001-000'); // CEP da loja
    }

    /**
     * Calcula frete para um CEP de destino
     */
    public function calcularFrete($cepDestino, $itens, $valorPedido = 0)
    {
        try {
            // Limpar e validar CEP
            $cepDestino = $this->limparCep($cepDestino);
            if (!$this->validarCep($cepDestino)) {
                throw new \Exception('CEP inválido');
            }

            // Calcular peso e dimensões
            $peso = $this->calcularPeso($itens);
            $dimensoes = $this->calcularDimensoes($itens);
            
            // Verificar cache
            $cacheKey = "frete_{$cepDestino}_{$peso}_" . md5(serialize($dimensoes));
            
            return Cache::remember($cacheKey, 3600, function() use ($cepDestino, $peso, $dimensoes, $valorPedido) {
                return $this->buscarFreteCorreios($cepDestino, $peso, $dimensoes, $valorPedido);
            });
            
        } catch (\Exception $e) {
            Log::error('Erro ao calcular frete: ' . $e->getMessage());
            return $this->getFreteSimulado($cepDestino, $peso);
        }
    }

    /**
     * Busca frete real nos Correios via API
     */
    private function buscarFreteCorreios($cepDestino, $peso, $dimensoes, $valorPedido)
    {
        // Simulação de integração com API dos Correios
        // Em produção, usar a API real dos Correios ou Melhor Envio
        
        $servicos = [
            'PAC' => [
                'codigo' => '04510',
                'nome' => 'PAC',
                'prazo_base' => 8,
                'valor_base' => 15.00
            ],
            'SEDEX' => [
                'codigo' => '04014',
                'nome' => 'SEDEX',
                'prazo_base' => 3,
                'valor_base' => 25.00
            ]
        ];

        $resultados = [];
        foreach ($servicos as $tipo => $servico) {
            $valor = $this->calcularValorFrete($servico['valor_base'], $peso, $dimensoes);
            $prazo = $this->calcularPrazoEntrega($servico['prazo_base'], $cepDestino);
            
            // Frete grátis acima de R$ 150
            if ($valorPedido >= 150) {
                $valor = 0;
            }
            
            $resultados[] = [
                'codigo' => $servico['codigo'],
                'nome' => $servico['nome'],
                'valor' => $valor,
                'prazo' => $prazo,
                'valor_formatado' => $valor > 0 ? 'R$ ' . number_format($valor, 2, ',', '.') : 'Grátis',
                'prazo_formatado' => $prazo . ' dias úteis'
            ];
        }
        
        return [
            'success' => true,
            'opcoes' => $resultados,
            'cep_destino' => $cepDestino
        ];
    }

    /**
     * Frete simulado para fallback
     */
    private function getFreteSimulado($cepDestino, $peso)
    {
        $regiao = $this->identificarRegiao($cepDestino);
        $multiplicador = $this->getMultiplicadorRegiao($regiao);
        
        return [
            'success' => true,
            'opcoes' => [
                [
                    'codigo' => 'PAC_SIM',
                    'nome' => 'PAC',
                    'valor' => 12.50 * $multiplicador,
                    'prazo' => 8 + ($multiplicador > 1 ? 2 : 0),
                    'valor_formatado' => 'R$ ' . number_format(12.50 * $multiplicador, 2, ',', '.'),
                    'prazo_formatado' => (8 + ($multiplicador > 1 ? 2 : 0)) . ' dias úteis'
                ],
                [
                    'codigo' => 'SEDEX_SIM',
                    'nome' => 'SEDEX',
                    'valor' => 22.50 * $multiplicador,
                    'prazo' => 3 + ($multiplicador > 1 ? 1 : 0),
                    'valor_formatado' => 'R$ ' . number_format(22.50 * $multiplicador, 2, ',', '.'),
                    'prazo_formatado' => (3 + ($multiplicador > 1 ? 1 : 0)) . ' dias úteis'
                ]
            ],
            'simulado' => true,
            'cep_destino' => $cepDestino
        ];
    }

    /**
     * Busca endereço por CEP usando API ViaCEP
     */
    public function buscarEnderecoPorCep($cep)
    {
        try {
            $cep = $this->limparCep($cep);
            
            $cacheKey = "endereco_cep_{$cep}";
            return Cache::remember($cacheKey, 86400, function() use ($cep) {
                $response = $this->client->get("https://viacep.com.br/ws/{$cep}/json/");
                $dados = json_decode($response->getBody(), true);
                
                if (isset($dados['erro'])) {
                    throw new \Exception('CEP não encontrado');
                }
                
                return [
                    'success' => true,
                    'cep' => $dados['cep'],
                    'logradouro' => $dados['logradouro'],
                    'bairro' => $dados['bairro'],
                    'cidade' => $dados['localidade'],
                    'uf' => $dados['uf'],
                    'endereco_completo' => trim($dados['logradouro'] . ', ' . $dados['bairro'] . ' - ' . $dados['localidade'] . '/' . $dados['uf'])
                ];
            });
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'CEP não encontrado ou inválido'
            ];
        }
    }

    // Métodos auxiliares
    private function limparCep($cep)
    {
        return preg_replace('/\D/', '', $cep);
    }

    private function validarCep($cep)
    {
        return preg_match('/^\d{8}$/', $cep);
    }

    private function calcularPeso($itens)
    {
        $pesoTotal = 0;
        foreach ($itens as $item) {
            $pesoItem = $item->livro->peso ?? 0.3; // 300g padrão
            $pesoTotal += $pesoItem * $item->quantity;
        }
        return max($pesoTotal, 0.1); // Mínimo 100g
    }

    private function calcularDimensoes($itens)
    {
        // Dimensões padrão para livros
        $comprimento = 20; // cm
        $largura = 14; // cm
        $altura = count($itens) * 2; // 2cm por livro
        
        return [
            'comprimento' => max($comprimento, 16),
            'largura' => max($largura, 11),
            'altura' => max($altura, 2)
        ];
    }

    private function calcularValorFrete($valorBase, $peso, $dimensoes)
    {
        $valorPeso = $peso * 2.0; // R$ 2 por kg
        $fatorDimensao = ($dimensoes['comprimento'] * $dimensoes['largura'] * $dimensoes['altura']) / 6000;
        
        return $valorBase + $valorPeso + $fatorDimensao;
    }

    private function calcularPrazoEntrega($prazoBase, $cepDestino)
    {
        $regiao = $this->identificarRegiao($cepDestino);
        $adicional = 0;
        
        switch ($regiao) {
            case 'norte':
            case 'nordeste':
                $adicional = 3;
                break;
            case 'centro-oeste':
                $adicional = 2;
                break;
            case 'sul':
                $adicional = 1;
                break;
        }
        
        return $prazoBase + $adicional;
    }

    private function identificarRegiao($cep)
    {
        $primeiro = substr($cep, 0, 1);
        
        return match($primeiro) {
            '0', '1', '2', '3' => 'sudeste',
            '4', '5' => 'sul',
            '6', '7' => 'nordeste',
            '8', '9' => 'norte',
            default => 'centro-oeste'
        };
    }

    private function getMultiplicadorRegiao($regiao)
    {
        return match($regiao) {
            'sudeste' => 1.0,
            'sul' => 1.2,
            'nordeste' => 1.5,
            'norte' => 1.8,
            'centro-oeste' => 1.3,
            default => 1.0
        };
    }
}

// app/Http/Controllers/FreteController.php - Controller para AJAX

namespace App\Http\Controllers;

use App\Services\FreteService;
use Illuminate\Http\Request;

class FreteController extends Controller
{
    public function __construct(private FreteService $freteService)
    {
    }

    public function calcular(Request $request)
    {
        $request->validate([
            'cep' => 'required|string|size:8'
        ]);

        $cart = session('cart_items', []);
        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'error' => 'Carrinho vazio'
            ]);
        }

        $valorPedido = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
        
        $resultado = $this->freteService->calcularFrete(
            $request->cep,
            $cart,
            $valorPedido
        );

        return response()->json($resultado);
    }

    public function buscarCep(Request $request)
    {
        $request->validate([
            'cep' => 'required|string|size:8'
        ]);

        $resultado = $this->freteService->buscarEnderecoPorCep($request->cep);
        
        return response()->json($resultado);
    }
}

// resources/views/components/calculadora-frete.blade.php - Componente frontend

<div class="frete-calculator card">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-truck me-2"></i>Calcular Frete
        </h6>
    </div>
    <div class="card-body">
        <form id="freteForm">
            <div class="input-group mb-3">
                <input type="text" 
                       class="form-control" 
                       id="cepInput" 
                       placeholder="Digite seu CEP"
                       maxlength="9"
                       pattern="\d{5}-?\d{3}">
                <button type="submit" class="btn btn-primary" id="calcularBtn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
        
        <div id="freteResultado" class="d-none">
            <!-- Resultados serão inseridos aqui via JavaScript -->
        </div>
        
        <div id="freteLoading" class="text-center d-none">
            <i class="fas fa-spinner fa-spin"></i> Calculando...
        </div>
        
        <div id="freteError" class="alert alert-danger d-none"></div>
        
        <small class="text-muted">
            <i class="fas fa-info-circle me-1"></i>
            Frete grátis acima de R$ 150,00
        </small>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('freteForm');
    const cepInput = document.getElementById('cepInput');
    const calcularBtn = document.getElementById('calcularBtn');
    const resultado = document.getElementById('freteResultado');
    const loading = document.getElementById('freteLoading');
    const error = document.getElementById('freteError');

    // Máscara para CEP
    cepInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        if (value.length > 5) {
            value = value.substring(0, 5) + '-' + value.substring(5, 8);
        }
        this.value = value;
    });

    // Calcular frete
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const cep = cepInput.value.replace(/\D/g, '');
        if (cep.length !== 8) {
            mostrarErro('CEP deve ter 8 dígitos');
            return;
        }

        mostrarLoading();
        
        fetch('/frete/calcular', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ cep: cep })
        })
        .then(response => response.json())
        .then(data => {
            esconderLoading();
            if (data.success) {
                mostrarResultado(data);
            } else {
                mostrarErro(data.error || 'Erro ao calcular frete');
            }
        })
        .catch(error => {
            esconderLoading();
            mostrarErro('Erro de conexão. Tente novamente.');
        });
    });

    function mostrarLoading() {
        loading.classList.remove('d-none');
        resultado.innerHTML = html;
        resultado.classList.remove('d-none');
        error.classList.add('d-none');
    }

    function mostrarErro(mensagem) {
        error.textContent = mensagem;
        error.classList.remove('d-none');
        resultado.classList.add('d-none');
    }
});
</script>

<style>
.frete-opcao:hover {
    background-color: #f8f9fa;
    cursor: pointer;
}

.frete-calculator .card-header {
    background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-info) 100%);
    color: white;
}

#cepInput:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}
</style>classList.add('d-none');
        error.classList.add('d-none');
        calcularBtn.disabled = true;
    }

    function esconderLoading() {
        loading.classList.add('d-none');
        calcularBtn.disabled = false;
    }

    function mostrarResultado(data) {
        let html = '<h6 class="mb-3">Opções de Entrega:</h6>';
        
        data.opcoes.forEach(opcao => {
            html += `
                <div class="frete-opcao border rounded p-3 mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${opcao.nome}</strong><br>
                            <small class="text-muted">${opcao.prazo_formatado}</small>
                        </div>
                        <div class="text-end">
                            <strong class="text-success">${opcao.valor_formatado}</strong>
                        </div>
                    </div>
                </div>
            `;
        });
        
        if (data.simulado) {
            html += '<small class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Valores estimados</small>';
        }
        
        resultado.