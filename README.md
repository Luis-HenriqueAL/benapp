# ⛪ benApp — Sistema de Gestão de Células & Liturgias

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2" />
  <img src="https://img.shields.io/badge/PostgreSQL-15-4169E1?style=for-the-badge&logo=postgresql&logoColor=white" alt="PostgreSQL 15" />
  <img src="https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS" />
  <img src="https://img.shields.io/badge/Python-Flask-3776AB?style=for-the-badge&logo=python&logoColor=white" alt="Python Flask" />
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License" />
</p>

<p align="center">
  <b>benApp</b> é uma plataforma moderna, leve e responsiva (Web-Mobile First) projetada para otimizar a gestão de células, grupos de igrejas, liturgias semanais, escalas de voluntários e repertórios de cifras.
</p>

---

## ✨ Principais Funcionalidades

### 👥 1. Gestão de Células & Membros (Multi-Tenant)
- **Isolamento de Dados**: Banco de dados estruturado para suporte nativo a múltiplos grupos (`celula_id`).
- **Controle de Acesso RBAC**: Perfis de **Líder** e **Membro** com matriz dinâmica de permissões.

### 📅 2. Liturgia Dinâmica & Escala Semanal
- **Momentos Personalizados**: Crie e ordene momentos do encontro (Quebra-gelo, Louvor, Estudo, Oração, Jantar, etc.).
- **Escala de Responsabilidades**: Vincule voluntários a funções específicas para cada data com registro de histórico.

### 🎵 3. Visualizador Avançado de Cifras e Letras
- **Integração Automática com Cifra Club**: Busca e importa cifras completas automaticamente via microserviço Python dedicado.
- **Transposição Cromática Instantânea**: Altere o tom da música (`-1 Semi` / `+1 Semi`) com ajuste automático para tonalidades maiores, menores (ex: `Em`, `Am`) e acordes compostos.
- **Autorolagem Acelerada**: Controle de rolagem automática da tela com slider de velocidade ajustável (1x a 10x).
- **Modos de Exibição**: Alternância entre **Cifra**, **Apenas Letra** (oculta acordes/tablaturas) e exibição de **Tablaturas (🎸 Tabs)**.
- **Navegação Fluida**: Botão *"Próxima Música"* para acompanhar o louvor sem interrupções.

### 🎟️ 4. Acesso de Visitantes por Código de Convite (Read-Only)
- **Código Único por Visitante**: Geração automática de código alfanumérico amigável de 6 caracteres (ex: `V8K2P9`) ao registrar presenças.
- **Portal do Convidado**: Tela de acesso dedicada em `/visitante` onde o visitante digita seu código e visualiza a programação e cifras do encontro.
- **Experiência Limpa & Segura**: Interface restrita estritamente para leitura, ocultando botões de edição ou confirmação de presenças.

### 🙋‍♂️ 5. Confirmação de Presenças & Recorrência
- Botão rápido *"Eu Vou"* para confirmação individual de voluntários.
- Acompanhamento do histórico de frequência e contagem de visitas acumuladas (1ª visita, 2ª visita, etc.).

---

## 🛠️ Stack Tecnológica

| Camada | Tecnologia |
| :--- | :--- |
| **Backend Principal** | PHP 8.2 Vanilla (Arquitetura MVC limpa) |
| **Banco de Dados** | PostgreSQL 15 (Docker) com Fallback SQLite para dev local |
| **Microserviço de Cifras** | Python 3.11 + Flask (Scraper Cifra Club) |
| **Frontend & UI** | HTML5, JavaScript ES6 Vanilla, Tailwind CSS CDN (Mobile-First) |
| **Containerização** | Docker & Docker Compose |

---

## 🚀 Como Executar o Projeto

### Pré-requisitos
- [Docker](https://www.docker.com/) instalado.
- [Docker Compose](https://docs.docker.com/compose/) instalado.

### 1. Clonar o Repositório
```bash
git clone https://github.com/Luis-HenriqueAL/benapp.git
cd benapp
```

### 2. Configurar as Variáveis de Ambiente
O projeto já inclui um arquivo `.env` pré-configurado para o ambiente Docker. Se necessário, copie a partir do modelo:
```bash
cp .env.example .env
```

### 3. Iniciar com Docker Compose
Execute o comando abaixo na raiz do projeto para construir e subir todos os serviços (`app`, `db`, `cifraclub-api`):

```bash
docker compose up -d --build
```

Aguarde alguns segundos até que os containers estejam rodando. A aplicação estará disponível em:
👉 **[http://localhost:8080](http://localhost:8080)**

---

## 🔑 Credenciais Padrão para Teste

O banco de dados é inicializado automaticamente com os seguintes usuários de demonstração:

| Perfil | E-mail | Senha | Acesso |
| :--- | :--- | :--- | :--- |
| **👔 Líder Principal** | `admin@celula.com` | `senha123` | Acesso total (Criação de eventos, escalas, membros) |
| **🙋‍♂️ Voluntário João** | `joao@celula.com` | `senha123` | Acesso membro (Visualização de escalas e presenças) |
| **🎟️ Visitante** | *Código gerado no cadastro* | *Acesso sem senha* | Acesso leitor em `/visitante` |

---

## 📂 Estrutura de Diretórios

```
benApp/
├── Config/            # Conexão com banco de dados e rotinas de boot
├── Controllers/       # Controladores MVC (Auth, Escala, Presença, Visitante, Cifra)
├── Models/            # Models com lógica de negócios e isolamento Multi-Tenant
├── Services/          # Microserviços (CifraClub API em Python, NotificationService)
├── Views/             # Views HTML/PHP responsivas com Tailwind CSS
├── db/                # DDL do banco PostgreSQL (init.sql) e fallback SQLite
├── docs/              # Documentações técnicas e históricas de changelogs
├── public/            # Ponto de entrada (index.php) e roteamento de requisições
├── tests/             # Suíte de testes unitários em PHP
├── docker-compose.yml # Orquestração dos containers (app, db, cifraclub-api)
├── Dockerfile         # Imagem Apache + PHP 8.2 PDO PostgreSQL
└── README.md          # Documentação oficial do repositório
```

---

## 🧪 Suíte de Testes

Para executar os testes unitários da aplicação diretamente dentro do container Docker:

```bash
docker exec benapp-app-1 php tests/run_tests.php
```

---

## 📄 Licença

Este projeto está sob a licença [MIT](LICENSE).

---

<p align="center">
  Desenvolvido com 💜 para facilitar o serviço e a comunhão nas células.
</p>
