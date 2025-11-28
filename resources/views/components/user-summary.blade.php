@props([
    'user',
    'showCounts' => true,
])

<div>
    <h3 class="text-sm font-medium text-gray-700 mb-3">Usuário</h3>
    <div class="flex items-center mb-4">
        <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold mr-3">
            {{ strtoupper(substr($user->name ?? '?', 0, 1)) }}
        </div>
        <div>
            <div class="font-medium text-gray-900">{{ $user->name }}</div>
            <div class="text-sm text-gray-500">{{ $user->email }}</div>
        </div>
    </div>
    @if($showCounts)
        <div class="grid grid-cols-3 gap-3">
            <div class="text-center border rounded p-3">
                <div class="text-indigo-600 font-bold">{{ $user->emprestimos()->count() }}</div>
                <div class="text-xs text-gray-500">Empréstimos</div>
            </div>
            <div class="text-center border rounded p-3">
                <div class="text-yellow-600 font-bold">{{ $user->reservas()->where('status', 'ativa')->count() }}</div>
                <div class="text-xs text-gray-500">Reservas Ativas</div>
            </div>
            <div class="text-center border rounded p-3">
                <div class="text-red-600 font-bold">{{ $user->multas()->where('status', 'pendente')->count() }}</div>
                <div class="text-xs text-gray-500">Multas</div>
            </div>
        </div>
    @endif
</div>