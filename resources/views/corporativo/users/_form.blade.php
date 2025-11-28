<form id="edit-user-form" action="{{ isset($user) ? route('corporativo.users.update', $user) : route('corporativo.users.store') }}" method="POST" class="space-y-6">
    @csrf
    @if(isset($user))
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
            <input type="text" name="name" value="{{ old('name', isset($user) ? $user->name : '') }}" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email', isset($user) ? $user->email : '') }}" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        @if(auth()->user() && auth()->user()->isSuperAdmin())
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Senha (opcional)</label>
                <input type="password" name="password" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Mínimo 8 caracteres">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Senha</label>
                <input type="password" name="password_confirmation" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Escola</label>
            <select name="escola_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Sem escola</option>
                @foreach($escolas as $escola)
                    <option value="{{ $escola->id }}" {{ (old('escola_id', isset($user) ? $user->escola_id : null) == $escola->id) ? 'selected' : '' }}>{{ $escola->nome }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cargos</label>
            <select name="cargos[]" multiple class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[120px]">
                @php $userCargos = old('cargos', isset($user) ? $user->cargos->pluck('id')->toArray() : []); @endphp
                @foreach($cargos as $cargo)
                    <option value="{{ $cargo->id }}" {{ in_array($cargo->id, $userCargos) ? 'selected' : '' }}>{{ $cargo->nome }}</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">Segure Ctrl/Cmd para selecionar múltiplos cargos</p>
        </div>

        <div class="md:col-span-2 flex items-center space-x-2">
            <input type="checkbox" id="ativo" name="ativo" {{ old('ativo', isset($user) ? $user->ativo : true) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 border-gray-300 rounded">
            <label for="ativo" class="text-sm text-gray-700">Usuário ativo</label>
        </div>
    </div>

    <div class="pt-4 border-t border-gray-200 flex justify-end space-x-3">
        <button type="button" onclick="closeEditUserModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-md font-medium">Cancelar</button>
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-medium">Salvar</button>
    </div>
</form>