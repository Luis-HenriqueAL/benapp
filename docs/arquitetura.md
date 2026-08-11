# Arquitetura do Sistema

Documento descritivo das decisões arquiteturais e componentes do **benApp**.

## Visão Geral
O sistema é construído em arquitetura monolítica MVC leve (Model-View-Controller) utilizando **PHP Vanilla** de alta performance.

## Módulos e Componentes

### 1. Camada de Controle & Autenticação
- `AuthController`: Gerencia login, logout e verificação de permissões do usuário.
- `UsuarioController`: Responsável pelo CRUD completo do Módulo de Usuários, controle de perfis/permissões e isolamento por célula.
- `CelulaController`: Gerencia a exibição (`/celula`) e atualização (`/celula/update`) do Módulo de Informações da Célula, com busca via API ViaCEP e tratamento dinâmico de anfitriões e líderes.
- `EscalaController`: Controla regras de criação (`create`), listagem (`index`) e persistência (`store`) de escalas e liturgias via rota POST `/escala/store`, validando multi-tenancy e conflitos de horários.
- `LiturgiaController`: Controla a manutenção dos modelos litúrgicos.

### 2. Camada de Modelo & Dados
- PostgreSQL configurado com **Multi-tenancy** estrito baseado na coluna `celula_id`.
- Tabela `celulas_info`: Armazena dados cadastrais da célula, incluindo colunas `anfitrioes` (JSONB) e `lideres` (JSONB) para suporte flexível a múltiplos anfitriões e líderes.
- Tabela `usuarios` contendo a coluna única `email` para autenticação segura e gerenciamento do Módulo de Usuários.
- Triggers SQL automatizados (`t_liturgia_estudo`) para garantir a regra inamovível do momento de estudo em cada liturgia.

### 3. Infraestrutura & Gerenciamento de Pacotes
- **Docker & Docker Compose**: Garantem a consistência entre ambientes de desenvolvimento, teste e produção.
- **Composer**: Gerenciador de dependências PHP e autoloader PSR-4 padronizado para a aplicação.

### 4. Frontend, UX & Acessibilidade
- **Layout Web-Mobile State-of-the-Art**: Interface responsiva construída com Tailwind CSS, menus expansíveis/retráteis (Sidebar) e componentes otimizados para touch e desktop.
- **Módulo de Informações da Célula & ViaCEP**: Formulários cadastrais com consulta assíncrona de CEP à API pública do ViaCEP, auto-preenchendo logradouro, bairro, cidade e estado.
- **Tratamento de Roteamento & Assets**: Sistema de rotas otimizado para evitar falso-positivos de erro 404 em assets estáticos e navegação.
- **Formulário Dinâmico de Escalas**: Interface `Views/Escala/create.php` padronizada com o título "Nova Escala", adição/remoção dinâmica de momentos e atribuição de voluntários.
- **Acessibilidade WCAG**: Aplicação de padrões de acessibilidade visual e estrutural (contraste de cores, marcadores ARIA, foco navegável via teclado e semântica HTML5).

### 5. Qualidade de Código, Suíte de Testes & Documentação
- **Suíte de Testes Unitários**: Testes automatizados PHPUnit em `tests/EscalaControllerTest.php` e `tests/UsuarioControllerTest.php` cobrindo submissão de formulários, validação de exceções e mocks.
- **DocBlocks (100% de Cobertura)**: Todas as funções, métodos, propriedades, parâmetros e tipos de retorno totalmente documentados com padrão PHPDoc em todos os arquivos da aplicação.

### 6. Cibersegurança & Proteção
- **Criptografia de Senhas**: Uso de `password_hash` com algoritmo Bcrypt.
- **Proteção CSRF**: Validação rigorosa de tokens de requisição via `SecurityHelper::verifyCsrfToken()` utilizando comparação em tempo constante (`hash_equals`).
- **Sanitização XSS**: Escape universal de saídas de texto e HTML via `SecurityHelper::e()`.
- **Validação de Esteira Integrada**: Projeto auditado e aprovado sequencialmente pela esteira de engenharia (PM, Arquiteto, Backend, Frontend, QA & Cibersegurança).
