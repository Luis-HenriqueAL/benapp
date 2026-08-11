-- Script SQL oficial de inicializacao do SQLite (Desenvolvimento Local XAMPP/Windows)

CREATE TABLE IF NOT EXISTS celulas_info (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    celula_id INT NOT NULL UNIQUE,
    nome VARCHAR(255) NOT NULL,
    dia_semana VARCHAR(50),
    horario TIME,
    cep VARCHAR(10),
    logradouro VARCHAR(255),
    numero VARCHAR(20),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    anfitrioes TEXT DEFAULT '[]',
    lideres TEXT DEFAULT '[]',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    celula_id INT NOT NULL DEFAULT 1,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    perfil VARCHAR(50) NOT NULL DEFAULT 'MEMBRO',
    status VARCHAR(20) NOT NULL DEFAULT 'ativo'
);

CREATE TABLE IF NOT EXISTS perfis (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    celula_id INT NOT NULL DEFAULT 1,
    nome VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    descricao TEXT,
    is_native BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (celula_id, slug)
);

CREATE TABLE IF NOT EXISTS perfil_permissoes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    perfil_id INT NOT NULL REFERENCES perfis(id) ON DELETE CASCADE,
    permissao VARCHAR(100) NOT NULL,
    UNIQUE (perfil_id, permissao)
);

CREATE TABLE IF NOT EXISTS liturgias (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    celula_id INT NOT NULL DEFAULT 1,
    data_culto DATE NOT NULL DEFAULT CURRENT_DATE,
    data_liturgia DATE,
    tema VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS momentos_liturgia (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    celula_id INT NOT NULL DEFAULT 1,
    liturgia_id INT NOT NULL REFERENCES liturgias(id) ON DELETE CASCADE,
    tipo VARCHAR(100) NOT NULL,
    detalhes TEXT
);

CREATE TABLE IF NOT EXISTS momentos_predefinidos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    celula_id INT NOT NULL DEFAULT 1,
    titulo VARCHAR(255) NOT NULL,
    ordem INT NOT NULL DEFAULT 0,
    duracao_minutos INT DEFAULT 15,
    obrigatorio BOOLEAN DEFAULT FALSE,
    is_louvor BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS escalas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    celula_id INT NOT NULL DEFAULT 1,
    liturgia_id INT NOT NULL REFERENCES liturgias(id) ON DELETE CASCADE,
    usuario_id INT,
    funcao_id VARCHAR(100),
    data_escala TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS presencas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    celula_id INT NOT NULL DEFAULT 1,
    liturgia_id INT NOT NULL,
    usuario_id INT NULL,
    nome_visitante VARCHAR(255) NULL,
    qtd_visitas INT DEFAULT 1,
    tipo VARCHAR(20) DEFAULT 'membro',
    registrado_por_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS liturgia_musicas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    celula_id INT NOT NULL DEFAULT 1,
    liturgia_id INT NOT NULL REFERENCES liturgias(id) ON DELETE CASCADE,
    momento_titulo VARCHAR(255) NULL,
    titulo VARCHAR(255) NOT NULL,
    artista VARCHAR(255) NULL,
    tom VARCHAR(10) NULL,
    cifraclub_url VARCHAR(500) NULL,
    cifra_texto TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seeds Iniciais
INSERT OR IGNORE INTO usuarios (celula_id, nome, email, senha, perfil)
VALUES (1, 'Líder Principal', 'admin@celula.com', '$2y$10$w8T06sLq7vWwJmJ5c4wMLeM9wK0y1A8Cq1g/WqH.gWf2gN8kYtBie', 'LIDER');
