<!-- Right Side Expandable Menu -->
<div style="position: fixed; right: 24px; bottom: 24px; z-index: 99999;">
    <!-- Expandable Menu -->
    <div id="rightMenu" style="position: absolute; bottom: 0; right: 0; background: white; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: 1px solid #e5e7eb; padding: 12px 0; width: 224px; transform: scale(0); transform-origin: bottom right; transition: all 0.3s ease; opacity: 0;">
        <div style="padding: 12px 16px; border-bottom: 1px solid #f3f4f6;">
            <h3 style="font-size: 14px; font-weight: 600; color: #1f2937;">Menu Rápido</h3>
        </div>
        @permission('escalas.listar')
            <a href="{{ route('historico.index') }}" style="display: flex; align-items: center; padding: 12px 16px; font-size: 14px; color: #374151; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#eef2ff'; this.style.color='#4338ca';" onmouseout="this.style.backgroundColor=''; this.style.color='#374151';">
                <i class="fas fa-history" style="margin-right: 12px; color: #6366f1;"></i>
                <span>Históricos</span>
            </a>
        @endpermission

        @auth
            @php
                $user = auth()->user();
                $prefs = $user->preferences ?? [];
                $closed = (bool) ($prefs['onboarding_closed'] ?? false);
                $owner = method_exists($user, 'isSchoolOwner') ? $user->isSchoolOwner() : false;
            @endphp
            @if($closed && $owner)
                <form method="POST" action="{{ route('onboarding.reopen') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" style="width: 100%; display: flex; align-items: center; padding: 12px 16px; font-size: 14px; color: #374151; text-decoration: none; background: transparent; border: none; text-align: left; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#eef2ff'; this.style.color='#4338ca';" onmouseout="this.style.backgroundColor=''; this.style.color='#374151';">
                        <i class="fas fa-life-ring" style="margin-right: 12px; color: #6366f1;"></i>
                        <span>Reabrir ajudante</span>
                    </button>
                </form>
            @endif
        @endauth
    </div>
</div>