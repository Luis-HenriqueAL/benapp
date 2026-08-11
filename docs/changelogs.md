# Changelogs

Histórico de alterações e versões do projeto benApp.

## [1.7.0] - Tela /liturgia/momentos, Resolução findByCelula em UsuarioModel, Menu Lateral e DocBlocks 100%
### Adicionado / Otimizado
- **Módulo de Momentos Litúrgicos (`/liturgia/momentos`)**: Implementação do controller `MomentoLiturgiaController` e views para cadastro e gerenciamento de momentos litúrgicos.
- **Atualização do Menu Lateral**: Adicionado o link de navegação rápida para a rota `/liturgia/momentos` no menu lateral responsivo (Sidebar).
- **100% Cobertura DocBlocks**: Garantida e mantida a documentação PHPDoc em todos os novos métodos, classes e funções refatoradas.

### Corrigido
- **Resolução da chamada `findByCelula`**: Corrigida a consulta no `UsuarioModel` (`Models/Usuario.php`), garantindo a correta filtragem de usuários por célula (`celula_id`) sob o padrão multi-tenant.

### Aprovado
- Homologação e validação sequencial concluídas por toda a esteira de engenharia (PM, Arquiteto, Backend, Frontend, QA & Cibersegurança).

---

## [1.6.0] - Simplificação na Criação de Escalas, Autopreenchimento e Reordenação Litúrgica
### Adicionado / Otimizado
- **Autopreenchimento de Dados da Célula**: Integração na criação de escalas (`Views/Escala/create.php` e `EscalaController`), carregando dinamicamente o nome, horário padrão e dia da semana da célula vinculada.
- **Reordenação de Momentos Litúrgicos**: Botões de movimentação rápida (Subir / Descer) e funcionalidade Drag & Drop nativa (HTML5) com reindexação dinâmica automática de inputs de formulário (`reindexMomentos`).
- **100% Cobertura DocBlocks**: Blocos PHPDoc mantidos integralmente em todo o código PHP.

### Aprovado
- Homologação e validação sequencial concluídas por toda a esteira de engenharia (PM, Arquiteto, Backend, Frontend, QA & Cibersegurança).

---

## [1.5.0] - Módulo de Informações da Célula, Integração ViaCEP, Correção 404 e DocBlocks 100%
### Adicionado / Otimizado
- **Módulo de Informações da Célula**: Interface e controle (`/celula` e POST `/celula/update`) para atualização e exibição cadastral das células com persistência PostgreSQL em `celulas_info` e colunas JSONB (`anfitrioes` e `lideres`).
- **Integração ViaCEP**: Autopreenchimento assíncrono de CEP no frontend para agilizar o preenchimento de endereço da célula.
- **Correção de Falso-Positivos 404**: Otimização no sistema de roteamento e manipulação de assets estáticos, eliminando falso-positivos de erro 404.
- **100% Cobertura DocBlocks**: PHPDoc completo mantido e verificado em todas as classes, métodos e funções da aplicação.

### Aprovado
- Homologação e validação sequencial concluídas por toda a esteira de engenharia (PM, Arquiteto, Backend, Frontend, QA & Cibersegurança).

---

## [1.4.0] - Padronização "Nova Escala", Rota /escala/store, Proteções e Testes Unitários
### Adicionado / Otimizado
- **Padronização Visual & UX**: Título "Nova Escala" em `Views/Escala/create.php` com interface Web-Mobile otimizada para inclusão dinâmica de momentos litúrgicos.
- **Rota POST `/escala/store`**: Mapeamento da rota `/escala/store` e tratamento via `EscalaController::store()`.
- **Cibersegurança & Validação**: Proteção CSRF obrigatória com token, sanitização XSS, isolamento multi-tenant (`celula_id`) e checagem de conflitos de horário de voluntários.
- **Suíte de Testes Unitários**: Cobertura de testes em `tests/EscalaControllerTest.php` cobrindo submissão válida de formulário, validação de campos obrigatórios ausentes e liturgia inexistente via Mocks/Reflection.

### Aprovado
- Homologação e validação sequencial concluídas por toda a esteira de engenharia (PM, Arquiteto, Backend, Frontend, QA & Cibersegurança).

---

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
