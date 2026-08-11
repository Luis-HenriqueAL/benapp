-- Script SQL de inicializacao do PostgreSQL (Multi-tenant)
-- Garantia da chave celula_id em todas as tabelas

CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    celula_id INT NOT NULL DEFAULT 1,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    perfil VARCHAR(50) NOT NULL DEFAULT 'MEMBRO',
    status VARCHAR(20) NOT NULL DEFAULT 'ativo'
);

CREATE TABLE IF NOT EXISTS liturgias (
    id SERIAL PRIMARY KEY,
    celula_id INT NOT NULL,
    data_liturgia DATE NOT NULL
);

CREATE TABLE IF NOT EXISTS momentos_liturgia (
    id SERIAL PRIMARY KEY,
    celula_id INT NOT NULL,
    liturgia_id INT NOT NULL REFERENCES liturgias(id) ON DELETE CASCADE,
    tipo VARCHAR(100) NOT NULL,
    detalhes TEXT
);

CREATE TABLE IF NOT EXISTS escalas (
    id SERIAL PRIMARY KEY,
    celula_id INT NOT NULL,
    liturgia_id INT NOT NULL REFERENCES liturgias(id) ON DELETE CASCADE,
    data_escala TIMESTAMP NOT NULL
);

CREATE TABLE IF NOT EXISTS atribuicoes (
    id SERIAL PRIMARY KEY,
    celula_id INT NOT NULL,
    escala_id INT NOT NULL REFERENCES escalas(id) ON DELETE CASCADE,
    usuario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    funcao VARCHAR(100) NOT NULL,
    CONSTRAINT sem_conflito_escala UNIQUE (celula_id, escala_id, usuario_id)
);

-- Trigger para garantir o momento 'estudo' obrigatório na criação de liturgia
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
