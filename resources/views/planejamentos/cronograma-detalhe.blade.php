@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="container mx-auto py-6">
            <x-breadcrumbs :items="[
                ['title' => 'Planejamentos', 'url' => route('planejamentos.index')],
                ['title' => $planejamento->titulo ?: ('Planejamento #' . $planejamento->id), 'url' => route('planejamentos.show', $planejamento)],
                ['title' => 'Cronograma Diário', 'url' => '#']
            ]" />

            <div id="cronograma-card">
                @include('planejamentos.partials.cronograma-detalhe-card')
            </div>
        </div>
        </div>
    </div>
    <script>
        (function() {
            const container = document.getElementById('cronograma-card');
            if (!container) return;

            function loadPartial(url, replaceState = true) {
                if (!url || url === '#') return;
                const loadingClass = 'opacity-60';
                container.classList.add(loadingClass);
                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(resp => {
                    if (!resp.ok) throw new Error('Falha ao carregar conteúdo');
                    return resp.text();
                })
                .then(html => {
                    container.innerHTML = html;
                    container.classList.remove(loadingClass);
                    if (replaceState) {
                        try { history.pushState({}, '', url); } catch (e) {}
                    }
                    attachHandlers();
                })
                .catch(() => {
                    container.classList.remove(loadingClass);
                });
            }

            function onNavClick(e) {
                const a = e.target.closest('a[data-ajax-cronograma="true"]');
                if (!a) return;
                const disabled = a.getAttribute('aria-disabled') === 'true';
                if (disabled || a.getAttribute('href') === '#') return;
                e.preventDefault();
                loadPartial(a.href, true);
            }

            function attachHandlers() {
                container.querySelectorAll('a[data-ajax-cronograma="true"]').forEach(a => {
                    a.addEventListener('click', onNavClick);
                });
            }

            window.addEventListener('popstate', function() {
                loadPartial(location.href, false);
            });

            attachHandlers();
        })();
    </script>
@endsection