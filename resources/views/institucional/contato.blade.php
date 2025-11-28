@extends('layouts.marketing')

@section('title', 'Contato')

@section('content')
    <section class="py-16">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-slate-900">Fale com nosso time</h1>
            <p class="mt-4 text-slate-600">Preencha o formulário e receba uma demonstração personalizada.</p>

            @if(session('status'))
                <div class="mt-6 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-green-700">{{ session('status') }}</div>
            @endif

            <form action="{{ route('institucional.leads.store') }}" method="post" class="mt-8 space-y-6">
                @csrf
                <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">

                <div>
                    <label class="block text-sm font-medium text-slate-700">Nome</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" required>
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">E-mail</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" required>
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Telefone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                        @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Escola</label>
                        <input type="text" name="school" value="{{ old('school') }}" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                        @error('school')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Cargo</label>
                    <input type="text" name="role" value="{{ old('role') }}" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                    @error('role')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Mensagem</label>
                    <textarea name="message" rows="5" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" required>{{ old('message') }}</textarea>
                    @error('message')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <input type="hidden" name="origin_url" value="{{ request()->fullUrl() }}">
                <input type="hidden" name="utm_source" value="{{ request()->get('utm_source') }}">
                <input type="hidden" name="utm_medium" value="{{ request()->get('utm_medium') }}">
                <input type="hidden" name="utm_campaign" value="{{ request()->get('utm_campaign') }}">
                <input type="hidden" name="utm_term" value="{{ request()->get('utm_term') }}">
                <input type="hidden" name="utm_content" value="{{ request()->get('utm_content') }}">

                <label class="flex items-start gap-3 text-sm text-slate-700">
                    <input type="checkbox" name="consent" value="1" class="mt-1 rounded border-slate-300" required>
                    <span>Autorizo o contato para fins de demonstração e concordo com o tratamento dos dados conforme a LGPD.</span>
                </label>
                @error('consent')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

                <div class="pt-2">
                    <button type="submit" class="inline-flex items-center rounded-md bg-sky-600 px-6 py-3 text-white hover:bg-sky-700">Enviar</button>
                </div>
            </form>

            <div class="mt-10">
                <a href="mailto:contato@exemplo.com" class="text-sky-700 hover:text-sky-800">contato@exemplo.com</a>
                <span class="mx-2 text-slate-400">•</span>
                <a href="https://wa.me/5500000000000" target="_blank" rel="noopener" class="text-sky-700 hover:text-sky-800">WhatsApp</a>
            </div>
        </div>
    </section>
@endsection

