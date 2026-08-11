# Changelogs

Histórico de alterações e versões do projeto benApp.

## [1.3.0] - Redesenho State-of-the-Art Web-Mobile, WCAG, XSS, CSRF e DocBlocks 100%
### Adicionado / Reformulado
- **Layout State-of-the-Art Web-Mobile**: Redesenho completo da interface do usuário com Tailwind CSS, menus expansíveis/retráteis e navegação fluida em telas mobile e desktop.
- **Acessibilidade WCAG**: Implementação de conformidade WCAG, garantindo contraste adequado, navegação via teclado e marcações semânticas com suporte a leitores de tela (ARIA).
- **Sanitização XSS & CSRF**: Sanitização rigorosa das saídas de renderização (`SecurityHelper::e`) e validação de tokens CSRF (`SecurityHelper::verifyCsrfToken` com `hash_equals`) em 100% dos formulários e rotas de escrita.
- **Padronização de DocBlocks**: 100% do codebase (Controllers, Models, Helpers, Services) documentado com blocos PHPDoc padronizados.

### Aprovado
- Homologação e validação sequencial concluídas por toda a esteira de engenharia (PM, Arquiteto, Backend, Frontend, QA & Cibersegurança).

---

## [1.2.0] - Módulo de Usuários, Menu Lateral Retrátil, Composer e DocBlocks 100%
### Adicionado
- **Módulo de Usuários**: CRUD completo, gerenciamento de perfis e controle de acesso com isolamento multi-tenant (`celula_id`).
- **Menu Lateral Retrátil**: Componente de navegação lateral (Sidebar) retrátil e totalmente responsivo no layout principal.
- **Gerenciamento de Dependências (Composer)**: Integração do Composer para autoloader e gestão de pacotes PHP no projeto.
- **Documentação de Código**: Cobertura de 100% em DocBlocks (PHPDoc) para todas as funções e métodos da aplicação.

### Aprovado
- Aprovação unânime de toda a esteira de engenharia (PM, Arquiteto, Backend, Frontend, QA & Cibersegurança).

---

## [1.1.0] - Iteração de Autenticação, CSRF e Correção de Migração
### Corrigido
- Ajustado o schema PostgreSQL em `db/init.sql` resolvendo o erro de coluna de e-mail na tabela `usuarios`.
- Corrigida a validação e mapeamento de campos de usuários no login.

### Adicionado
- Fluxo completo de Autenticação (`AuthController`, views de login e controle de sessão).
- Proteção contra ataques CSRF (`SecurityHelper::generateCsrfToken` e `verifyCsrfToken`).
- Suporte a hashes de senha com `password_hash` (Bcrypt).
- Validação e aprovação técnica sequencial da equipe de subagentes (PM, Arquiteto, Backend, Frontend, QA & Cibersegurança).

---

## [1.0.0] - MVP Aprovado
### Adicionado
- Configuração do ambiente Docker (PHP, PostgreSQL).
- Estrutura base seguindo o Padrão MVC (PHP Vanilla).
- Interface do usuário utilizando Tailwind CSS.
- Auditoria de Segurança e QA finalizadas.
- Estrutura inicial de documentação.
