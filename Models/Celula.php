<?php

namespace Models;

use Config\Database;
use PDO;

/**
 * Class Celula
 * Model responsável pela persistência e manipulação dos dados cadastrais e de localização das células (multi-tenant).
 */
class Celula {
    /** @var PDO Conexão PDO com o banco de dados. */
    private $conn;

    /** @var string Nome da tabela no banco de dados. */
    private $table_name = "celulas_info";

    /**
     * Construtor da classe Celula.
     */
    public function __construct() {
        $this->conn = Database::getConnection();
    }

    /**
     * Busca as informações cadastrais de uma célula por seu identificador tenant.
     *
     * @param int $celula_id Identificador único da célula.
     * @return array|false Registro da célula ou false se não encontrado.
     */
    public function findByCelulaId($celula_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE celula_id = :celula_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celula_id', $celula_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $result['nome_celula'] = $result['nome'] ?? '';
            $result['anfitrioes'] = is_string($result['anfitrioes'] ?? null) 
                ? json_decode($result['anfitrioes'], true) 
                : ($result['anfitrioes'] ?? []);
            $result['lideres'] = is_string($result['lideres'] ?? null) 
                ? json_decode($result['lideres'], true) 
                : ($result['lideres'] ?? []);
        }

        return $result;
    }

    /**
     * Salva ou atualiza as informações da célula garantindo o isolamento multi-tenant por celula_id.
     *
     * @param int $celula_id Identificador único da célula.
     * @param array $data Dados da célula.
     * @return bool Retorna verdadeiro em caso de sucesso.
     */
    public function save($celula_id, array $data) {
        return $this->saveOrUpdate($celula_id, $data);
    }

    /**
     * Salva ou atualiza as informações da célula garantindo o isolamento multi-tenant por celula_id.
     *
     * @param int $celula_id Identificador único da célula.
     * @param array $data Dados contendo nome/nome_celula, dia_semana, horario, cep, logradouro, numero, complemento, bairro, cidade, estado, anfitrioes (JSONB array), lideres (JSONB array).
     * @return bool Retorna verdadeiro em caso de sucesso.
     */
    public function saveOrUpdate($celula_id, array $data) {
        $existing = $this->findByCelulaId($celula_id);

        if ($existing) {
            $query = "UPDATE " . $this->table_name . " SET 
                        nome = :nome,
                        dia_semana = :dia_semana,
                        horario = :horario,
                        cep = :cep,
                        logradouro = :logradouro,
                        numero = :numero,
                        complemento = :complemento,
                        bairro = :bairro,
                        cidade = :cidade,
                        estado = :estado,
                        anfitrioes = :anfitrioes,
                        lideres = :lideres
                      WHERE celula_id = :celula_id";
        } else {
            $query = "INSERT INTO " . $this->table_name . " 
                        (celula_id, nome, dia_semana, horario, cep, logradouro, numero, complemento, bairro, cidade, estado, anfitrioes, lideres)
                      VALUES 
                        (:celula_id, :nome, :dia_semana, :horario, :cep, :logradouro, :numero, :complemento, :bairro, :cidade, :estado, :anfitrioes, :lideres)";
        }

        $stmt = $this->conn->prepare($query);

        $nome = $data['nome_celula'] ?? $data['nome'] ?? 'Célula sem nome';
        $dia_semana = $data['dia_semana'] ?? null;
        $horario = !empty($data['horario']) ? $data['horario'] : null;
        $cep = $data['cep'] ?? null;
        $logradouro = $data['logradouro'] ?? null;
        $numero = $data['numero'] ?? null;
        $complemento = $data['complemento'] ?? null;
        $bairro = $data['bairro'] ?? null;
        $cidade = $data['cidade'] ?? null;
        $estado = $data['estado'] ?? null;
        
        $anfitrioesJson = json_encode($data['anfitrioes'] ?? []);
        $lideresJson = json_encode($data['lideres'] ?? []);

        $stmt->bindParam(':celula_id', $celula_id);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':dia_semana', $dia_semana);
        $stmt->bindParam(':horario', $horario);
        $stmt->bindParam(':cep', $cep);
        $stmt->bindParam(':logradouro', $logradouro);
        $stmt->bindParam(':numero', $numero);
        $stmt->bindParam(':complemento', $complemento);
        $stmt->bindParam(':bairro', $bairro);
        $stmt->bindParam(':cidade', $cidade);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':anfitrioes', $anfitrioesJson);
        $stmt->bindParam(':lideres', $lideresJson);

        return $stmt->execute();
    }
}

