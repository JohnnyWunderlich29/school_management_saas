<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 md:gap-6 mb-6 md:mb-8">
    <!-- Alunos -->
    <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow">
        <div class="p-4 md:p-5 bg-gradient-to-r from-blue-500 to-blue-600">
            <div class="flex items-center">
                <div class="flex-shrink-0 rounded-md bg-blue-100 bg-opacity-30 p-2 md:p-3">
                    <i class="fas fa-user-graduate text-white text-lg md:text-xl"></i>
                </div>
                <div class="ml-3 md:ml-5 min-w-0 flex-1">
                    <h3 class="text-xs md:text-sm font-medium text-blue-100 truncate">Alunos</h3>
                    <div class="mt-1 flex items-baseline">
                        <p class="text-xl md:text-2xl font-semibold text-white">{{ $totalAlunos ?? 0 }}</p>
                        <p class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-blue-100 hidden sm:block">cadastrados
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @permission('alunos.listar')
            <div class="px-4 md:px-5 py-3 bg-gray-50">
                <a href="{{ route('alunos.index') }}"
                    class="text-sm text-blue-600 hover:text-blue-500 font-medium flex items-center group">
                    Ver todos
                    <i class="fas fa-arrow-right ml-1 text-xs group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>
        @endpermission
    </div>

    <!-- Responsáveis -->
    <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow">
        <div class="p-4 md:p-5 bg-gradient-to-r from-green-500 to-green-600">
            <div class="flex items-center">
                <div class="flex-shrink-0 rounded-md bg-green-100 bg-opacity-30 p-2 md:p-3">
                    <i class="fas fa-users text-white text-lg md:text-xl"></i>
                </div>
                <div class="ml-3 md:ml-5 min-w-0 flex-1">
                    <h3 class="text-xs md:text-sm font-medium text-green-100 truncate">Responsáveis</h3>
                    <div class="mt-1 flex items-baseline">
                        <p class="text-xl md:text-2xl font-semibold text-white">{{ $totalResponsaveis ?? 0 }}</p>
                        <p class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-green-100 hidden sm:block">
                            registrados</p>
                    </div>
                </div>
            </div>
        </div>
        @permission('responsaveis.listar')
            <div class="px-4 md:px-5 py-3 bg-gray-50">
                <a href="{{ route('responsaveis.index') }}"
                    class="text-sm text-green-600 hover:text-green-500 font-medium flex items-center group">
                    Ver todos
                    <i class="fas fa-arrow-right ml-1 text-xs group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>
        @endpermission
    </div>

    <!-- Funcionários -->
    <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow">
        <div class="p-4 md:p-5 bg-gradient-to-r from-purple-500 to-purple-600">
            <div class="flex items-center">
                <div class="flex-shrink-0 rounded-md bg-purple-100 bg-opacity-30 p-2 md:p-3">
                    <i class="fas fa-user-tie text-white text-lg md:text-xl"></i>
                </div>
                <div class="ml-3 md:ml-5 min-w-0 flex-1">
                    <h3 class="text-xs md:text-sm font-medium text-purple-100 truncate">Funcionários</h3>
                    <div class="mt-1 flex items-baseline">
                        <p class="text-xl md:text-2xl font-semibold text-white">{{ $totalFuncionarios ?? 0 }}</p>
                        <p class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-purple-100 hidden sm:block">ativos
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @permission('funcionarios.listar')
            <div class="px-4 md:px-5 py-3 bg-gray-50">
                <a href="{{ route('funcionarios.index') }}"
                    class="text-sm text-purple-600 hover:text-purple-500 font-medium flex items-center group">
                    Ver todos
                    <i class="fas fa-arrow-right ml-1 text-xs group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>
        @endpermission
    </div>

    <!-- Salas -->
    <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow">
        <div class="p-4 md:p-5 bg-gradient-to-r from-orange-500 to-orange-600">
            <div class="flex items-center">
                <div class="flex-shrink-0 rounded-md bg-orange-100 bg-opacity-30 p-2 md:p-3">
                    <i class="fas fa-door-open text-white text-lg md:text-xl"></i>
                </div>
                <div class="ml-3 md:ml-5 min-w-0 flex-1">
                    <h3 class="text-xs md:text-sm font-medium text-orange-100 truncate">Salas</h3>
                    <div class="mt-1 flex items-baseline">
                        <p class="text-xl md:text-2xl font-semibold text-white">{{ $totalSalas ?? 0 }}</p>
                        <p class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-orange-100 hidden sm:block">
                            disponíveis</p>
                    </div>
                </div>
            </div>
        </div>
        @permission('salas.listar')
            <div class="px-4 md:px-5 py-3 bg-gray-50">
                <a href="{{ route('salas.index') }}"
                    class="text-sm text-orange-600 hover:text-orange-500 font-medium flex items-center group">
                    Ver todas
                    <i class="fas fa-arrow-right ml-1 text-xs group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>
        @endpermission
    </div>

    <!-- Presenças -->
    <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow">
        <div class="p-4 md:p-5 bg-gradient-to-r from-yellow-500 to-yellow-600">
            <div class="flex items-center">
                <div class="flex-shrink-0 rounded-md bg-yellow-100 bg-opacity-30 p-2 md:p-3">
                    <i class="fas fa-clipboard-check text-white text-lg md:text-xl"></i>
                </div>
                <div class="ml-3 md:ml-5 min-w-0 flex-1">
                    <h3 class="text-xs md:text-sm font-medium text-yellow-100 truncate">Presenças Hoje</h3>
                    <div class="mt-1 flex items-baseline">
                        <p class="text-xl md:text-2xl font-semibold text-white">{{ $presencasHoje ?? 0 }}</p>
                        <p class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-yellow-100 hidden sm:block">
                            registradas</p>
                    </div>
                </div>
            </div>
        </div>
        @permission('presencas.listar')
            <div class="px-4 md:px-5 py-3 bg-gray-50">
                <a href="{{ route('presencas.index') }}"
                    class="text-sm text-yellow-600 hover:text-yellow-500 font-medium flex items-center group">
                    Ver todos
                    <i class="fas fa-arrow-right ml-1 text-xs group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>
        @endpermission
    </div>
</div>
