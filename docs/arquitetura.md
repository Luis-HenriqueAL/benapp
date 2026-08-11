# Arquitetura do Sistema

Documento descritivo das decisões arquiteturais e componentes do **benApp**.

## Visão Geral
O sistema é construído em arquitetura monolítica MVC leve (Model-View-Controller) utilizando **PHP Vanilla** de alta performance.

## Módulos e Componentes

### 1. Camada de Controle & Autenticação
- `AuthController`: Gerencia login, logout e verificação de permissões do usuário.
- `EscalaController`, `LiturgiaController`, `UsuarioController`: Controlam as regras de negócio dos módulos correspondentes.

### 2. Camada de Modelo & Dados
- PostgreSQL configurado com **Multi-tenancy** estrito baseado na coluna `celula_id`.
- Tabela `usuarios` contendo a coluna única `email` para autenticação segura.
- Triggers SQL automatizados (`t_liturgia_estudo`) para garantir a regra inamovível do momento de estudo em cada liturgia.

### 3. Infraestrutura & Containerização
- **Docker & Docker Compose**: Garantem a consistência entre ambientes de desenvolvimento, teste e produção.

### 4. Frontend & Interface
- Tailwind CSS com layout responsivo e moderno, mantendo identidade visual limpa (cards flutuantes e paleta de cores corporativa).

### 5. Segurança
- Criptografia de senhas utilizando hash Bcrypt (`password_hash`).
- Proteção contra ataques CSRF via `SecurityHelper::verifyCsrfToken()` com comparação de tempo constante (`hash_equals`).
- Sanitização rigorosa contra XSS com `SecurityHelper::e()`.
- Testes automatizados (PHPUnit) e esteira de validação por agentes especializados (QA & Cibersegurança).
