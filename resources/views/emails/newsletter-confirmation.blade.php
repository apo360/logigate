<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirme a sua subscrição</title>
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            line-height: 1.6;
            color: #1E293B;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #0047AB, #00B4D8);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .content {
            padding: 40px 30px;
            background-color: white;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #0047AB, #003580);
            color: white;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 12px;
            font-weight: 600;
            margin: 20px 0;
            transition: transform 0.3s ease;
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 71, 171, 0.3);
        }
        .footer {
            background-color: #f8fafc;
            padding: 30px;
            text-align: center;
            color: #64748b;
            font-size: 14px;
            border-top: 1px solid #e2e8f0;
        }
        .note {
            background-color: #f0f9ff;
            border-left: 4px solid #00B4D8;
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📧 Confirme a sua subscrição</h1>
            <p style="margin:10px 0 0; opacity:0.9;">Falta apenas um passo!</p>
        </div>
        
        <div class="content">
            <p style="font-size:18px; margin-bottom:20px;">Olá,</p>
            
            <p style="margin-bottom:20px;">
                Recebemos o seu pedido de subscrição da <strong>Newsletter Logigate</strong>. 
                Para começar a receber as nossas atualizações sobre legislação aduaneira 
                e novidades do sistema, por favor confirme o seu email:
            </p>
            
            <div style="text-align: center;">
                <a href="{{ url('/api/v1/newsletter/confirm/' . $subscriber->token) }}" 
                   class="button">
                    ✅ Confirmar Subscrição
                </a>
            </div>
            
            <div class="note">
                <strong>📌 Nota:</strong> Se não foi você que solicitou esta subscrição, 
                pode ignorar este email. O seu email será automaticamente removido da 
                nossa lista em 48 horas.
            </div>
            
            <p style="color:#64748b; font-size:14px; margin-top:30px;">
                Se o botão não funcionar, copie e cole este link no seu navegador:<br>
                <span style="color:#0047AB; word-break:break-all;">
                    {{ url('/api/v1/newsletter/confirm/' . $subscriber->token) }}
                </span>
            </p>
        </div>
        
        <div class="footer">
            <p>© 2024 Logigate by Hongayetu LDA. Todos os direitos reservados.</p>
            <p style="margin-top:10px;">
                <a href="{{ url('/') }}" style="color:#0047AB; text-decoration:none;">Visite o nosso site</a>
            </p>
        </div>
    </div>
</body>
</html>