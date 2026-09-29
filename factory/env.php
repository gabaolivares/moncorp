<?php

if (!function_exists('env')) {

    function carregar_env(string $caminho): void
    {
        static $carregado = false;
        if ($carregado || !file_exists($caminho)) {
            return;
        }

        $conteudo = file_get_contents($caminho);

        // Remove BOM UTF-8 (EF BB BF) que o VS Code às vezes adiciona
        $conteudo = preg_replace('/^\xEF\xBB\xBF/', '', $conteudo);

        // Normaliza quebras de linha (Windows \r\n → \n)
        $conteudo = str_replace(["\r\n", "\r"], "\n", $conteudo);

        $linhas = explode("\n", $conteudo);

        foreach ($linhas as $linha) {
            $linha = trim($linha);

            if ($linha === '' || strpos($linha, '#') === 0) {
                continue;
            }

            if (strpos($linha, '=') === false) {
                continue;
            }

            [$chave, $valor] = explode('=', $linha, 2);
            $chave = trim($chave);
            $valor = trim($valor);

            $valor = trim($valor, "\"'");

            // Só define se a chave for válida (não vazia)
            if ($chave !== '' && !array_key_exists($chave, $_ENV)) {
                $_ENV[$chave] = $valor;
                putenv("$chave=$valor");
            }
        }

        $carregado = true;
    }

    function env(string $chave, $padrao = null)
    {
        carregar_env(__DIR__ . '/../.env.example');

        $valor = getenv($chave);
        if ($valor === false || $valor === '') {
            $valor = $_ENV[$chave] ?? null;
        }

        if ($valor === null || $valor === '') {
            return $padrao;
        }
        return $valor;
    }
}
