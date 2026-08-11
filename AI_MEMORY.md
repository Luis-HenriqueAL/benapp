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
- **MVC & Clean Architecture**: Separação rigorosa entre Models (`Usuario`, `Escala`, `Liturgia`, `CelulaInfo`), Controllers (`AuthController`, `EscalaController`, `UsuarioController`, `CelulaController`, `MomentoLiturgiaController`, etc.) e Views.
- **Módulo de Momentos Litúrgicos & Navegação**: Tela `/liturgia/momentos` (`MomentoLiturgiaController`) para gestão visual de momentos da liturgia com link de atalho atualizado no Menu Lateral (Sidebar).
- **Módulo de Informações da Célula**: Interface e rotas (`/celula` e `/celula/update`) para gestão cadastral da célula com persistência PostgreSQL em `celulas_info` (suporte a `anfitrioes` e `lideres` via JSONB).
- **Integração ViaCEP**: Autopreenchimento assíncrono de endereço via API ViaCEP no frontend ao digitar o CEP.
- **Simplificação da Criação de Escalas & Liturgia Dinâmica**: Autopreenchimento de dados da célula (nome, horário e dia do encontro) na criação de escalas e reordenação de momentos litúrgicos via botões Subir/Descer e Drag & Drop (HTML5).
- **Tratamento de Rotas & Asset Handling**: Resolução e eliminação de falso-positivos de erro 404 em roteamento e requisições de assets estáticos.
- **Módulo de Usuários & Multi-tenancy**: CRUD completo e consulta de usuários por célula via `findByCelula` no `UsuarioModel` (`Models/Usuario.php`) corrigida com isolamento multi-tenant por `celula_id`.
- **Interface Web-Mobile State-of-the-Art**: Redesenho responsivo de alta precisão UX/UI com Menu Lateral Retrátil (Sidebar) atualizado com atalho para momentos litúrgicos, adaptável a dispositivos móveis e desktop.
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
  - Visualizador de Cifras Aprimorado: Paleta clara (bg-slate-50/text-slate-800), autorolagem ajustável por slider (1x–10x), botão **"Próxima Música"**, modo **"Apenas Letra"** com filtro correto de linhas de acordes e header fixo com backdrop-blur.
  - Modo de Visualização "Apenas Letra" & Cifra Completa: Adicionados botões no topo de `Views/Escala/cifra.php` (**`🎼 Cifra`** VS **`📝 Apenas Letra`**) que filtram dinamicamente as linhas de acordes no navegador para vocalistas e membros.
  - Transposição Cromática de Tom em Tempo Real: Adicionado painel interativo em `Views/Escala/cifra.php` com botões **`-1 Semi`**, **`+1 Semi`** e **`Resetar`**, suporte a acordes maiores simples (`C`, `D`, `E`, `F`, `G`, `A`, `B`), inversões (`G/B`), sufixos complexos e algoritmo inteligente de identificação de linhas de cifragem (`isChordLine`) preservando a letra da música intacta.
  - Microserviço Docker `cifraclub-api` & Módulo de Cifras: Criação do container Python em `services/cifraclub-api` (`app.py`, `Dockerfile`, `requirements.txt`), registro no `docker-compose.yml` (porta 3000), extração resiliente com múltiplos seletores de container HTML (`find_all('pre')`, `#cifra_cnt`, `.cifra`), botão **"Buscar / Atualizar Cifra no Cifra Club"** (`force_refresh=1`) e auto-healing cache no banco de dados.
  - Confirmação por Outro Membro, Cadastro de Visitantes e Autoria de Registros: Gravação do usuário autor da confirmação (`registrado_por_id`) em `Models/Presenca.php`, trava contra duplicidade (`visitanteJaConfirmado`), autocompletar de histórico e controle de permissão de cancelamento (o usuário só pode cancelar a própria presença ou confirmações criadas por ele mesmo, exceto perfil Líder) em `PresencaController` e `Views/Escala/show.php`.
  - Confirmação por Outro Membro, Cadastro de Visitantes e Autoria de Registros: Gravação do usuário autor da confirmação (`registrado_por_id`) em `Models/Presenca.php`, trava contra duplicidade (`visitanteJaConfirmado`), autocompletar de histórico e controle de permissão de cancelamento (o usuário só pode cancelar a própria presença ou confirmações criadas por ele mesmo, exceto perfil Líder) em `PresencaController` e `Views/Escala/show.php`.
  - Edição do Perfil Membro & Acesso Padrão a Escalas: Atualização de `Models/Perfil.php` (`ensureMembroPerfil`) para persistir o perfil `MEMBRO` no banco com permissão inicial `escala.view`, liberação do botão de edição no `Views/perfil/index.php` e garantia de que qualquer usuário com perfil Membro acesse a home de Escalas (`/`) ao logar.
  - Eliminação do Redirecionamento Infinito (`ERR_TOO_MANY_REDIRECTS`): Implementação do método `SecurityHelper::getDefaultRoute()` que calcula dinamicamente a rota inicial autorizada para qualquer perfil de usuário (Líder, Supervisor, Membro ou Voluntário) e atualização dos redirecionamentos no `AuthController`, `EscalaController`, `UsuarioController`, `CelulaController`, `MomentoLiturgiaController` e `PerfilController`.
  - Suporte a Variáveis de Ambiente (`.env`): Criação do arquivo `.env`, modelo `.env.example`, utilitário `Helpers/EnvHelper.php` e inicialização no `public/index.php` para injeção automática de parâmetros de banco de dados e ambiente no PHP.
  - Correção de Autenticação e Resiliência de Conexão: Atualização de `Config/Database.php` com suporte a múltiplos hosts PostgreSQL (`127.0.0.1`, `localhost`, `db`) e fallback automático para SQLite em ambiente XAMPP/Windows, além da adequação do `Models/Usuario.php` para inserção segura do usuário inicial `admin@celula.com` / `senha123`.
  - Adequação de Terminologia: Remoção completa do termo "culto" das telas, rótulos de menu (`Escalas`), formulários de permissões e fallbacks de tema (`Encontro de Célula`), adequando o sistema 100% à realidade de gestão de células.
  - Refinamento do Layout de Usuários (`Views/usuarios/index.php`): Ampliação do padding dos cards (`p-5`), ampliação do avatar (`w-12 h-12`), melhoria do espaçamento vertical (`space-y-4`) e respiros internos entre nome, e-mail e badges para eliminar o visual comprimido.
  - Padronização Visual de Cabeçalhos (State-of-the-Art Web-Mobile UI): Uniformização de todos os títulos e ações superiores dentro de cards flutuantes brancos arredondados (`.bg-white.rounded-3xl.p-5`) nas telas *Detalhes da Escala*, *Nova Escala*, *Editar Liturgia*, *Momentos da Liturgia*, *Informações da Célula*, *Novo/Editar Usuário* e *Formulário de Perfis*.
  - Edição de Liturgia & Escala: Implementação de rotas (`/escala/edit` e `/escala/update`), suporte no `EscalaController` (`edit`/`updateStore`), métodos `update` no `Liturgia` model e `deleteByLiturgiaId` no `Escala` model, além de tela dedicada `Views/Escala/edit.php` com reordenação drag & drop e troca de voluntários.
  - Auto-edição de Perfil: Qualquer usuário autenticado (independente de ter a permissão `usuarios.manage`) pode alterar seu próprio nome, e-mail e redefinir senha através do `UsuarioController` (`edit`/`update`), com trava que impede a alteração não autorizada de seu próprio perfil de acesso/status e atalho direto no cabeçalho do Sidebar (`Views/layout.php`).
  - Correção e fortalecimento do Controle de Acesso (RBAC): Ocultação estrita de módulos sem permissão no Sidebar e na Dock flutuante (`Views/layout.php`), bloqueio de ações de criação/edição/exclusão em Views (`Escala`, `usuarios`, `liturgia`, `perfil`) e adição de middlewares/checagens `SecurityHelper::hasPermissao()` nos Controllers (`CelulaController`, `MomentoLiturgiaController`, `PerfilController`, `EscalaController`).
  - Resolução da chamada `findByCelula` no `UsuarioModel` (`Models/Usuario.php`) corrigindo e garantindo a busca de usuários com filtro multi-tenant.
  - Implementação da nova tela e rotas `/liturgia/momentos` (`MomentoLiturgiaController`) para cadastro e gestão de momentos da liturgia.
  - Atualização da barra de navegação/menu lateral (Sidebar) com inclusão de link para a nova tela `/liturgia/momentos`.
  - Simplificação da criação de escalas com autopreenchimento de dados da célula (nome, horário e dia da semana) e reordenação flexível de momentos litúrgicos (Subir/Descer e Drag & Drop).
  - Módulo de Informações da Célula (`CelulaController`, `Views/Celula/index.php`, `Models/CelulaInfo.php`) com rotas GET `/celula` e POST `/celula/update`.
  - Integração frontend com a API ViaCEP para consulta e preenchimento dinâmico de endereços.
  - Correção de falso-positivos de erro 404 em assets e roteamento da aplicação.
  - 100% de cobertura de DocBlocks (PHPDoc) mantida integralmente em todas as classes, métodos e funções.
  - Validação unânime e sequencial concluída por toda a esteira de engenharia (PM, Arquiteto, Backend, Frontend, QA & Cibersegurança).
  - Padronização do título "Nova Escala" em `Views/Escala/create.php` e layout de montagem dinâmica de cultos/liturgias.
  - Rota POST `/escala/store` configurada e integrada no controller (`EscalaController::store()`).
  - Proteções ativas: CSRF (`SecurityHelper`), sanitização contra XSS, validação de multi-tenancy (`celula_id`) e prevenção contra conflitos de horários.
  - Suíte de testes unitários (`tests/EscalaControllerTest.php`, `tests/UsuarioControllerTest.php` e `tests/MomentoLiturgiaControllerTest.php`) implementada.
  - Redesenho do Layout State-of-the-Art Web-Mobile finalizado e otimizado com Sidebar retrátil.
  - Ajustes e validações de Acessibilidade WCAG implementados.
  - Módulo de Usuários completo com CRUD, controle de acesso e isolamento por `celula_id`.
  - Sistema de Autenticação (Login, Logout e Controle de Sessão) validado.
  - Composer integrado com autoload PSR-4.
- **Falta Fazer (Backlog)**:
  - Relatórios avançados pós-MVP e integrações externas adicionais.
