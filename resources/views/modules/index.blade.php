@extends('layouts.app')

@section('title', 'Módulos do Sistema')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Módulos do Sistema</h1>
        <p class="text-gray-600 mt-2">Gerencie os módulos disponíveis para sua escola</p>
    </div>

    <!-- Seção de Módulos -->
    <x-modules-section />
</div>
@endsection

@push('scripts')
<script>
// Configurações globais para a página de módulos
window.modulesConfig = {
    contractUrl: '{{ route("modules.contract", ":id") }}',
    cancelUrl: '{{ route("modules.cancel", ":id") }}',
    toggleUrl: '{{ route("modules.toggle", ":id") }}',
    csrfToken: '{{ csrf_token() }}',
    // Determina a escola alvo para carregar módulos
    // Prioriza: query param -> escola vinculada ao usuário -> escola selecionada na sessão (super admin)
    schoolId: '{{ request('school_id') ?? (Auth::user() && Auth::user()->escola_id ? Auth::user()->escola_id : (Auth::user() && Auth::user()->isSuperAdmin() ? (session('escola_atual') ?? '') : '')) }}'
};
</script>
@endpush