<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Contacto - Logigate</title>
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            line-height: 1.6;
            color: #1E293B;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8fafc;
        }
        .header {
            background: linear-gradient(135deg, #0047AB, #003580);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 12px 12px 0 0;
        }
        .content {
            background-color: white;
            padding: 30px;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .field {
            margin-bottom: 20px;
        }
        .label {
            font-weight: 600;
            color: #0047AB;
            margin-bottom: 5px;
            display: block;
        }
        .value {
            color: #1E293B;
            padding: 10px;
            background-color: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #00B4D8;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            color: #64748b;
            font-size: 14px;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            background-color: #00B4D8;
            color: white;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0; font-size:24px;">📬 Novo Contacto - Logigate</h1>
            <p style="margin:10px 0 0; opacity:0.9;">Mensagem recebida através do website</p>
        </div>
        
        <div class="content">
            <div class="field">
                <span class="label">👤 Nome:</span>
                <div class="value">{{ $message->nome }}</div>
            </div>
            
            <div class="field">
                <span class="label">📧 Email:</span>
                <div class="value">
                    <a href="mailto:{{ $message->email }}" style="color:#0047AB;">
                        {{ $message->email }}
                    </a>
                </div>
            </div>
            
            <div class="field">
                <span class="label">📞 Telefone:</span>
                <div class="value">
                    <a href="tel:{{ $message->telefone }}" style="color:#0047AB;">
                        {{ $message->telefone }}
                    </a>
                </div>
            </div>
            
            @if($message->empresa)
            <div class="field">
                <span class="label">🏢 Empresa:</span>
                <div class="value">{{ $message->empresa }}</div>
            </div>
            @endif
            
            <div class="field">
                <span class="label">📌 Assunto:</span>
                <div class="value">{{ $message->assunto }}</div>
            </div>
            
            <div class="field">
                <span class="label">💬 Mensagem:</span>
                <div class="value" style="white-space: pre-line;">{{ $message->mensagem }}</div>
            </div>
            
            <div class="badge">
                ID: #{{ $message->id }} • {{ $message->created_at->format('d/m/Y H:i') }}
            </div>
            
            <div class="footer">
                <p>Este email foi enviado através do formulário de contacto do website Logigate.</p>
                <p style="font-size:12px;">IP: {{ $message->ip_address }} • User Agent: {{ $message->user_agent }}</p>
            </div>
        </div>
    </div>
</body>
</html>