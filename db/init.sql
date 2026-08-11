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

-- Célula inicial de exemplo
INSERT INTO celulas_info (celula_id, nome, dia_semana, horario, cep, logradouro, numero, complemento, bairro, cidade, estado, anfitrioes, lideres)
VALUES
(1, 'Célula Boas Novas', 'Quarta-feira', '19:30', '01001-000', 'Praça da Sé', '100', 'Apto 12', 'Sé', 'São Paulo', 'SP', '[{"nome": "Carlos Anfitrião", "telefone1": "(11) 98888-7777", "telefone2": ""}]'::jsonb, '[{"nome": "Líder Principal", "telefones": ["(11) 99999-8888"]}]'::jsonb)
ON CONFLICT (celula_id) DO NOTHING;

-- Tabela de Parametrizacao de Liturgia (Momentos Predefinidos por Célula)
CREATE TABLE IF NOT EXISTS momentos_predefinidos (
    id SERIAL PRIMARY KEY,
    celula_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    ordem INT NOT NULL DEFAULT 0,
    duracao_minutos INT DEFAULT 15,
    obrigatorio BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Momentos predefinidos iniciais (Template da Célula)
INSERT INTO momentos_predefinidos (celula_id, titulo, ordem, duracao_minutos, obrigatorio)
VALUES
(1, 'Quebra-Gelo / Recepção', 1, 15, FALSE),
(1, 'Louvor e Adoração', 2, 20, FALSE),
(1, 'Estudo / Palavra', 3, 40, TRUE),
(1, 'Oração e Avisos', 4, 15, FALSE);


