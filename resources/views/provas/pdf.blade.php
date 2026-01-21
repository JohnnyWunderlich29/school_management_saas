<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>{{ $prova->titulo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            color: #333;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .school-name {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .exam-info {
            margin-bottom: 20px;
        }

        .student-field {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 20px;
        }

        .student-field table {
            width: 100%;
        }

        .questao {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .questao-titulo {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .questao-imagem {
            max-width: 100%;
            height: auto;
            margin-bottom: 10px;
            text-align: center;
        }

        .questao-imagem img {
            max-width: 300px;
        }

        .alternativa {
            margin-left: 20px;
            margin-bottom: 5px;
        }

        .alternativa-box {
            display: inline-block;
            width: 15px;
            height: 15px;
            border: 1px solid #000;
            margin-right: 10px;
            vertical-align: middle;
        }

        .descritiva-espaco {
            border-bottom: 1px dotted #ccc;
            height: 100px;
            margin-top: 10px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="school-name">{{ $prova->escola->nome }}</div>
        <div>Avaliação Acadêmica</div>
    </div>

    <div class="exam-info">
        <table style="width: 100%;">
            <tr>
                <td><strong>Título:</strong> {{ $prova->titulo }}</td>
                <td style="text-align: right;"><strong>Data:</strong> {{ $prova->data_aplicacao->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td><strong>Disciplina:</strong> {{ $prova->disciplina->nome }}</td>
                <td style="text-align: right;"><strong>Professor:</strong>
                    {{ $prova->professor ? $prova->professor->nome_completo : 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Turma:</strong> {{ $prova->turma->nome }}</td>
                <td style="text-align: right;"><strong>Valor:</strong> {{ $prova->questoes->sum('valor') }} pts</td>
            </tr>
        </table>
    </div>

    <div class="student-field">
        <table>
            <tr>
                <td style="width: 70%;"><strong>Aluno(a):</strong>
                    __________________________________________________________________</td>
                <td style="text-align: right;"><strong>Nota:</strong> __________</td>
            </tr>
        </table>
    </div>

    @if ($prova->descricao)
        <div style="margin-bottom: 20px; font-style: italic; font-size: 10pt;">
            <strong>Instruções:</strong> {{ $prova->descricao }}
        </div>
    @endif

    <div class="questoes">
        @foreach ($prova->questoes as $index => $questao)
            <div class="questao">
                <div class="questao-titulo">
                    Questão {{ $index + 1 }} ({{ number_format($questao->valor, 1, ',', '.') }} pts)
                </div>
                <div class="enunciado">
                    {!! nl2br(e($questao->enunciado)) !!}
                </div>

                @if ($questao->imagem_path)
                    <div class="questao-imagem">
                        <img src="{{ public_path('storage/' . $questao->imagem_path) }}" alt="Imagem da questão">
                    </div>
                @endif

                @if ($questao->tipo === 'multipla_escolha')
                    <div class="alternativas" style="margin-top: 10px;">
                        @foreach ($questao->alternativas as $aIndex => $alternativa)
                            <div class="alternativa">
                                <span class="alternativa-box"></span>
                                ({{ chr(65 + $aIndex) }})
                                {{ $alternativa->texto }}
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="descritiva-espaco"></div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="footer">
        Gerado pelo sistema de gestão escolar - {{ date('d/m/Y H:i') }}
    </div>
</body>

</html>
