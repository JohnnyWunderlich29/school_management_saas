<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cargo;

class AtualizarTipoCargosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Atualizando tipos de cargos existentes...');
        
        $cargos = Cargo::all();
        $atualizados = 0;
        
        foreach ($cargos as $cargo) {
            $tipoCargoAtual = $cargo->tipo_cargo;
            $novoTipoCargo = $this->identificarTipoCargo($cargo->nome);
            
            if ($novoTipoCargo && $tipoCargoAtual !== $novoTipoCargo) {
                $cargo->update(['tipo_cargo' => $novoTipoCargo]);
                $this->command->info("Cargo '{$cargo->nome}' atualizado para tipo '{$novoTipoCargo}'");
                $atualizados++;
            }
        }
        
        $this->command->info("Total de cargos atualizados: {$atualizados}");
        
        // Mostrar estatísticas por tipo
        $this->mostrarEstatisticas();
    }
    
    /**
     * Identifica o tipo do cargo baseado no nome
     */
    private function identificarTipoCargo(string $nome): ?string
    {
        $nome = strtolower($nome);
        
        // Mapeamento de palavras-chave para tipos
        $mapeamento = [
            'professor' => 'professor',
            'coordenador' => 'coordenador',
            'administrador' => 'administrador',
            'admin' => 'administrador',
            'super administrador' => 'administrador',
            'secretario' => 'secretario',
            'secretário' => 'secretario',
            'diretor' => 'diretor',
            'diretora' => 'diretor',
            'funcionario' => 'funcionario',
            'funcionário' => 'funcionario',
        ];
        
        // Verificar correspondência exata primeiro
        if (isset($mapeamento[$nome])) {
            return $mapeamento[$nome];
        }
        
        // Verificar se contém palavras-chave
        foreach ($mapeamento as $palavra => $tipo) {
            if (strpos($nome, $palavra) !== false) {
                return $tipo;
            }
        }
        
        // Se não encontrou correspondência, retornar 'outro'
        return 'outro';
    }
    
    /**
     * Mostra estatísticas dos tipos de cargo
     */
    private function mostrarEstatisticas(): void
    {
        $this->command->info('\n=== ESTATÍSTICAS POR TIPO DE CARGO ===');
        
        $tipos = ['professor', 'coordenador', 'administrador', 'secretario', 'diretor', 'funcionario', 'outro'];
        
        foreach ($tipos as $tipo) {
            $count = Cargo::where('tipo_cargo', $tipo)->count();
            if ($count > 0) {
                $this->command->info("- {$tipo}: {$count} cargos");
                
                // Mostrar nomes dos cargos deste tipo
                $cargos = Cargo::where('tipo_cargo', $tipo)->pluck('nome')->toArray();
                $this->command->line('  Cargos: ' . implode(', ', $cargos));
            }
        }
        
        // Mostrar cargos sem tipo definido
        $semTipo = Cargo::whereNull('tipo_cargo')->count();
        if ($semTipo > 0) {
            $this->command->warn("- Sem tipo definido: {$semTipo} cargos");
        }
    }
}