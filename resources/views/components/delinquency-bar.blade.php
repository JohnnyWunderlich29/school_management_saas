@php
$escolaId = null;
if (auth()->check()) {
    $user = auth()->user();
    $isSuperAdmin = method_exists($user, 'isSuperAdmin') ? $user->isSuperAdmin() : false;
    $hasSuporte = method_exists($user, 'temCargo') ? $user->temCargo('Suporte') : false;
    if ($isSuperAdmin || $hasSuporte) {
        $escolaId = session('escola_atual');
    } else {
        $escolaId = $user->escola_id;
    }
}

$escola = $escolaId ? \App\Models\Escola::find($escolaId) : null;
$status = $escola ? $escola->getStatusPagamento() : 'em_dia';

// Detecta plano trial e calcula dias restantes (clamp e dias inteiros)
$isTrial = false;
$trialDaysRemaining = null;
if ($escola) {
    $isTrial = (($escola->plan && $escola->plan->is_trial) || (strtolower((string) $escola->plano) === 'trial'));
    if ($isTrial) {
        $trialDays = ($escola->plan && $escola->plan->trial_days) ? (int) $escola->plan->trial_days : 0;

        // Definir data de término do trial:
        // 1) Se houver data_vencimento válida, usar como término
        // 2) Caso contrário, usar created_at + trial_days
        $trialEndsAt = null;
        if ($escola->data_vencimento) {
            $trialEndsAt = $escola->data_vencimento->copy();
        }
        if (!$trialEndsAt && $escola->created_at && $trialDays > 0) {
            $trialEndsAt = $escola->created_at->copy()->addDays($trialDays);
        }

        if ($trialEndsAt) {
            // Dias restantes inteiros, negativo vira 0
            $trialDaysRemaining = max(now()->diffInDays($trialEndsAt, false), 0);
        } else {
            // Fallback: sem datas válidas, sem dias restantes
            $trialDaysRemaining = 0;
        }
    }
}

$mostrarBarra = $escola && ($status !== 'em_dia' || $isTrial);
$valorMensal = $escola ? $escola->getTotalMonthlyValue() : 0.0;
@endphp

@if(auth()->check() && !request()->routeIs('login') && !request()->routeIs('register') && $mostrarBarra)
    <div id="delinquency-bar" class="fixed top-0 left-0 right-0 z-50">
        <div class="
            @if($status === 'inadimplente') bg-red-600
            @elseif($status === 'vencido') bg-amber-500
            @elseif($isTrial) bg-indigo-600
            @else bg-gray-600
            @endif text-white">
            <div class="max-w-full mx-auto px-4 py-2 flex items-center justify-center gap-3 text-sm">
                @if($status !== 'em_dia')
                    <i class="fas fa-triangle-exclamation"></i>
                    <span>
                        @if($status === 'inadimplente')
                            Sua escola está inadimplente.
                        @else
                            Pagamento vencido.
                        @endif
                        Valor devido: R$ {{ number_format($valorMensal, 2, ',', '.') }}
                    </span>
                    <a href="{{ route('profile.escola') }}" class="ml-4 underline font-medium">Ver detalhes</a>
                @elseif($isTrial)
                    <i class="fas fa-info-circle"></i>
                    @php $dias = (int) ($trialDaysRemaining ?? 0); @endphp
                    <span>
                        Você está no plano Trial. Restam {{ $dias }} dia{{ $dias === 1 ? '' : 's' }}.
                    </span>
                    <a href="{{ route('profile.escola') }}" class="ml-4 underline font-medium">Ver detalhes</a>
                @endif
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var bar = document.getElementById('delinquency-bar');
            if (bar) {
                var h = bar.offsetHeight;
                var currentPaddingTop = parseInt(window.getComputedStyle(document.body).paddingTop) || 0;
                document.body.style.paddingTop = (currentPaddingTop + h) + 'px';
            }
        });
    </script>
@endif