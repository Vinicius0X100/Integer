@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<style>
    .hover-scale {
        transition: transform 0.2s ease, background-color 0.2s ease;
    }
    .hover-scale:hover {
        transform: translateY(-3px);
        background-color: rgba(255,255,255,0.2) !important;
    }
    .backdrop-blur {
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
    
    /* Financial Section Styles */
    .financial-wrapper {
        border-radius: 20px;
        overflow: hidden; /* Ensure blur doesn't leak */
        transition: all 0.5s ease;
    }
    
    .financial-content {
        transition: filter 0.5s ease;
        filter: blur(20px); /* Strong blur by default */
        pointer-events: none; /* Prevent interaction when locked */
        user-select: none;
    }
    
    .financial-content.unlocked {
        filter: blur(0);
        pointer-events: auto;
        user-select: auto;
    }

    .financial-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 50;
        display: flex;
        justify-content: center;
        align-items: center;
        transition: opacity 0.5s ease, visibility 0.5s ease;
        background: rgba(255, 255, 255, 0.05); /* Very slight tint */
    }

    .financial-overlay.hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }

    .lock-card {
        background: var(--apple-card-bg);
        backdrop-filter: saturate(180%) blur(20px);
        -webkit-backdrop-filter: saturate(180%) blur(20px);
        border: 1px solid var(--apple-border);
        padding: 40px;
        border-radius: 24px;
        text-align: center;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        max-width: 400px;
        width: 90%;
        transform: translateY(0);
        transition: transform 0.3s ease;
    }
    
    .lock-card:hover {
        transform: translateY(-5px);
    }

    .password-input-group {
        overflow: hidden;
        max-height: 0;
        opacity: 0;
        transition: all 0.4s ease;
    }

    .password-input-group.show {
        max-height: 100px; /* Enough space for input */
        opacity: 1;
        margin-top: 20px;
    }
    
    .skeleton-text {
        color: transparent;
        background-color: rgba(128, 128, 128, 0.2);
        border-radius: 4px;
        display: inline-block;
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .5; }
    }
</style>

<div class="row g-4">
    <!-- Welcome Section -->
    <div class="col-12">
        <div class="card border-0 shadow-sm bg-primary text-white overflow-hidden position-relative">
            <div class="card-body p-5 position-relative" style="z-index: 2;">
                <div class="row align-items-center">
                    <div class="col-lg-8 mb-4 mb-lg-0">
                        <h2 class="fw-bold mb-2">Bem-vindo, {{ Auth::user()->nome_exibicao ?? Auth::user()->nome }}!</h2>
                        <p class="lead opacity-75 mb-4">
                            Visão geral do sistema Integer. Aqui estão os dados em tempo real da sua plataforma.
                        </p>
                        <div class="d-flex gap-3 flex-wrap">
                            <a href="{{ route('users.create') }}" class="btn btn-light rounded-pill px-4 fw-bold text-primary hover-scale">
                                <i class="bi bi-plus-lg me-2"></i> Novo Usuário
                            </a>
                            <a href="{{ route('users.index') }}" class="btn btn-outline-light rounded-pill px-4 fw-bold hover-scale">
                                <i class="bi bi-people me-2"></i> Gerenciar Usuários
                            </a>
                            <button id="hideFinancialBtn" class="btn btn-warning rounded-pill px-4 fw-bold hover-scale d-none" onclick="lockFinancials()">
                                <i class="bi bi-eye-slash-fill me-2"></i> Ocultar Financeiro
                            </button>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="weather-widget p-3 rounded-4 bg-white bg-opacity-10 backdrop-blur border border-white border-opacity-25 text-end">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="text-start">
                                    <i id="weather-icon" class="bi bi-cloud-sun fs-1"></i>
                                </div>
                                <div>
                                    <h1 class="display-4 fw-bold mb-0 lh-1" id="clock-time">--:--</h1>
                                    <p class="mb-0 opacity-75 small text-uppercase" id="clock-date">--</p>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-end border-top border-white border-opacity-25 pt-3 mt-2">
                                <div class="text-start">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-geo-alt-fill opacity-75"></i>
                                        <span id="weather-location" class="fw-bold">Localizando...</span>
                                    </div>
                                    <small class="opacity-75 d-block" id="weather-desc">--</small>
                                </div>
                                <div class="display-6 fw-bold" id="weather-temp">--°</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Abstract Decoration -->
            <div class="position-absolute top-0 end-0 h-100 w-50 d-none d-md-block" 
                 style="background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%); transform: skewX(-20deg) translateX(20%);">
            </div>
        </div>
    </div>

    <!-- Stats Cards Row 1: Users (Always Visible) -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-uppercase text-muted small fw-bold mb-1">Total de Usuários</h6>
                        <h2 class="display-5 fw-bold mb-0 count-up" data-value="{{ $totalUsers }}">0</h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                        <i class="bi bi-people-fill fs-4"></i>
                    </div>
                </div>
                
                <div class="mt-4">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Ativos ({{ $activeUsers }})</span>
                        <span>Inativos ({{ $inactiveUsers }})</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        @php
                            $activePercent = $totalUsers > 0 ? ($activeUsers / $totalUsers) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $activePercent }}%"></div>
                        <div class="progress-bar bg-secondary opacity-25" role="progressbar" style="width: {{ 100 - $activePercent }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-uppercase text-muted small fw-bold mb-1">Novos (30 dias)</h6>
                        <h2 class="display-5 fw-bold mb-0 count-up" data-value="{{ $newUsersMonth }}">0</h2>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-3">
                        <i class="bi bi-person-plus-fill fs-4"></i>
                    </div>
                </div>
                
                <div class="mt-4">
                    <p class="mb-0 small {{ $growth >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                        <i class="bi {{ $growth >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i> 
                        {{ number_format(abs($growth), 1) }}% 
                        <span class="text-muted fw-normal">vs mês anterior</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-uppercase text-muted small fw-bold mb-1">Armazenamento</h6>
                        <h2 class="display-5 fw-bold mb-0">45%</h2>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3">
                        <i class="bi bi-hdd-fill fs-4"></i>
                    </div>
                </div>
                
                <div class="mt-4">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>450GB usados</span>
                        <span>1TB total</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 45%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Productivity & Roles Section (Always Visible) -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-uppercase text-muted small fw-bold mb-1">Serviços Concluídos</h6>
                        <h2 class="display-6 fw-bold mb-0">{{ $servicosConcluidos }}</h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                </div>
                <p class="mb-0 small text-muted">Total de serviços finalizados com sucesso.</p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold mb-0">Distribuição por Papel</h5>
                <p class="text-muted small">Usuários por nível de acesso</p>
            </div>
            <div class="card-body p-4 d-flex align-items-center justify-content-center">
                <div style="height: 250px; width: 100%;">
                    <canvas id="roleDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold mb-0">Produtividade de Serviços</h5>
                <p class="text-muted small">Comparativo: Criados vs Concluídos</p>
            </div>
            <div class="card-body p-4">
                <canvas id="productivityChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- PROTECTED FINANCIAL SECTION WRAPPER -->
    <div class="col-12 financial-wrapper position-relative">
        
        <!-- Overlay for Protection -->
        <div id="financialOverlay" class="financial-overlay">
            <div class="lock-card">
                <div id="lockIconContainer">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-inline-block mb-3">
                        <i class="bi bi-shield-lock-fill fs-1"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Dados Financeiros Protegidos</h4>
                    <p class="text-muted mb-4 small">Esta seção contém informações sensíveis. Digite sua senha de administrador para visualizar.</p>
                    
                    <button class="btn btn-primary rounded-pill px-5 fw-bold w-100" onclick="showPasswordInput()">
                        Desbloquear
                    </button>
                </div>

                <div id="passwordInputContainer" class="password-input-group">
                    <div class="form-group mb-3 text-start">
                        <label class="form-label small fw-bold text-muted">Senha do Administrador</label>
                        <input type="password" id="adminPassword" class="form-control form-control-lg" placeholder="Digite sua senha..." onkeypress="handleEnter(event)">
                        <div class="invalid-feedback" id="passwordError">Senha incorreta.</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-light rounded-pill flex-grow-1" onclick="hidePasswordInput()">Cancelar</button>
                        <button class="btn btn-primary rounded-pill flex-grow-1 fw-bold" id="btnUnlock" onclick="unlockFinancials()">
                            <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                            Acessar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Blurred Financial Content -->
        <div id="financialContent" class="financial-content">
            <div class="row g-4">
                <!-- Financial Cards -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="text-uppercase text-muted small fw-bold mb-1">Lucro Realizado (Mês)</h6>
                                    <h2 class="display-6 fw-bold mb-0 text-success" id="val-lucro-realizado">----</h2>
                                </div>
                                <div class="bg-success bg-opacity-10 text-success rounded-circle p-3">
                                    <i class="bi bi-currency-dollar fs-4"></i>
                                </div>
                            </div>
                            <p class="mb-0 small text-muted">Baseado em serviços concluídos este mês.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="text-uppercase text-muted small fw-bold mb-1">Lucro Presumido</h6>
                                    <h2 class="display-6 fw-bold mb-0 text-info" id="val-lucro-presumido">----</h2>
                                </div>
                                <div class="bg-info bg-opacity-10 text-info rounded-circle p-3">
                                    <i class="bi bi-graph-up-arrow fs-4"></i>
                                </div>
                            </div>
                            <p class="mb-0 small text-muted">Previsão de serviços em andamento/pendentes.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4 d-flex flex-column justify-content-center">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="text-uppercase text-muted small fw-bold mb-1">Ticket Médio</h6>
                                    <h2 class="display-6 fw-bold mb-0 text-dark" id="val-ticket-medio">----</h2>
                                </div>
                                <div class="bg-secondary bg-opacity-10 text-dark rounded-circle p-3">
                                    <i class="bi bi-receipt fs-4"></i>
                                </div>
                            </div>
                            <p class="mb-0 small text-muted">Valor médio recebido por serviço concluído.</p>
                            <hr class="my-4 opacity-10">
                            <div class="d-flex align-items-center gap-3">
                                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-2">
                                    <i class="bi bi-graph-up-arrow"></i>
                                </div>
                                <div>
                                    <small class="d-block text-muted lh-1">Maior Lucro (Quad)</small>
                                    <span class="fw-bold text-dark" id="val-max-quad">----</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Financials (Quadrimestres) -->
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                            <h5 class="fw-bold mb-0">Lucro por Quadrimestre</h5>
                            <p class="text-muted small">Comparativo de desempenho dos últimos períodos</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3" id="quadrimesters-container">
                                <!-- Placeholders for 4 Quadrimestres -->
                                @for($i=0; $i<4; $i++)
                                <div class="col-md-3">
                                    <div class="p-3 rounded-3 h-100 bg-light border border-light">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-secondary bg-opacity-25 text-secondary rounded-pill">----</span>
                                        </div>
                                        <h4 class="mb-0 fw-bold text-dark">----</h4>
                                        <small class="text-muted">Lucro Realizado</small>
                                    </div>
                                </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Chart -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold mb-0">Evolução Financeira</h5>
                                <p class="text-muted small">Lucro realizado nos últimos 6 meses</p>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <canvas id="financialChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END PROTECTED SECTION -->

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // --- Global Chart Instances ---
    let financialChartInstance = null;
    let roleChartInstance = null;
    let productivityChartInstance = null;
    let userEvolutionChartInstance = null;

    document.addEventListener('DOMContentLoaded', function() {
        // Animation for Numbers
        const counters = document.querySelectorAll('.count-up');
        counters.forEach(counter => {
            const target = +counter.getAttribute('data-value');
            const duration = 1000; // 1s
            const increment = target / (duration / 16); // 60fps
            
            let current = 0;
            const updateCount = () => {
                current += increment;
                if (current < target) {
                    counter.innerText = Math.ceil(current);
                    requestAnimationFrame(updateCount);
                } else {
                    counter.innerText = target;
                }
            };
            updateCount();
        });

        // --- Weather Widget Logic (Mock) ---
        const updateClock = () => {
            const now = new Date();
            document.getElementById('clock-time').innerText = now.toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'});
            document.getElementById('clock-date').innerText = now.toLocaleDateString('pt-BR', {weekday: 'long', day: 'numeric', month: 'long'});
        };
        setInterval(updateClock, 1000);
        updateClock();

        // Simulate Weather Fetch
        setTimeout(() => {
            document.getElementById('weather-location').innerText = 'São Paulo, SP';
            document.getElementById('weather-temp').innerText = '24°';
            document.getElementById('weather-desc').innerText = 'Parcialmente Nublado';
            document.getElementById('weather-icon').className = 'bi bi-cloud-sun fs-1';
        }, 1500);

        // --- Initialize Public Charts ---
        initPublicCharts();

        // --- Initialize Financial Chart (Empty/Placeholder) ---
        const ctxFin = document.getElementById('financialChart').getContext('2d');
        financialChartInstance = new Chart(ctxFin, {
            type: 'line',
            data: {
                labels: ['--', '--', '--', '--', '--', '--'],
                datasets: [{
                    label: 'Lucro (Bloqueado)',
                    data: [0, 0, 0, 0, 0, 0],
                    borderColor: '#e0e0e0',
                    backgroundColor: 'rgba(224, 224, 224, 0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { display: false }, x: { display: false } }
            }
        });
    });

    // --- Interaction Logic for Protection ---

    function showPasswordInput() {
        document.getElementById('lockIconContainer').classList.add('d-none');
        document.getElementById('passwordInputContainer').classList.add('show');
        setTimeout(() => document.getElementById('adminPassword').focus(), 100);
    }

    function hidePasswordInput() {
        document.getElementById('passwordInputContainer').classList.remove('show');
        document.getElementById('lockIconContainer').classList.remove('d-none');
        document.getElementById('adminPassword').value = '';
        document.getElementById('adminPassword').classList.remove('is-invalid');
    }

    function handleEnter(e) {
        if (e.key === 'Enter') unlockFinancials();
    }

    function unlockFinancials() {
        const password = document.getElementById('adminPassword').value;
        const btn = document.getElementById('btnUnlock');
        const spinner = btn.querySelector('.spinner-border');
        const errorDiv = document.getElementById('passwordError');

        if (!password) return;

        // Loading state
        btn.disabled = true;
        spinner.classList.remove('d-none');
        document.getElementById('adminPassword').classList.remove('is-invalid');

        fetch('{{ route("dashboard.financial_data") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ password: password })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Unlock Success
                updateFinancialData(data.data);
                
                // Animate Unlock
                const overlay = document.getElementById('financialOverlay');
                const content = document.getElementById('financialContent');
                
                overlay.classList.add('hidden');
                content.classList.add('unlocked');
                
                // Show Hide Button
                document.getElementById('hideFinancialBtn').classList.remove('d-none');
                
                // Clear password input
                document.getElementById('adminPassword').value = '';
            } else {
                // Error
                document.getElementById('adminPassword').classList.add('is-invalid');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Erro ao comunicar com o servidor.');
        })
        .finally(() => {
            btn.disabled = false;
            spinner.classList.add('d-none');
        });
    }

    function lockFinancials() {
        const overlay = document.getElementById('financialOverlay');
        const content = document.getElementById('financialContent');
        
        overlay.classList.remove('hidden');
        content.classList.remove('unlocked');
        
        // Reset overlay state
        hidePasswordInput();
        
        // Hide button
        document.getElementById('hideFinancialBtn').classList.add('d-none');
    }

    function updateFinancialData(data) {
        // Update Text Values
        document.getElementById('val-lucro-realizado').innerText = 'R$ ' + data.lucroRealizadoMes;
        document.getElementById('val-lucro-presumido').innerText = 'R$ ' + data.lucroPresumido;
        document.getElementById('val-ticket-medio').innerText = 'R$ ' + data.ticketMedio;
        document.getElementById('val-max-quad').innerText = 'R$ ' + data.maxQuadLucro;

        // Update Quadrimesters
        const quadContainer = document.getElementById('quadrimesters-container');
        quadContainer.innerHTML = ''; // Clear placeholders
        
        data.quadrimesters.forEach(quad => {
            const activeClass = quad.is_current 
                ? 'bg-primary bg-opacity-10 border border-primary border-opacity-25' 
                : 'bg-light border border-light';
            
            const badgeClass = quad.is_current 
                ? 'bg-primary' 
                : 'bg-secondary bg-opacity-25 text-secondary';
                
            const textClass = quad.is_current ? 'text-primary' : 'text-dark';
            
            const html = `
                <div class="col-md-3">
                    <div class="p-3 rounded-3 h-100 ${activeClass}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge ${badgeClass} rounded-pill">
                                ${quad.label}
                            </span>
                            ${quad.is_current ? '<small class="text-primary fw-bold"><i class="bi bi-circle-fill small me-1"></i>Atual</small>' : ''}
                        </div>
                        <h4 class="mb-0 fw-bold ${textClass}">
                            R$ ${quad.lucro}
                        </h4>
                        <small class="text-muted">Lucro Realizado</small>
                    </div>
                </div>
            `;
            quadContainer.insertAdjacentHTML('beforeend', html);
        });

        // Update Financial Chart
        if (financialChartInstance) {
            financialChartInstance.data.labels = data.chart.labels;
            financialChartInstance.data.datasets = [
                {
                    label: 'Lucro Realizado',
                    data: data.chart.lucro,
                    borderColor: '#198754', // success
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Receita Total',
                    data: data.chart.receita,
                    borderColor: '#0d6efd', // primary
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: false,
                    tension: 0.4
                }
            ];
            financialChartInstance.options.scales = {
                y: { display: true, beginAtZero: true },
                x: { display: true }
            };
            financialChartInstance.options.plugins.legend.display = true;
            financialChartInstance.update();
        }
    }

    function initPublicCharts() {
        // Role Distribution Chart
        const ctxRole = document.getElementById('roleDistributionChart').getContext('2d');
        roleChartInstance = new Chart(ctxRole, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($roles->keys()) !!},
                datasets: [{
                    data: {!! json_encode($roles->values()) !!},
                    backgroundColor: [
                        '#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545', '#fd7e14'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 20 }
                    }
                },
                cutout: '70%'
            }
        });

        // Productivity Chart
        const ctxProd = document.getElementById('productivityChart').getContext('2d');
        productivityChartInstance = new Chart(ctxProd, {
            type: 'bar',
            data: {
                labels: {!! json_encode($months) !!},
                datasets: [
                    {
                        label: 'Criados',
                        data: {!! json_encode($createdServicesCounts) !!},
                        backgroundColor: '#0d6efd',
                        borderRadius: 4
                    },
                    {
                        label: 'Concluídos',
                        data: {!! json_encode($completedServicesCounts) !!},
                        backgroundColor: '#198754',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
</script>
@endsection