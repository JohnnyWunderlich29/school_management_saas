@extends('layouts.marketing')

@section('title', 'Gestão Escolar - Solução Completa')

@section('content')
    <section class="bg-gradient-to-br from-sky-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 grid md:grid-cols-2 gap-10 items-center">
            <div>
                <h1 class="text-3xl md:text-5xl font-bold text-slate-900">Gestão escolar moderna para sua instituição</h1>
                <p class="mt-5 text-lg text-slate-600">Centralize matrículas, grade de aulas, financeiro, comunicação e relatórios em uma plataforma única, segura e intuitiva.</p>
                <div class="mt-8 flex gap-4">
                    <a href="{{ route('institucional.contato') }}" class="inline-flex items-center rounded-md bg-sky-600 px-6 py-3 text-white hover:bg-sky-700">Solicitar demonstração</a>
                    <a href="{{ route('institucional.funcionalidades') }}" class="inline-flex items-center rounded-md border border-slate-300 px-6 py-3 text-slate-700 hover:bg-slate-50">Ver funcionalidades</a>
                </div>
            </div>
            <div class="md:justify-self-end">
                <div class="aspect-[4/3] rounded-xl bg-sky-100"></div>
            </div>
        </div>
    </section>

    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-semibold text-slate-900">Funcionalidades principais</h2>
            <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="rounded-lg border border-slate-200 p-6">
                    <h3 class="font-semibold text-slate-900">Matrículas e alunos</h3>
                    <p class="mt-2 text-slate-600">Fluxo completo de matrícula, cadastro de responsáveis e transferência.</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-6">
                    <h3 class="font-semibold text-slate-900">Grade de aulas</h3>
                    <p class="mt-2 text-slate-600">Organização de turmas, turnos, disciplinas e professores.</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-6">
                    <h3 class="font-semibold text-slate-900">Financeiro</h3>
                    <p class="mt-2 text-slate-600">Gestão de cobranças, despesas e relatórios financeiros.</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-6">
                    <h3 class="font-semibold text-slate-900">Comunicação centralizada</h3>
                    <p class="mt-2 text-slate-600">Envio de avisos, notificações e acompanhamento de presença.</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-6">
                    <h3 class="font-semibold text-slate-900">Biblioteca e reservas</h3>
                    <p class="mt-2 text-slate-600">Controle de acervo, empréstimos e reservas.</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-6">
                    <h3 class="font-semibold text-slate-900">Relatórios e indicadores</h3>
                    <p class="mt-2 text-slate-600">Visão 360° com dados consolidados para tomada de decisão.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-semibold text-slate-900">Como funciona</h2>
            <div class="mt-8 grid md:grid-cols-3 gap-6">
                <div class="rounded-lg border border-slate-200 p-6">
                    <p class="text-sm text-slate-500">Passo 1</p>
                    <h3 class="mt-1 font-semibold">Implantação</h3>
                    <p class="mt-2 text-slate-600">Acompanhamos a configuração inicial e importação de dados.</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-6">
                    <p class="text-sm text-slate-500">Passo 2</p>
                    <h3 class="mt-1 font-semibold">Configuração</h3>
                    <p class="mt-2 text-slate-600">Defina turnos, disciplinas, permissões e preferências.</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-6">
                    <p class="text-sm text-slate-500">Passo 3</p>
                    <h3 class="mt-1 font-semibold">Operação</h3>
                    <p class="mt-2 text-slate-600">Rotinas diárias simples e rápidas, com suporte contínuo.</p>
                </div>
            </div>
            <div class="mt-10">
                <a href="{{ route('institucional.contato') }}" class="inline-flex items-center rounded-md bg-sky-600 px-6 py-3 text-white hover:bg-sky-700">Falar com especialista</a>
            </div>
        </div>
    </section>
@endsection

