<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seu Código de Confirmação</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #2C3E50;
            font-size: 24px;
            text-align: center;
        }
        p {
            font-size: 16px;
            line-height: 1.5;
            text-align: center;
        }
        .code {
            font-size: 24px;
            font-weight: bold;
            color: #E74C3C;
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
            display: inline-block;
            margin: 20px 0;
        }
        .footer {
            font-size: 12px;
            color: #999;
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Seu Código de Confirmação</h1>
        <p>Use o código abaixo para confirmar seu email:</p>
        <p class="code">{{ $code }}</p>
        <p class="footer">Se você não solicitou este código, por favor, ignore este e-mail.</p>
    </div>
</body>
</html>

