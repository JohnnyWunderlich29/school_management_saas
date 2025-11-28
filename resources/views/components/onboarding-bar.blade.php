@php
    use Illuminate\Support\Facades\Config;
    $steps = Config::get('onboarding.steps', []);

    $user = auth()->user();
    $isAdminSupport = $user && ((method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || (method_exists($user, 'temCargo') && $user->temCargo('Suporte')));

    // Escolhe de quem ler as preferências: do próprio usuário ou do dono da escola atual (quando SuperAdmin/Suporte)
    $contextUser = $user;
    if ($isAdminSupport) {
        $escolaId = session('escola_atual') ?? ($user->escola_id ?? null);
        if ($escolaId) {
            $owner = \App\Models\User::where('escola_id', $escolaId)->orderBy('id', 'asc')->first();
            if ($owner) {
                $contextUser = $owner;
            }
        }
    }

    $prefs = $contextUser->preferences ?? [];
    $completed = $prefs['onboarding_completed'] ?? session('onboarding.completed', []);
    $total = count($steps);
    $done = count($completed);
    $progress = $total > 0 ? (int) round(($done / $total) * 100) : 0;
    $closed = (bool) ($prefs['onboarding_closed'] ?? session('onboarding.closed', false));
    $minimized = (bool) ($prefs['onboarding_minimized'] ?? session('onboarding.minimized', false));
    $isOwner = $contextUser && method_exists($contextUser, 'isSchoolOwner') ? $contextUser->isSchoolOwner() : false;
    $canView = $isOwner || $isAdminSupport;
    $show = $canView && !$closed && $total > 0 && $done < $total;
    $ownerName = $contextUser->name ?? null;
    $ownerEmail = $contextUser->email ?? null;
@endphp

@if($show)
<div id="onboarding-bar" class="fixed bottom-0 left-0 right-0 z-40" role="region" aria-label="Barra de onboarding"
     x-data="{ minimized: {{ $minimized ? 'true' : 'false' }}, startY: 0, endY: 0, anim: false, threshold: 32, visible: false }"
     x-init="$nextTick(() => { visible = true })"
     x-show="visible"
     x-transition:enter="transform transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transform transition ease-in duration-250"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-4">
    <div class="mx-auto max-w-7xl px-2 sm:px-3 pb-2 sm:pb-3">
        <div class="bg-white border border-gray-200 shadow-lg rounded-t-xl overflow-hidden transition-all duration-300 ease-out" x-bind:class="minimized ? 'h-14' : ''">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 p-3">
                <div class="sm:hidden w-full flex justify-center">
                    <div class="w-10 h-1.5 bg-gray-300 rounded-full"
                         x-on:touchstart="startY = $event.touches[0].clientY; anim = true; setTimeout(() => { anim = false }, 300)"
                         x-on:touchend="endY = $event.changedTouches[0].clientY; const dy = startY - endY; if (dy > threshold) { minimized = false; fetch('{{ route('onboarding.minimize') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'), 'Accept': 'application/json' }, body: JSON.stringify({ minimized }) }); } else if (-dy > threshold) { minimized = true; fetch('{{ route('onboarding.minimize') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'), 'Accept': 'application/json' }, body: JSON.stringify({ minimized }) }); }"
                         :class="anim ? 'rb-animate' : ''"></div>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <div class="hidden sm:flex items-center text-sm font-medium text-gray-800">
                        <i class="fas fa-shoe-prints text-indigo-600 mr-2"></i>
                        Primeiros passos
                    </div>
                    <div class="flex-1 sm:flex-none w-full sm:w-auto">
                        <div class="w-full sm:w-40 bg-gray-200 rounded-full h-2" aria-label="Progresso">
                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                    <span class="text-xs text-gray-700 whitespace-nowrap">{{ $progress }}% concluído</span>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    @if($isAdminSupport && Config::get('onboarding.show_context_badge', true))
                        <span class="inline-flex items-center px-2 py-1 text-[11px] font-medium rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200" title="Dono: {{ $ownerName }}{{ $ownerEmail ? ' • '.$ownerEmail : '' }}" aria-label="Dono: {{ $ownerName }}{{ $ownerEmail ? ' • '.$ownerEmail : '' }}">
                            <i class="fas fa-user-shield mr-1"></i>
                            Visualizando onboarding do dono da escola atual
                        </span>
                    @endif
                    <button id="btn-minimize-onboarding" aria-controls="onboarding-bar" x-bind:aria-label="minimized ? 'Expandir barra de onboarding' : 'Minimizar barra de onboarding'" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs bg-gray-100 border border-gray-300 text-gray-800 rounded hover:bg-gray-200" x-on:click="minimized = !minimized; fetch('{{ route('onboarding.minimize') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'), 'Accept': 'application/json' }, body: JSON.stringify({ minimized }) });">
                        <i x-bind:class="minimized ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
                        <span x-text="minimized ? 'Expandir' : 'Minimizar'"></span>
                    </button>
                    <button id="btn-close-onboarding" aria-controls="onboarding-bar" aria-label="Fechar barra de onboarding" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs bg-red-600 text-white rounded hover:bg-red-700"
                            x-on:click="visible = false; fetch('{{ route('onboarding.close') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'), 'Accept': 'application/json' }, body: JSON.stringify({ closed: true }) });">
                        <i class="fas fa-times"></i>
                        Fechar
                    </button>
                </div>
            </div>

            <div class="px-3 pb-3" x-show="!minimized" x-transition :aria-hidden="minimized ? 'true' : 'false'">
                <div class="flex items-center gap-2 overflow-x-auto snap-x">
                    @foreach($steps as $step)
                        @php $doneStep = in_array($step['slug'], $completed ?? [], true); @endphp
                        <div class="flex items-center gap-2">
                            @php
                                $href = isset($step['route']) ? route($step['route'], $step['params'] ?? []) : ($step['url'] ?? '#');
                            @endphp
                            <a href="{{ $href }}" class="snap-start inline-flex items-center gap-2 px-3 py-2 text-xs rounded-full border transition-colors {{ $doneStep ? 'bg-green-50 border-green-200 text-green-700 hover:bg-green-100' : 'bg-indigo-50 border-indigo-200 text-indigo-700 hover:bg-indigo-100' }}">
                                @if($doneStep)
                                    <i class="fas fa-check-circle text-green-600"></i>
                                @else
                                    <i class="far fa-circle text-indigo-600"></i>
                                @endif
                                <span class="font-medium">{{ $step['label'] }}</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function(){
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const minimizeBtn = document.getElementById('btn-minimize-onboarding');
        const closeBtn = document.getElementById('btn-close-onboarding');
        const bar = document.getElementById('onboarding-bar');

        if (minimizeBtn) {
            // Interação de minimizar/expandir agora é controlada pelo Alpine.js no botão
            // Mantemos este listener vazio para evitar comportamento duplicado.
            minimizeBtn.addEventListener('click', function() { /* handled by Alpine */ });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', async function() {
                try {
                    await fetch('{{ route('onboarding.close') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ closed: true })
                    });
                    bar.remove();
                } catch (e) {}
            });
        }
    })();
</script>
@endpush
@push('styles')
<style>
@keyframes rubberBand {
  0% { transform: scale3d(1, 1, 1); }
  30% { transform: scale3d(1.25, 0.75, 1); }
  40% { transform: scale3d(0.75, 1.25, 1); }
  50% { transform: scale3d(1.15, 0.85, 1); }
  65% { transform: scale3d(0.95, 1.05, 1); }
  75% { transform: scale3d(1.05, 0.95, 1); }
  100% { transform: scale3d(1, 1, 1); }
}
.rb-animate {
  animation: rubberBand 0.45s ease;
}
</style>
@endpush
@endif