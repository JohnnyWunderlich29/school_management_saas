<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AlertService;
use Illuminate\Support\Facades\Session;

class AlertMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $response = $next($request);

            // Converter mensagens de sessão antigas para o novo sistema
            $this->convertLegacyMessages();

            return $response;
        } catch (\Exception $e) {
            // Capturar detalhes do erro para debugging
            $errorDetails = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url' => $request->fullUrl(),
                'method' => $request->method()
            ];

            // Exibir erro detalhado no sistema de alertas
            AlertService::error(
                'Erro do Sistema: ' . $e->getMessage() . 
                ' (Arquivo: ' . basename($e->getFile()) . 
                ', Linha: ' . $e->getLine() . ')'
            );

            // Log do erro completo
            \Log::error('Erro capturado pelo AlertMiddleware', $errorDetails);

            // Re-lançar a exceção para que o Laravel possa tratá-la normalmente
            throw $e;
        }
    }

    /**
     * Converte mensagens de sessão antigas para o novo sistema de alertas
     *
     * @return void
     */
    private function convertLegacyMessages()
    {
        // Converter mensagem de sucesso
        if (Session::has('success')) {
            AlertService::success(Session::get('success'));
            Session::forget('success');
        }

        // Converter mensagem de erro
        if (Session::has('error')) {
            AlertService::error(Session::get('error'));
            Session::forget('error');
        }

        // Converter mensagem de aviso
        if (Session::has('warning')) {
            AlertService::warning(Session::get('warning'));
            Session::forget('warning');
        }

        // Converter mensagem de informação
        if (Session::has('info')) {
            AlertService::info(Session::get('info'));
            Session::forget('info');
        }

        // Converter mensagem de status
        if (Session::has('status')) {
            AlertService::success(Session::get('status'));
            Session::forget('status');
        }

        // Converter erros de validação
        if (Session::has('errors')) {
            $errors = Session::get('errors');
            if ($errors && method_exists($errors, 'any') && $errors->any()) {
                AlertService::validationErrors($errors);
            }
        }
    }
}