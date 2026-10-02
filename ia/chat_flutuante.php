<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Sao_Paulo');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pergunta'])) {

    while (ob_get_level()) ob_end_clean();

    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');

    $origem = $_SERVER['HTTP_ORIGIN'] ?? '';
    $host_atual = ($_SERVER['HTTPS'] ?? 'off') === 'on' ? 'https://' : 'http://';
    $host_atual .= $_SERVER['HTTP_HOST'] ?? '';

    if ($origem && $origem !== $host_atual) {
        http_response_code(403);
        echo json_encode(["resposta" => "Requisição bloqueada por segurança."]);
        exit;
    }

    $pergunta_do_usuario = trim($_POST['pergunta'] ?? '');
    $eh_discreto = isset($_POST['discreto']) && $_POST['discreto'] === 'true';

    if (mb_strlen($pergunta_do_usuario) > 2000) {
        echo json_encode(["resposta" => "Sua mensagem é muito longa. Tente resumir em até 2000 caracteres."]);
        exit;
    }

    if (empty($pergunta_do_usuario)) {
        echo json_encode(["resposta" => "Por favor, digite alguma coisa."]);
        exit;
    }

    require_once __DIR__ . '/../factory/env.php';
    $chave_groq = env('GROQ_API_KEY');

    if (empty($chave_groq)) {
        echo json_encode(["resposta" => "Erro de configuração: chave da IA não encontrada no servidor."]);
        exit;
    }

    $url = "https://api.groq.com/openai/v1/chat/completions";

    $super_texto = "Você é o assistente virtual oficial do 'MONCORP'. Este é um projeto tecnológico e acadêmico desenvolvido sob infraestrutura Linux (pilha LAMP) pelos pesquisadores Gabriel Olivares, Higor Matheus e Iury Paulo. 
    Seu Objetivo: Auxiliar os usuários tirando dúvidas sobre o site, cálculos de saúde (como IMC), sugestões de treinos, alongamentos e dicas de alimentação.
    Regras de Comportamento:
    1. Tom de voz acolhedor, motivador, claro e objetivo.
    2. Aviso Ético (OBRIGATÓRIO): Ao sugerir dietas ou treinos, inclua um aviso claro de que você é uma IA de suporte tecnológico e não substitui um médico, nutricionista ou profissional de Educação Física.
    3. Vocabulário: Nunca utilize o termo 'biometria'. Utilize 'medidas corporais' ou 'avaliação física'.
    4. Foco exclusivo em saúde, bem-estar e tecnologia do sistema MonCorp.";

    if (!isset($_SESSION['memoria_chat_moncorp']) || !is_array($_SESSION['memoria_chat_moncorp'])) {
        $_SESSION['memoria_chat_moncorp'] = [];
    }

    $mensagens_api = [
        ["role" => "system", "content" => $super_texto]
    ];

    if (!$eh_discreto) {
        $memoria = $_SESSION['memoria_chat_moncorp'];
        if (count($memoria) > 10) {
            $memoria = array_slice($memoria, -10);
        }
        foreach ($memoria as $msg) {
            if (!empty($msg['content'])) {
                $mensagens_api[] = ["role" => $msg['role'], "content" => $msg['content']];
            }
        }
    }

    $mensagens_api[] = ["role" => "user", "content" => $pergunta_do_usuario];

    $dados = [
        "model" => "openai/gpt-oss-20b",
        "messages" => $mensagens_api,
        "temperature" => 0.7
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $chave_groq
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);

    $resultado = curl_exec($ch);
    $erro_curl = curl_error($ch);
    curl_close($ch);

    if ($erro_curl || empty($resultado)) {
        echo json_encode(["resposta" => "Desculpe, nossos servidores estão com instabilidade no momento. Tente novamente mais tarde."]);
        exit;
    }

    $retorno_json = json_decode($resultado, true);

    if (isset($retorno_json['choices'][0]['message']['content'])) {
        $resposta = $retorno_json['choices'][0]['message']['content'];

        if (!$eh_discreto) {
            $_SESSION['memoria_chat_moncorp'][] = ["role" => "user", "content" => $pergunta_do_usuario];
            $_SESSION['memoria_chat_moncorp'][] = ["role" => "assistant", "content" => $resposta];

            if (count($_SESSION['memoria_chat_moncorp']) > 10) {
                $_SESSION['memoria_chat_moncorp'] = array_slice($_SESSION['memoria_chat_moncorp'], -10);
            }
        }

        echo json_encode(["resposta" => $resposta]);
    } else {
        $erro_groq = $retorno_json['error']['message'] ?? "Erro desconhecido";
        error_log("Erro Groq: " . $erro_groq);
        echo json_encode(["resposta" => "Não foi possível obter uma resposta agora. Tente novamente."]);
    }
    exit;
}
?>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.6/dist/purify.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    :root {
        --mc-primary: #0fb9b1;
        --mc-bg-dark: #1e272e;
        --mc-bg-panel: rgba(44, 62, 80, 0.95);
        --mc-text-light: #f5f6fa;
        --mc-text-muted: #a4b0be;
        --mc-border: rgba(255, 255, 255, 0.08);
    }

    .chat-btn {
        position: fixed; bottom: 25px; right: 25px;
        background: linear-gradient(135deg, #0fb9b1, #0c948e);
        color: white; border: none; border-radius: 50px;
        width: 65px; height: 65px; display: flex; align-items: center; justify-content: center;
        cursor: pointer; box-shadow: 0 8px 24px rgba(15, 185, 177, 0.4); z-index: 9999;
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .chat-btn:hover { transform: scale(1.1) rotate(5deg); }
    .chat-btn svg { width: 30px; height: 30px; fill: currentColor; }

    .chat-window {
        position: fixed; bottom: 105px; right: 25px;
        width: 380px; max-width: calc(100vw - 50px);
        background: var(--mc-bg-panel); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
        border-radius: 18px; box-shadow: 0 16px 40px rgba(0,0,0,0.4); border: 1px solid var(--mc-border);
        display: flex; flex-direction: column; z-index: 9999; overflow: hidden;
        font-family: 'Inter', sans-serif;
        opacity: 0; transform: translateY(30px); pointer-events: none;
        transition: opacity 0.4s ease, transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    .chat-window.active { opacity: 1; transform: translateY(0); pointer-events: auto; }

    .chat-header {
        background: rgba(30, 39, 46, 0.7); padding: 18px 20px;
        display: flex; align-items: center; gap: 12px; border-bottom: 1px solid var(--mc-border);
    }
    .header-avatar {
        width: 40px; height: 40px; background: var(--mc-primary); border-radius: 50%;
        display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(15, 185, 177, 0.3);
    }
    .header-avatar svg { width: 22px; height: 22px; fill: #fff; }
    .header-info { display: flex; flex-direction: column; flex-grow: 1; }
    .header-title { color: var(--mc-text-light); font-weight: 600; font-size: 15px; }
    .header-status { color: var(--mc-primary); font-size: 12px; display: flex; align-items: center; gap: 5px; font-weight: 500;}
    .status-dot { width: 8px; height: 8px; background: var(--mc-primary); border-radius: 50%; box-shadow: 0 0 8px var(--mc-primary);}

    /* Botão X para fechar o chat */
    .chat-close-btn {
        background: rgba(255, 255, 255, 0.08);
        border: none;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--mc-text-muted);
        transition: all 0.25s ease;
        flex-shrink: 0;
    }
    .chat-close-btn:hover {
        background: rgba(255, 71, 87, 0.9);
        color: #fff;
        transform: rotate(90deg) scale(1.05);
    }
    .chat-close-btn svg {
        width: 16px;
        height: 16px;
        stroke: currentColor;
        stroke-width: 2.5;
        stroke-linecap: round;
    }

    .chat-history {
        padding: 20px; height: 350px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; scroll-behavior: smooth;
    }
    .chat-history::-webkit-scrollbar { width: 6px; }
    .chat-history::-webkit-scrollbar-track { background: transparent; }
    .chat-history::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }

    @media (max-width: 480px) {
        .chat-btn { bottom: 15px; right: 15px; width: 56px; height: 56px; }
        .chat-window {
            bottom: 0; right: 0; left: 0;
            width: 100%; max-width: 100%;
            border-radius: 18px 18px 0 0;
            max-height: 80vh;
        }
        .chat-history { height: 50vh; padding: 15px; }
    }

    .msg-usuario, .msg-ia {
        padding: 12px 16px; font-size: 14px; line-height: 1.5; max-width: 85%; word-wrap: break-word; animation: fadeIn 0.3s ease-out;
    }
    .msg-usuario {
        background: #34495e; color: var(--mc-text-light); border-radius: 16px 16px 2px 16px; align-self: flex-end;
    }
    .msg-ia {
        background: rgba(15, 185, 177, 0.15); color: #d1d8e0; border: 1px solid rgba(15, 185, 177, 0.3);
        border-radius: 16px 16px 16px 2px; align-self: flex-start;
    }
    .msg-ia strong { color: var(--mc-primary); }

    .chat-input-area {
        display: flex; padding: 15px; background: rgba(30, 39, 46, 0.8); border-top: 1px solid var(--mc-border); gap: 10px;
    }
    .chat-input-area input {
        flex-grow: 1; padding: 12px 16px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1);
        background: rgba(255,255,255,0.05); color: var(--mc-text-light); font-family: 'Inter', sans-serif; font-size: 14px; outline: none;
        transition: border 0.3s, background 0.3s;
    }
    .chat-input-area input:focus { border-color: var(--mc-primary); background: rgba(255,255,255,0.08); }
    .chat-input-area input::placeholder { color: var(--mc-text-muted); }

    .btn-enviar {
        background: var(--mc-primary); color: white; border: none; width: 44px; height: 44px;
        border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: transform 0.2s;
    }
    .btn-enviar:hover { background: #0c948e; transform: scale(1.05); }
    .btn-enviar svg { width: 18px; height: 18px; fill: white; margin-left: -2px; }

    .typing-indicator {
        display: none; align-self: flex-start; background: rgba(15, 185, 177, 0.1);
        padding: 12px 16px; border-radius: 16px 16px 16px 2px; gap: 4px; align-items: center;
    }
    .typing-dot {
        width: 6px; height: 6px; background: var(--mc-primary); border-radius: 50%; animation: typing 1.4s infinite ease-in-out;
    }
    .typing-dot:nth-child(1) { animation-delay: 0s; }
    .typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .typing-dot:nth-child(3) { animation-delay: 0.4s; }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes typing { 0%, 100% { transform: translateY(0); opacity: 0.4; } 50% { transform: translateY(-4px); opacity: 1; } }
</style>

<button class="chat-btn" onclick="toggleChat()">
    <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.2L4 17.2V4h16v12z"/></svg>
</button>

<div class="chat-window" id="janelaChat">
    <div class="chat-header">
        <div class="header-avatar">
            <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 9c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm4.12 6.5H7.88C7.45 18.5 7.08 17.89 7 17.15 7.18 15.35 10.3 14 12 14s4.82 1.35 5 3.15c-.08.74-.45 1.35-.88 1.35z"/></svg>
        </div>
        <div class="header-info">
            <span class="header-title">IA MonCorp</span>
            <span class="header-status"><div class="status-dot"></div> Online</span>
        </div>

        <button class="chat-close-btn" onclick="toggleChat()" aria-label="Fechar chat" title="Fechar chat">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M6 6L18 18M18 6L6 18" />
            </svg>
        </button>
    </div>

    <div class="chat-history" id="historicoChat"></div>

    <div class="typing-indicator" id="loadingIA">
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
    </div>

    <div class="chat-input-area">
        <input type="text" id="campoPergunta" placeholder="Escreva sua mensagem..." onkeypress="if(event.key === 'Enter') enviarPergunta()">
        <button class="btn-enviar" onclick="enviarPergunta()">
            <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
        </button>
    </div>
</div>

<script>
    function toggleChat() {
        const chat = document.getElementById('janelaChat');
        chat.classList.toggle('active');
        if (chat.classList.contains('active')) {
            document.getElementById('campoPergunta').focus();
        }
    }

    function processarMensagemParaIA(pergunta, mostrarNoHistorico = true) {
        const historico = document.getElementById('historicoChat');
        const loading = document.getElementById('loadingIA');

        if (pergunta === '') return;

        if (mostrarNoHistorico) {
            const div = document.createElement('div');
            div.className = 'msg-usuario';
            div.textContent = pergunta;
            historico.appendChild(div);
        }

        loading.style.display = 'flex';
        historico.scrollTop = historico.scrollHeight;

        const formData = new FormData();
        formData.append('pergunta', pergunta);

        fetch('ia/chat_flutuante.php', {
            method: 'POST',
            body: formData
        })
        .then(async response => {
            const text = await response.text();
            try {
                return JSON.parse(text);
            } catch (error) {
                console.error("Erro na conversão JSON. Resposta bruta:", text);
                throw new Error("Formato de resposta inválido.");
            }
        })
        .then(data => {
            loading.style.display = 'none';
            const respostaIA = data.resposta || "Erro: Resposta vazia.";

            const htmlBruto = marked.parse(respostaIA);
            const htmlSeguro = DOMPurify.sanitize(htmlBruto, {
                ALLOWED_TAGS: ['p','br','strong','em','ul','ol','li','h1','h2','h3','h4','h5','h6','code','pre','blockquote','table','thead','tbody','tr','th','td','a','hr'],
                ALLOWED_ATTR: ['href','title','target','rel']
            });

            const divIA = document.createElement('div');
            divIA.className = 'msg-ia';
            divIA.innerHTML = htmlSeguro;
            historico.appendChild(divIA);

            historico.scrollTop = historico.scrollHeight;
        })
        .catch(error => {
            console.error("Erro:", error);
            loading.style.display = 'none';
            const divErro = document.createElement('div');
            divErro.className = 'msg-ia';
            divErro.textContent = 'Desculpe, falha ao conectar com a IA do MonCorp.';
            historico.appendChild(divErro);
            historico.scrollTop = historico.scrollHeight;
        });
    }

    function enviarPergunta() {
        const campo = document.getElementById('campoPergunta');
        const pergunta = campo.value.trim();
        if(pergunta !== "") {
            processarMensagemParaIA(pergunta, true);
            campo.value = '';
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        const historico = document.getElementById('historicoChat');
        const div = document.createElement('div');
        div.className = 'msg-ia';
        const nome = <?= json_encode($_SESSION['usuario_nome'] ?? 'Visitante') ?>;
        div.textContent = `Olá, ${nome}! Sou o assistente de IA do MonCorp. Como posso te auxiliar nos seus treinos ou nutrição hoje?`;
        historico.appendChild(div);
    });
</script>
