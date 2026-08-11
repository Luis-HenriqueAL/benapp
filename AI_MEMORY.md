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
- Composer (Gerenciamento de Dependências e Autoload)
- Tailwind CSS (Interface Web-Mobile State-of-the-Art)

## Decisões de Arquitetura
- **MVC & Clean Architecture**: Separação rigorosa entre Models (`Usuario`, `Escala`, `Liturgia`), Controllers (`AuthController`, `EscalaController`, `UsuarioController`, etc.) e Views.
- **Módulo de Usuários**: CRUD completo e gestão de usuários com controle de permissões por papel/perfil e isolamento multi-tenant por `celula_id`.
- **Interface Web-Mobile State-of-the-Art**: Redesenho responsivo de alta precisão UX/UI com Menu Lateral Retrátil (Sidebar), adaptável a dispositivos móveis e desktop.
- **Acessibilidade WCAG**: Conformidade com padrões WCAG (contraste visual, atributos ARIA, suporte a leitores de tela e navegação por teclado).
- **Gerenciamento de Dependências**: Composer configurado no projeto com suporte a autoload PSR-4.
- **Padronização de Código**: 100% de cobertura de DocBlocks (PHPDoc) em todas as funções, métodos e classes do codebase.
- **Banco de Dados & Migrações**: PostgreSQL multi-tenant isolado por `celula_id`. Schema unificado com suporte a autenticação por `email` único e triggers automáticos (`t_liturgia_estudo`).
- **Segurança & Proteção Cibersegurança**:
  - Hash de senhas via `password_hash` (BCrypt).
  - Validação estrita de tokens CSRF através de `SecurityHelper::verifyCsrfToken()` com comparação de tempo constante (`hash_equals`).
  - Sanitização rigorosa HTML e proteção contra XSS via `SecurityHelper::e()`.

## Status Atual
- **Concluído**:
  - Redesenho do Layout State-of-the-Art Web-Mobile finalizado e otimizado.
  - Ajustes e validações de Acessibilidade WCAG implementados.
  - Proteção contra vulnerabilidades XSS (sanitização universal) e CSRF (validação de tokens) ativas.
  - Cobertura de 100% de DocBlocks mantida e atualizada em todo o projeto.
  - Módulo de Usuários completo com CRUD, controle de acesso e isolamento por `celula_id`.
  - Sistema de Autenticação (Login, Logout e Controle de Sessão) validado.
  - Composer integrado com autoload PSR-4.
  - Banco de Dados PostgreSQL multi-tenant e containerização Docker operacionais.
  - Validação sequencial e unânime concluída por toda a esteira de engenharia (PM, Arquiteto, Backend, Frontend, QA & Cibersegurança).
- **Falta Fazer (Backlog)**:
  - Relatórios avançados pós-MVP e integrações externas.
