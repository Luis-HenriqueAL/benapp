-- Script SQL oficial de inicializacao do PostgreSQL (Multi-tenant)
-- Todas as tabelas e schemas estao centralizados exclusivamente neste diretorio (db/)

CREATE TABLE IF NOT EXISTS celulas_info (
    id SERIAL PRIMARY KEY,
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
    anfitrioes JSONB DEFAULT '[]'::jsonb,
    lideres JSONB DEFAULT '[]'::jsonb,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    celula_id INT NOT NULL DEFAULT 1,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    perfil VARCHAR(50) NOT NULL DEFAULT 'MEMBRO',
    status VARCHAR(20) NOT NULL DEFAULT 'ativo',
    is_lider_principal BOOLEAN DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS perfis (
    id SERIAL PRIMARY KEY,
    celula_id INT NOT NULL DEFAULT 1,
    nome VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    descricao TEXT,
    is_native BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_perfil_slug UNIQUE (celula_id, slug)
);

CREATE TABLE IF NOT EXISTS perfil_permissoes (
    id SERIAL PRIMARY KEY,
    perfil_id INT NOT NULL REFERENCES perfis(id) ON DELETE CASCADE,
    permissao VARCHAR(100) NOT NULL,
    CONSTRAINT uq_perfil_permissao UNIQUE (perfil_id, permissao)
);

CREATE TABLE IF NOT EXISTS liturgias (
    id SERIAL PRIMARY KEY,
    celula_id INT NOT NULL DEFAULT 1,
    data_culto DATE NOT NULL DEFAULT CURRENT_DATE,
    data_liturgia DATE,
    tema VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS momentos_liturgia (
    id SERIAL PRIMARY KEY,
    celula_id INT NOT NULL DEFAULT 1,
    liturgia_id INT NOT NULL REFERENCES liturgias(id) ON DELETE CASCADE,
    tipo VARCHAR(100) NOT NULL,
    detalhes TEXT,
    is_louvor BOOLEAN DEFAULT FALSE,
    is_palavra BOOLEAN DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS momentos_predefinidos (
    id SERIAL PRIMARY KEY,
    celula_id INT NOT NULL DEFAULT 1,
    titulo VARCHAR(255) NOT NULL,
    ordem INT NOT NULL DEFAULT 0,
    duracao_minutos INT DEFAULT 15,
    obrigatorio BOOLEAN DEFAULT FALSE,
    is_louvor BOOLEAN DEFAULT FALSE,
    is_palavra BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS escalas (
    id SERIAL PRIMARY KEY,
    celula_id INT NOT NULL DEFAULT 1,
    liturgia_id INT NOT NULL REFERENCES liturgias(id) ON DELETE CASCADE,
    usuario_id INT,
    funcao_id VARCHAR(100),
    data_escala TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS presencas (
    id SERIAL PRIMARY KEY,
    celula_id INT NOT NULL DEFAULT 1,
    liturgia_id INT NOT NULL,
    usuario_id INT NULL,
    nome_visitante VARCHAR(255) NULL,
    qtd_visitas INT DEFAULT 1,
    tipo VARCHAR(20) DEFAULT 'membro',
    registrado_por_id INT NULL,
    codigo_acesso VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS liturgia_musicas (
    id SERIAL PRIMARY KEY,
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

-- Triggers
CREATE OR REPLACE FUNCTION trg_liturgia_estudo() RETURNS trigger AS $$
BEGIN
    INSERT INTO momentos_liturgia (celula_id, liturgia_id, tipo, detalhes)
    VALUES (NEW.celula_id, NEW.id, 'estudo', 'Momento de estudo obrigatório');
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS t_liturgia_estudo ON liturgias;
CREATE TRIGGER t_liturgia_estudo
AFTER INSERT ON liturgias
FOR EACH ROW EXECUTE FUNCTION trg_liturgia_estudo();

-- Seeds Iniciais
INSERT INTO usuarios (celula_id, nome, email, senha, perfil, is_lider_principal)
VALUES 
(1, 'Líder Principal', 'admin@celula.com', '$2y$10$qDRL6sLNw6GMxZ05oketB.CNiy.fkpYpTpfXaw96hXRwqvwW3TR/q', 'LIDER', TRUE),
(1, 'Voluntário João', 'joao@celula.com', '$2y$10$qDRL6sLNw6GMxZ05oketB.CNiy.fkpYpTpfXaw96hXRwqvwW3TR/q', 'MEMBRO', FALSE)
ON CONFLICT (email) DO NOTHING;

INSERT INTO momentos_predefinidos (celula_id, titulo, ordem, duracao_minutos, obrigatorio, is_louvor, is_palavra)
VALUES
(1, 'Quebra-Gelo / Recepção', 1, 15, FALSE, FALSE, FALSE),
(1, 'Louvor e Adoração', 2, 20, FALSE, TRUE, FALSE),
(1, 'Estudo / Palavra', 3, 40, TRUE, FALSE, TRUE),
(1, 'Oração e Avisos', 4, 15, FALSE, FALSE, FALSE)
ON CONFLICT DO NOTHING;
