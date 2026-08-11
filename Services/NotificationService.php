<?php

namespace Services;

/**
 * Class NotificationService
 * Serviço de envio de notificações de escalas via E-mail e SMS.
 */
class NotificationService {

    /**
     * Dispara notificação de convocação de escala para o voluntário (mock).
     *
     * @param array $usuario Dados do usuário (nome, email, telefone).
     * @param array $escala Dados da escala (funcao, data_culto).
     * @return bool Retorna verdadeiro indicando envio bem-sucedido.
     */
    public function sendEscalaNotification($usuario, $escala) {
        $message = "Olá {$usuario['nome']}, você foi escalado para {$escala['funcao']} na data {$escala['data_culto']}.";
        
        // Mock de envio de e-mail e SMS (registrado nos logs do PHP)
        error_log("[EMAIL SENT] To: {$usuario['email']} | Message: $message");
        error_log("[SMS SENT] To: {$usuario['telefone']} | Message: $message");

        return true;
    }
}
