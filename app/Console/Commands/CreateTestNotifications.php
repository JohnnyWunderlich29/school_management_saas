<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CreateTestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:create-test 
                            {--count=10 : Número de notificações a criar}
                            {--user= : ID do usuário específico (opcional)}
                            {--global : Criar notificações globais}
                            {--clean : Limpar notificações existentes antes de criar}';

    /**
     * The console command description.
     */
    protected $description = 'Criar notificações de teste para desenvolvimento e debugging';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $count = (int) $this->option('count');
            $userId = $this->option('user');
            $isGlobal = $this->option('global');
            $clean = $this->option('clean');

            // Validar parâmetros
            if ($count <= 0 || $count > 100) {
                $this->error('O número de notificações deve estar entre 1 e 100.');
                return Command::FAILURE;
            }

            // Limpar notificações existentes se solicitado
            if ($clean) {
                $this->info('Limpando notificações existentes...');
                $deleted = Notification::truncate();
                $this->info('Notificações limpas com sucesso.');
            }

            // Verificar se há usuários no sistema
            $usersCount = User::count();
            if ($usersCount === 0) {
                $this->error('Nenhum usuário encontrado no sistema. Crie usuários primeiro.');
                return Command::FAILURE;
            }

            // Obter usuário específico ou primeiro usuário disponível
            if ($userId) {
                $user = User::find($userId);
                if (!$user) {
                    $this->error("Usuário com ID {$userId} não encontrado.");
                    return Command::FAILURE;
                }
            } else {
                $user = User::first();
            }

            $this->info("Criando {$count} notificações de teste...");
            $this->info($isGlobal ? 'Tipo: Globais' : "Tipo: Para usuário {$user->name} (ID: {$user->id})");

            $bar = $this->output->createProgressBar($count);
            $bar->start();

            $types = ['success', 'info', 'warning', 'error'];
            $titles = [
                'success' => [
                    'Operação realizada com sucesso',
                    'Dados salvos com sucesso',
                    'Processo concluído',
                    'Tarefa finalizada'
                ],
                'info' => [
                    'Nova atualização disponível',
                    'Informação importante',
                    'Lembrete do sistema',
                    'Notificação informativa'
                ],
                'warning' => [
                    'Atenção necessária',
                    'Verificação pendente',
                    'Ação recomendada',
                    'Aviso do sistema'
                ],
                'error' => [
                    'Erro detectado',
                    'Falha na operação',
                    'Problema identificado',
                    'Erro crítico'
                ]
            ];

            $messages = [
                'success' => [
                    'Sua solicitação foi processada com sucesso.',
                    'Os dados foram salvos corretamente no sistema.',
                    'O processo foi concluído sem erros.',
                    'A tarefa foi finalizada com êxito.'
                ],
                'info' => [
                    'Uma nova versão do sistema está disponível.',
                    'Informações importantes sobre o sistema.',
                    'Lembre-se de verificar suas configurações.',
                    'Notificação informativa do sistema.'
                ],
                'warning' => [
                    'Sua atenção é necessária para esta questão.',
                    'Há uma verificação pendente que requer sua ação.',
                    'Recomendamos que você tome uma ação.',
                    'O sistema detectou uma situação que requer atenção.'
                ],
                'error' => [
                    'Um erro foi detectado no sistema.',
                    'A operação falhou e precisa ser repetida.',
                    'Foi identificado um problema que requer correção.',
                    'Erro crítico que necessita atenção imediata.'
                ]
            ];

            $actionUrls = [
                '/dashboard',
                '/alunos',
                '/funcionarios',
                '/escalas',
                '/reports'
            ];

            $actionTexts = [
                'Ver detalhes',
                'Acessar',
                'Verificar',
                'Corrigir',
                'Visualizar'
            ];

            $created = 0;

            for ($i = 0; $i < $count; $i++) {
                $type = $types[array_rand($types)];
                $title = $titles[$type][array_rand($titles[$type])];
                $message = $messages[$type][array_rand($messages[$type])];
                
                $hasAction = rand(0, 1) === 1;
                $actionUrl = $hasAction ? $actionUrls[array_rand($actionUrls)] : null;
                $actionText = $hasAction ? $actionTexts[array_rand($actionTexts)] : null;

                $data = [
                    'test' => true,
                    'created_by_command' => true,
                    'batch_id' => uniqid('test_'),
                    'priority' => rand(1, 5)
                ];

                try {
                    if ($isGlobal) {
                        Notification::createGlobal(
                            $type,
                            $title,
                            $message,
                            $data,
                            $actionUrl,
                            $actionText
                        );
                    } else {
                        Notification::createForUser(
                            $user->id,
                            $type,
                            $title,
                            $message,
                            $data,
                            $actionUrl,
                            $actionText
                        );
                    }
                    $created++;
                } catch (\Exception $e) {
                    Log::error('Erro ao criar notificação de teste', [
                        'error' => $e->getMessage(),
                        'iteration' => $i
                    ]);
                }

                $bar->advance();
                
                // Pequena pausa para simular criação em momentos diferentes
                if ($i % 3 === 0) {
                    usleep(100000); // 0.1 segundo
                }
            }

            $bar->finish();
            $this->newLine(2);

            // Estatísticas finais
            $this->info("✅ {$created} notificações criadas com sucesso!");
            
            $totalNotifications = Notification::count();
            $totalUnread = Notification::whereNull('read_at')->count();
            
            if (!$isGlobal) {
                $userNotifications = Notification::forUser($user->id)->count();
                $userUnread = Notification::getUnreadCountForUser($user->id);
                
                $this->table(
                    ['Métrica', 'Valor'],
                    [
                        ['Total de notificações no sistema', $totalNotifications],
                        ['Total não lidas no sistema', $totalUnread],
                        ["Notificações do usuário {$user->name}", $userNotifications],
                        ["Não lidas do usuário {$user->name}", $userUnread]
                    ]
                );
            } else {
                $globalNotifications = Notification::where('is_global', true)->count();
                
                $this->table(
                    ['Métrica', 'Valor'],
                    [
                        ['Total de notificações no sistema', $totalNotifications],
                        ['Total não lidas no sistema', $totalUnread],
                        ['Notificações globais', $globalNotifications]
                    ]
                );
            }

            $this->info('\n🎯 Para testar as notificações:');
            $this->info('1. Acesse: http://localhost:8000/');
            $this->info('2. Verifique o ícone de notificações no header');
            $this->info('3. Clique para ver o dropdown de notificações');
            $this->info('4. Teste marcar como lida e marcar todas como lidas');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Erro ao criar notificações de teste: ' . $e->getMessage());
            Log::error('Erro no comando de criar notificações de teste', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }
}