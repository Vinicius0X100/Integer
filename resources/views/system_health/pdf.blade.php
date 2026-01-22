<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Monitoramento de Sistemas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #0071e3;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f7;
            font-weight: bold;
        }
        .status-online {
            color: green;
            font-weight: bold;
        }
        .status-offline {
            color: red;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Monitoramento de Sistemas</h1>
        <p>Gerado em: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Data/Hora</th>
                <th>Sistema</th>
                <th>Status</th>
                <th>Latência</th>
                <th>Código</th>
                <th>Erro</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td>{{ $log->system_name }}</td>
                    <td>
                        <span class="{{ $log->status ? 'status-online' : 'status-offline' }}">
                            {{ $log->status ? 'ONLINE' : 'OFFLINE' }}
                        </span>
                    </td>
                    <td>{{ $log->response_time_ms }} ms</td>
                    <td>{{ $log->status_code ?? '-' }}</td>
                    <td>{{ Str::limit($log->error_message, 40) ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Integer - Sistema de Gestão Sacratech</p>
    </div>
</body>
</html>
