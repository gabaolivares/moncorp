CREATE DATABASE IF NOT EXISTS moncorp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE moncorp;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    genero VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    peso DECIMAL(5,2) NOT NULL,
    altura DECIMAL(3,2) NOT NULL,
    imc DECIMAL(4,2) NOT NULL,
    nivel_atividade VARCHAR(50) NOT NULL,
    objetivo VARCHAR(100) NOT NULL,
    tipo_usuario ENUM('CLIENTE', 'ADMINISTRADOR') DEFAULT 'CLIENTE',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS planos_alimentares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    detalhes_refeicao TEXT,
    nivel_indicado VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS rotinas_treino (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    categoria VARCHAR(50),
    nivel_indicado VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS exercicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_rotina INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    series INT,
    repeticoes INT,
    duracao VARCHAR(50),
    FOREIGN KEY (id_rotina) REFERENCES rotinas_treino(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS avaliacoes_fisicas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    peso DECIMAL(5,2) NOT NULL,
    altura DECIMAL(3,2) NOT NULL,
    imc DECIMAL(4,2) NOT NULL,
    data_avaliacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS historico_imc (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    peso DECIMAL(5,2) NOT NULL,
    altura DECIMAL(4,2) NOT NULL,
    imc DECIMAL(5,2) NOT NULL,
    classificacao VARCHAR(50) NOT NULL,
    data_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS objetivos_saude (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    tipo_objetivo VARCHAR(100),
    descricao TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS metas_diarias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_objetivo INT NOT NULL,
    tarefa VARCHAR(255) NOT NULL,
    status BOOLEAN DEFAULT FALSE,
    data_meta DATE,
    FOREIGN KEY (id_objetivo) REFERENCES objetivos_saude(id) ON DELETE CASCADE
);