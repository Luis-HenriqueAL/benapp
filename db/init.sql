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

-- Usuários iniciais (senha: senha123)
-- Hash gerado por password_hash('senha123', PASSWORD_BCRYPT)
INSERT INTO usuarios (celula_id, nome, email, senha, perfil)
VALUES 
(1, 'Líder Principal', 'admin@celula.com', '$2y$10$w8T06sLq7vWwJmJ5c4wMLeM9wK0y1A8Cq1g/WqH.gWf2gN8kYtBie', 'LIDER'),
(1, 'Voluntário João', 'joao@celula.com', '$2y$10$w8T06sLq7vWwJmJ5c4wMLeM9wK0y1A8Cq1g/WqH.gWf2gN8kYtBie', 'MEMBRO')
ON CONFLICT (email) DO NOTHING;
