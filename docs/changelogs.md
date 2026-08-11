# Changelogs

Histórico de alterações e versões do projeto benApp.

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
