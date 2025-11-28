<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview - Planejamento</title>
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; color: #111827; }
        .container { max-width: 900px; margin: 0 auto; padding: 24px; }
        h1 { font-size: 1.5rem; margin-bottom: 8px; }
        h2 { font-size: 1.25rem; margin: 16px 0 8px; }
        .muted { color: #6b7280; }
        .section { border: 1px solid #e5e7eb; border-radius: 8px; margin: 16px 0; }
        .section-header { padding: 12px 16px; border-bottom: 1px solid #e5e7eb; background: #f9fafb; }
        .section-body { padding: 16px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .item { padding: 8px 0; }
        .label { font-size: 0.875rem; color: #6b7280; }
        .value { font-weight: 500; }
        @media print {
            .no-print { display: none !important; }
            body { color: #000; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print" style="margin-bottom:12px; display:flex; gap:8px;">
            <button onclick="window.print()" style="padding:8px 12px; border:1px solid #d1d5db; background:#fff; border-radius:6px; cursor:pointer;">Imprimir</button>
            <a href="{{ route('planejamentos.view', $planejamento) }}" style="padding:8px 12px; border:1px solid #d1d5db; background:#fff; border-radius:6px; text-decoration:none;">Voltar à visualização</a>
        </div>

        <header>
            <h1>{{ $planejamento->titulo }}</h1>
            <div class="muted">
                Professor: {{ $planejamento->professor->name ?? $planejamento->criador->name ?? '—' }}
                • Turma: {{ $planejamento->turma->nome ?? '—' }}
                • Disciplina: {{ $planejamento->disciplina->nome ?? '—' }}
                • Período: {{ optional($planejamento->data_inicio)->format('d/m/Y') }} a {{ optional($planejamento->data_fim)->format('d/m/Y') }}
            </div>
        </header>

        <section class="section">
            <div class="section-header"><h2>Informações Gerais</h2></div>
            <div class="section-body">
                @include('planejamentos.view.sections.informacoes', ['planejamento' => $planejamento])
            </div>
        </section>

        <section class="section">
            <div class="section-header"><h2>Configuração</h2></div>
            <div class="section-body">
                @include('planejamentos.view.sections.configuracao', ['planejamento' => $planejamento])
            </div>
        </section>

        <section class="section">
            <div class="section-header"><h2>Período e Duração</h2></div>
            <div class="section-body">
                @include('planejamentos.view.sections.periodo', ['planejamento' => $planejamento])
            </div>
        </section>

        <section class="section">
            <div class="section-header"><h2>Conteúdo Pedagógico</h2></div>
            <div class="section-body">
                @include('planejamentos.view.sections.conteudo', ['planejamento' => $planejamento])
            </div>
        </section>

        <section class="section">
            <div class="section-header"><h2>Metodologia</h2></div>
            <div class="section-body">
                @include('planejamentos.view.sections.metodologia', ['planejamento' => $planejamento])
            </div>
        </section>

        <section class="section">
            <div class="section-header"><h2>Avaliação</h2></div>
            <div class="section-body">
                @include('planejamentos.view.sections.avaliacao', ['planejamento' => $planejamento])
            </div>
        </section>

        <section class="section">
            <div class="section-header"><h2>Recursos</h2></div>
            <div class="section-body">
                @include('planejamentos.view.sections.recursos', ['planejamento' => $planejamento])
            </div>
        </section>

        <section class="section">
            <div class="section-header"><h2>Observações</h2></div>
            <div class="section-body">
                @include('planejamentos.view.sections.observacoes', ['planejamento' => $planejamento])
            </div>
        </section>
    </div>
</body>
</html>