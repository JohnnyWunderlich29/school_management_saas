@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dados do backend
            const presencasPorDia = @json(isset($dadosAnaliticos['presencasPorDia']) ? $dadosAnaliticos['presencasPorDia'] : []);
            const presencasPorSala = @json(isset($dadosAnaliticos['presencasPorSala']) ? $dadosAnaliticos['presencasPorSala'] : []);

            // Configuração comum dos gráficos
            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderWidth: 0,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            borderDash: [2, 2]
                        },
                        ticks: {
                            color: '#6b7280',
                            font: {
                                size: 12
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6b7280',
                            font: {
                                size: 12
                            },
                            maxRotation: 0
                        }
                    }
                }
            };

            // Gráfico de Presenças por Dia
            if (presencasPorDia.length > 0) {
                const ctxDia = document.getElementById('presencasPorDiaChart').getContext('2d');
                // Debug: inspeciona a série recebida do backend
                console.log('presencasPorDia (7d):', presencasPorDia);
                const totalRegistros7d = presencasPorDia.reduce((sum, item) => sum + (Number(item.total) || 0), 0);
                const emptyMsgEl = document.getElementById('presencasPorDiaEmptyMsg');
                if (emptyMsgEl) {
                    emptyMsgEl.classList.toggle('hidden', totalRegistros7d > 0);
                }
                window.presencasPorDiaChart = new Chart(ctxDia, {
                    type: 'line',
                    data: {
                        labels: presencasPorDia.map(item => {
                            const date = new Date(item.data);
                            return date.toLocaleDateString('pt-BR', {
                                weekday: 'short',
                                day: '2-digit'
                            });
                        }),
                        datasets: [{
                                label: 'Presenças (confirmadas)',
                                data: presencasPorDia.map(item => item.presentes),
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#3b82f6',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                pointHoverRadius: 7
                            },
                            {
                                label: 'Registros (total)',
                                data: presencasPorDia.map(item => item.total),
                                borderColor: '#9CA3AF',
                                backgroundColor: 'rgba(156, 163, 175, 0.15)',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.35,
                                pointBackgroundColor: '#9CA3AF',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 1,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                borderDash: [6, 4]
                            }
                        ]
                    },
                    options: {
                        ...commonOptions,
                        plugins: {
                            ...commonOptions.plugins,
                            legend: {
                                display: true
                            },
                            tooltip: {
                                ...commonOptions.plugins.tooltip,
                                callbacks: {
                                    label: function(ctx) {
                                        const v = ctx.parsed.y ?? 0;
                                        const label = ctx.dataset.label || '';
                                        if (label.includes('Confirmadas')) return `Confirmadas: ${v}`;
                                        if (label.includes('Registros')) return `Registros: ${v}`;
                                        return `${label}: ${v}`;
                                    },
                                    footer: function(items) {
                                        if (!items || !items.length) return '';
                                        const idx = items[0].dataIndex;
                                        const total = presencasPorDia[idx]?.total ?? 0;
                                        const presentes = presencasPorDia[idx]?.presentes ?? 0;
                                        const ausentes = Math.max(0, total - presentes);
                                        return `Ausentes: ${ausentes}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            ...commonOptions.scales,
                            y: {
                                ...commonOptions.scales.y,
                                ticks: {
                                    ...commonOptions.scales.y.ticks,
                                    precision: 0,
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            // Gráfico de Presenças por Sala
            if (presencasPorSala.length > 0) {
                const ctxSala = document.getElementById('presencasPorSalaChart').getContext('2d');
                window.presencasPorSalaChart = new Chart(ctxSala, {
                    type: 'bar',
                    data: {
                        labels: presencasPorSala.map(item => item.sala || 'Sala sem nome'),
                        datasets: [{
                            label: 'Presenças',
                            data: presencasPorSala.map(item => item.presentes),
                            backgroundColor: [
                                '#10b981',
                                '#3b82f6',
                                '#8b5cf6',
                                '#f59e0b',
                                '#ef4444'
                            ],
                            borderRadius: 6,
                            borderSkipped: false
                        }]
                    },
                    options: commonOptions
                });
            }
        });
    </script>
@endpush
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inicioHidden = document.getElementById('inicio_hidden');
            const fimHidden = document.getElementById('fim_hidden');
            const aplicarBtn = document.getElementById('aplicarPeriodo');
            const limparLink = document.getElementById('limparPeriodo');
            const emptyMsgEl = document.getElementById('presencasPorDiaEmptyMsg');
            const taxaValueEl = document.getElementById('metricTaxaPresencaValue');
            const taxaBarEl = document.getElementById('metricTaxaPresencaBar');
            const alertasValueEl = document.getElementById('metricAlertasValue');
            const professoresValueEl = document.getElementById('metricProfessoresValue');

            function syncPeriodoInputs() {
                const di = document.querySelector('input[name="data_inicio"]');
                const df = document.querySelector('input[name="data_fim"]');
                if (di && df) {
                    inicioHidden.value = di.value || df.value || '';
                    fimHidden.value = df.value || di.value || '';
                }
            }

            ['change', 'input'].forEach(evt => {
                document.addEventListener(evt, (e) => {
                    if (e.target && (e.target.name === 'data_inicio' || e.target.name ===
                            'data_fim')) {
                        syncPeriodoInputs();
                    }
                });
            });

            function updatePresencasPorDiaChart(data) {
                if (!window.presencasPorDiaChart) return;
                const labels = (data || []).map(item => {
                    const date = new Date(item.data);
                    return date.toLocaleDateString('pt-BR', {
                        weekday: 'short',
                        day: '2-digit'
                    });
                });
                const presentes = (data || []).map(item => item.presentes || 0);
                const totais = (data || []).map(item => item.total || 0);
                const totalRegistros = totais.reduce((sum, v) => sum + (Number(v) || 0), 0);
                emptyMsgEl?.classList.toggle('hidden', totalRegistros > 0);
                window.presencasPorDiaChart.data.labels = labels;
                window.presencasPorDiaChart.data.datasets[0].data = presentes;
                window.presencasPorDiaChart.data.datasets[1].data = totais;
                window.presencasPorDiaChart.update();
            }

            function updatePresencasPorSalaChart(data) {
                if (!window.presencasPorSalaChart) return;
                const labels = (data || []).map(item => item.sala || 'Sala sem nome');
                const valores = (data || []).map(item => item.presentes || 0);
                window.presencasPorSalaChart.data.labels = labels;
                window.presencasPorSalaChart.data.datasets[0].data = valores;
                window.presencasPorSalaChart.update();
            }

            function updateMetricCards(da) {
                const taxa = Number(da.taxaPresencaGeral ?? 0) || 0;
                if (taxaValueEl) taxaValueEl.textContent = `${taxa}%`;
                if (taxaBarEl) taxaBarEl.style.width = `${taxa}%`;
                const alertasCount = Array.isArray(da.alertasBaixaFrequencia) ? da.alertasBaixaFrequencia.length :
                    Number(da.alertasBaixaFrequenciaCount ?? 0) || 0;
                if (alertasValueEl) alertasValueEl.textContent = `${alertasCount}`;
                const profs = Number(da.totalProfessoresComAtividade ?? 0) || 0;
                if (professoresValueEl) professoresValueEl.textContent = `${profs}`;

                // Atualizar badges dinamicamente
                updateBadges(taxa, alertasCount, profs);
            }

            function updateBadges(taxaPresenca, alertasCount, professoresAtivos) {
                // Badge Taxa de Presença
                const taxaBadge = document.getElementById('metricTaxaPresencaBadge');
                if (taxaBadge) {
                    let badgeClass, badgeIcon, badgeText;
                    if (taxaPresenca >= 90) {
                        badgeClass = 'bg-green-100 text-green-800';
                        badgeIcon = 'fas fa-arrow-up';
                        badgeText =
                            '<span class="hidden sm:inline">Excelente</span><span class="sm:hidden">Exc</span>';
                    } else if (taxaPresenca >= 75) {
                        badgeClass = 'bg-yellow-100 text-yellow-800';
                        badgeIcon = 'fas fa-minus';
                        badgeText = 'Bom';
                    } else {
                        badgeClass = 'bg-red-100 text-red-800';
                        badgeIcon = 'fas fa-arrow-down';
                        badgeText =
                            '<span class="hidden sm:inline">Atenção</span><span class="sm:hidden">Atç</span>';
                    }
                    taxaBadge.className =
                        `inline-flex items-center px-2 md:px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeClass}`;
                    taxaBadge.innerHTML = `<i class="${badgeIcon} mr-1"></i>${badgeText}`;
                }

                // Badge Alertas
                const alertasBadge = document.getElementById('metricAlertasBadge');
                if (alertasBadge) {
                    let badgeClass, badgeIcon, badgeText;
                    if (alertasCount > 0) {
                        badgeClass = 'bg-red-100 text-red-800';
                        badgeIcon = 'fas fa-bell';
                        badgeText = 'Ativo';
                    } else {
                        badgeClass = 'bg-green-100 text-green-800';
                        badgeIcon = 'fas fa-check';
                        badgeText = 'OK';
                    }
                    alertasBadge.className =
                        `inline-flex items-center px-2 md:px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeClass}`;
                    alertasBadge.innerHTML = `<i class="${badgeIcon} mr-1"></i>${badgeText}`;
                }

                // Badge Professores (mantém sempre "Ativos" por enquanto, mas pode ser expandido)
                const professoresBadge = document.getElementById('metricProfessoresBadge');
                if (professoresBadge) {
                    // Por enquanto mantém sempre verde "Ativos", mas pode ser expandido com regras futuras
                    professoresBadge.className =
                        'inline-flex items-center px-2 md:px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
                    professoresBadge.innerHTML = '<i class="fas fa-users mr-1"></i>Ativos';
                }
            }

            function updateTables(da) {
                updateProfessoresAtivosTable(da.desempenhoProfessores || []);
                updateAlertasFrequenciaTable(da.alertasBaixaFrequencia || []);
            }

            function updateProfessoresAtivosTable(professores) {
                const container = document.getElementById('professoresAtivosContainer');
                if (!container) return;

                if (professores.length === 0) {
                    container.innerHTML = `
        <div class="text-center py-8 md:py-12">
          <i class="fas fa-chalkboard-teacher text-gray-400 text-3xl md:text-4xl mb-4"></i>
          <p class="text-gray-500 text-sm md:text-base">Nenhum professor ativo encontrado</p>
        </div>
      `;
                    return;
                }

                // Mobile Cards
                const mobileCards = professores.slice(0, 3).map(professor => `
      <div class="bg-gray-50 p-3 rounded-lg border">
        <div class="flex items-center justify-between mb-2">
          <h4 class="font-medium text-gray-900 text-sm">${professor.nome || 'Sem nome'}</h4>
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
            <i class="fas fa-circle text-green-400 mr-1" style="font-size: 6px;"></i>
            Ativo
          </span>
        </div>
        <p class="text-xs text-gray-600 mb-1">${professor.cargo || 'Professor'}</p>
        <p class="text-xs text-gray-500">
          <i class="fas fa-clock mr-1"></i>
          Última atividade: ${professor.ultima_atividade || 'Hoje'}
        </p>
      </div>
    `).join('');

                // Desktop Table Rows
                const tableRows = professores.slice(0, 5).map(professor => `
      <tr class="hover:bg-gray-50">
        <td class="px-3 md:px-6 py-4 whitespace-nowrap">
          <div class="flex items-center">
            <div class="flex-shrink-0 h-8 w-8 md:h-10 md:w-10">
              <div class="h-8 w-8 md:h-10 md:w-10 rounded-full bg-purple-100 flex items-center justify-center">
                <i class="fas fa-user text-purple-600 text-xs md:text-sm"></i>
              </div>
            </div>
            <div class="ml-3 md:ml-4">
              <div class="text-sm font-medium text-gray-900">${professor.nome || 'Sem nome'}</div>
              <div class="text-xs md:text-sm text-gray-500 md:hidden">${professor.cargo || 'Professor'}</div>
            </div>
          </div>
        </td>
        <td class="px-3 md:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
          ${professor.cargo || 'Professor'}
        </td>
        <td class="px-3 md:px-6 py-4 whitespace-nowrap">
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
            <i class="fas fa-circle text-green-400 mr-1" style="font-size: 6px;"></i>
            Ativo
          </span>
        </td>
      </tr>
    `).join('');

                container.innerHTML = `
      <div class="block sm:hidden space-y-3" id="professoresAtivosMobile">
        ${mobileCards}
      </div>
      <table class="hidden sm:table min-w-full divide-y divide-gray-200" id="professoresAtivosTable">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Professor</th>
            <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Cargo</th>
            <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          ${tableRows}
        </tbody>
      </table>
    `;
            }

            function updateAlertasFrequenciaTable(alertas) {
                const container = document.getElementById('alertasFrequenciaContainer');
                if (!container) return;

                if (alertas.length === 0) {
                    container.innerHTML = `
        <div class="text-center py-8 md:py-12">
          <i class="fas fa-check-circle text-green-400 text-3xl md:text-4xl mb-4"></i>
          <p class="text-gray-500 text-sm md:text-base">Nenhum alerta de frequência</p>
          <p class="text-gray-400 text-xs md:text-sm mt-1">Todas as frequências estão normais</p>
        </div>
      `;
                    return;
                }

                // Mobile Cards
                const mobileCards = alertas.slice(0, 3).map(alerta => `
      <div class="bg-red-50 p-3 rounded-lg border border-red-200">
        <div class="flex items-center justify-between mb-2">
          <h4 class="font-medium text-gray-900 text-sm">${alerta.nome || 'Sem nome'}</h4>
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
            ${Number(alerta.frequencia || 0).toFixed(1)}%
          </span>
        </div>
        <p class="text-xs text-gray-600 mb-1">${alerta.turma?.nome || alerta.grupo_id || 'Turma não informada'}</p>
        <p class="text-xs text-red-600">
          <i class="fas fa-exclamation-triangle mr-1"></i>
          Frequência baixa
        </p>
      </div>
    `).join('');

                // Desktop Table Rows
                const tableRows = alertas.slice(0, 5).map(alerta => `
      <tr class="hover:bg-gray-50">
        <td class="px-3 md:px-6 py-4 whitespace-nowrap">
          <div class="flex items-center">
            <div class="flex-shrink-0 h-8 w-8 md:h-10 md:w-10">
              <div class="h-8 w-8 md:h-10 md:w-10 rounded-full bg-red-100 flex items-center justify-center">
                <i class="fas fa-user-graduate text-red-600 text-xs md:text-sm"></i>
              </div>
            </div>
            <div class="ml-3 md:ml-4">
              <div class="text-sm font-medium text-gray-900">${alerta.nome || 'Sem nome'}</div>
              <div class="text-xs md:text-sm text-gray-500 md:hidden">${alerta.turma?.nome || 'Turma não informada'}</div>
            </div>
          </div>
        </td>
        <td class="px-3 md:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
          ${alerta.turma?.nome || 'Não informada'}
        </td>
        <td class="px-3 md:px-6 py-4 whitespace-nowrap">
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            ${Number(alerta.frequencia || 0).toFixed(1)}%
          </span>
        </td>
      </tr>
    `).join('');

                container.innerHTML = `
      <div class="block sm:hidden space-y-3" id="alertasFrequenciaMobile">
        ${mobileCards}
      </div>
      <table class="hidden sm:table min-w-full divide-y divide-gray-200" id="alertasFrequenciaTable">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aluno</th>
            <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Turma</th>
            <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frequência</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          ${tableRows}
        </tbody>
      </table>
    `;
            }

            aplicarBtn?.addEventListener('click', async () => {
                syncPeriodoInputs();
                const inicio = encodeURIComponent(inicioHidden.value || '');
                const fim = encodeURIComponent(fimHidden.value || '');
                const url = `{{ route('dashboard') }}?inicio=${inicio}&fim=${fim}&ajax=1`;
                try {
                    const resp = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const json = await resp.json();
                    const da = json?.dadosAnaliticos || {};
                    updatePresencasPorDiaChart(da.presencasPorDia || []);
                    updatePresencasPorSalaChart(da.presencasPorSala || []);
                    updateMetricCards(da);
                    updateTables(da);
                } catch (err) {
                    console.error('Erro ao atualizar Analytics via AJAX', err);
                }
            });

            limparLink?.addEventListener('click', async (e) => {
                e.preventDefault();
                try {
                    const url = `{{ route('dashboard') }}?ajax=1&clear_periodo=1`;
                    const resp = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const json = await resp.json();
                    const da = json?.dadosAnaliticos || {};
                    // Atualizar inputs visíveis do filtro para o novo período
                    const di = document.querySelector('input[name="data_inicio"]');
                    const df = document.querySelector('input[name="data_fim"]');
                    if (di && df && json?.analytics) {
                        di.value = json.analytics.inicio || '';
                        df.value = json.analytics.fim || '';
                        syncPeriodoInputs();
                    }
                    updatePresencasPorDiaChart(da.presencasPorDia || []);
                    updatePresencasPorSalaChart(da.presencasPorSala || []);
                    updateMetricCards(da);
                    updateTables(da);
                } catch (err) {
                    console.error('Erro ao limpar período via AJAX', err);
                }
            });

            syncPeriodoInputs();

            // Atualização automática do Top 5 Salas (presenças) a cada 30s
            let autoRefreshTimerSala;

            function setupAutoRefreshPresencasPorSala() {
                try {
                    if (autoRefreshTimerSala) clearInterval(autoRefreshTimerSala);
                } catch (_) {}
                autoRefreshTimerSala = setInterval(async () => {
                    if (!window.presencasPorSalaChart) return;
                    const inicio = encodeURIComponent(inicioHidden?.value || '');
                    const fim = encodeURIComponent(fimHidden?.value || '');
                    const url = `{{ route('dashboard') }}?inicio=${inicio}&fim=${fim}&ajax=1`;
                    try {
                        const resp = await fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (!resp.ok) return;
                        const json = await resp.json();
                        const da = json?.dadosAnaliticos || {};
                        const dataSala = Array.isArray(da.presencasPorSala) ? da.presencasPorSala : [];
                        updatePresencasPorSalaChart(dataSala);
                    } catch (_) {
                        /* silencioso para não poluir console */
                    }
                }, 30000);
            }

            setupAutoRefreshPresencasPorSala();
        });
    </script>
@endpush

@push('scripts')
    <script>
        // Analytics: DnD por grid, visibilidade, restauração e persistência (local + backend)
        (function() {
            const csrfToken = (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')) || window
                .csrfToken || '';
            const debounce = (fn, wait) => {
                let t;
                return (...args) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn(...args), wait);
                };
            };

            const manager = {
                grids: {}, // gridId -> { defaultOrder, gridKey }
                cards: {}, // cardKey -> HTMLElement
                state: { // preferências locais atuais
                    visible: {},
                    orders: {}
                },
                remoteAll: null, // objeto completo vindo do backend (pode conter outras chaves usadas por outras seções)
                gridIdToKey: {
                    'analyticsMetricsGrid': 'metrics',
                    'analyticsChartsGrid': 'charts',
                    'analyticsTablesGrid': 'tables',
                    'analyticsRecentGrid': 'recent',
                    'analyticsExtraGrid': 'extra'
                },
                storageKeyFor(gridKey) {
                    return `analytics:dnd:${gridKey}`;
                },
                async loadRemote() {
                    try {
                        const res = await fetch("{{ route('dashboard.preferences.index') }}", {
                            credentials: 'same-origin'
                        });
                        if (!res.ok) return null;
                        const data = await res.json();
                        this.remoteAll = data?.state || null;
                        return this.remoteAll;
                    } catch (_) {
                        return null;
                    }
                },
                async saveRemoteMerged() {
                    try {
                        const all = Object.assign({}, this.remoteAll || {});
                        all.analytics = {
                            visible: this.state.visible,
                            orders: this.state.orders
                        };
                        await fetch("{{ route('dashboard.preferences.save') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                state: all
                            })
                        });
                        this.remoteAll = all;
                    } catch (_) {}
                },
                debouncedSave: null,
                applyVisibility() {
                    Object.keys(this.cards).forEach(key => {
                        const el = this.cards[key];
                        const visible = this.state.visible[key] !== false; // default: true
                        el.classList.toggle('hidden', !visible);
                    });
                },
                applyOrderFor(gridId) {
                    const grid = document.getElementById(gridId);
                    if (!grid) return;
                    const gridKey = this.gridIdToKey[gridId] || gridId;
                    const reg = this.grids[gridId];
                    const defaultOrder = reg?.defaultOrder || Array.from(grid.querySelectorAll('[data-card-key]'))
                        .map(el => el.dataset.cardKey);
                    const current = (Array.isArray(this.state.orders[gridKey]) ? this.state.orders[gridKey]
                        .slice() : []);
                    const base = (current.length ? current : defaultOrder);
                    const final = base.concat(defaultOrder.filter(k => !base.includes(k)));
                    final.forEach(k => {
                        const el = grid.querySelector(`[data-card-key="${k}"]`);
                        if (el) grid.appendChild(el);
                    });
                },
                applyAll() {
                    this.applyVisibility();
                    Object.keys(this.grids).forEach(gridId => this.applyOrderFor(gridId));
                },
                restoreGrid(gridId) {
                    const gridKey = this.gridIdToKey[gridId] || gridId;
                    const reg = this.grids[gridId];
                    if (!reg) return;
                    this.state.orders[gridKey] = reg.defaultOrder.slice();
                    try {
                        localStorage.removeItem(this.storageKeyFor(gridKey));
                    } catch (_) {}
                    this.applyOrderFor(gridId);
                    this.debouncedSave();
                }
            };

            manager.debouncedSave = debounce(() => manager.saveRemoteMerged(), 500);

            function initAnalyticsDnD(gridId) {
                const grid = document.getElementById(gridId);
                if (!grid) return;
                const items = Array.from(grid.querySelectorAll('[data-card-key]'));
                if (!items.length) return;

                // registrar cards e grid
                items.forEach(el => {
                    manager.cards[el.dataset.cardKey] = el;
                });
                const gridKey = manager.gridIdToKey[gridId] || gridId;
                const defaultOrder = items.map(el => el.dataset.cardKey);
                manager.grids[gridId] = {
                    defaultOrder,
                    gridKey
                };

                // ordem inicial: localStorage primeiro
                let order = [];
                try {
                    const saved = JSON.parse(localStorage.getItem(manager.storageKeyFor(gridKey)) || '[]') || [];
                    order = saved.filter(k => defaultOrder.includes(k));
                } catch (_) {
                    order = [];
                }
                if (!order.length) order = defaultOrder.slice();
                else order = order.concat(defaultOrder.filter(k => !order.includes(k)));
                manager.state.orders[gridKey] = order.slice();

                const applyOrder = () => {
                    manager.state.orders[gridKey].forEach(k => {
                        const el = grid.querySelector(`[data-card-key="${k}"]`);
                        if (el) grid.appendChild(el);
                    });
                };
                applyOrder();

                let dragKey = null;
                grid.addEventListener('dragstart', (e) => {
                    const handle = e.target.closest('[data-drag-handle]');
                    if (!handle || !grid.contains(handle)) return;
                    const card = handle.closest('[data-card-key]');
                    if (!card) return;
                    dragKey = card.dataset.cardKey;
                    if (e.dataTransfer) {
                        e.dataTransfer.effectAllowed = 'move';
                        try {
                            e.dataTransfer.setData('text/plain', dragKey);
                        } catch (_) {}
                    }
                });
                grid.addEventListener('dragover', (e) => {
                    if (!dragKey) return;
                    const overCard = e.target.closest('[data-card-key]');
                    if (!overCard || !grid.contains(overCard)) return;
                    e.preventDefault();
                });
                grid.addEventListener('drop', (e) => {
                    if (!dragKey) return;
                    const target = e.target.closest('[data-card-key]');
                    if (!target || !grid.contains(target)) return;
                    e.preventDefault();
                    const targetKey = target.dataset.cardKey;
                    if (dragKey === targetKey) {
                        dragKey = null;
                        return;
                    }
                    const from = manager.state.orders[gridKey].indexOf(dragKey);
                    const to = manager.state.orders[gridKey].indexOf(targetKey);
                    if (from === -1 || to === -1) {
                        dragKey = null;
                        return;
                    }
                    manager.state.orders[gridKey].splice(to, 0, ...manager.state.orders[gridKey].splice(from,
                        1));
                    try {
                        localStorage.setItem(manager.storageKeyFor(gridKey), JSON.stringify(manager.state
                            .orders[gridKey]));
                    } catch (_) {}
                    applyOrder();
                    dragKey = null;
                    manager.debouncedSave();
                });

                const highlight = (el, on) => {
                    el.classList.toggle('ring-2', on);
                    el.classList.toggle('ring-indigo-400', on);
                    el.classList.toggle('ring-offset-2', on);
                };
                grid.querySelectorAll('[data-card-key]').forEach(el => {
                    el.addEventListener('dragenter', () => {
                        if (dragKey) highlight(el, true);
                    });
                    el.addEventListener('dragleave', () => highlight(el, false));
                    el.addEventListener('drop', () => highlight(el, false));
                });
            }

            document.addEventListener('DOMContentLoaded', async function() {
                // inicializa DnD dos grids
                ['analyticsMetricsGrid', 'analyticsChartsGrid', 'analyticsTablesGrid',
                    'analyticsRecentGrid', 'analyticsExtraGrid'
                ]
                .forEach(initAnalyticsDnD);

                // visibilidade: default para todos os cards é true
                Object.keys(manager.cards).forEach(k => {
                    if (typeof manager.state.visible[k] === 'undefined') manager.state.visible[k] =
                        true;
                });
                manager.applyVisibility();

                // botões de restauração por grid
                document.querySelectorAll('[data-action="restore-analytics-grid"]').forEach(btn => {
                    btn.addEventListener('click', () => manager.restoreGrid(btn.dataset.gridId));
                });

                // carrega do backend e reaplica (merge seguro)
                const remoteAll = await manager.loadRemote();
                const remoteAnalytics = remoteAll && remoteAll.analytics ? remoteAll.analytics : null;
                if (remoteAnalytics) {
                    manager.state.visible = Object.assign({}, manager.state.visible, remoteAnalytics
                        .visible || {});
                    manager.state.orders = Object.assign({}, manager.state.orders, remoteAnalytics.orders ||
                    {});
                    manager.applyAll();
                }

                // Modal Personalizar (abrir/fechar/salvar)
                const modal = document.getElementById('modalPersonalizarAnalyticsCards');
                const btnOpen = document.getElementById('btnPersonalizarAnalyticsCards');
                const extraTriggers = document.querySelectorAll(
                    '[data-open-modal="modalPersonalizarAnalyticsCards"]');
                const errorEl = document.getElementById('modalAnalyticsPrefError');
                const toggles = modal ? modal.querySelectorAll('.toggle-analytics-card') : [];

                if (modal) {
                    const openModal = () => {
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                        if (errorEl) {
                            errorEl.textContent = '';
                            errorEl.classList.add('hidden');
                        }
                        toggles.forEach(t => {
                            const k = t.dataset.cardKey;
                            t.checked = manager.state.visible[k] !== false;
                        });
                    };
                    if (btnOpen) {
                        btnOpen.addEventListener('click', openModal);
                    }
                    extraTriggers.forEach(el => el.addEventListener('click', openModal));
                    modal.querySelectorAll('[data-action="close-modal"]').forEach(btn => btn
                        .addEventListener('click', () => {
                            modal.classList.add('hidden');
                            modal.classList.remove('flex');
                        }));
                    const btnRestoreAll = modal.querySelector('[data-action="restore-analytics-all"]');
                    if (btnRestoreAll) {
                        btnRestoreAll.addEventListener('click', async () => {
                            const original = btnRestoreAll.textContent;
                            btnRestoreAll.disabled = true;
                            btnRestoreAll.textContent = 'Restaurando...';
                            try {
                                // Visibilidade: tudo visível
                                Object.keys(manager.cards).forEach(k => {
                                    manager.state.visible[k] = true;
                                });
                                // Ordem por grid: volta ao padrão e limpa localStorage
                                Object.keys(manager.grids).forEach(gridId => {
                                    const reg = manager.grids[gridId];
                                    manager.state.orders[reg.gridKey] = reg.defaultOrder
                                        .slice();
                                    try {
                                        localStorage.removeItem(manager.storageKeyFor(
                                            reg.gridKey));
                                    } catch (_) {}
                                });
                                manager.applyAll();
                                await manager.saveRemoteMerged();
                                btnRestoreAll.textContent = 'Restaurado!';
                            } catch (_) {
                                if (errorEl) {
                                    errorEl.textContent =
                                        'Falha ao restaurar. Tente novamente.';
                                    errorEl.classList.remove('hidden');
                                }
                                btnRestoreAll.textContent = original;
                            }
                            setTimeout(() => {
                                btnRestoreAll.disabled = false;
                                btnRestoreAll.textContent = original;
                            }, 800);
                        });
                    }
                    const btnSave = modal.querySelector('[data-action="save-analytics-preferences"]');
                    if (btnSave) {
                        btnSave.addEventListener('click', async () => {
                            const saveText = btnSave.querySelector('.save-text');
                            const saveLoading = btnSave.querySelector('.save-loading');
                            btnSave.disabled = true;
                            if (saveLoading) saveLoading.classList.remove('hidden');
                            if (saveText) saveText.textContent = 'Salvando...';
                            toggles.forEach(t => {
                                manager.state.visible[t.dataset.cardKey] = !!t.checked;
                            });
                            manager.applyVisibility();
                            let ok = true;
                            try {
                                await manager.saveRemoteMerged();
                            } catch (_) {
                                ok = false;
                            }
                            if (saveText) saveText.textContent = ok ? 'Salvo!' :
                                'Erro ao salvar';
                            if (!ok && errorEl) {
                                errorEl.textContent =
                                    'Falha ao salvar preferências. Verifique sua conexão.';
                                errorEl.classList.remove('hidden');
                            }
                            setTimeout(() => {
                                if (saveLoading) saveLoading.classList.add('hidden');
                                btnSave.disabled = false;
                                if (saveText) saveText.textContent = 'Salvar';
                                if (ok) {
                                    modal.classList.add('hidden');
                                    modal.classList.remove('flex');
                                }
                            }, 800);
                        });
                    }
                }
            });
        })();
    </script>
@endpush

@push('scripts')
    <script>
        // Sparklines para cards financeiros (gradientes e tooltips)
        document.addEventListener('DOMContentLoaded', function() {
            const serieReceitas = @json($serieReceitas ?? []);
            const serieReceitasDinheiro = @json($serieReceitasDinheiro ?? []);
            const serieReceitasGateway = @json($serieReceitasGateway ?? []);
            const serieReceitasTotal = @json($serieReceitasTotal ?? []);
            const serieReceitasPendentes = @json($serieReceitasPendentes ?? []);
            const serieDespesas = @json($serieDespesas ?? []);
            const serieDespesasLiquidadas = @json($serieDespesasLiquidadas ?? []);
            const serieDespesasPendentes = @json($serieDespesasPendentes ?? []);
            const serieInadimplencia = @json($serieInadimplencia ?? []);
            const serieTickets = @json($serieTickets ?? []);
            const serieMrr = @json($serieMrr ?? []);

            const fmtBRL = (v) => new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(v || 0);
            const fmtNum = (v) => new Intl.NumberFormat('pt-BR').format(v || 0);
            const fmtDayMonth = (dateStr) => {
                // Formata rótulos em dd/MM ou MM/YYYY conforme padrão
                if (!dateStr) return '';
                // YYYY-MM-DD
                const fullDate = /^\d{4}-\d{2}-\d{2}$/;
                // YYYY-MM
                const yearMonth = /^\d{4}-\d{2}$/;
                if (fullDate.test(dateStr)) {
                    const [y, m, d] = dateStr.split('-');
                    return `${d}/${m}`;
                }
                if (yearMonth.test(dateStr)) {
                    const [y, m] = dateStr.split('-');
                    // Padroniza para dd/MM usando dia 01
                    return `01/${m}`;
                }
                // Já está em dd/MM ou outro formato desconhecido
                return dateStr;
            };

            const baseOpts = {
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: 'rgba(255, 255, 255, 0.92)',
                        titleColor: '#111827',
                        bodyColor: '#111827',
                        borderColor: 'rgba(17, 24, 39, 0.12)',
                        borderWidth: 1,
                        displayColors: false,
                        cornerRadius: 8,
                        padding: 8
                    }
                },
                layout: {
                    padding: 0
                },
                scales: {
                    x: {
                        display: false
                    },
                    y: {
                        display: false
                    }
                },
                elements: {
                    point: {
                        radius: 0
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            };

            const hexToRgb = (hex) => {
                const h = hex.replace('#', '');
                const bigint = parseInt(h, 16);
                const r = (bigint >> 16) & 255;
                const g = (bigint >> 8) & 255;
                const b = bigint & 255;
                return {
                    r,
                    g,
                    b
                };
            };

            const charts = {};
            const makeSpark = (canvasId, values, colors, format, datasetLabel, labels, description) => {
                const el = document.getElementById(canvasId);
                if (!el) return null;
                const ctx = el.getContext('2d');
                // Gradiente alinhado ao card (left-to-right)
                const width = el.clientWidth || el.width;
                const gradStroke = ctx.createLinearGradient(0, 0, width, 0);
                gradStroke.addColorStop(0, colors[0]);
                gradStroke.addColorStop(1, colors[1]);
                const c0 = hexToRgb(colors[0]);
                const c1 = hexToRgb(colors[1]);
                const gradFill = ctx.createLinearGradient(0, 0, width, 0);
                // Afinar opacidade para ficar mais claro/sutil
                gradFill.addColorStop(0, `rgba(${c0.r}, ${c0.g}, ${c0.b}, 0.14)`);
                gradFill.addColorStop(1, `rgba(${c1.r}, ${c1.g}, ${c1.b}, 0.06)`);
                const opts = JSON.parse(JSON.stringify(baseOpts));
                opts.plugins.tooltip.callbacks = {
                    title: (items) => {
                        const idx = (items && items[0]) ? items[0].dataIndex : 0;
                        const raw = (labels && labels[idx]) ? labels[idx] : '';
                        return fmtDayMonth(raw);
                    },
                    label: (context) => {
                        const v = context.raw;
                        const val = format === 'currency' ? fmtBRL(v) : fmtNum(v);
                        return `${datasetLabel || ''}: ${val}`.trim();
                    },
                    footer: () => description || ''
                };
                const chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: (labels && labels.length ? labels : values.map((_, i) => i + 1)),
                        datasets: [{
                            label: datasetLabel || '',
                            data: values,
                            borderColor: gradStroke,
                            backgroundColor: gradFill,
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: opts
                });
                charts[canvasId] = chart;
                return chart;
            };

            // Helper: filtra uma série diária pelo mês selecionado (YYYY-MM)
            const selectedMonth = (document.getElementById('financeMonth')?.value || '').slice(0, 7);
            const filterMonthVals = (serie, mapVal) => {
                const arr = Array.isArray(serie) ? serie : [];
                const filt = arr.filter(p => (p.data || '').startsWith(selectedMonth));
                return {
                    values: filt.map(p => mapVal ? mapVal(p) : p.valor || 0),
                    labels: filt.map(p => p.data || '')
                };
            };

            // Receitas (faturas pagas por dia via paid_at) - emerald 500→600
            const r0 = filterMonthVals(serieReceitas, p => (p.valor_cents || 0) / 100);
            makeSpark(
                'sparkReceitas',
                r0.values,
                ['#10b981', '#059669'],
                'currency',
                'Receitas',
                r0.labels,
                'Faturas pagas por dia (Invoices.total_cents via paid_at).'
            );
            // Despesas (valor direto) - rose 500→600
            const d0 = filterMonthVals(serieDespesas, p => (p.valor || 0));
            makeSpark(
                'sparkDespesas',
                d0.values,
                ['#f43f5e', '#e11d48'],
                'currency',
                'Despesas (Total)',
                d0.labels,
                'Despesas diárias (Despesa.valor) somadas por data.'
            );
            // Inadimplência (contagem diária) - orange 500→600
            const i0 = filterMonthVals(serieInadimplencia, p => (p.valor || 0));
            makeSpark(
                'sparkInadimplencia',
                i0.values,
                ['#f59e0b', '#d97706'],
                'number',
                'Inadimplência',
                i0.labels,
                'Faturas vencidas não pagas por dia (Invoice.status != paid/canceled).'
            );
            // Tickets abertos (criados por dia) - indigo 500→600
            const t0 = filterMonthVals(serieTickets, p => (p.valor || 0));
            makeSpark(
                'sparkTickets',
                t0.values,
                ['#6366f1', '#4f46e5'],
                'number',
                'Tickets',
                t0.labels,
                'Conversas de suporte ativas criadas por dia (Conversa.tipo = suporte, ativo = true), filtradas por escola.'
            );
            // MRR (histórico em R$) - teal 500→600
            makeSpark(
                'sparkMrr',
                (serieMrr || []).map(p => (p.valor_cents || 0) / 100),
                ['#14b8a6', '#0d9488'],
                'currency',
                'MRR',
                (serieMrr || []).map(p => p.mes || ''),
                'Receita recorrente mensal de assinaturas ativas (Subscription.amount_cents).'
            );
            // Método predominante (trend de receitas do período) - cyan 500→600
            const m0 = filterMonthVals(serieReceitas, p => (p.valor_cents || 0) / 100);
            makeSpark(
                'sparkMetodo',
                m0.values,
                ['#06b6d4', '#0891b2'],
                'currency',
                'Método',
                m0.labels,
                'Tendência de receitas líquidas; método predominante do mês via Payments.method.'
            );

            // Filtro por mês (YYYY-MM) para atualizar gráficos vinculados
            const monthEl = document.getElementById('financeMonth');
            const baseSelect = document.getElementById('receitasBase');
            let receitasBase = (typeof localStorage !== 'undefined' && localStorage.getItem('receitasBase')) ||
                'due_date';
            if (baseSelect) {
                try {
                    baseSelect.value = receitasBase;
                } catch (_) {}
            }
            const original = {
                // Receitas recebidas (por due_date)
                receitas: (serieReceitas || []).map(p => ({
                    d: p.data || '',
                    v: (p.valor_cents || 0) / 100
                })),
                // Total faturado e pendentes (por due_date)
                receitas_total: (serieReceitasTotal || []).map(p => ({
                    d: p.data || '',
                    v: (p.valor_cents || 0) / 100
                })),
                receitas_pend: (serieReceitasPendentes || []).map(p => ({
                    d: p.data || '',
                    v: (p.valor_cents || 0) / 100
                })),
                // Quebra por método (apenas recebidas)
                receitas_dinheiro: (serieReceitasDinheiro || []).map(p => ({
                    d: p.data || '',
                    v: (p.valor_cents || 0) / 100
                })),
                receitas_gateway: (serieReceitasGateway || []).map(p => ({
                    d: p.data || '',
                    v: (p.valor_cents || 0) / 100
                })),
                despesas: (serieDespesas || []).map(p => ({
                    d: p.data || '',
                    v: (p.valor || 0)
                })),
                despesas_liq: (serieDespesasLiquidadas || []).map(p => ({
                    d: p.data || '',
                    v: (p.valor || 0)
                })),
                despesas_pend: (serieDespesasPendentes || []).map(p => ({
                    d: p.data || '',
                    v: (p.valor || 0)
                })),
                inadimplencia: (serieInadimplencia || []).map(p => ({
                    d: p.data || '',
                    v: (p.valor || 0)
                })),
                tickets: (serieTickets || []).map(p => ({
                    d: p.data || '',
                    v: (p.valor || 0)
                })),
                metodo: (serieReceitas || []).map(p => ({
                    d: p.data || '',
                    v: (p.valor_cents || 0) / 100
                })),
                mrr: (serieMrr || []).map(p => ({
                    m: p.mes || '',
                    v: (p.valor_cents || 0) / 100
                }))
            };

            // Dataset bruto para listas de pendentes (últimos 3 meses)
            const pendentesTodos = @json($pendentesTodos ?? []);
            const despesasPendTodos = @json($despesasPendTodos ?? []);

            const elListaAvencer = document.getElementById('listaPendentesAVencer');
            const elListaVencidas = document.getElementById('listaPendentesVencidas');
            const elDespPendAvencer = document.getElementById('listaDespesasPendAVencer');
            const elDespPendVencidas = document.getElementById('listaDespesasPendVencidas');

            const updateChart = (id, points, datasetLabel) => {
                const c = charts[id];
                if (!c) return;
                c.data.labels = points.map(p => p.d);
                c.data.datasets[0].data = points.map(p => p.v);
                c.data.datasets[0].label = datasetLabel || c.data.datasets[0].label;
                c.update();
            };

            // Helper: gerar pontos para todos os dias do mês selecionado
            const daysInMonth = (ym) => {
                if (!ym) return [];
                const [yStr, mStr] = ym.split('-');
                const y = parseInt(yStr, 10);
                const m = parseInt(mStr, 10);
                const lastDay = new Date(y, m, 0).getDate(); // m é 1-12
                return Array.from({
                    length: lastDay
                }, (_, i) => String(i + 1).padStart(2, '0'));
            };

            const normalizeMonthSeries = (ym, arr) => {
                if (!ym) return arr;
                const map = new Map();
                arr.forEach(p => {
                    const d = (p.d || '').slice(0, 10);
                    if (d.startsWith(ym)) map.set(d, p.v || 0);
                });
                return daysInMonth(ym).map(dd => {
                    const key = `${ym}-${dd}`;
                    return {
                        d: key,
                        v: map.has(key) ? map.get(key) : 0
                    };
                });
            };

            const applyMonthFilter = (month) => {
                // MRR não vinculado ao mês; não altera
                // Normaliza os demais gráficos para todos os dias do mês selecionado (YYYY-MM)
                // Auto-ajuste: se mês selecionado é futuro e base==paid_at, trocar para due_date
                try {
                    const ymSel = (month || '').slice(0, 7);
                    const ymNow = new Date().toISOString().slice(0, 7);
                    if (ymSel && ymSel > ymNow && receitasBase === 'paid_at') {
                        receitasBase = 'due_date';
                        if (baseSelect) baseSelect.value = 'due_date';
                        if (localStorage) localStorage.setItem('receitasBase', 'due_date');
                    }
                } catch (_) {}

                const r = normalizeMonthSeries(month, original.receitas); // recebidas
                const rTot = normalizeMonthSeries(month, original.receitas_total);
                const rPend = normalizeMonthSeries(month, original.receitas_pend);
                const rDin = normalizeMonthSeries(month, original.receitas_dinheiro);
                const rGtw = normalizeMonthSeries(month, original.receitas_gateway);
                const dl = normalizeMonthSeries(month, original.despesas_liq);
                const dp = normalizeMonthSeries(month, original.despesas_pend);
                // total diário = liq + pend
                const d = (dl || []).map((p, idx) => ({
                    d: p.d,
                    v: (p.v || 0) + ((dp[idx]?.v) || 0)
                }));
                const i = normalizeMonthSeries(month, original.inadimplencia);
                const t = normalizeMonthSeries(month, original.tickets);
                const m = normalizeMonthSeries(month, original.metodo);

                updateChart('sparkReceitas', r, 'Receitas');
                updateChart('sparkDespesas', d, 'Despesas');
                updateChart('sparkInadimplencia', i, 'Inadimplência');
                updateChart('sparkTickets', t, 'Tickets');
                updateChart('sparkMetodo', m, 'Método');

                // No-data messages (exibe se todos os valores do mês são zero)
                const toggleNoData = (id, points) => {
                    const el = document.querySelector(`[data-no-data-for="${id}"]`);
                    if (!el) return;
                    const hasAny = (points || []).some(p => (p.v || 0) !== 0);
                    el.classList.toggle('hidden', hasAny);
                };
                toggleNoData('sparkReceitas', r);
                toggleNoData('sparkDespesas', d);
                toggleNoData('sparkInadimplencia', i);
                toggleNoData('sparkTickets', t);
                toggleNoData('sparkMetodo', m);

                // Totais no topo (mês inteiro)
                const sum = (arr) => arr.reduce((acc, p) => acc + (p.v || 0), 0);
                const setTotal = (key, val, type) => {
                    const el = document.querySelector(`[data-total-for="${key}"]`);
                    if (!el) return;
                    el.textContent = type === 'currency' ? fmtBRL(val) : fmtNum(val);
                };
                // Receitas KPIs no header
                // Recebido: alinhar ao mês selecionado somando a série por paid_at (sempre)
                setTotal('receitas-recebido', sum(r), 'currency');
                // Total/Pendentes: alternar base via seletor (due_date | paid_at)
                const base = receitasBase || 'due_date';
                const totalVal = base === 'paid_at' ? sum(r) : sum(rTot);
                setTotal('receitas-total', totalVal, 'currency');
                const pendEl = document.querySelector('[data-total-for="receitas-pendentes"]');
                if (base === 'paid_at') {
                    if (pendEl) {
                        pendEl.textContent = '—';
                        pendEl.setAttribute('title', 'Pendentes não se aplicam quando base = paid_at');
                    }
                } else {
                    setTotal('receitas-pendentes', sum(rPend), 'currency');
                    if (pendEl) pendEl.removeAttribute('title');
                }
                setTotal('receitas-dinheiro', sum(rDin), 'currency');
                setTotal('receitas-gateway', sum(rGtw), 'currency');
                setTotal('despesas', sum(dl), 'currency');
                setTotal('despesas-pendentes', sum(dp), 'currency');
                setTotal('despesas-total', sum(dl) + sum(dp), 'currency');
                setTotal('tickets', sum(t), 'number');

                // Listas de pendentes (filtradas pelo mês selecionado)
                if (Array.isArray(pendentesTodos) && (elListaAvencer || elListaVencidas)) {
                    const ym = (month || '').slice(0, 7);
                    const todayStr = new Date().toISOString().slice(0, 10);
                    const startsWithYm = (s) => (s || '').slice(0, 7) === ym;
                    const iso10 = (s) => (s || '').slice(0, 10);
                    // A vencer: restringe por mês e exclui status overdue
                    const avencer = pendentesTodos
                        .filter(rw => startsWithYm(rw.due_date))
                        .filter(rw => (rw.status || '') !== 'overdue')
                        .filter(rw => iso10(rw.due_date) >= todayStr);
                    // Vencidas: inclui todas com due_date < hoje OU status overdue
                    const vencidas = pendentesTodos
                        .filter(rw => (rw.status || '') === 'overdue' || iso10(rw.due_date) < todayStr);

                    const formatDM = (iso) => {
                        const s = (iso || '').slice(0, 10);
                        if (!s) return '';
                        const [Y, M, D] = s.split('-');
                        return `${D}/${M}`;
                    };
                    const toItem = (rw) => {
                        const payer = [rw.payer_nome || '', rw.payer_sobrenome || ''].join(' ').trim();
                        const payerHtml = payer ? `<span class=\"ml-2 text-gray-500\"> ${payer}</span>` :
                            '';
                        return `
                            <div class=\"flex items-center justify-between py-1.5 border-b border-gray-100 last:border-0\">
                                <div class=\"text-xs text-gray-700\">
                                    <span class=\"inline-block text-gray-500\">${formatDM(rw.due_date)}</span>
                                    ${payerHtml}
                                </div>
                                <div class=\"text-xs font-semibold text-gray-900\">${fmtBRL((rw.total_cents || 0) / 100)}</div>
                            </div>`;
                    };

                    const limit = 8;
                    if (elListaAvencer) {
                        elListaAvencer.innerHTML = avencer.length ?
                            avencer.slice(0, limit).map(toItem).join('') :
                            '<p class="text-xs text-gray-500">Nada a vencer</p>';
                    }
                    if (elListaVencidas) {
                        elListaVencidas.innerHTML = vencidas.length ?
                            vencidas.slice(0, limit).map(toItem).join('') :
                            '<p class="text-xs text-gray-500">Sem vencidos</p>';
                    }
                    setTotal('pendentes-avencer', avencer.reduce((acc, rw) => acc + ((rw.total_cents || 0) /
                        100), 0), 'currency');
                    setTotal('pendentes-vencidos', vencidas.reduce((acc, rw) => acc + ((rw.total_cents || 0) /
                        100), 0), 'currency');
                }

                // Listas de despesas pendentes (filtradas pelo mês selecionado)
                if (Array.isArray(despesasPendTodos) && (elDespPendAvencer || elDespPendVencidas)) {
                    const ym = (month || '').slice(0, 7);
                    const todayStr = new Date().toISOString().slice(0, 10);
                    const rows = despesasPendTodos.filter(rw => (rw.data || '').startsWith(ym));
                    const avencer = rows.filter(rw => (rw.data || '') >= todayStr);
                    const vencidas = rows.filter(rw => (rw.data || '') < todayStr);

                    const formatDM = (iso) => {
                        console.log('Data Despesa', iso);
                        console.log('iso', iso.split('T'));
                        if (!iso) return '';
                        const [Y, M, D] = iso.split('T')[0].split('-');
                        return `${D}/${M}`;
                    };
                    const toItem = (rw) => {
                        const catHtml = rw.categoria ?
                            `<span class=\"ml-2 text-gray-500\">— ${rw.categoria}</span>` : '';
                        return `
                            <div class=\"flex items-center justify-between py-1.5 border-b border-gray-100 last:border-0\">
                                <div class=\"text-xs text-gray-700 flex\">
                                    <span class=\"inline-block w-14 text-gray-500\">${formatDM(rw.data)}</span>
                                    <span class=\"ml-2\">${rw.descricao || ''}</span>
                                </div>
                                <div class=\"text-xs font-semibold text-gray-900\">${fmtBRL((rw.valor || 0))}</div>
                            </div>`;
                    };

                    const limit = 8;
                    if (elDespPendAvencer) {
                        elDespPendAvencer.innerHTML = avencer.length ?
                            avencer.slice(0, limit).map(toItem).join('') :
                            '<p class="text-xs text-gray-500">Nada a vencer</p>';
                    }
                    if (elDespPendVencidas) {
                        elDespPendVencidas.innerHTML = vencidas.length ?
                            vencidas.slice(0, limit).map(toItem).join('') :
                            '<p class="text-xs text-gray-500">Sem vencidas</p>';
                    }
                    const sumVal = (arr) => arr.reduce((acc, rw) => acc + (rw.valor || 0), 0);
                    setTotal('despesas-pend-avencer', sumVal(avencer), 'currency');
                    setTotal('despesas-pend-vencidas', sumVal(vencidas), 'currency');
                }
            };

            if (monthEl) {
                monthEl.addEventListener('change', (e) => {
                    const m = e.target.value || '';
                    applyMonthFilter(m);
                });
                // aplica filtro inicial
                applyMonthFilter(monthEl.value || '');
            }

            if (baseSelect) {
                baseSelect.addEventListener('change', (e) => {
                    receitasBase = e.target.value || 'due_date';
                    try {
                        if (localStorage) localStorage.setItem('receitasBase', receitasBase);
                    } catch (_) {}
                    const m = (monthEl && monthEl.value) ? monthEl.value : '';
                    applyMonthFilter(m);
                });
            }
        });
    </script>
    <script>
        // Personalização e ordenação de cards financeiros (namespaced em state.finance)
        (function() {
            const grid = document.getElementById('financeCardsGrid');
            if (!grid) return;

            const storageKey = grid.dataset.storageKey || 'dashboard.finance.cards';
            const modal = document.getElementById('modalPersonalizarCards');
            const btnOpen = document.getElementById('btnPersonalizarCards');
            const toggles = modal ? modal.querySelectorAll('.toggle-card') : [];
            const csrfToken = (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')) || window
                .csrfToken || '';
            const errorEl = modal ? modal.querySelector('#modalPrefError') : null;

            const defaultState = {
                order: ['finance-receitas', 'finance-despesas', 'finance-inadimplencia', 'finance-tickets',
                    'finance-mrr', 'finance-metodo', 'finance-blank'
                ],
                visible: {
                    'finance-receitas': true,
                    'finance-despesas': true,
                    'finance-inadimplencia': true,
                    'finance-tickets': true,
                    'finance-mrr': true,
                    'finance-metodo': true,
                    'finance-blank': true
                }
            };

            let state = Object.assign({}, defaultState); // representa apenas state.finance
            let remoteAll = null; // objeto completo no backend

            const getState = async () => {
                try {
                    const res = await fetch("{{ route('dashboard.preferences.index') }}", {
                        credentials: 'same-origin'
                    });
                    if (!res.ok) return null;
                    const data = await res.json();
                    remoteAll = data?.state || null;
                    return remoteAll?.finance || null;
                } catch (_) {
                    return null;
                }
            };

            const setState = async (newState) => {
                try {
                    const all = Object.assign({}, remoteAll || {});
                    all.finance = newState;
                    await fetch("{{ route('dashboard.preferences.save') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            state: all
                        })
                    });
                    remoteAll = all;
                } catch (_) {}
            };

            const loadAndApplyState = async () => {
                const remote = await getState();
                state = Object.assign({}, defaultState, remote || {});
                applyState();
            };

            const cardsByKey = {};
            grid.querySelectorAll('[data-card-key]').forEach(el => {
                cardsByKey[el.dataset.cardKey] = el;
            });

            const applyState = () => {
                // visibility
                Object.keys(cardsByKey).forEach(key => {
                    const visible = state.visible[key] !== false;
                    cardsByKey[key].classList.toggle('hidden', !visible);
                });
                // order
                const baseOrder = Array.isArray(state.order) ? state.order.slice() : defaultState.order.slice();
                const allKeys = Object.keys(cardsByKey);
                const finalOrder = [...baseOrder, ...allKeys.filter(k => !baseOrder.includes(k))];
                finalOrder.forEach(key => {
                    const el = cardsByKey[key];
                    if (el) grid.appendChild(el);
                });
                // toggles
                toggles.forEach(t => {
                    const key = t.dataset.cardKey;
                    t.checked = state.visible[key] !== false;
                });
                // clear any residual drop highlight
                Object.values(cardsByKey).forEach(el => el.classList.remove('ring-2', 'ring-indigo-400',
                    'ring-offset-2'));
            };
            loadAndApplyState();

            // Modal events
            if (btnOpen && modal) {
                btnOpen.addEventListener('click', () => {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    if (errorEl) {
                        errorEl.textContent = '';
                        errorEl.classList.add('hidden');
                    }
                });
                modal.querySelectorAll('[data-action="close-modal"]').forEach(btn => btn.addEventListener('click',
                    () => {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                    }));
                const btnSave = modal.querySelector('[data-action="save-preferences"]');
                if (btnSave) {
                    btnSave.addEventListener('click', async () => {
                        const saveText = btnSave.querySelector('.save-text');
                        const saveLoading = btnSave.querySelector('.save-loading');
                        btnSave.disabled = true;
                        if (saveLoading) saveLoading.classList.remove('hidden');
                        if (saveText) saveText.textContent = 'Salvando...';
                        toggles.forEach(t => {
                            state.visible[t.dataset.cardKey] = !!t.checked;
                        });
                        let ok = true;
                        try {
                            await setState(state);
                        } catch (e) {
                            ok = false;
                        }
                        applyState();
                        if (saveText) saveText.textContent = ok ? 'Salvo!' : 'Erro ao salvar';
                        if (!ok && errorEl) {
                            errorEl.textContent = 'Falha ao salvar preferências. Verifique sua conexão.';
                            errorEl.classList.remove('hidden');
                        }
                        setTimeout(() => {
                            if (saveLoading) saveLoading.classList.add('hidden');
                            btnSave.disabled = false;
                            if (saveText) saveText.textContent = 'Salvar';
                            if (ok) {
                                modal.classList.add('hidden');
                                modal.classList.remove('flex');
                            }
                        }, 800);
                    });
                }
                const btnRestore = modal.querySelector('[data-action="restore-default"]');
                if (btnRestore) {
                    btnRestore.addEventListener('click', async () => {
                        const original = btnRestore.textContent;
                        btnRestore.disabled = true;
                        btnRestore.textContent = 'Restaurando...';
                        try {
                            state = Object.assign({}, defaultState);
                            await setState(state);
                            applyState();
                            btnRestore.textContent = 'Restaurado!';
                            setTimeout(() => {
                                btnRestore.disabled = false;
                                btnRestore.textContent = original;
                                modal.classList.add('hidden');
                                modal.classList.remove('flex');
                            }, 800);
                        } catch (_) {
                            if (errorEl) {
                                errorEl.textContent = 'Falha ao restaurar preferências. Tente novamente.';
                                errorEl.classList.remove('hidden');
                            }
                            btnRestore.disabled = false;
                            btnRestore.textContent = original;
                        }
                    });
                }
            }

            // Hide button on card
            grid.querySelectorAll('[data-action="hide-card"]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const card = e.currentTarget.closest('[data-card-key]');
                    const key = card?.dataset.cardKey;
                    if (!key) return;
                    state.visible[key] = false;
                    setState(state);
                    applyState();
                });
            });

            // Drag-and-drop ordering via HTML5 drag events
            let dragKey = null;
            grid.addEventListener('dragstart', (e) => {
                // only allow drag starting from the card header handle
                if (!e.target.closest('[data-card-header]')) return;
                const card = e.target.closest('[data-card-key]');
                if (!card) return;
                dragKey = card.dataset.cardKey;
                e.dataTransfer.effectAllowed = 'move';
            });
            grid.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
            });
            // visual feedback: highlight drop target
            Object.values(cardsByKey).forEach(el => {
                el.addEventListener('dragenter', () => {
                    if (!dragKey) return;
                    if (el.classList.contains('hidden')) return;
                    el.classList.add('ring-2', 'ring-indigo-400', 'ring-offset-2');
                });
                el.addEventListener('dragleave', () => {
                    el.classList.remove('ring-2', 'ring-indigo-400', 'ring-offset-2');
                });
                el.addEventListener('drop', () => {
                    el.classList.remove('ring-2', 'ring-indigo-400', 'ring-offset-2');
                });
            });
            grid.addEventListener('drop', (e) => {
                e.preventDefault();
                const target = e.target.closest('[data-card-key]');
                if (!dragKey || !target) return;
                // block drop on hidden cards
                if (target.classList.contains('hidden')) return;
                const targetKey = target.dataset.cardKey;
                if (dragKey === targetKey) return;
                let order = (Array.isArray(state.order) ? state.order.slice() : defaultState.order.slice())
                    .filter(k => cardsByKey[k]);
                const allKeys = Object.keys(cardsByKey);
                order = [...order, ...allKeys.filter(k => !order.includes(k))];
                const fromIdx = order.indexOf(dragKey);
                const toIdx = order.indexOf(targetKey);
                if (fromIdx === -1 || toIdx === -1) return;
                order.splice(toIdx, 0, order.splice(fromIdx, 1)[0]);
                state.order = order;
                setState(state);
                applyState();
            });
        })();
    </script>
@endpush
