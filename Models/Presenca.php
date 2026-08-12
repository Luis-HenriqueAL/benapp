<?php

namespace Models;

use Config\Database;
use PDO;

/**
 * Class Presenca
 * Model responsável pelo registro de presenças de usuários em eventos/liturgias.
 */
class Presenca {
    /** @var PDO Conexão PDO com o banco de dados. */
    private $conn;

    /** @var string Nome da tabela no banco de dados. */
    private $table_name = "presencas";

    /**
     * Construtor da classe Presenca.
     */
    public function __construct() {
        $this->conn = Database::getConnection();
        $this->ensureSchema();
    }

    /**
     * Garante a criação da tabela de presenças (idempotente, suporta PostgreSQL e SQLite).
     *
     * @return void
     */
    private function ensureSchema() {
        try {
            $driver = $this->conn->getAttribute(\PDO::ATTR_DRIVER_NAME);
            if ($driver === 'sqlite') {
                $this->conn->exec("
                    CREATE TABLE IF NOT EXISTS presencas (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        celula_id INT NOT NULL DEFAULT 1,
                        liturgia_id INT NOT NULL,
                        usuario_id INT NULL,
                        nome_visitante VARCHAR(255) NULL,
                        qtd_visitas INT DEFAULT 1,
                        tipo VARCHAR(20) DEFAULT 'membro',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    );
                ");

                // Verifica se usuario_id possui restrição NOT NULL em tabelas pré-existentes
                $stmt = $this->conn->query("PRAGMA table_info(presencas)");
                $cols = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
                foreach ($cols as $col) {
                    if ($col['name'] === 'usuario_id' && $col['notnull'] == 1) {
                        $this->conn->exec("
                            CREATE TABLE presencas_temp (
                                id INTEGER PRIMARY KEY AUTOINCREMENT,
                                celula_id INT NOT NULL DEFAULT 1,
                                liturgia_id INT NOT NULL,
                                usuario_id INT NULL,
                                nome_visitante VARCHAR(255) NULL,
                                qtd_visitas INT DEFAULT 1,
                                tipo VARCHAR(20) DEFAULT 'membro',
                                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                            );
                            INSERT INTO presencas_temp (id, celula_id, liturgia_id, usuario_id, created_at)
                            SELECT id, celula_id, liturgia_id, usuario_id, created_at FROM presencas;
                            DROP TABLE presencas;
                            ALTER TABLE presencas_temp RENAME TO presencas;
                        ");
                        break;
                    }
                }
            } else {
                $this->conn->exec("
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
                ");
            }
        } catch (\PDOException $e) {
            // Ignora se tabela já existir
        }

        try { $this->conn->exec("ALTER TABLE presencas ADD COLUMN nome_visitante VARCHAR(255);"); } catch (\PDOException $e) {}
        try { $this->conn->exec("ALTER TABLE presencas ADD COLUMN qtd_visitas INT DEFAULT 1;"); } catch (\PDOException $e) {}
        try { $this->conn->exec("ALTER TABLE presencas ADD COLUMN tipo VARCHAR(20) DEFAULT 'membro';"); } catch (\PDOException $e) {}
        try { $this->conn->exec("ALTER TABLE presencas ADD COLUMN registrado_por_id INT;"); } catch (\PDOException $e) {}
        try { $this->conn->exec("ALTER TABLE presencas ADD COLUMN codigo_acesso VARCHAR(20);"); } catch (\PDOException $e) {}
    }

    /**
     * Busca uma presença pelo ID garantindo isolamento por célula.
     *
     * @param int $celula_id Identificador da célula.
     * @param int $presenca_id ID do registro na tabela presencas.
     * @return array|false Dados do registro de presença.
     */
    public function findById($celula_id, $presenca_id) {
        $query = "SELECT * FROM {$this->table_name} WHERE id = :id AND celula_id = :celula_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $presenca_id, ':celula_id' => $celula_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica se o usuário tem autorização para remover um registro de presença específico.
     *
     * @param array $presenca Registro da presença.
     * @param int $usuarioLogadoId ID do usuário autenticado.
     * @param bool $isLider Se o usuário tem permissão de líder/admin.
     * @return bool Verdadeiro se permitido.
     */
    public function podeRemover($presenca, $usuarioLogadoId, $isLider = false) {
        if ($isLider) return true;
        if (!$presenca) return false;
        if (!empty($presenca['usuario_id']) && (int)$presenca['usuario_id'] === (int)$usuarioLogadoId) {
            return true;
        }
        if (!empty($presenca['registrado_por_id']) && (int)$presenca['registrado_por_id'] === (int)$usuarioLogadoId) {
            return true;
        }
        return false;
    }

    /**
     * Verifica se um visitante já foi adicionado na liturgia/encontro atual.
     *
     * @param int $liturgia_id Identificador do evento.
     * @param string $nomeVisitante Nome do visitante.
     * @return bool Verdadeiro se já cadastrado.
     */
    public function visitanteJaConfirmado($liturgia_id, $nomeVisitante) {
        $query = "
            SELECT COUNT(*) as total 
            FROM {$this->table_name} 
            WHERE liturgia_id = :liturgia_id 
              AND tipo = 'visitante' 
              AND LOWER(TRIM(nome_visitante)) = LOWER(TRIM(:nome))
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':liturgia_id' => $liturgia_id, ':nome' => $nomeVisitante]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row['total'] ?? 0) > 0;
    }

    /**
     * Busca a lista histórica de visitantes cadastrados anteriormente na célula.
     *
     * @param int $celula_id Identificador da célula.
     * @return array Lista de visitantes históricos com máxima quantidade de visitas.
     */
    public function findVisitantesByCelula($celula_id) {
        $query = "
            SELECT nome_visitante, MAX(qtd_visitas) as max_visitas, MAX(created_at) as ultima_visita
            FROM {$this->table_name}
            WHERE celula_id = :celula_id AND tipo = 'visitante' AND nome_visitante IS NOT NULL AND nome_visitante != ''
            GROUP BY nome_visitante
            ORDER BY max_visitas DESC, nome_visitante ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':celula_id' => $celula_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Registra a presença de um usuário cadastrado em um evento gravando a autoria.
     *
     * @param int $celula_id Identificador do tenant.
     * @param int $liturgia_id Identificador do evento.
     * @param int $usuario_id Identificador do usuário confirmado.
     * @param int|null $registrado_por_id Identificador de quem efetuou a confirmação.
     * @return bool Verdadeiro se inserido com sucesso.
     */
    public function registrar($celula_id, $liturgia_id, $usuario_id, $registrado_por_id = null) {
        try {
            if ($this->jaConfirmado($liturgia_id, $usuario_id)) {
                return true;
            }
            $registradoPor = $registrado_por_id ?: $usuario_id;
            $query = "INSERT INTO {$this->table_name} (celula_id, liturgia_id, usuario_id, registrado_por_id, tipo) VALUES (:celula_id, :liturgia_id, :usuario_id, :registrado_por_id, 'membro')";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':celula_id'         => $celula_id,
                ':liturgia_id'       => $liturgia_id,
                ':usuario_id'        => $usuario_id,
                ':registrado_por_id' => $registradoPor
            ]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Registra a presença de um visitante no evento gravando a autoria.
     *
     * @param int $celula_id Identificador do tenant.
     * @param int $liturgia_id Identificador do evento.
     * @param string $nomeVisitante Nome completo do visitante.
     * @param int $qtdVisitas Quantidade de vezes que já foi à célula.
     * @param int|null $registrado_por_id Identificador do usuário que cadastrou o visitante.
     * @throws \Exception Se o visitante já estiver adicionado neste encontro.
     * @return bool Verdadeiro se registrado com sucesso.
     */
    public function registrarVisitante($celula_id, $liturgia_id, $nomeVisitante, $qtdVisitas = 1, $registrado_por_id = null) {
        $nomeVisitante = trim($nomeVisitante);
        if (empty($nomeVisitante)) {
            throw new \Exception("Por favor, informe o nome do visitante.");
        }

        if ($this->visitanteJaConfirmado($liturgia_id, $nomeVisitante)) {
            throw new \Exception("O visitante '{$nomeVisitante}' já está confirmado neste encontro.");
        }

        $codigoAcesso = self::generateCodigoAcesso();
        $query = "INSERT INTO {$this->table_name} (celula_id, liturgia_id, usuario_id, nome_visitante, qtd_visitas, tipo, registrado_por_id, codigo_acesso) 
                  VALUES (:celula_id, :liturgia_id, NULL, :nome_visitante, :qtd_visitas, 'visitante', :registrado_por_id, :codigo_acesso)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':celula_id'         => $celula_id,
            ':liturgia_id'       => $liturgia_id,
            ':nome_visitante'    => $nomeVisitante,
            ':qtd_visitas'       => max(1, (int)$qtdVisitas),
            ':registrado_por_id' => $registrado_por_id,
            ':codigo_acesso'     => $codigoAcesso
        ]);
    }

    /**
     * Gera um código de acesso de visitante único de 6 caracteres alfanuméricos em caixa alta.
     *
     * @return string Código gerado (ex: V8K2P9).
     */
    public static function generateCodigoAcesso() {
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $code = 'V';
        for ($i = 0; $i < 5; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
    }

    /**
     * Busca um registro de visitante pelo seu código de acesso.
     *
     * @param string $codigo Código de acesso do visitante.
     * @return array|false Dados do registro de presença ou false se não encontrado.
     */
    public function findByCodigoAcesso($codigo) {
        $codigo = strtoupper(trim($codigo));
        if (empty($codigo)) return false;

        $query = "
            SELECT p.*, l.tema as liturgia_tema, l.data_culto, c.nome as celula_nome
            FROM {$this->table_name} p
            INNER JOIN liturgias l ON p.liturgia_id = l.id
            LEFT JOIN celulas_info c ON p.celula_id = c.id
            WHERE UPPER(TRIM(p.codigo_acesso)) = :codigo AND p.tipo = 'visitante'
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':codigo' => $codigo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Remove a presença de um usuário ou visitante de um evento pelo ID da presença.
     *
     * @param int $celula_id Identificador do tenant.
     * @param int $presenca_id ID do registro na tabela presencas.
     * @return bool Verdadeiro se removido.
     */
    public function removerById($celula_id, $presenca_id) {
        $query = "DELETE FROM {$this->table_name} WHERE id = :id AND celula_id = :celula_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $presenca_id, ':celula_id' => $celula_id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Remove a presença de um usuário em um evento.
     *
     * @param int $celula_id Identificador do tenant.
     * @param int $liturgia_id Identificador do evento.
     * @param int $usuario_id Identificador do usuário.
     * @return bool Verdadeiro se removido com sucesso.
     */
    public function remover($celula_id, $liturgia_id, $usuario_id) {
        $query = "DELETE FROM {$this->table_name} WHERE celula_id = :celula_id AND liturgia_id = :liturgia_id AND usuario_id = :usuario_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':celula_id' => $celula_id, ':liturgia_id' => $liturgia_id, ':usuario_id' => $usuario_id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Verifica se um usuário está confirmado em um evento.
     *
     * @param int $liturgia_id Identificador do evento.
     * @param int $usuario_id Identificador do usuário.
     * @return bool Verdadeiro se o usuário estiver confirmado.
     */
    public function jaConfirmado($liturgia_id, $usuario_id) {
        if (!$usuario_id) return false;
        $query = "SELECT COUNT(*) as total FROM {$this->table_name} WHERE liturgia_id = :liturgia_id AND usuario_id = :usuario_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':liturgia_id' => $liturgia_id, ':usuario_id' => $usuario_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row['total'] ?? 0) > 0;
    }

    /**
     * Retorna todos os usuários e visitantes confirmados em um evento com seus dados.
     *
     * @param int $celula_id Identificador do tenant.
     * @param int $liturgia_id Identificador do evento.
     * @return array Lista de confirmados.
     */
    public function findByLiturgia($celula_id, $liturgia_id) {
        $query = "
            SELECT 
                p.*, 
                COALESCE(u.nome, p.nome_visitante) as usuario_nome, 
                COALESCE(u.perfil, 'VISITANTE') as usuario_perfil
            FROM {$this->table_name} p
            LEFT JOIN usuarios u ON p.usuario_id = u.id
            WHERE p.celula_id = :celula_id AND p.liturgia_id = :liturgia_id
            ORDER BY p.created_at ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':celula_id' => $celula_id, ':liturgia_id' => $liturgia_id]);
        $presencas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($presencas as &$p) {
            if ($p['tipo'] === 'visitante' && empty($p['codigo_acesso'])) {
                $novoCodigo = self::generateCodigoAcesso();
                $upd = $this->conn->prepare("UPDATE {$this->table_name} SET codigo_acesso = :code WHERE id = :id");
                $upd->execute([':code' => $novoCodigo, ':id' => $p['id']]);
                $p['codigo_acesso'] = $novoCodigo;
            }
        }
        return $presencas;
    }
}
