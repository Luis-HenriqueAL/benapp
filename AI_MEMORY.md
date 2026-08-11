# AI Memory - benApp

## Escopo
Sistema de Gestão de Células (benApp).

**Regras de Negócio e MVP**:
- Escalas geradas automaticamente por mês (com base nas anteriores, evitando repetições). O líder pode clicar para gerar e editar livremente.
- Administradores/Líderes têm acesso total. Voluntários interagem com a escala (aceitar/recusar/solicitar substituição).
- Liturgia não possui limite de momentos, mas o momento "estudo" é obrigatório e inamovível (garantido via Trigger PostgreSQL).
- Validação de conflitos: O sistema impede e alerta caso um voluntário seja alocado para funções simultâneas no mesmo horário/dia.
- Multi-tenant: Isolamento estrito por `celula_id`. Líderes com permissão superior podem visualizar dados de outras células.

## Stack
- PHP Vanilla (PHP 8.2+)
- Padrão MVC (Model-View-Controller)
- PostgreSQL (Multi-tenant)
- Docker & Docker Compose

## Decisões de Arquitetura
- **MVC & Clean Design**: Separação clara entre Models (`Usuario`, `Escala`, `Liturgia`), Controllers (`AuthController`, `EscalaController`, etc.) e Views.
- **Banco de Dados & Migrações**: PostgreSQL multi-tenant isolado por `celula_id`. Schema unificado com suporte a autenticação por `email` único e triggers automáticos (`t_liturgia_estudo`).
- **Segurança**:
  - Senhas criptografadas via `password_hash` (BCrypt).
  - Validação estrita de tokens CSRF através de `SecurityHelper::verifyCsrfToken()` usando `hash_equals`.
  - Sanitização HTML e proteção contra XSS via `SecurityHelper::e()`.
- **Containerização**: Docker e Docker Compose configurados para ambiente reprodutível de desenvolvimento e testes.

## Status Atual
- **Concluído (Iteração Atual)**:
  - Resolução do erro da coluna de e-mail (`usuarios.email`) e consolidação do schema PostgreSQL.
  - Implementação completa do sistema de Autenticação (Login, Logout e Controle de Sessão).
  - Proteção CSRF integrada em todos os formulários e formulários de login/escalas.
  - Testes unitários com PHPUnit e validação de segurança.
  - Validação e aprovação em sequência por toda a equipe de subagentes (PM, Arquiteto, Backend, Frontend, QA & Cibersegurança).
- **Falta Fazer (Backlog)**:
  - Relatórios avançados pós-MVP e integrações adicionais.
