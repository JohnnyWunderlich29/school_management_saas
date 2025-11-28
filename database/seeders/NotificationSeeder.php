<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();
        
        if ($users->isEmpty()) {
            return;
        }

        // Notificações globais
        Notification::create([
            'type' => 'system',
            'title' => 'Sistema de Notificações Ativado',
            'message' => 'O novo sistema de notificações foi implementado com sucesso! Agora você receberá alertas importantes sobre atividades da escola.',
            'data' => json_encode(['feature' => 'notifications']),
            'is_global' => true,
            'action_url' => '/notifications',
            'action_text' => 'Ver Notificações',
            'created_at' => Carbon::now()->subHours(2)
        ]);

        Notification::create([
            'type' => 'announcement',
            'title' => 'Novo Sistema de Relatórios',
            'message' => 'Implementamos um sistema avançado de relatórios! Gere relatórios de presença, escalas, desempenho e financeiros com facilidade.',
            'data' => json_encode(['feature' => 'reports']),
            'is_global' => true,
            'action_url' => '/reports',
            'action_text' => 'Acessar Relatórios',
            'created_at' => Carbon::now()->subHours(1)
        ]);

        Notification::create([
            'type' => 'maintenance',
            'title' => 'Manutenção Programada',
            'message' => 'Haverá uma manutenção programada no sistema no próximo domingo das 02:00 às 04:00. Durante este período, o sistema pode ficar indisponível.',
            'data' => json_encode(['scheduled_date' => Carbon::now()->addDays(7)->format('Y-m-d H:i:s')]),
            'is_global' => true,
            'created_at' => Carbon::now()->subMinutes(30)
        ]);

        // Notificações específicas para usuários
        foreach ($users->take(3) as $user) {
            Notification::create([
                'type' => 'reminder',
                'title' => 'Lembrete: Registrar Presenças',
                'message' => 'Não se esqueça de registrar as presenças dos alunos de hoje. É importante manter os registros atualizados.',
                'data' => json_encode(['date' => Carbon::today()->format('Y-m-d')]),
                'user_id' => $user->id,
                'action_url' => '/presencas',
                'action_text' => 'Registrar Presenças',
                'created_at' => Carbon::now()->subMinutes(15)
            ]);

            Notification::create([
                'type' => 'info',
                'title' => 'Perfil Atualizado',
                'message' => 'Seu perfil foi atualizado com sucesso. Verifique se todas as informações estão corretas.',
                'data' => json_encode(['profile_updated' => true]),
                'user_id' => $user->id,
                'action_url' => '/profile',
                'action_text' => 'Ver Perfil',
                'read_at' => $user->id === $users->first()->id ? Carbon::now()->subMinutes(5) : null,
                'created_at' => Carbon::now()->subHours(3)
            ]);
        }

        // Notificação de alerta para o primeiro usuário
        if ($users->isNotEmpty()) {
            Notification::create([
                'type' => 'alert',
                'title' => 'Atenção: Muitas Ausências',
                'message' => 'Foi detectado um número elevado de ausências na turma A. Recomendamos verificar a situação dos alunos.',
                'data' => json_encode([
                    'class' => 'Turma A',
                    'absence_count' => 15,
                    'threshold' => 10
                ]),
                'user_id' => $users->first()->id,
                'action_url' => '/presencas',
                'action_text' => 'Verificar Presenças',
                'created_at' => Carbon::now()->subMinutes(45)
            ]);
        }
    }
}