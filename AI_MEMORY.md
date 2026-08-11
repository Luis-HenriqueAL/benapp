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
- **MVC & Clean Architecture**: Separação rigorosa entre Models (`Usuario`, `Escala`, `Liturgia`, `CelulaInfo`), Controllers (`AuthController`, `EscalaController`, `UsuarioController`, `CelulaController`, etc.) e Views.
- **Módulo de Informações da Célula**: Interface e rotas (`/celula` e `/celula/update`) para gestão cadastral da célula com persistência PostgreSQL em `celulas_info` (suporte a `anfitrioes` e `lideres` via JSONB).
- **Integração ViaCEP**: Autopreenchimento assíncrono de endereço via API ViaCEP no frontend ao digitar o CEP.
- **Tratamento de Rotas & Asset Handling**: Resolução e eliminação de falso-positivos de erro 404 em roteamento e requisições de assets estáticos.
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
  - Módulo de Informações da Célula (`CelulaController`, `Views/Celula/index.php`, `Models/CelulaInfo.php`) com rotas GET `/celula` e POST `/celula/update`.
  - Integração frontend com a API ViaCEP para consulta e preenchimento dinâmico de endereços.
  - Correção de falso-positivos de erro 404 em assets e roteamento da aplicação.
  - 100% de cobertura de DocBlocks (PHPDoc) mantida em todas as classes, métodos e funções.
  - Validação unânime e sequencial concluída por toda a esteira de engenharia (PM, Arquiteto, Backend, Frontend, QA & Cibersegurança).
  - Padronização do título "Nova Escala" em `Views/Escala/create.php` e layout de montagem dinâmica de cultos/liturgias.
  - Rota POST `/escala/store` configurada e integrada no controller (`EscalaController::store()`).
  - Proteções ativas: CSRF (`SecurityHelper`), sanitização contra XSS, validação de multi-tenancy (`celula_id`) e prevenção contra conflitos de horários.
  - Suíte de testes unitários (`tests/EscalaControllerTest.php` e `tests/UsuarioControllerTest.php`) implementada.
  - Redesenho do Layout State-of-the-Art Web-Mobile finalizado e otimizado.
  - Ajustes e validações de Acessibilidade WCAG implementados.
  - Módulo de Usuários completo com CRUD, controle de acesso e isolamento por `celula_id`.
  - Sistema de Autenticação (Login, Logout e Controle de Sessão) validado.
  - Composer integrado com autoload PSR-4.
- **Falta Fazer (Backlog)**:
  - Relatórios avançados pós-MVP e integrações externas adicionais.
