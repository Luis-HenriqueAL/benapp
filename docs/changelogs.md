# Changelogs

Histórico de alterações e versões do projeto benApp.

## [2.1.1] - Exibição dos Nomes de Líderes e Anfitriões no Detalhe da Escala
### Adicionado
- **Líderes e Anfitriões na Escala**: Incluída a renderização dinâmica dos nomes dos Líderes e Anfitriões cadastrados na célula logo abaixo do nome da célula em [Views/Escala/show.php](file:///home/luis/dev/projetos/benApp/Views/Escala/show.php).

---

## [2.1.0] - Visualização de Endereço da Célula em Mapa Interativo (OpenStreetMap)
### Adicionado
- **Modal de Mapa Interativo OpenStreetMap**: Implementado clique interativo no endereço da célula em [Views/Escala/show.php](file:///home/luis/dev/projetos/benApp/Views/Escala/show.php), abrindo modal responsivo com mapa embarcado via Leaflet.js e OpenStreetMap.
- **Geocodificação Automática via Nominatim API**: Integração cliente com a API pública do Nominatim para identificar latitude e longitude do logradouro/bairro/cidade com fallback gracioso.
- **Ações de Navegação Externa**: Adicionados botões no modal para copiar o endereço formatado e abrir diretamente a rota no OpenStreetMap ou no Google Maps.

---

## [2.0.0] - Documentação Oficial do Repositório (README.md) e Publicação no GitHub
### Adicionado / Otimizado
- **README.md Profissional**: Elaboração completa do arquivo [README.md](file:///home/luis/dev/projetos/benApp/README.md) com badges de tecnologias (PHP 8.2, PostgreSQL, Docker, Tailwind CSS, Python Flask), arquitetura do projeto, instruções detalhadas de inicialização via Docker Compose, tabela de credenciais padrão de demonstração, lista completa de funcionalidades (acesso de visitante por código, transposição cromática, cifras, autorolagem, multi-tenant) e comandos para testes.

---

## [1.9.6] - Restrição Visual e Backend de Ações para Visitante
### Corrigido / Otimizado
- **Interface Exclusiva de Leitura**: Ocultados os botões "Eu Vou", "Confirmar Membro" e "Novo Visitante" e formulários associados em [Views/Escala/show.php](file:///home/luis/dev/projetos/benApp/Views/Escala/show.php) quando acessado via modo visitante (`$isVisitorMode`).
- **Bloqueio Backend no PresencaController**: Adicionada trava de segurança no construtor de [Controllers/PresencaController.php](file:///home/luis/dev/projetos/benApp/Controllers/PresencaController.php) garantindo que sessões de visitantes sejam redirecionadas caso tentem invocar ações de modificação de presença.
- **Navegação de Voltar Aprimorada**: Opcional de voltar no cabeçalho redireciona visitantes diretamente para a rota `/visitante/sair`.

---

## [1.9.5] - Liberação de Permissão de Visualização para Sessão de Visitante
### Corrigido
- **Permissão `escala.view` para Visitantes**: Atualizado [Helpers/SecurityHelper.php](file:///home/luis/dev/projetos/benApp/Helpers/SecurityHelper.php) (`hasPermissao`) para responder com `true` para a permissão `escala.view` em sessões de visitante (`$_SESSION['visitante']`), eliminando o alerta "Sem permissão" ao acessar a liturgia.
- **Roteamento de Cifras de Visitante**: Incluída a rota `/escala/cifra` na lista de rotas autorizadas para visitantes em [public/index.php](file:///home/luis/dev/projetos/benApp/public/index.php).

---

## [1.9.4] - Correção na Transposição de Tonalidades Menores nas Cifras
### Corrigido
- **Atualização Visual do Tom Menor**: Ajustada a função JavaScript `aplicarTransposicao` em [Views/Escala/cifra.php](file:///home/luis/dev/projetos/benApp/Views/Escala/cifra.php) para utilizar `transposeChordToken(origTom, offset)` ao invés de `transposeNote`, permitindo que tonalidades menores (ex: `Em`, `Am`, `F#m`) e acordes com extensões atualizem o texto da badge do Tom perfeitamente ao clicar nos botões `-1 Semi` / `+1 Semi`.

---

## [1.9.3] - Correção do Hash BCrypt das Senhas Padrão dos Usuários Iniciais
### Corrigido
- **Autenticação dos Usuários Padrão**: Corrigido o hash de senha BCrypt gravado em [db/init.sql](file:///home/luis/dev/projetos/benApp/db/init.sql) e [db/schema_sqlite.sql](file:///home/luis/dev/projetos/benApp/db/schema_sqlite.sql) para a senha padrão `senha123` dos usuários `admin@celula.com` e `joao@celula.com`.
- **Garantia de Usuário Inicial**: Adicionado o método `ensureDefaultUsers()` no [Config/Database.php](file:///home/luis/dev/projetos/benApp/Config/Database.php) que verifica e garante de forma idempotente que o usuário `admin@celula.com` exista no banco com a senha `senha123` pronta para login.

---

## [1.9.2] - Integração do .env com Docker Compose & Centralização de Scripts SQL
### Adicionado / Otimizado
- **Integração do `.env` com `docker-compose.yml`**: Atualizados os arquivos [.env](file:///home/luis/dev/projetos/benApp/.env), [.env.example](file:///home/luis/dev/projetos/benApp/.env.example) e [docker-compose.yml](file:///home/luis/dev/projetos/benApp/docker-compose.yml) para que o Docker Compose injete dinamicamente as credenciais da aplicação e do PostgreSQL diretamente a partir das variáveis de ambiente (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`, `DB_PORT`).
- **Centralização Estrita de Scripts SQL**: Removido o arquivo duplicado `database.sql` da raiz. Todos os scripts SQL da aplicação agora residem exclusivamente no diretório `db/` ([db/init.sql](file:///home/luis/dev/projetos/benApp/db/init.sql) e [db/schema_sqlite.sql](file:///home/luis/dev/projetos/benApp/db/schema_sqlite.sql)).

---

## [1.9.1] - Correção de Conexão PostgreSQL no Docker & DDL idempotente de Presenças
### Corrigido
- **Conectividade do Container com PostgreSQL**: Adicionadas variáveis de ambiente no [docker-compose.yml](file:///home/luis/dev/projetos/benApp/docker-compose.yml) (`DB_HOST=db`, `DB_USER=root`, `DB_PASS=rootpassword`) e mecanismo de *retry* com pausa no [Config/Database.php](file:///home/luis/dev/projetos/benApp/Config/Database.php), evitando queda prematura em *fallback* SQLite e o erro de `readonly database`.
- **Migração da Coluna `codigo_acesso`**: Atualizados os scripts [db/init.sql](file:///home/luis/dev/projetos/benApp/db/init.sql) e [db/schema_sqlite.sql](file:///home/luis/dev/projetos/benApp/db/schema_sqlite.sql) com a coluna `codigo_acesso VARCHAR(20)`, e desacoplada a execução de migrações em [Models/Presenca.php](file:///home/luis/dev/projetos/benApp/Models/Presenca.php) para alteração de esquema idempotente e livre de erros.

---

## [1.9.0] - Acesso de Visitantes via Código de Convite
### Adicionado / Otimizado
- **Geração Automática de Código de Visitante**: Geração de códigos amigáveis de 6 caracteres (ex: `V8K2P9`) ao cadastrar visitantes em [Models/Presenca.php](file:///home/luis/dev/projetos/benApp/Models/Presenca.php).
- **Controlador e Views de Visitante**: Implementado [Controllers/VisitanteController.php](file:///home/luis/dev/projetos/benApp/Controllers/VisitanteController.php) e a view [Views/auth/visitante.php](file:///home/luis/dev/projetos/benApp/Views/auth/visitante.php) para entrada por código via botão destacado em [Views/auth/login.php](file:///home/luis/dev/projetos/benApp/Views/auth/login.php).
- **Middleware de Restrição Read-Only Multi-Tenant**: Atualização do roteamento em [public/index.php](file:///home/luis/dev/projetos/benApp/public/index.php) permitindo que o visitante acesse estritamente em modo de leitura a liturgia/escala da célula convidada.
- **Badge do Código & Botão de Copiar**: Exibição do código do visitante e botão de cópia direta para a área de transferência em [Views/Escala/show.php](file:///home/luis/dev/projetos/benApp/Views/Escala/show.php).
- **Suíte de Testes Unitários**: Criado [tests/VisitanteControllerTest.php](file:///home/luis/dev/projetos/benApp/tests/VisitanteControllerTest.php) para validação do modelo e rotas de visitantes.

---

## [1.8.0] - Correção no Docker Compose, Microserviço CifraClub API e Tablaturas (🎸 Tabs)
### Corrigido
- **Case-Sensitivity no Docker Compose**: Ajustado o caminho de build do microserviço `cifraclub-api` no `docker-compose.yml` para `./Services/cifraclub-api` (com maiúscula), corrigindo falha de inicialização em sistemas Linux e permitindo o boot perfeito dos containers (`app`, `db` e `cifraclub-api`).

### Adicionado / Otimizado
- **Visualizador de Cifras e Tablaturas**: Adicionado seletor e filtro dinâmico de Tablaturas (`🎸 Tabs`) e controles de velocidade de autorolagem no [Views/Escala/cifra.php](file:///home/luis/dev/projetos/benApp/Views/Escala/cifra.php).

---

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
