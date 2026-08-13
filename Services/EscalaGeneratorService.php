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
            $isLouvor = is_array($momento) ? (!empty($momento['is_louvor']) || !empty($momento['isLouvor'])) : false;
            $isPalavra = is_array($momento) ? (!empty($momento['is_palavra']) || !empty($momento['isPalavra'])) : false;

            $tituloLower = mb_strtolower($titulo);
            if (!$isLouvor && (strpos($tituloLower, 'louvor') !== false || strpos($tituloLower, 'música') !== false || strpos($tituloLower, 'adoracao') !== false || strpos($tituloLower, 'adoração') !== false)) {
                $isLouvor = true;
            }
            if (!$isPalavra && (strpos($tituloLower, 'palavra') !== false || strpos($tituloLower, 'estudo') !== false || strpos($tituloLower, 'pregação') !== false || strpos($tituloLower, 'pregacao') !== false)) {
                $isPalavra = true;
            }

            $usuarioSorteadoId = null;

            // Louvor e Palavra -> Líder Principal
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

                usort($candidatos, function($a, $b) use ($contagemRecente) {
                    $cntA = $contagemRecente[(int)$a['id']] ?? 0;
                    $cntB = $contagemRecente[(int)$b['id']] ?? 0;

                    if ($cntA === $cntB) {
                        return rand(-1, 1);
                    }
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
