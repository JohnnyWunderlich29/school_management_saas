<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use App\Services\AlertService;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // Para requisições AJAX/JSON, retornar resposta JSON
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Sua sessão expirou. Por favor, faça login novamente.',
                'error' => 'Unauthenticated'
            ], 401);
        }

        // Para requisições normais, limpar sessão e redirecionar para login
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        AlertService::sessionExpired();
        return redirect()->guest(route('login'));
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        // Tratar exceções de autenticação especificamente
        if ($e instanceof AuthenticationException) {
            return $this->unauthenticated($request, $e);
        }

        // Tratar CSRF expirado (419) para evitar necessidade de F5
        if ($e instanceof TokenMismatchException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sessão expirada. Atualize a página e tente novamente.',
                    'error' => 'TokenMismatch'
                ], 419);
            }
            // Invalida a sessão atual e gera novo token
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            AlertService::sessionExpired();
            return redirect()->route('login')->with('error', 'Sua sessão expirou. Faça login novamente.');
        }

        // Tratar exceções de validação
        if ($e instanceof ValidationException && !$request->expectsJson()) {
            AlertService::validationErrors($e->errors());
            return redirect()->back()->withInput();
        }

        // Tratar exceções HTTP (403, 404, etc.)
        if ($e instanceof HttpException && !$request->expectsJson()) {
            $statusCode = $e->getStatusCode();
            
            switch ($statusCode) {
                case 403:
                    AlertService::accessDenied();
                    break;
                case 404:
                    AlertService::error('A página solicitada não foi encontrada.');
                    break;
                case 500:
                    AlertService::systemError('Ocorreu um erro interno no servidor.', $e);
                    break;
                default:
                    AlertService::error('Ocorreu um erro inesperado.');
            }
        }

        // Para erros não tratados em produção
        if (!config('app.debug') && !$request->expectsJson()) {
            AlertService::systemError('Ocorreu um erro inesperado. Nossa equipe foi notificada.', $e);
        }

        return parent::render($request, $e);
    }
}