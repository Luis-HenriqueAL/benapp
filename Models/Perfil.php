<?php

namespace Models;

use Config\Database;
use PDO;

/**
 * Class Perfil
 * Model responsável pelo gerenciamento de perfis customizados e suas permissões por célula.
 */
class Perfil {
    /** @var PDO Conexão PDO com o banco de dados. */
    private $conn;

    /** @var string Nome da tabela de perfis. */
    private $table_name = "perfis";

    /**
     * Lista de todas as permissões disponíveis no sistema, organizadas por módulo.
     * Chave: identificador único da permissão.
     * Valor: rótulo legível para exibição na UI.
     *
     * @var array<string,string>
     */
    public static $permissoesDisponiveis = [
        'escala.view'         => 'Ver Escalas e Cultos',
        'escala.create'       => 'Criar Escalas',
        'escala.delete'       => 'Excluir Eventos',
        'usuarios.view'       => 'Ver Membros da Equipe',
        'usuarios.manage'     => 'Adicionar / Editar Membros',
        'celula.edit'         => 'Editar Informações da Célula',
        'liturgia.momentos'   => 'Gerenciar Momentos da Liturgia',
        'perfil.manage'       => 'Gerenciar Perfis e Permissões',
    ];

    /**
     * Construtor da classe Perfil.
     */
    public function __construct() {
        $this->conn = Database::getConnection();
        $this->ensureSchema();
    }

    /**
     * Garante a criação das tabelas de perfis e permissões no banco de dados (idempotente).
     *
     * @return void
     */
    private function ensureSchema() {
        try {
            $driver = $this->conn->getAttribute(\PDO::ATTR_DRIVER_NAME);
            if ($driver === 'sqlite') {
                $this->conn->exec("
                    CREATE TABLE IF NOT EXISTS perfis (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        celula_id INT NOT NULL DEFAULT 1,
                        nome VARCHAR(100) NOT NULL,
                        descricao TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    );
                ");
                $this->conn->exec("
                    CREATE TABLE IF NOT EXISTS perfil_permissoes (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        perfil_id INT NOT NULL,
                        chave_permissao VARCHAR(100) NOT NULL,
                        UNIQUE(perfil_id, chave_permissao)
                    );
                ");
            } else {
                $this->conn->exec("
                    CREATE TABLE IF NOT EXISTS perfis (
                        id SERIAL PRIMARY KEY,
                        celula_id INT NOT NULL DEFAULT 1,
                        nome VARCHAR(100) NOT NULL,
                        descricao TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    );
                ");
                $this->conn->exec("
                    CREATE TABLE IF NOT EXISTS perfil_permissoes (
                        id SERIAL PRIMARY KEY,
                        perfil_id INT NOT NULL REFERENCES perfis(id) ON DELETE CASCADE,
                        chave_permissao VARCHAR(100) NOT NULL,
                        CONSTRAINT uq_perfil_permissao UNIQUE (perfil_id, chave_permissao)
                    );
                ");
            }
        } catch (\PDOException $e) {
            // Ignora se tabelas já existirem
        }
    }

    /**
     * Lista todos os perfis customizados da célula, incluindo suas permissões.
     *
     * @param int $celula_id Identificador do tenant.
     * @return array Lista de perfis com a chave 'permissoes' como array.
     */
    public function findAll($celula_id) {
        $query = "SELECT * FROM {$this->table_name} WHERE celula_id = :celula_id ORDER BY nome ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celula_id', $celula_id, PDO::PARAM_INT);
        $stmt->execute();
        $perfis = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($perfis as &$perfil) {
            $perfil['permissoes'] = $this->getPermissoesByPerfilId($perfil['id']);
        }

        return $perfis;
    }

    /**
     * Busca um perfil por ID garantindo isolamento de tenant.
     *
     * @param int $celula_id Identificador do tenant.
     * @param int $id Identificador do perfil.
     * @return array|false Dados do perfil ou false se não encontrado.
     */
    public function findById($celula_id, $id) {
        $query = "SELECT * FROM {$this->table_name} WHERE id = :id AND celula_id = :celula_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id, ':celula_id' => $celula_id]);
        $perfil = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($perfil) {
            $perfil['permissoes'] = $this->getPermissoesByPerfilId($perfil['id']);
        }

        return $perfil;
    }

    /**
     * Cria um novo perfil customizado para a célula.
     *
     * @param int $celula_id Identificador do tenant.
     * @param string $nome Nome do perfil.
     * @param string $descricao Descrição do perfil.
     * @param array $permissoes Lista de chaves de permissão a atribuir.
     * @return int|false ID do perfil criado ou false em caso de falha.
     */
    public function create($celula_id, $nome, $descricao, array $permissoes) {
        $query = "INSERT INTO {$this->table_name} (celula_id, nome, descricao) VALUES (:celula_id, :nome, :descricao)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':celula_id' => $celula_id, ':nome' => $nome, ':descricao' => $descricao]);
        $perfilId = (int)$this->conn->lastInsertId();

        if ($perfilId) {
            $this->syncPermissoes($perfilId, $permissoes);
        }

        return $perfilId ?: false;
    }

    /**
     * Atualiza os dados e permissões de um perfil existente.
     *
     * @param int $celula_id Identificador do tenant.
     * @param int $id Identificador do perfil.
     * @param string $nome Novo nome.
     * @param string $descricao Nova descrição.
     * @param array $permissoes Lista de chaves de permissão.
     * @return bool Retorna verdadeiro em caso de sucesso.
     */
    public function update($celula_id, $id, $nome, $descricao, array $permissoes) {
        $query = "UPDATE {$this->table_name} SET nome = :nome, descricao = :descricao WHERE id = :id AND celula_id = :celula_id";
        $stmt = $this->conn->prepare($query);
        $ok = $stmt->execute([':nome' => $nome, ':descricao' => $descricao, ':id' => $id, ':celula_id' => $celula_id]);

        if ($ok) {
            $this->syncPermissoes($id, $permissoes);
        }

        return $ok;
    }

    /**
     * Remove um perfil customizado da célula (e suas permissões via CASCADE).
     *
     * @param int $celula_id Identificador do tenant.
     * @param int $id Identificador do perfil.
     * @return bool Retorna verdadeiro se deletado.
     */
    public function delete($celula_id, $id) {
        $query = "DELETE FROM {$this->table_name} WHERE id = :id AND celula_id = :celula_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id, ':celula_id' => $celula_id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Retorna as permissões atribuídas a um perfil como array de chaves.
     *
     * @param int $perfil_id Identificador do perfil.
     * @return array Lista de chaves de permissão (ex: ['escala.view', 'escala.create']).
     */
    public function getPermissoesByPerfilId($perfil_id) {
        $query = "SELECT chave_permissao FROM perfil_permissoes WHERE perfil_id = :perfil_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':perfil_id' => $perfil_id]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'chave_permissao');
    }

    /**
     * Retorna as permissões de um usuário com base no nome do seu perfil customizado.
     * Retorna todas as permissões disponíveis se o perfil for LIDER.
     *
     * @param int $celula_id Identificador do tenant.
     * @param string $nomePerfil Nome do perfil do usuário (ex: 'LIDER', 'MEMBRO', ou custom).
     * @return array Lista de chaves de permissão ativas para o usuário.
     */
    public function getPermissoesPorNome($celula_id, $nomePerfil) {
        if ($nomePerfil === 'LIDER') {
            return array_keys(self::$permissoesDisponiveis);
        }

        $query = "
            SELECT pp.chave_permissao 
            FROM perfil_permissoes pp
            INNER JOIN {$this->table_name} p ON pp.perfil_id = p.id
            WHERE p.celula_id = :celula_id AND p.nome = :nome
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':celula_id' => $celula_id, ':nome' => $nomePerfil]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'chave_permissao');
    }

    /**
     * Sincroniza as permissões de um perfil, removendo as antigas e inserindo as novas.
     *
     * @param int $perfil_id Identificador do perfil.
     * @param array $permissoes Lista de chaves de permissão a salvar.
     * @return void
     */
    private function syncPermissoes($perfil_id, array $permissoes) {
        $this->conn->prepare("DELETE FROM perfil_permissoes WHERE perfil_id = :perfil_id")
            ->execute([':perfil_id' => $perfil_id]);

        $validas = array_keys(self::$permissoesDisponiveis);
        $stmt = $this->conn->prepare("INSERT INTO perfil_permissoes (perfil_id, chave_permissao) VALUES (:perfil_id, :chave)");

        foreach ($permissoes as $chave) {
            if (in_array($chave, $validas, true)) {
                $stmt->execute([':perfil_id' => $perfil_id, ':chave' => $chave]);
            }
        }
    }
}
