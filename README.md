# MonCorp — Sistema de Monitoramento Corporal com IA

Sistema web em **PHP puro + MySQL** para monitoramento de saúde, com calculadora de IMC, histórico de avaliações físicas, biblioteca de treinos e assistente nutricional com Inteligência Artificial integrada à **API Groq**.

🔗 **Deploy:** [gabai.fun](https://gabai.fun)

---

# Índice

- [Funcionalidades](#-funcionalidades)
- [Tecnologias](#-tecnologias)
- [Segurança aplicada](#-segurança-aplicada)
- [Estrutura do projeto](#-estrutura-do-projeto)
- [Como rodar localmente](#-como-rodar-localmente)
- [Autor](#-autor)
- [Licença](#-licença)

---

# Funcionalidades

- **Calculadora de IMC** com classificação automática (abaixo do peso, normal, sobrepeso, obesidade)
- **Histórico de avaliações físicas** com data, peso, altura e IMC
- **Assistente nutricional com IA** — chat flutuante integrado à API Groq (modelo LLM)
- **Biblioteca de treinos** — academia e calistenia, com **cronômetro integrado**
- **Sistema de login e cadastro** com autenticação segura
- **Dashboard personalizado** com perfil do usuário
- **Configurações de conta** — edição de dados e preferências

---

# Tecnologias

| Camada | Tecnologia |
|---|---|
| **Back-end** | PHP 8.3 (puro, sem frameworks) |
| **Banco de dados** | MySQL (mysqli + prepared statements) |
| **Front-end** | HTML5, CSS3, JavaScript (Fetch API, DOM) |
| **IA** | API Groq (modelos LLM) |
| **Infraestrutura** | Hostinger, cPanel, HTTPS, Cloudflare |

---

# Segurança aplicada

Este projeto foi desenvolvido com foco em **segurança de aplicações web**, aplicando as seguintes boas práticas:

- ✅ **Variáveis de ambiente** (`.env`) — nenhuma credencial hardcoded
- ✅ **Prepared statements** — proteção contra SQL Injection
- ✅ **`htmlspecialchars()`** em todas as saídas — proteção contra XSS
- ✅ **DOMPurify** na renderização de respostas da IA — proteção extra contra XSS
- ✅ **Proteção CSRF leve** em requisições sensíveis
- ✅ **`session_regenerate_id()`** no login e cadastro — previne session fixation
- ✅ **`mysqli_report()` + `try/catch`** — tratamento robusto de erros de banco
- ✅ **Mensagens genéricas no login** — previne enumeração de usuários
- ✅ **Cookies de sessão** com `HttpOnly`, `Secure` e `SameSite=Lax`
- ✅ **`.htaccess`** bloqueando acesso a arquivos sensíveis
- ✅ **SSL verificado** em chamadas cURL externas

---

# Estrutura do projeto

```
moncorp/
├── index.php               # Dashboard + Calculadora de IMC
├── avaliacao.php           # Histórico de avaliações físicas
├── treinos.php             # Biblioteca de treinos + cronômetro
├── nutricao.php            # Plano nutricional com IA
├── configuracoes.php       # Configurações do usuário
├── banco/
│   └── banco.sql           # Schema do banco de dados
├── css/
│   └── estilo.css          # Estilos globais
├── factory/
│   ├── conexao.php         # Conexão MySQL
│   ├── env.php             # Leitor do .env
│   ├── seguranca.php       # Helpers de segurança
│   ├── logout.php          # Logout seguro
│   └── .htaccess           # Bloqueios
├── ia/
│   ├── chat_flutuante.php  # Chat com IA + endpoint da API
│   ├── env.php             # Leitor do .env
│   └── .htaccess           # Bloqueios
└── model/
    ├── login.php
    ├── cadastro.php
    ├── processa_login.php
    └── processa_cadastro.php
```

---

# Como rodar localmente

### Pré-requisitos
- PHP 8.3 ou superior
- MySQL 8.0 ou superior
- Servidor web (Apache, Nginx, ou `php -S localhost:8000`)

### Passo a passo

1. **Clone o repositório**
   ```bash
   git clone https://github.com/seu-usuario/moncorp.git
   cd moncorp
   ```

2. **Crie o arquivo `.env`** na raiz com:
   ```
   DB_HOST=localhost
   DB_USER=seu_usuario
   DB_PASS=sua_senha
   DB_NAME=moncorp
   GROQ_API_KEY=sua_chave_groq_aqui
   ```

3. **Importe o banco de dados**
   ```bash
   mysql -u root -p < banco/banco.sql
   ```

4. **Inicie o servidor**
   ```bash
   php -S localhost:8000
   ```

5. **Acesse no navegador**
   ```
   http://localhost:8000
   ```

---

# Screenshots

> Em breve — prints das telas principais do sistema.

---

# Autor

**Gabriel Olivares**

- 🎓 Estudante de Técnico em Informática — IFSP Campus Guarulhos
- 💼 [LinkedIn](https://linkedin.com/in/gabriel-olivares-ti)
- 📧 olivaresgaba@gmail.com
- 🐙 [GitHub](https://github.com/gabaolivares)

---

# Licença

Este projeto está sob a licença **MIT** — consulte o arquivo [LICENSE](LICENSE) para mais detalhes.

---

⭐ Se este projeto foi útil para você, considere dar uma estrela!
