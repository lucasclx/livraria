<?php
// database/seeders/CupomSeeder.php - VERSÃO CORRIGIDA

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cupom;
use Carbon\Carbon;

class CupomSeeder extends Seeder
{
    public function run()
    {
        $cupons = [
            [
                'codigo' => 'BEMVINDO10',
                'descricao' => 'Desconto de 10% para novos clientes',
                'tipo' => 'percentual',
                'valor' => 10.00,
                'valor_minimo_pedido' => 50.00,
                'limite_uso' => null,
                'primeiro_pedido_apenas' => true,
                'valido_de' => Carbon::now(),
                'valido_ate' => Carbon::now()->addMonths(6),
                'ativo' => true,
            ],
            [
                'codigo' => 'FRETE15',
                'descricao' => 'R$ 15 de desconto no frete',
                'tipo' => 'valor_fixo',
                'valor' => 15.00,
                'valor_minimo_pedido' => 80.00,
                'limite_uso' => 100,
                'primeiro_pedido_apenas' => false,
                'valido_de' => Carbon::now(),
                'valido_ate' => Carbon::now()->addMonth(),
                'ativo' => true,
            ],
            [
                'codigo' => 'NATAL2025', // CORRIGIDO DE 2024 PARA 2025
                'descricao' => 'Promoção de Natal - 20% off',
                'tipo' => 'percentual',
                'valor' => 20.00,
                'valor_minimo_pedido' => 75.00,
                'limite_uso' => 200,
                'primeiro_pedido_apenas' => false,
                'valido_de' => Carbon::now(), // CORRIGIDO PARA DATA ATUAL
                'valido_ate' => Carbon::now()->addMonths(3), // CORRIGIDO PARA 3 MESES À FRENTE
                'ativo' => true,
            ],
            [
                'codigo' => 'LIVROS50',
                'descricao' => 'R$ 50 off em compras acima de R$ 200',
                'tipo' => 'valor_fixo',
                'valor' => 50.00,
                'valor_minimo_pedido' => 200.00,
                'limite_uso' => 50,
                'primeiro_pedido_apenas' => false,
                'valido_de' => Carbon::now(),
                'valido_ate' => Carbon::now()->addMonths(3),
                'ativo' => true,
            ],
            [
                'codigo' => 'FIDELIDADE5',
                'descricao' => '5% para clientes fiéis',
                'tipo' => 'percentual',
                'valor' => 5.00,
                'valor_minimo_pedido' => 30.00,
                'limite_uso' => null,
                'primeiro_pedido_apenas' => false,
                'valido_de' => Carbon::now(),
                'valido_ate' => Carbon::now()->addYear(),
                'ativo' => true,
            ],
        ];

        foreach ($cupons as $cupomData) {
            Cupom::updateOrCreate(
                ['codigo' => $cupomData['codigo']],
                $cupomData
            );
        }

        // CORRIGIR CUPOM EXPIRADO EXISTENTE
        $cupomExistente = Cupom::where('codigo', 'NATAL2024')->first();
        if ($cupomExistente) {
            $cupomExistente->update([
                'codigo' => 'NATAL2025',
                'valido_de' => Carbon::now(),
                'valido_ate' => Carbon::now()->addMonths(3),
                'ativo' => true
            ]);
        }

        $this->command->info('Cupons corrigidos com sucesso!');
        $this->command->info('Códigos disponíveis:');
        foreach ($cupons as $cupom) {
            $status = $cupom['ativo'] ? '✅' : '❌';
            $this->command->line("  {$status} {$cupom['codigo']} - {$cupom['descricao']}");
        }
    }
}
?>