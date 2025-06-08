<?php

namespace App\Http\Controllers;

use App\Models\Livro;
use App\Models\Order;
use App\Models\Categoria;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RelatorioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $periodo = request('periodo', '30');
        $dataInicio = Carbon::now()->subDays($periodo);

        // Métricas principais
        $metricas = [
            'vendas_total' => Order::where('created_at', '>=', $dataInicio)->sum('total'),
            'pedidos_total' => Order::where('created_at', '>=', $dataInicio)->count(),
            'ticket_medio' => 0,
            'livros_vendidos' => DB::table('cart_items')
                ->join('carts', 'cart_items.cart_id', '=', 'carts.id')
                ->join('orders', 'orders.cart_id', '=', 'carts.id')
                ->where('orders.created_at', '>=', $dataInicio)
                ->sum('cart_items.quantity'),
            'novos_usuarios' => User::where('created_at', '>=', $dataInicio)->count(),
        ];

        $metricas['ticket_medio'] = $metricas['pedidos_total'] > 0 
            ? $metricas['vendas_total'] / $metricas['pedidos_total'] 
            : 0;

        // Top livros mais vendidos
        $topLivros = DB::table('cart_items')
            ->join('livros', 'cart_items.livro_id', '=', 'livros.id')
            ->join('carts', 'cart_items.cart_id', '=', 'carts.id')
            ->join('orders', 'orders.cart_id', '=', 'carts.id')
            ->where('orders.created_at', '>=', $dataInicio)
            ->select('livros.titulo', 'livros.autor', DB::raw('SUM(cart_items.quantity) as vendas'))
            ->groupBy('livros.id', 'livros.titulo', 'livros.autor')
            ->orderByDesc('vendas')
            ->limit(10)
            ->get();

        // Vendas por categoria
        $vendasCategoria = DB::table('cart_items')
            ->join('livros', 'cart_items.livro_id', '=', 'livros.id')
            ->join('categorias', 'livros.categoria_id', '=', 'categorias.id')
            ->join('carts', 'cart_items.cart_id', '=', 'carts.id')
            ->join('orders', 'orders.cart_id', '=', 'carts.id')
            ->where('orders.created_at', '>=', $dataInicio)
            ->select('categorias.nome', DB::raw('SUM(cart_items.quantity * cart_items.price) as total'))
            ->groupBy('categorias.id', 'categorias.nome')
            ->orderByDesc('total')
            ->get();

        // Vendas diárias
        $vendasDiarias = Order::where('created_at', '>=', $dataInicio)
            ->select(DB::raw('DATE(created_at) as data'), DB::raw('SUM(total) as total'))
            ->groupBy('data')
            ->orderBy('data')
            ->get();

        // Estoque baixo
        $estoqueBaixo = Livro::whereColumn('estoque', '<=', 'estoque_minimo')
            ->orWhere('estoque', 0)
            ->with('categoria')
            ->orderBy('estoque')
            ->limit(20)
            ->get();

        return view('admin.relatorios.index', compact(
            'metricas', 'topLivros', 'vendasCategoria', 
            'vendasDiarias', 'estoqueBaixo', 'periodo'
        ));
    }

    public function vendas(Request $request)
    {
        $dataInicio = Carbon::parse($request->get('data_inicio', Carbon::now()->subMonth()));
        $dataFim = Carbon::parse($request->get('data_fim', Carbon::now()));

        $vendas = Order::with(['cart.items.livro'])
            ->whereBetween('created_at', [$dataInicio, $dataFim])
            ->orderByDesc('created_at')
            ->paginate(50);

        $totais = [
            'pedidos' => $vendas->total(),
            'valor_total' => Order::whereBetween('created_at', [$dataInicio, $dataFim])->sum('total'),
            'ticket_medio' => 0
        ];

        $totais['ticket_medio'] = $totais['pedidos'] > 0 
            ? $totais['valor_total'] / $totais['pedidos'] 
            : 0;

        return view('admin.relatorios.vendas', compact('vendas', 'totais', 'dataInicio', 'dataFim'));
    }

    public function estoque()
    {
        $livros = Livro::with('categoria')
            ->select('*', DB::raw('(estoque * preco) as valor_estoque'))
            ->orderBy('estoque')
            ->paginate(50);

        $resumo = [
            'total_livros' => Livro::count(),
            'valor_total_estoque' => Livro::sum(DB::raw('estoque * preco')),
            'sem_estoque' => Livro::where('estoque', 0)->count(),
            'estoque_baixo' => Livro::whereColumn('estoque', '<=', 'estoque_minimo')->count()
        ];

        return view('admin.relatorios.estoque', compact('livros', 'resumo'));
    }

    public function categorias()
    {
        $categorias = Categoria::withCount('livros')
            ->with(['livros' => function($query) {
                $query->selectRaw('categoria_id, SUM(vendas_total) as total_vendas, SUM(estoque * preco) as valor_estoque')
                      ->groupBy('categoria_id');
            }])
            ->get();

        return view('admin.relatorios.categorias', compact('categorias'));
    }
}