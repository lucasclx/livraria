<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Verificar se já existe antes de criar
        $admin = User::where('email', 'admin@livraria.com')->first();
        
        if (!$admin) {
            User::create([
                'name' => 'Administrador',
                'email' => 'admin@livraria.com',
                'password' => Hash::make('admin123'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]);
            
            $this->command->info('Usuário admin criado com sucesso!');
            $this->command->info('Email: admin@livraria.com');
            $this->command->info('Senha: admin123');
        } else {
            // Garantir que tenha permissão admin
            if (!$admin->is_admin) {
                $admin->update(['is_admin' => true]);
                $this->command->info('Permissões admin atualizadas para usuário existente!');
            } else {
                $this->command->info('Usuário admin já existe!');
                $this->command->info('Email: admin@livraria.com');
                $this->command->info('Senha: admin123');
            }
        }

        // Criar usuário de teste
        $testUser = User::where('email', 'user@livraria.com')->first();
        
        if (!$testUser) {
            User::create([
                'name' => 'Usuário Teste',
                'email' => 'user@livraria.com',
                'password' => Hash::make('user123'),
                'is_admin' => false,
                'email_verified_at' => now(),
            ]);
            
            $this->command->info('Usuário teste criado com sucesso!');
            $this->command->info('Email: user@livraria.com');
            $this->command->info('Senha: user123');
        }
    }
}