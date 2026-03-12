<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo à Newsletter Logigate!</title>
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
        .feature-box {
            background: linear-gradient(135deg, #f0f9ff, #ffffff);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
        }
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .feature-icon {
            width: 40px;
            height: 40px;
            background: #0047AB;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 15px;
        }
        .button {
            display: inline-block;
            background: transparent;
            color: #0047AB;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 600;
            border: 2px solid #0047AB;
            transition: all 0.3s ease;
        }
        .button:hover {
            background: #0047AB;
            color: white;
        }
        .footer {
            background-color: #f8fafc;
            padding: 30px;
            text-align: center;
            color: #64748b;
            font-size: 14px;
            border-top: 1px solid #e2e8f0;
        }
        .unsubscribe {
            color: #94a3b8;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Bem-vindo à Logigate!</h1>
            <p style="margin:10px 0 0; opacity:0.9;">A sua subscrição foi confirmada</p>
        </div>
        
        <div class="content">
            <p style="font-size:18px; margin-bottom:20px;">Olá,</p>
            
            <p style="margin-bottom:20px;">
                É com grande satisfação que lhe damos as boas-vindas à nossa newsletter! 
                Agora vai começar a receber:
            </p>
            
            <div class="feature-box">
                <div class="feature-item">
                    <div class="feature-icon">📋</div>
                    <div>
                        <strong>Atualizações Legislativas</strong>
                        <p style="margin:5px 0 0; color:#64748b;">Mudanças na legislação aduaneira angolana</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">🚀</div>
                    <div>
                        <strong>Novidades do Sistema</strong>
                        <p style="margin:5px 0 0; color:#64748b;">Novas funcionalidades e melhorias</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">💡</div>
                    <div>
                        <strong>Dicas e Melhores Práticas</strong>
                        <p style="margin:5px 0 0; color:#64748b;">Otimize a sua gestão aduaneira</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">🎁</div>
                    <div>
                        <strong>Ofertas Exclusivas</strong>
                        <p style="margin:5px 0 0; color:#64748b;">Promoções especiais para subscritores</p>
                    </div>
                </div>
            </div>
            
            <p style="margin:30px 0 20px;">
                Enquanto isso, que tal conhecer melhor a nossa plataforma?
            </p>
            
            <div style="text-align: center;">
                <a href="{{ url('/#demo') }}" class="button">
                    👀 Solicitar Demonstração
                </a>
            </div>
            
            <p style="margin-top:30px; color:#64748b;">
                Se tiver alguma dúvida ou sugestão, responda a este email ou contacte-nos 
                através do nosso site.
            </p>
        </div>
        
        <div class="footer">
            <p>© 2024 Logigate by Hongayetu LDA. Todos os direitos reservados.</p>
            <p class="unsubscribe" style="margin-top:15px;">
                Pretende cancelar a subscrição? 
                <a href="{{ url('/api/v1/newsletter/unsubscribe?email=' . $subscriber->email) }}" 
                   style="color:#94a3b8;">Clique aqui</a>
            </p>
        </div>
    </div>
</body>
</html>