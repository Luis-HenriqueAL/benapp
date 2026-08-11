<?php

namespace Services;

class NotificationService {
    
    public function sendEscalaNotification($usuario, $escala_detalhes) {
        $message = "Olá {$usuario['nome']}, você foi escalado para a função: {$escala_detalhes['funcao']} no dia {$escala_detalhes['data_culto']}.";
        
        // Mock de envio de E-mail
        if (!empty($usuario['email'])) {
            $this->sendEmail($usuario['email'], "Aviso: Nova Escala", $message);
        }
        
        // Mock de envio de SMS/WhatsApp
        if (!empty($usuario['telefone'])) {
            $this->sendSMS($usuario['telefone'], $message);
        }
    }

    private function sendEmail($to, $subject, $message) {
        // Exemplo mockado (poderia usar PHPMailer futuramente)
        error_log("MOCK EMAIL -> Para: $to | Assunto: $subject | Mensagem: $message");
        return true;
    }

    private function sendSMS($to, $message) {
        // Exemplo mockado (poderia integrar com API Twilio/Zenvia futuramente)
        error_log("MOCK SMS -> Para: $to | Mensagem: $message");
        return true;
    }
}
