<?php

namespace App\Services;

class AlertService
{
    /**
     * Tipos de alerta disponíveis
     */
    const TYPE_SUCCESS = 'success';
    const TYPE_ERROR = 'error';
    const TYPE_WARNING = 'warning';
    const TYPE_INFO = 'info';

    /**
     * Adiciona um alerta à sessão
     *
     * @param string $message
     * @param string $type
     * @param array $options
     * @return void
     */
    public static function add($message, $type = self::TYPE_INFO, $options = [])
    {

        $alert = [
            'message' => $message,
            'type' => $type,
            'dismissible' => $options['dismissible'] ?? true,
            'timeout' => $options['timeout'] ?? 5000,
            'errors' => $options['errors'] ?? null,
            'actions' => $options['actions'] ?? [],
            'id' => uniqid('alert_')
        ];

        $alerts = session('alerts', []);
        $alerts[] = $alert;
        session(['alerts' => $alerts]);
    }

    /**
     * Adiciona um alerta de sucesso
     *
     * @param string $message
     * @param array $options
     * @return void
     */
    public static function success($message, $options = [])
    {
        self::add($message, self::TYPE_SUCCESS, $options);
    }

    /**
     * Adiciona um alerta de erro
     *
     * @param string $message
     * @param array $options
     * @return void
     */
    public static function error($message, $options = [])
    {
        self::add($message, self::TYPE_ERROR, $options);
    }

    /**
     * Adiciona um alerta de aviso
     *
     * @param string $message
     * @param array $options
     * @return void
     */
    public static function warning($message, $options = [])
    {
        self::add($message, self::TYPE_WARNING, $options);
    }

    /**
     * Adiciona um alerta de informação
     *
     * @param string $message
     * @param array $options
     * @return void
     */
    public static function info($message, $options = [])
    {
        self::add($message, self::TYPE_INFO, $options);
    }

    /**
     * Adiciona um alerta de erro de validação
     *
     * @param \Illuminate\Support\MessageBag|array $errors
     * @param array $options
     * @return array Lista de erros processados
     */
    public static function validationErrors($errors, $options = [])
    {
        $errorList = [];
        
        if (is_object($errors) && method_exists($errors, 'all')) {
            // MessageBag do Laravel
            $errorList = $errors->all();
        } elseif (is_array($errors)) {
            // Array de erros - pode ser associativo ou indexado
            $errorList = self::flattenErrorArray($errors);
        } else {
            // String simples
            $errorList = [$errors];
        }

        \Log::info('AlertService validationErrors called with:', [
            'originalErrors' => $errors,
            'processedErrorList' => $errorList,
            'errorCount' => count($errorList)
        ]);

        if (empty($errorList)) {
            \Log::warning('AlertService validationErrors called with empty error list');
            return [];
        }

        $message = 'Por favor, corrija os seguintes erros:';
        $options['errors'] = $errorList;
        $options['timeout'] = $options['timeout'] ?? 8000;

        self::add($message, self::TYPE_ERROR, $options);
        
        // Retorna os erros processados para uso em respostas JSON
        return $errorList;
    }

    /**
     * Converte array de erros (associativo ou não) em lista simples
     *
     * @param array $errors
     * @return array
     */
    private static function flattenErrorArray($errors)
    {
        $flattened = [];
        
        foreach ($errors as $key => $value) {
            if (is_array($value)) {
                // Se o valor é um array, achatar recursivamente
                $flattened = array_merge($flattened, self::flattenErrorArray($value));
            } elseif (is_object($value)) {
                // Se é um objeto (como ViewErrorBag), tentar converter para string
                if (method_exists($value, '__toString')) {
                    $flattened[] = (string) $value;
                } elseif (method_exists($value, 'toArray')) {
                    $flattened = array_merge($flattened, self::flattenErrorArray($value->toArray()));
                } else {
                    $flattened[] = 'Erro de validação';
                }
            } else {
                // String simples
                $flattened[] = (string) $value;
            }
        }
        
        return $flattened;
    }

    /**
     * Adiciona um alerta com ações personalizadas
     *
     * @param string $message
     * @param string $type
     * @param array $actions
     * @param array $options
     * @return void
     */
    public static function withActions($message, $type, $actions, $options = [])
    {
        $options['actions'] = $actions;
        $options['timeout'] = $options['timeout'] ?? 0; // Não remove automaticamente se tem ações
        self::add($message, $type, $options);
    }

    /**
     * Obtém todos os alertas da sessão
     *
     * @return array
     */
    public static function getAlerts()
    {
        return session('alerts', []);
    }

    /**
     * Limpa todos os alertas da sessão
     *
     * @return void
     */
    public static function clear()
    {
        session()->forget('alerts');
    }

    /**
     * Adiciona um alerta de erro de sistema com ações de suporte
     *
     * @param string $message
     * @param \Exception|null $exception
     * @param array $options
     * @return void
     */
    public static function systemError($message, $exception = null, $options = [])
    {
        $actions = [
            [
                'label' => 'Tentar Novamente',
                'action' => 'reload',
                'class' => 'bg-red-600 hover:bg-red-700 text-white'
            ],
            [
                'label' => 'Reportar Problema',
                'action' => 'report',
                'class' => 'bg-gray-600 hover:bg-gray-700 text-white'
            ]
        ];

        if ($exception && config('app.debug')) {
            $message .= ' (Erro: ' . $exception->getMessage() . ')';
        }

        self::withActions($message, self::TYPE_ERROR, $actions, $options);
    }

    /**
     * Adiciona um alerta de sessão expirada
     *
     * @return void
     */
    public static function sessionExpired()
    {
        $actions = [
            [
                'label' => 'Fazer Login',
                'action' => 'login',
                'url' => route('login'),
                'class' => 'bg-blue-600 hover:bg-blue-700 text-white'
            ]
        ];

        self::withActions(
            'Sua sessão expirou. Por favor, faça login novamente.',
            self::TYPE_WARNING,
            $actions
        );
    }

    /**
     * Adiciona um alerta de permissão negada
     *
     * @param string $action
     * @return void
     */
    public static function accessDenied($action = 'realizar esta ação')
    {
        $actions = [
            [
                'label' => 'Voltar',
                'action' => 'back',
                'class' => 'bg-gray-600 hover:bg-gray-700 text-white'
            ]
        ];

        self::withActions(
            "Você não tem permissão para {$action}.",
            self::TYPE_ERROR,
            $actions
        );
    }
}