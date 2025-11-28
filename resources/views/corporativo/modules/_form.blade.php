@php
    /** @var \App\Models\Module|null $module */
    $editing = isset($module) && $module !== null;
    $action = $editing ? route('corporativo.modules.update', $module) : route('corporativo.modules.store');
    $method = $editing ? 'PUT' : 'POST';
@endphp

<form id="edit-module-form" action="{{ $action }}" method="POST">
    @csrf
    @if($editing)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Chave (name)</label>
            <input type="text" name="name" value="{{ old('name', $module->name ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
            @error('name')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Nome Exibido</label>
            <input type="text" name="display_name" value="{{ old('display_name', $module->display_name ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
            @error('display_name')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Descrição</label>
            <textarea name="description" rows="3"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('description', $module->description ?? '') }}</textarea>
            @error('description')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Categoria</label>
            <input type="text" name="category" value="{{ old('category', $module->category ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
            @error('category')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Preço</label>
            <input type="number" step="0.01" name="price" value="{{ old('price', $module->price ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
            @error('price')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Ícone</label>
            <input type="text" name="icon" value="{{ old('icon', $module->icon ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
            @error('icon')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Cor</label>
            <input type="text" name="color" value="{{ old('color', $module->color ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="#000000 ou classes">
            @error('color')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Features (JSON)</label>
            <textarea name="features_json" rows="3" placeholder='{"featureA": true}'
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('features_json', isset($module) && is_array($module->features) ? json_encode($module->features) : (is_string($module->features ?? null) ? $module->features : '')) }}</textarea>
            @error('features_json')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Ordem</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $module->sort_order ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
            @error('sort_order')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center space-x-6">
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_core" value="1" {{ old('is_core', $module->is_core ?? false) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-700">Core</span>
            </label>
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $module->is_active ?? true) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-700">Ativo</span>
            </label>
        </div>
    </div>

    <div class="mt-6 flex items-center justify-end space-x-3">
        <button type="button" onclick="closeEditModuleModal()"
                class="px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">Cancelar</button>
        <button type="submit"
                class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">Salvar</button>
    </div>
</form>