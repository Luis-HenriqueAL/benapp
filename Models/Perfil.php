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
    }

    /**
     * Garante que o perfil MEMBRO exista para a célula no banco de dados com permissão padrão escala.view.
     *
     * @param int $celula_id Identificador da célula.
     * @return int ID do perfil MEMBRO.
     */
    public function ensureMembroPerfil($celula_id) {
        $stmt = $this->conn->prepare("SELECT id FROM {$this->table_name} WHERE celula_id = :celula_id AND UPPER(nome) = 'MEMBRO' LIMIT 1");
        $stmt->execute([':celula_id' => $celula_id]);
        $id = $stmt->fetchColumn();

        if (!$id) {
            $stmtInsert = $this->conn->prepare("INSERT INTO {$this->table_name} (celula_id, nome, slug, descricao) VALUES (:celula_id, 'MEMBRO', 'membro', 'Perfil nativo de membro da célula')");
            $stmtInsert->execute([':celula_id' => $celula_id]);
            $id = $this->conn->lastInsertId();
            $this->syncPermissoes($id, ['escala.view']);
        }
        return $id;
    }

    /**
     * Lista todos os perfis customizados da célula, incluindo suas permissões.
     *
     * @param int $celula_id Identificador do tenant.
     * @return array Lista de perfis com a chave 'permissoes' como array.
     */
    public function findAll($celula_id) {
        $this->ensureMembroPerfil($celula_id);

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
     * @param array $permissoes Lista de permissões ativas.
     * @return int ID do perfil criado.
     */
    public function create($celula_id, $nome, $descricao, array $permissoes = []) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nome))) ?: 'perfil-' . time();
        $query = "INSERT INTO {$this->table_name} (celula_id, nome, slug, descricao) VALUES (:celula_id, :nome, :slug, :descricao)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':celula_id' => $celula_id,
            ':nome'      => $nome,
            ':slug'      => $slug,
            ':descricao' => $descricao,
        ]);

        $perfil_id = $this->conn->lastInsertId();
        $this->syncPermissoes($perfil_id, $permissoes);

        return $perfil_id;
    }

    /**
     * Atualiza um perfil existente e suas permissões associadas.
     *
     * @param int $celula_id Identificador do tenant.
     * @param int $id Identificador do perfil.
     * @param string $nome Nome do perfil.
     * @param string $descricao Descrição do perfil.
     * @param array $permissoes Lista de permissões ativas.
     * @return bool Retorna verdadeiro se atualizado.
     */
    public function update($celula_id, $id, $nome, $descricao, array $permissoes = []) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nome))) ?: 'perfil-' . time();
        $query = "UPDATE {$this->table_name} SET nome = :nome, slug = :slug, descricao = :descricao WHERE id = :id AND celula_id = :celula_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':id'        => $id,
            ':celula_id' => $celula_id,
            ':nome'      => $nome,
            ':slug'      => $slug,
            ':descricao' => $descricao,
        ]);

        $this->syncPermissoes($id, $permissoes);
        return true;
    }

    /**
     * Remove um perfil customizado da célula (e suas permissões via CASCADE).
     *
     * @param int $celula_id Identificador do tenant.
     * @param int $id Identificador do perfil.
     * @throws \Exception Se for uma tentativa de deletar o perfil MEMBRO nativo.
     * @return bool Retorna verdadeiro se deletado.
     */
    public function delete($celula_id, $id) {
        $perfil = $this->findById($celula_id, $id);
        if ($perfil && strtoupper($perfil['nome']) === 'MEMBRO') {
            throw new \Exception("O perfil Membro é nativo do sistema e não pode ser excluído, apenas editado.");
        }

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
        try {
            $query = "SELECT chave_permissao FROM perfil_permissoes WHERE perfil_id = :perfil_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':perfil_id' => $perfil_id]);
            $res = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'chave_permissao');
            if (!empty($res)) return $res;
        } catch (\PDOException $e) {}

        try {
            $query = "SELECT permissao FROM perfil_permissoes WHERE perfil_id = :perfil_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':perfil_id' => $perfil_id]);
            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'permissao');
        } catch (\PDOException $e) {
            return [];
        }
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

        $this->ensureMembroPerfil($celula_id);

        try {
            $query = "
                SELECT pp.chave_permissao 
                FROM perfil_permissoes pp
                INNER JOIN {$this->table_name} p ON pp.perfil_id = p.id
                WHERE p.celula_id = :celula_id AND UPPER(p.nome) = UPPER(:nome)
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':celula_id' => $celula_id, ':nome' => $nomePerfil]);
            $perms = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'chave_permissao');
            if (!empty($perms)) return $perms;
        } catch (\PDOException $e) {}

        try {
            $query = "
                SELECT pp.permissao 
                FROM perfil_permissoes pp
                INNER JOIN {$this->table_name} p ON pp.perfil_id = p.id
                WHERE p.celula_id = :celula_id AND UPPER(p.nome) = UPPER(:nome)
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':celula_id' => $celula_id, ':nome' => $nomePerfil]);
            $perms = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'permissao');
            if (!empty($perms)) return $perms;
        } catch (\PDOException $e) {}

        if (strtoupper($nomePerfil) === 'MEMBRO') {
            return ['escala.view'];
        }

        return [];
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

        try {
            $stmt = $this->conn->prepare("INSERT INTO perfil_permissoes (perfil_id, chave_permissao) VALUES (:perfil_id, :chave)");
            foreach ($permissoes as $chave) {
                if (in_array($chave, $validas, true)) {
                    $stmt->execute([':perfil_id' => $perfil_id, ':chave' => $chave]);
                }
            }
        } catch (\PDOException $e) {
            try {
                $stmt = $this->conn->prepare("INSERT INTO perfil_permissoes (perfil_id, permissao) VALUES (:perfil_id, :chave)");
                foreach ($permissoes as $chave) {
                    if (in_array($chave, $validas, true)) {
                        $stmt->execute([':perfil_id' => $perfil_id, ':chave' => $chave]);
                    }
                }
            } catch (\PDOException $ex) {}
        }
    }
}
