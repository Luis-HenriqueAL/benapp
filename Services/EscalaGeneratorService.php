<?php

namespace Services;

use Config\Database;
use Models\Usuario;
use PDO;

/**
 * Class EscalaGeneratorService
 * Serviço responsável por gerar a distribuição automática e justa de voluntários na escala da célula.
 */
class EscalaGeneratorService {
    /** @var PDO Conexão com o banco de dados. */
    private $conn;

    /** @var Usuario Model de usuários. */
    private $usuarioModel;

    /**
     * Construtor da classe EscalaGeneratorService.
     */
    public function __construct() {
        $this->conn = Database::getConnection();
        $this->usuarioModel = new Usuario();
    }

    /**
     * Gera atribuições automáticas para uma lista de momentos da liturgia.
     *
     * @param int $celula_id Identificador do tenant.
     * @param array $momentos Lista de momentos (ex: [['id' => 1, 'titulo' => 'Louvor', 'is_louvor' => true, 'is_palavra' => false], ...])
     * @return array Atribuições sugeridas contendo 'usuario_id' para cada momento.
     */
    public function gerarAtribuicoes($celula_id, array $momentos) {
        $usuarios = $this->usuarioModel->findByCelulaId($celula_id);
        $voluntariosAtivos = array_values(array_filter($usuarios, function($u) {
            return ($u['status'] ?? 'ativo') === 'ativo';
        }));

        if (empty($voluntariosAtivos)) {
            return [];
        }

        // Identifica o Líder Principal
        $liderPrincipal = $this->usuarioModel->findLiderPrincipalByCelula($celula_id);
        $liderPrincipalId = $liderPrincipal ? (int)$liderPrincipal['id'] : (int)$voluntariosAtivos[0]['id'];

        // Busca flags de momentos predefinidos cadastrados no banco
        $stmtMom = $this->conn->prepare("SELECT titulo, is_louvor, is_palavra FROM momentos_predefinidos WHERE celula_id = :celula_id");
        $stmtMom->execute([':celula_id' => $celula_id]);
        $dbMomentos = $stmtMom->fetchAll(PDO::FETCH_ASSOC);

        $momentoFlagsMap = [];
        foreach ($dbMomentos as $dbm) {
            $tKey = mb_strtolower(trim($dbm['titulo']));
            $momentoFlagsMap[$tKey] = [
                'is_louvor' => !empty($dbm['is_louvor']),
                'is_palavra' => !empty($dbm['is_palavra']),
            ];
        }

        // Coleta histórico de escalados recentemente para rotação
        $historicoEscalas = $this->obterHistoricoRecente($celula_id);

        $contagemRecente = [];
        foreach ($voluntariosAtivos as $v) {
            $vid = (int)$v['id'];
            $contagemRecente[$vid] = $historicoEscalas[$vid] ?? 0;
        }

        $escaladosEventoAtual = [];
        $atribuicoesSugeridas = [];

        foreach ($momentos as $idx => $momento) {
            $titulo = is_array($momento) ? ($momento['titulo'] ?? '') : (string)$momento;
            $tituloTrim = trim($titulo);
            $tKey = mb_strtolower($tituloTrim);

            $isLouvor = is_array($momento) ? (!empty($momento['is_louvor']) || !empty($momento['isLouvor'])) : false;
            $isPalavra = is_array($momento) ? (!empty($momento['is_palavra']) || !empty($momento['isPalavra'])) : false;

            if (isset($momentoFlagsMap[$tKey])) {
                if ($momentoFlagsMap[$tKey]['is_louvor']) $isLouvor = true;
                if ($momentoFlagsMap[$tKey]['is_palavra']) $isPalavra = true;
            }

            if (!$isLouvor && (strpos($tKey, 'louvor') !== false || strpos($tKey, 'música') !== false || strpos($tKey, 'musica') !== false || strpos($tKey, 'adoracao') !== false || strpos($tKey, 'adoração') !== false || strpos($tKey, 'cifra') !== false)) {
                $isLouvor = true;
            }
            if (!$isPalavra && (strpos($tKey, 'palavra') !== false || strpos($tKey, 'estudo') !== false || strpos($tKey, 'pregação') !== false || strpos($tKey, 'pregacao') !== false || strpos($tKey, 'mensagem') !== false || strpos($tKey, 'bíblia') !== false || strpos($tKey, 'biblia') !== false)) {
                $isPalavra = true;
            }

            $usuarioSorteadoId = null;

            // Louvor e Palavra -> Líder Principal obrigatoriamente
            if ($isLouvor || $isPalavra) {
                $usuarioSorteadoId = $liderPrincipalId;
            } else {
                // Outros momentos -> Sorteio randômico com rotação justa
                $candidatos = array_values(array_filter($voluntariosAtivos, function($v) use ($escaladosEventoAtual, $voluntariosAtivos) {
                    if (count($voluntariosAtivos) > count($escaladosEventoAtual)) {
                        return !in_array((int)$v['id'], $escaladosEventoAtual);
                    }
                    return true;
                }));

                if (empty($candidatos)) {
                    $candidatos = $voluntariosAtivos;
                }

                // Embaralha inicialmente para desempate aleatório entre membros com a mesma contagem de escala
                shuffle($candidatos);

                usort($candidatos, function($a, $b) use ($contagemRecente) {
                    $cntA = $contagemRecente[(int)$a['id']] ?? 0;
                    $cntB = $contagemRecente[(int)$b['id']] ?? 0;
                    return $cntA <=> $cntB;
                });

                $escolhido = $candidatos[0];
                $usuarioSorteadoId = (int)$escolhido['id'];
            }

            $escaladosEventoAtual[] = $usuarioSorteadoId;
            $contagemRecente[$usuarioSorteadoId] = ($contagemRecente[$usuarioSorteadoId] ?? 0) + 1;

            $atribuicoesSugeridas[] = [
                'idx' => $idx,
                'titulo' => $titulo,
                'is_louvor' => $isLouvor,
                'is_palavra' => $isPalavra,
                'usuario_id' => $usuarioSorteadoId
            ];
        }

        return $atribuicoesSugeridas;
    }

    /**
     * Coleta contagem recente de atribuições por usuário para rotação justa.
     *
     * @param int $celula_id
     * @return array
     */
    private function obterHistoricoRecente($celula_id) {
        try {
            $query = "
                SELECT e.usuario_id, COUNT(e.id) as total
                FROM escalas e
                JOIN liturgias l ON e.liturgia_id = l.id
                WHERE l.celula_id = :celula_id
                GROUP BY e.usuario_id
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':celula_id' => $celula_id]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $historico = [];
            foreach ($rows as $r) {
                if (!empty($r['usuario_id'])) {
                    $historico[(int)$r['usuario_id']] = (int)$r['total'];
                }
            }
            return $historico;
        } catch (\PDOException $e) {
            return [];
        }
    }
}
