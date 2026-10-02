<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Monitoramento Corporal</title>
    
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
            max-width: 550px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.5); 
            border-top: 4px solid var(--primary); 
        }

        .auth-box h2 { color: var(--primary); text-align: center; margin-bottom: 10px; font-size: 28px; }
        .auth-box p { text-align: center; color: var(--text-muted); margin-bottom: 30px; font-size: 14px; }
        
        .auth-box form { display: flex; flex-direction: column; gap: 15px; }
        
        
        .row { display: flex; gap: 15px; flex-wrap: wrap; }
        .row .input-group { flex: 1; min-width: 180px; }

        .input-group { display: flex; flex-direction: column; gap: 8px; }
        .input-group label { font-size: 15px; font-weight: bold; color: var(--text-light); }
        
        
        .input-group input, 
        .input-group select { 
            padding: 15px; 
            border-radius: 8px; 
            border: 1px solid var(--bg-input); 
            background-color: var(--bg-input); 
            color: #fff; 
            font-size: 16px;
            outline: none; 
            transition: 0.3s;
            width: 100%;
        }
        .input-group input:focus, 
        .input-group select:focus { border-color: var(--primary); }
        
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
            margin-top: 15px;
        }
        .btn-salvar:hover { background-color: var(--primary-hover); }
        
        .auth-link { text-align: center; margin-top: 25px; font-size: 14px; color: var(--text-muted); }
        .auth-link a { color: var(--primary); text-decoration: none; font-weight: bold; transition: 0.3s; }
        .auth-link a:hover { color: var(--primary-hover); text-decoration: underline; }

        @media (max-width: 480px) {
            .auth-box { padding: 25px 20px; }
            .auth-box h2 { font-size: 22px; }
            .row { flex-direction: column; gap: 12px; }
            .row .input-group { min-width: 100%; }
        }
    </style>
</head>
<body>

    <div class="auth-box">
        <h2>Criar Conta</h2>
        <p>Preencha os dados abaixo para o sistema personalizar sua evolução.</p>
        
        
        <form action="processa_cadastro.php" method="POST">
            
            <div class="row">
                <div class="input-group">
                    <label>Nome Completo</label>
                    <input type="text" name="nome" placeholder="Ex: João da Silva" required>
                </div>
                <div class="input-group">
                    <label>Gênero</label>
                    <select name="genero" required>
                        <option value="" disabled selected>Selecione...</option>
                        <option value="Feminino">Feminino</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Outro">Prefiro não informar</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="input-group">
                    <label>E-mail</label>
                    <input type="email" name="email" placeholder="seuemail@exemplo.com" required>
                </div>
                <div class="input-group">
                    <label>Senha</label>
                    <input type="password" name="senha" placeholder="Crie uma senha" required>
                </div>
            </div>

            
            <div class="row">
                <div class="input-group">
                    <label>Peso Atual (kg)</label>
                    <input type="number" step="0.1" name="peso" placeholder="Ex: 75.5" required>
                </div>
                <div class="input-group">
                    <label>Altura (m)</label>
                    <input type="number" step="0.01" name="altura" placeholder="Ex: 1.75" required>
                </div>
            </div>

            
            <div class="row">
                <div class="input-group">
                    <label>Nível de Atividade</label>
                    <select name="atividade" required>
                        <option value="" disabled selected>Como é sua rotina?</option>
                        <option value="sedentario">Sedentário</option>
                        <option value="iniciante">Iniciante (1-2 dias/sem)</option>
                        <option value="intermediario">Intermediário (3-4 dias/sem)</option>
                        <option value="avancado">Avançado</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Seu Maior Objetivo</label>
                    <select name="objetivo" required>
                        <option value="" disabled selected>O que você busca?</option>
                        <option value="Perder peso">Perder peso</option>
                        <option value="Ganhar massa">Ganhar massa muscular</option>
                        <option value="Saude">Mais saúde / Condicionamento</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-salvar">Finalizar Cadastro</button>
        </form>

        <div class="auth-link">
            Já possui uma conta? <a href="login.php">Faça login aqui</a>
        </div>
    </div>

</body>
</html>
