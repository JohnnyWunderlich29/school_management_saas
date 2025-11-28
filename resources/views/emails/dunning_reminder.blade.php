<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Lembrete de Cobrança</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827;">
    <p>Olá {{ data_get($payload, 'payer_name', 'Responsável') }},</p>
    <p>Este é um lembrete sobre a fatura número <strong>{{ data_get($payload, 'invoice_number', 'N/A') }}</strong> com vencimento em <strong>{{ data_get($payload, 'due_date', 'N/A') }}</strong>.</p>
    <p>Valor: <strong>R$ {{ number_format((data_get($payload,'amount_cents',0)/100), 2, ',', '.') }}</strong></p>
    @if (data_get($payload, 'payment_url'))
        <p>Para pagar, utilize o link: <a href="{{ data_get($payload,'payment_url') }}">{{ data_get($payload,'payment_url') }}</a></p>
    @endif
    <p>Obrigado.</p>
</body>
</html>