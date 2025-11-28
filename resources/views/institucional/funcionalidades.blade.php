@extends('layouts.marketing')

@section('title', 'Funcionalidades do Sistema')

@section('content')
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-slate-900">Funcionalidades</h1>
            <p class="mt-4 text-slate-600">Conheça os módulos que tornam a gestão da sua escola mais simples e eficiente.</p>

            <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="rounded-lg border border-slate-200 p-6">
                    <h3 class="font-semibold text-slate-900">Matrículas</h3>
                    <p class="mt-2 text-slate-600">Fluxo guiado de matrícula, cadastro de responsáveis e transferência.</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-6">
                    <h3 class="font-semibold text-slate-900">Grade de Aulas</h3>
                    <p class="mt-2 text-slate-600">Organize turmas, disciplinas, turnos, professores e salas.</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-6">
                    <h3 class="font-semibold text-slate-900">Financeiro</h3>
                    <p class="mt-2 text-slate-600">Cobranças, despesas, relatórios e integrações de pagamento.</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-6">
                    <h3 class="font-semibold text-slate-900">Comunicação</h3>
                    <p class="mt-2 text-slate-600">Envie avisos e notificações e acompanhe presença e ocorrências.</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-6">
                    <h3 class="font-semibold text-slate-900">Biblioteca</h3>
                    <p class="mt-2 text-slate-600">Controle de acervo, empréstimos, reservas e relatórios.</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-6">
                    <h3 class="font-semibold text-slate-900">Relatórios</h3>
                    <p class="mt-2 text-slate-600">Indicadores consolidados para decisões mais rápidas.</p>
                </div>
            </div>

            <div class="mt-12">
                <a href="{{ route('institucional.contato') }}" class="inline-flex items-center rounded-md bg-sky-600 px-6 py-3 text-white hover:bg-sky-700">Solicitar demonstração</a>
            </div>
        </div>
    </section>
@endsection

