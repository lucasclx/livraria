<?php
// routes/web.php - Sistema Completo de Livraria

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Controllers
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LivroController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\FavoriteController;

/*
|--------------------------------------------------------------------------
| Rotas Públicas
|--------------------------------------------------------------------------
*/

// Página inicial - Vitrine da livraria
Route::get('/', function () {
    // Buscar livros em destaque para a página inicial
    $livrosDestaque = \App\Models\Livro::ativo()
                                      ->destaque()
                                      ->emEstoque()
                                      ->orderBy('avaliacao_media', 'desc')
                                      ->limit(8)
                                      ->get();
    
    $livrosPromocao = \App\Models\Livro::ativo()
                                      ->comDesconto()
                                      ->emEstoque()
                                      ->orderBy('desconto_percentual', 'desc')
                                      ->limit(6)
                                      ->get();
    
    $lancamentos = \App\Models\Livro::ativo()
                                   ->lancamentos(30)
                                   ->emEstoque()
                                   ->orderBy('created_at', 'desc')
                                   ->limit(6)
                                   ->get();
    
    return view('welcome', compact('livrosDestaque', 'livrosPromocao', 'lancamentos'));
})->name('home');

// Catálogo principal
Route::get('/catalogo', [LivroController::class, 'index'])->name('catalogo');

/*
|--------------------------------------------------------------------------
| Rotas de Livros (Públicas)
|--------------------------------------------------------------------------
*/

// CRUD básico de livros
Route::resource('livros', LivroController::class);

// Rotas especiais da livraria
Route::prefix('livraria')->name('livros.')->group(function () {
    
    // Seções especiais
    Route::get('/destaques', [LivroController::class, 'destaques'])->name('destaques');
    Route::get('/promocoes', [LivroController::class, 'promocoes'])->name('promocoes');
    Route::get('/lancamentos', [LivroController::class, 'lancamentos'])->name('lancamentos');
    Route::get('/mais-vendidos', [LivroController::class, 'maisVendidos'])->name('mais-vendidos');
    
    // Filtros por categoria/autor/editora
    Route::get('/categoria/{categoria}', [LivroController::class, 'categoria'])->name('categoria');
    Route::get('/autor/{autor}', [LivroController::class, 'autor'])->name('autor');
    Route::get('/editora/{editora}', [LivroController::class, 'editora'])->name('editora');
    
    // Busca avançada
    Route::get('/buscar', [LivroController::class, 'buscar'])->name('buscar');
    Route::post('/buscar', [LivroController::class, 'buscar'])->name('buscar.post');
});

/*
|--------------------------------------------------------------------------
| Rotas de Produtos (Sistema Geral)
|--------------------------------------------------------------------------
*/

// CRUD de produtos (para expansão futura)
Route::resource('produtos', ProdutoController::class);

// Rotas especiais para produtos
Route::prefix('loja')->name('produtos.')->group(function () {
    Route::get('/destaques', [ProdutoController::class, 'destaques'])->name('destaques');
    Route::get('/promocoes', [ProdutoController::class, 'promocoes'])->name('promocoes');
    Route::get('/categoria/{categoria}', [ProdutoController::class, 'categoria'])->name('categoria');
});

/*
|--------------------------------------------------------------------------
| Rotas de Categorias
|--------------------------------------------------------------------------
*/

Route::resource('categorias', CategoriaController::class);

// Rota para confirmação de exclusão
Route::get('/categorias/{categoria}/confirmar-exclusao', [CategoriaController::class, 'confirmDelete'])
    ->name('categorias.confirm-delete');

/*
|--------------------------------------------------------------------------
| Carrinho de Compras (Sem Autenticação)
|--------------------------------------------------------------------------
*/

Route::prefix('carrinho')->name('cart.')->group(function () {
    // Visualizar carrinho
    Route::get('/', [CartController::class, 'index'])->name('index');
    
    // Adicionar ao carrinho
    Route::post('/adicionar/{livro}', [CartController::class, 'add'])->name('add');
    
    // Atualizar item do carrinho
    Route::put('/item/{item}', [CartController::class, 'update'])->name('item.update');
    Route::patch('/item/{item}', [CartController::class, 'update'])->name('item.update.patch');
    
    // Remover item do carrinho
    Route::delete('/item/{item}', [CartController::class, 'remove'])->name('item.remove');
    
    // Limpar carrinho
    Route::post('/limpar', [CartController::class, 'clear'])->name('clear');
    
    // Aplicar cupom de desconto
    Route::post('/cupom', [CartController::class, 'applyCoupon'])->name('coupon');
    Route::delete('/cupom', [CartController::class, 'removeCoupon'])->name('coupon.remove');
});

/*
|--------------------------------------------------------------------------
| Rotas que Requerem Autenticação
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    
    // Dashboard do usuário
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/home', [HomeController::class, 'index'])->name('home.auth');
    
    /*
    |--------------------------------------------------------------------------
    | Checkout e Finalização de Compra
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('checkout')->name('checkout.')->group(function () {
        // Página de checkout
        Route::get('/', [CartController::class, 'checkout'])->name('index');
        
        // Processar checkout
        Route::post('/', [CartController::class, 'processCheckout'])->name('process');
        
        // Confirmação de pedido
        Route::get('/confirmacao/{order}', [CartController::class, 'confirmation'])->name('confirmation');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Pedidos do Usuário
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('meus-pedidos')->name('orders.')->group(function () {
        // Listar pedidos
        Route::get('/', [OrderController::class, 'index'])->name('index');
        
        // Visualizar pedido específico
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        
        // Cancelar pedido (se permitido)
        Route::patch('/{order}/cancelar', [OrderController::class, 'cancel'])->name('cancel');
        
        // Baixar comprovante
        Route::get('/{order}/comprovante', [OrderController::class, 'receipt'])->name('receipt');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Sistema de Favoritos
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('favoritos')->name('favorites.')->group(function () {
        // Listar favoritos
        Route::get('/', [FavoriteController::class, 'index'])->name('index');
        
        // Adicionar/remover favorito (AJAX)
        Route::post('/toggle/{livro}', [FavoriteController::class, 'toggle'])->name('toggle');
        
        // Remover favorito
        Route::delete('/{livro}', [FavoriteController::class, 'remove'])->name('remove');
        
        // Limpar todos os favoritos
        Route::delete('/', [FavoriteController::class, 'clear'])->name('clear');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Perfil do Usuário
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('perfil')->name('profile.')->group(function () {
        // Visualizar perfil
        Route::get('/', [HomeController::class, 'profile'])->name('show');
        
        // Editar perfil
        Route::get('/editar', [HomeController::class, 'editProfile'])->name('edit');
        Route::put('/editar', [HomeController::class, 'updateProfile'])->name('update');
        
        // Alterar senha
        Route::get('/senha', [HomeController::class, 'changePassword'])->name('password');
        Route::put('/senha', [HomeController::class, 'updatePassword'])->name('password.update');
        
        // Endereços de entrega
        Route::get('/enderecos', [HomeController::class, 'addresses'])->name('addresses');
        Route::post('/enderecos', [HomeController::class, 'storeAddress'])->name('addresses.store');
        Route::delete('/enderecos/{address}', [HomeController::class, 'removeAddress'])->name('addresses.remove');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Avaliações e Comentários
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('avaliacoes')->name('reviews.')->group(function () {
        // Criar avaliação
        Route::post('/livro/{livro}', [HomeController::class, 'storeReview'])->name('store');
        
        // Atualizar avaliação
        Route::put('/{review}', [HomeController::class, 'updateReview'])->name('update');
        
        // Remover avaliação
        Route::delete('/{review}', [HomeController::class, 'destroyReview'])->name('destroy');
        
        // Marcar avaliação como útil
        Route::post('/{review}/util', [HomeController::class, 'markReviewHelpful'])->name('helpful');
    });
});

/*
|--------------------------------------------------------------------------
| Rotas Administrativas (Middleware admin necessário)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard administrativo
    Route::get('/', [HomeController::class, 'adminDashboard'])->name('dashboard');
    
    /*
    |--------------------------------------------------------------------------
    | Gestão de Livros (Admin)
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('livros')->name('livros.')->group(function () {
        // Dashboard de livros
        Route::get('/dashboard', [LivroController::class, 'dashboard'])->name('dashboard');
        
        // Relatórios
        Route::get('/relatorio', [LivroController::class, 'relatorio'])->name('relatorio');
        Route::post('/relatorio/gerar', [LivroController::class, 'gerarRelatorio'])->name('relatorio.gerar');
        
        // Importação/Exportação
        Route::get('/importar', [LivroController::class, 'showImportForm'])->name('import.form');
        Route::post('/importar', [LivroController::class, 'importar'])->name('import');
        Route::get('/exportar', [LivroController::class, 'exportar'])->name('export');
        
        // Operações em lote
        Route::post('/lote/ativar', [LivroController::class, 'batchActivate'])->name('batch.activate');
        Route::post('/lote/desativar', [LivroController::class, 'batchDeactivate'])->name('batch.deactivate');
        Route::post('/lote/destaque', [LivroController::class, 'batchFeatured'])->name('batch.featured');
        Route::delete('/lote/excluir', [LivroController::class, 'batchDelete'])->name('batch.delete');
        
        // Gestão de estoque
        Route::get('/estoque', [LivroController::class, 'stockManagement'])->name('stock');
        Route::post('/estoque/atualizar', [LivroController::class, 'updateStock'])->name('stock.update');
        Route::get('/estoque/baixo', [LivroController::class, 'lowStock'])->name('stock.low');
        Route::get('/estoque/esgotado', [LivroController::class, 'outOfStock'])->name('stock.out');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Gestão de Produtos (Admin)
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('produtos')->name('produtos.')->group(function () {
        Route::get('/dashboard', [ProdutoController::class, 'dashboard'])->name('dashboard');
        Route::post('/importar', [ProdutoController::class, 'importar'])->name('import');
        Route::get('/exportar', [ProdutoController::class, 'exportar'])->name('export');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Gestão de Pedidos (Admin)
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('pedidos')->name('orders.')->group(function () {
        // Listar todos os pedidos
        Route::get('/', [OrderController::class, 'adminIndex'])->name('index');
        
        // Visualizar pedido
        Route::get('/{order}', [OrderController::class, 'adminShow'])->name('show');
        
        // Atualizar status do pedido
        Route::patch('/{order}/status', [OrderController::class, 'updateStatus'])->name('status');
        
        // Adicionar nota ao pedido
        Route::post('/{order}/nota', [OrderController::class, 'addNote'])->name('note');
        
        // Relatórios de vendas
        Route::get('/relatorio/vendas', [OrderController::class, 'salesReport'])->name('sales-report');
        Route::get('/relatorio/financeiro', [OrderController::class, 'financialReport'])->name('financial-report');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Gestão de Usuários (Admin)
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('usuarios')->name('users.')->group(function () {
        Route::get('/', [HomeController::class, 'usersIndex'])->name('index');
        Route::get('/{user}', [HomeController::class, 'usersShow'])->name('show');
        Route::patch('/{user}/status', [HomeController::class, 'toggleUserStatus'])->name('toggle-status');
        Route::delete('/{user}', [HomeController::class, 'destroyUser'])->name('destroy');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Configurações do Sistema (Admin)
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('configuracoes')->name('settings.')->group(function () {
        Route::get('/', [HomeController::class, 'settings'])->name('index');
        Route::post('/', [HomeController::class, 'updateSettings'])->name('update');
        
        // Backup e manutenção
        Route::post('/backup', [HomeController::class, 'createBackup'])->name('backup');
        Route::post('/manutencao', [HomeController::class, 'toggleMaintenance'])->name('maintenance');
        
        // Limpeza de cache
        Route::post('/cache/limpar', [HomeController::class, 'clearCache'])->name('cache.clear');
        Route::post('/logs/limpar', [HomeController::class, 'clearLogs'])->name('logs.clear');
    });
});

/*
|--------------------------------------------------------------------------
| API Routes (AJAX)
|--------------------------------------------------------------------------
*/

Route::prefix('api')->name('api.')->group(function () {
    
    // Busca em tempo real
    Route::get('/livros/buscar', [LivroController::class, 'buscarAjax'])->name('livros.buscar');
    Route::get('/produtos/buscar', [ProdutoController::class, 'buscarAjax'])->name('produtos.buscar');
    
    // Dados para filtros
    Route::get('/categorias', function() {
        return \App\Models\Livro::select('categoria')
            ->whereNotNull('categoria')
            ->distinct()
            ->orderBy('categoria')
            ->pluck('categoria');
    })->name('categorias');
    
    Route::get('/autores', function() {
        return \App\Models\Livro::select('autor')
            ->groupBy('autor')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('autor')
            ->pluck('autor');
    })->name('autores');
    
    Route::get('/editoras', function() {
        return \App\Models\Livro::select('editora')
            ->whereNotNull('editora')
            ->distinct()
            ->orderBy('editora')
            ->pluck('editora');
    })->name('editoras');
    
    Route::get('/generos', function() {
        return \App\Models\Livro::select('genero')
            ->whereNotNull('genero')
            ->distinct()
            ->orderBy('genero')
            ->pluck('genero');
    })->name('generos');
    
    // Informações do carrinho (AJAX)
    Route::get('/carrinho/count', [CartController::class, 'getCartCount'])->name('cart.count');
    Route::get('/carrinho/total', [CartController::class, 'getCartTotal'])->name('cart.total');
    
    // CEP e endereços
    Route::get('/cep/{cep}', [HomeController::class, 'getCepInfo'])->name('cep');
    
    // Middleware para APIs que precisam de autenticação
    Route::middleware('auth')->group(function () {
        // Favoritos
        Route::get('/favoritos/check/{livro}', [FavoriteController::class, 'check'])->name('favorites.check');
        
        // Notificações
        Route::get('/notificacoes', [HomeController::class, 'getNotifications'])->name('notifications');
        Route::post('/notificacoes/{notification}/marcar-lida', [HomeController::class, 'markNotificationRead'])
            ->name('notifications.read');
    });
    
    // APIs administrativas
    Route::middleware(['auth', 'admin'])->group(function () {
        // Estatísticas para dashboard
        Route::get('/dashboard/stats', [HomeController::class, 'getDashboardStats'])->name('dashboard.stats');
        Route::get('/dashboard/charts', [HomeController::class, 'getChartData'])->name('dashboard.charts');
        
        // Verificação de disponibilidade
        Route::get('/livros/{livro}/disponibilidade', [LivroController::class, 'checkAvailability'])
            ->name('livros.availability');
    });
});

/*
|--------------------------------------------------------------------------
| Rotas de Webhook e Integrações
|--------------------------------------------------------------------------
*/

Route::prefix('webhook')->name('webhook.')->group(function () {
    // Webhook para pagamentos (sem middleware de autenticação)
    Route::post('/pagamento', [OrderController::class, 'paymentWebhook'])->name('payment');
    
    // Webhook para correios/transportadoras
    Route::post('/entrega', [OrderController::class, 'deliveryWebhook'])->name('delivery');
    
    // Webhook para estoque (integrações externas)
    Route::post('/estoque', [LivroController::class, 'stockWebhook'])->name('stock');
});

/*
|--------------------------------------------------------------------------
| Rotas de Autenticação
|--------------------------------------------------------------------------
*/

Auth::routes([
    'verify' => true, // Habilita verificação de email
]);

/*
|--------------------------------------------------------------------------
| Rotas de Fallback e Páginas Especiais
|--------------------------------------------------------------------------
*/

// Página de manutenção personalizada
Route::get('/manutencao', function () {
    return view('maintenance');
})->name('maintenance');

// Política de privacidade e termos
Route::get('/privacidade', function () {
    return view('pages.privacy');
})->name('privacy');

Route::get('/termos', function () {
    return view('pages.terms');
})->name('terms');

// Sobre a livraria
Route::get('/sobre', function () {
    return view('pages.about');
})->name('about');

// Contato
Route::get('/contato', function () {
    return view('pages.contact');
})->name('contact');

Route::post('/contato', [HomeController::class, 'sendContactMessage'])->name('contact.send');

// FAQ
Route::get('/faq', function () {
    return view('pages.faq');
})->name('faq');

// Sitemap
Route::get('/sitemap.xml', [HomeController::class, 'sitemap'])->name('sitemap');

// RSS Feed
Route::get('/rss', [HomeController::class, 'rssFeed'])->name('rss');

/*
|--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------
*/

// Rota de fallback para páginas não encontradas
Route::fallback(function () {
    return view('errors.404');
});