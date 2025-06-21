<?php

namespace App\Http\Controllers;

use App\Models\Cupom;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CupomController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin')->except(['aplicar', 'remover']);
    }

    private function checkAdmin()
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Acesso negado. Apenas administradores podem acessar esta área.');
        }
    }

    /**
     * Lista todos os cupons (Admin)
     */
    public function index(Request $request)
    {
        $this->checkAdmin();

        $query = Cupom::query();

        // Filtros
        if ($request->filled('status')) {
            if ($request->status === 'ativo') {
                $query->where('ativo', true)
                      ->where('valido_ate', '>=', now());
            } elseif ($request->status === 'expirado') {
                $query->where('valido_ate', '<', now());
            } elseif ($request->status === 'inativo') {
                $query->where('ativo', false);
            }
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('busca')) {
            $query->where(function($q) use ($request) {
                $q->where('codigo', 'like', "%{$request->busca}%")
                  ->orWhere('descricao', 'like', "%{$request->busca}%");
            });
        }

        $cupons = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.cupons.index', compact('cupons'));
    }

    /**
     * Formulário de criação de cupom
     */
    public function create()
    {
        $this->checkAdmin();
        return view('admin.cupons.create');
    }

    /**
     * Armazena um novo cupom
     */
    public function store(Request $request)
    {
        $this->checkAdmin();

        $data = $request->validate([
            'codigo' => 'required|string|max:20|unique:cupons,codigo',
            'descricao' => 'required|string|max:255',
            'tipo' => 'required|in:percentual,valor_fixo',
            'valor' => 'required|numeric|min:0.01',
            'valor_minimo_pedido' => 'nullable|numeric|min:0',
            'limite_uso' => 'nullable|integer|min:1',
            'primeiro_pedido_apenas' => 'boolean',
            'valido_de' => 'required|date',
            'valido_ate' => 'required|date|after:valido_de',
            'ativo' => 'boolean',
        ]);

        // Converter código para maiúsculo
        $data['codigo'] = strtoupper($data['codigo']);
        $data['primeiro_pedido_apenas'] = $request->boolean('primeiro_pedido_apenas');
        $data['ativo'] = $request->boolean('ativo', true);

        // Validações adicionais
        if ($data['tipo'] === 'percentual' && $data['valor'] > 100) {
            return back()->withErrors(['valor' => 'Desconto percentual não pode ser maior que 100%'])
                        ->withInput();
        }

        Cupom::create($data);

        return redirect()->route('cupons.index')
                        ->with('success', 'Cupom criado com sucesso!');
    }

    /**
     * Exibe detalhes do cupom
     */
    public function show(Cupom $cupom)
    {
        $this->checkAdmin();

        $utilizacoes = $cupom->utilizacoes()
                            ->with(['user', 'order'])
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);

        return view('admin.cupons.show', compact('cupom', 'utilizacoes'));
    }

    /**
     * Formulário de edição
     */
    public function edit(Cupom $cupom)
    {
        $this->checkAdmin();
        return view('admin.cupons.edit', compact('cupom'));
    }

    /**
     * Atualiza o cupom
     */
    public function update(Request $request, Cupom $cupom)
    {
        $this->checkAdmin();

        $data = $request->validate([
            'codigo' => 'required|string|max:20|unique:cupons,codigo,' . $cupom->id,
            'descricao' => 'required|string|max:255',
            'tipo' => 'required|in:percentual,valor_fixo',
            'valor' => 'required|numeric|min:0.01',
            'valor_minimo_pedido' => 'nullable|numeric|min:0',
            'limite_uso' => 'nullable|integer|min:1',
            'primeiro_pedido_apenas' => 'boolean',
            'valido_de' => 'required|date',
            'valido_ate' => 'required|date|after:valido_de',
            'ativo' => 'boolean',
        ]);

        $data['codigo'] = strtoupper($data['codigo']);
        $data['primeiro_pedido_apenas'] = $request->boolean('primeiro_pedido_apenas');
        $data['ativo'] = $request->boolean('ativo');

        if ($data['tipo'] === 'percentual' && $data['valor'] > 100) {
            return back()->withErrors(['valor' => 'Desconto percentual não pode ser maior que 100%'])
                        ->withInput();
        }

        $cupom->update($data);

        return redirect()->route('cupons.index')
                        ->with('success', 'Cupom atualizado com sucesso!');
    }

    /**
     * Remove o cupom
     */
    public function destroy(Cupom $cupom)
    {
        $this->checkAdmin();

        if ($cupom->utilizacoes()->count() > 0) {
            return redirect()->route('cupons.index')
                           ->with('error', 'Não é possível excluir cupom que já foi utilizado.');
        }

        $cupom->delete();

        return redirect()->route('cupons.index')
                        ->with('success', 'Cupom excluído com sucesso!');
    }

    /**
     * Aplica cupom no carrinho (AJAX)
     */
    public function aplicar(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string'
        ]);

        $codigo = strtoupper($request->codigo);
        $cupom = Cupom::where('codigo', $codigo)->first();

        if (!$cupom) {
            return response()->json([
                'success' => false,
                'message' => 'Cupom não encontrado.'
            ], 404);
        }

        // Verificar se é válido
        $cartTotal = session('cart_total', 0);
        $isPrimeiroPedido = auth()->user()->orders()->count() === 0;

        if (!$cupom->isValido($cartTotal, $isPrimeiroPedido)) {
            $message = $this->getInvalidCouponMessage($cupom, $cartTotal, $isPrimeiroPedido);
            return response()->json([
                'success' => false,
                'message' => $message
            ], 400);
        }

        // Calcular desconto
        $desconto = $cupom->calcularDesconto($cartTotal);

        // Salvar na sessão
        session([
            'cupom_aplicado' => $cupom->codigo,
            'cupom_id' => $cupom->id,
            'desconto_cupom' => $desconto
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cupom aplicado com sucesso!',
            'desconto' => $desconto,
            'desconto_formatado' => 'R$ ' . number_format($desconto, 2, ',', '.'),
            'novo_total' => $cartTotal - $desconto,
            'novo_total_formatado' => 'R$ ' . number_format($cartTotal - $desconto, 2, ',', '.'),
            'cupom' => [
                'codigo' => $cupom->codigo,
                'descricao' => $cupom->descricao,
                'tipo' => $cupom->tipo,
                'valor' => $cupom->valor
            ]
        ]);
    }

    /**
     * Remove cupom do carrinho (AJAX)
     */
    public function remover()
    {
        session()->forget(['cupom_aplicado', 'cupom_id', 'desconto_cupom']);

        return response()->json([
            'success' => true,
            'message' => 'Cupom removido com sucesso!'
        ]);
    }

    /**
     * Gera código de cupom automaticamente
     */
    public function gerarCodigo()
    {
        $this->checkAdmin();

        do {
            $codigo = strtoupper(Str::random(8));
        } while (Cupom::where('codigo', $codigo)->exists());

        return response()->json(['codigo' => $codigo]);
    }

    /**
     * Retorna mensagem específica para cupom inválido
     */
    private function getInvalidCouponMessage($cupom, $cartTotal, $isPrimeiroPedido)
    {
        if (!$cupom->ativo) {
            return 'Este cupom está inativo.';
        }

        if ($cupom->valido_de > now()) {
            return 'Este cupom ainda não está válido.';
        }

        if ($cupom->valido_ate < now()) {
            return 'Este cupom expirou.';
        }

        if ($cupom->limite_uso && $cupom->vezes_usado >= $cupom->limite_uso) {
            return 'Este cupom atingiu o limite de uso.';
        }

        if ($cupom->valor_minimo_pedido && $cartTotal < $cupom->valor_minimo_pedido) {
            return 'Valor mínimo do pedido não atingido. Necessário: R$ ' . 
                   number_format($cupom->valor_minimo_pedido, 2, ',', '.');
        }

        if ($cupom->primeiro_pedido_apenas && !$isPrimeiroPedido) {
            return 'Este cupom é válido apenas para o primeiro pedido.';
        }

        return 'Cupom inválido.';
    }
}