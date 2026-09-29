<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Monitoramento Corporal</title>
    
    <style>
        
        :root {
            --bg-dark: #1e272e;
            --bg-card: #2c3e50;
            --bg-input: #34495e;
            --primary: #0fb9b1;
            --primary-hover: #2bcbba;
            --text-light: #d2dae2;
            --text-muted: #808e9b;
        }

        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        body { 
            background-color: var(--bg-dark); 
            color: var(--text-light); 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            padding: 20px;
        }

        
        .auth-box { 
            background-color: var(--bg-card); 
            padding: 40px; 
            border-radius: 12px; 
            width: 100%; 
            max-width: 400px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.5); 
            border-top: 4px solid var(--primary); 
        }

        .auth-box h2 { color: var(--primary); text-align: center; margin-bottom: 30px; font-size: 28px; }
        .auth-box form { display: flex; flex-direction: column; gap: 20px; }
        
        
        .input-group { display: flex; flex-direction: column; gap: 8px; }
        .input-group label { font-size: 15px; font-weight: bold; color: var(--text-light); }
        .input-group input { 
            padding: 15px; 
            border-radius: 8px; 
            border: 1px solid var(--bg-input); 
            background-color: var(--bg-input); 
            color: #fff; 
            font-size: 16px;
            outline: none; 
            transition: 0.3s;
        }
        .input-group input:focus { border-color: var(--primary); }
        
        
        .btn-salvar { 
            background-color: var(--primary); 
            color: #fff; 
            padding: 15px; 
            border: none; 
            border-radius: 8px; 
            font-size: 18px;
            font-weight: bold; 
            cursor: pointer; 
            transition: 0.3s; 
            margin-top: 10px;
        }
        .btn-salvar:hover { background-color: var(--primary-hover); }
        
        
        .auth-link { text-align: center; margin-top: 25px; font-size: 14px; color: var(--text-muted); }
        .auth-link a { color: var(--primary); text-decoration: none; font-weight: bold; transition: 0.3s; }
        .auth-link a:hover { color: var(--primary-hover); text-decoration: underline; }

        @media (max-width: 480px) {
            .auth-box { padding: 25px 20px; }
            .auth-box h2 { font-size: 22px; }
        }
    </style>
</head>
<body>

    <div class="auth-box">
        <h2>MonCorp</h2>
        
        
        <form action="processa_login.php" method="POST">
            <div class="input-group">
                <label>E-mail</label>
                <input type="email" name="email" placeholder="Seu e-mail cadastrado" required>
            </div>
            
            <div class="input-group">
                <label>Senha</label>
                <input type="password" name="senha" placeholder="Sua senha" required>
            </div>
            
            <button type="submit" class="btn-salvar">Entrar no Sistema</button>
        </form>

        <div class="auth-link">
            Ainda não tem conta? <a href="cadastro.php">Cadastre-se aqui</a>
        </div>
    </div>

</body>
</html>