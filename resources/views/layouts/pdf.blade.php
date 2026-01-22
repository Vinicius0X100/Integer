<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@yield('title', 'Relatório - Integer')</title>
    <style>
        @page {
            margin: 100px 25px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
        }
        header {
            position: fixed;
            top: -80px;
            left: 0;
            right: 0;
            height: 80px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 50px;
            border-top: 1px solid #eee;
            padding-top: 10px;
            font-size: 8pt;
            color: #777;
        }
        .header-content {
            display: table;
            width: 100%;
        }
        .logo-section {
            display: table-cell;
            width: 30%;
            vertical-align: middle;
        }
        .info-section {
            display: table-cell;
            width: 70%;
            text-align: right;
            vertical-align: middle;
        }
        .logo {
            max-height: 50px;
            max-width: 150px;
        }
        .company-name {
            font-size: 16pt;
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
        }
        .security-badge {
            font-size: 7pt;
            text-transform: uppercase;
            color: #666;
            letter-spacing: 1px;
            background-color: #f5f5f5;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
        }
        .footer-content {
            display: table;
            width: 100%;
        }
        .copyright {
            display: table-cell;
            text-align: left;
            width: 50%;
        }
        .page-number {
            display: table-cell;
            text-align: right;
            width: 50%;
        }
        .page-number:before {
            content: "Página " counter(page);
        }
        
        /* Utility classes */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .text-uppercase { text-transform: uppercase; }
        .mb-4 { margin-bottom: 1.5rem; }
        
        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f8f9fa;
            color: #444;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
            padding: 10px 8px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
        }
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        tr:nth-child(even) {
            background-color: #fcfcfc;
        }
        
        /* Status Badges in PDF */
        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
        }
        .badge-success { color: #198754; background-color: #d1e7dd; }
        .badge-warning { color: #ffc107; background-color: #fff3cd; }
        .badge-danger { color: #dc3545; background-color: #f8d7da; }
        .badge-info { color: #0dcaf0; background-color: #cff4fc; }
        .badge-secondary { color: #6c757d; background-color: #e2e3e5; }
    </style>
</head>
<body>
    <header>
        @section('header')
        <div class="header-content">
            <div class="logo-section">
                <!-- Fallback to text if image missing, but setup for image -->
                @if(file_exists(public_path('img/logo-black.png')))
                    <img src="{{ public_path('img/logo-black.png') }}" class="logo" alt="Integer">
                @elseif(file_exists(public_path('img/logo.png')))
                    <img src="{{ public_path('img/logo.png') }}" class="logo" alt="Integer">
                @else
                    <h1 class="company-name" style="margin:0;">Integer</h1>
                @endif
            </div>
            <div class="info-section">
                <div class="company-name">Integer</div>
                <div class="security-badge">
                    <span style="font-family: sans-serif;">🔒</span> Documento Protegido &bull; LGPD &bull; Uso Interno
                </div>
                <div style="font-size: 8pt; color: #888; margin-top: 4px;">
                    Gerado em: {{ date('d/m/Y H:i') }} por {{ auth()->user()->nome ?? 'Sistema' }}
                </div>
            </div>
        </div>
        @show
    </header>

    <footer>
        <div class="footer-content">
            <div class="copyright">
                &copy; {{ date('Y') }} Sacratech Softwares Ltda. Todos os direitos reservados.
                <br>
                <span style="font-size: 7pt; color: #999;">Integer é um serviço oferecido pela Sacratech Softwares.</span>
                <br>
                <span style="font-size: 7pt; color: #999;">Confidencialidade garantida nos termos da Lei 13.709/2018 (LGPD).</span>
            </div>
            <div class="page-number"></div>
        </div>
    </footer>

    <main>
        @yield('content')
    </main>
</body>
</html>