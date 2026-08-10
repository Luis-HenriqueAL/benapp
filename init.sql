CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    celula_id INT NOT NULL,
    nome VARCHAR(255) NOT NULL
);

CREATE TABLE liturgias (
    id SERIAL PRIMARY KEY,
    celula_id INT NOT NULL,
    data_liturgia DATE NOT NULL
);

CREATE TABLE momentos_liturgia (
    id SERIAL PRIMARY KEY,
    celula_id INT NOT NULL,
    liturgia_id INT NOT NULL REFERENCES liturgias(id) ON DELETE CASCADE,
    tipo VARCHAR(100) NOT NULL,
    detalhes TEXT
);

CREATE TABLE escalas (
    id SERIAL PRIMARY KEY,
    celula_id INT NOT NULL,
    liturgia_id INT NOT NULL REFERENCES liturgias(id) ON DELETE CASCADE,
    data_escala TIMESTAMP NOT NULL
);

CREATE TABLE atribuicoes (
    id SERIAL PRIMARY KEY,
    celula_id INT NOT NULL,
    escala_id INT NOT NULL REFERENCES escalas(id) ON DELETE CASCADE,
    usuario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    funcao VARCHAR(100) NOT NULL,
    CONSTRAINT sem_conflito_escala UNIQUE (celula_id, escala_id, usuario_id)
);

CREATE FUNCTION trg_liturgia_estudo() RETURNS trigger AS $$
BEGIN
    INSERT INTO momentos_liturgia (celula_id, liturgia_id, tipo, detalhes)
    VALUES (NEW.celula_id, NEW.id, 'estudo', 'Momento de estudo obrigatório');
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER t_liturgia_estudo
AFTER INSERT ON liturgias
FOR EACH ROW EXECUTE FUNCTION trg_liturgia_estudo();
