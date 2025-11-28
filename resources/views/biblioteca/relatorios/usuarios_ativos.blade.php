@php($usuarios = $usuarios ?? collect())
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">E-mail</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empréstimos</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reservas</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Atividade</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($usuarios as $index => $usuario)
                <tr class="odd:bg-gray-50">
                    <td class="px-4 py-2 text-sm text-gray-700">{{ $index + 1 }}</td>
                    <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $usuario->name }}</td>
                    <td class="px-4 py-2"><div class="text-xs text-gray-500">{{ $usuario->email }}</div></td>
                    <td class="px-4 py-2"><span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">{{ $usuario->emprestimos_count ?? 0 }}</span></td>
                    <td class="px-4 py-2"><span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800">{{ $usuario->reservas_count ?? 0 }}</span></td>
                    <td class="px-4 py-2 text-sm text-gray-700">{{ optional($usuario->updated_at)->format('d/m/Y H:i') ?? '—' }}</td>
                </tr>
            @empty
                <tr class="odd:bg-gray-50">
                    <td colspan="6" class="px-4 py-4 text-center text-sm text-gray-500">Nenhum dado disponível</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($usuarios, 'links'))
    <div class="mt-3 flex justify-center">
        {{ $usuarios->links() }}
    </div>
@endif