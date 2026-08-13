<?php

namespace Models;

use Config\Database;
use PDO;

/**
 * Class LiturgiaMusica
 * Model responsável pela gestão de músicas de louvor e cifras vinculadas às liturgias.
 */
class LiturgiaMusica {
    /** @var PDO Conexão PDO com o banco de dados. */
    private $conn;

    /** @var string Nome da tabela no banco de dados. */
    private $table_name = "liturgia_musicas";

    /**
     * Construtor da classe LiturgiaMusica.
     */
    public function __construct() {
        $this->conn = Database::getConnection();
    }

    /**
     * Adiciona uma música a uma liturgia.
     *
     * @param int $celula_id Identificador da célula (tenant).
     * @param int $liturgia_id Identificador da liturgia/evento.
     * @param string|null $momento_titulo Título do momento de louvor.
     * @param string $titulo Nome da música.
     * @param string|null $artista Nome do artista/banda.
     * @param string|null $tom Tom da música (Ex: G, C, F#m).
     * @param string|null $cifraclub_url Link do Cifra Club.
     * @param string|null $cifra_texto Conteúdo da letra/cifra.
     * @return bool Verdadeiro se inserido com sucesso.
     */
    public function create($celula_id, $liturgia_id, $momento_titulo, $titulo, $artista = null, $tom = null, $cifraclub_url = null, $cifra_texto = null) {
        $query = "INSERT INTO {$this->table_name} 
                  (celula_id, liturgia_id, momento_titulo, titulo, artista, tom, cifraclub_url, cifra_texto) 
                  VALUES (:celula_id, :liturgia_id, :momento_titulo, :titulo, :artista, :tom, :cifraclub_url, :cifra_texto)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':celula_id'      => $celula_id,
            ':liturgia_id'    => $liturgia_id,
            ':momento_titulo' => $momento_titulo,
            ':titulo'         => $titulo,
            ':artista'        => $artista,
            ':tom'            => $tom,
            ':cifraclub_url'  => $cifraclub_url,
            ':cifra_texto'    => $cifra_texto
        ]);
    }

    /**
     * Retorna todas as músicas vinculadas a uma liturgia específica.
     *
     * @param int $celula_id Identificador da célula.
     * @param int $liturgia_id Identificador da liturgia.
     * @return array Lista de músicas cadastradas.
     */
    public function findByLiturgia($celula_id, $liturgia_id) {
        $query = "SELECT * FROM {$this->table_name} WHERE celula_id = :celula_id AND liturgia_id = :liturgia_id ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':celula_id' => $celula_id, ':liturgia_id' => $liturgia_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca os detalhes de uma música pelo ID.
     *
     * @param int $celula_id Identificador da célula.
     * @param int $id ID da música.
     * @return array|false Dados da música.
     */
    public function findById($celula_id, $id) {
        $query = "SELECT * FROM {$this->table_name} WHERE id = :id AND celula_id = :celula_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id, ':celula_id' => $celula_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Remove uma música da liturgia.
     *
     * @param int $celula_id Identificador da célula.
     * @param int $id ID da música.
     * @return bool Verdadeiro se deletado.
     */
    public function delete($celula_id, $id) {
        $query = "DELETE FROM {$this->table_name} WHERE id = :id AND celula_id = :celula_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id, ':celula_id' => $celula_id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Atualiza o cache da cifra, tom e artista de uma música no banco.
     *
     * @param int $celula_id Identificador da célula.
     * @param int $id ID da música.
     * @param string $cifra_texto Texto da cifra extraída.
     * @param string|null $tom Tom da música.
     * @param string|null $artista Nome do artista.
     * @return bool
     */
    public function updateCifraCache($celula_id, $id, $cifra_texto, $tom = null, $artista = null) {
        $query = "UPDATE {$this->table_name} 
                  SET cifra_texto = :cifra_texto, 
                      tom = COALESCE(NULLIF(:tom, ''), tom), 
                      artista = COALESCE(NULLIF(:artista, ''), artista) 
                  WHERE id = :id AND celula_id = :celula_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':cifra_texto' => $cifra_texto,
            ':tom'         => $tom,
            ':artista'     => $artista,
            ':id'          => $id,
            ':celula_id'   => $celula_id
        ]);
    }
}
