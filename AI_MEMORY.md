# AI Memory - benApp

## Escopo
Sistema de Gestão de Células (benApp).

**Regras de Negócio e MVP**:
- Escalas geradas automaticamente por mês (com base nas anteriores, evitando repetições). O líder pode clicar para gerar e editar livremente.
- Administradores/Líderes têm acesso total. Voluntários interagem com a escala (aceitar/recusar/solicitar substituição).
- Liturgia não possui limite de momentos, mas o momento "estudo" é obrigatório e inamovível.
- Validação de conflitos: O sistema impede e alerta caso um voluntário seja alocado para funções simultâneas no mesmo horário/dia.
- Multi-tenant: Isolamento estrito por `celula_id`. Líderes com permissão superior podem visualizar dados de outras células.

## Stack
- PHP Vanilla
- Padrão MVC
- PostgreSQL (Multi-tenant)
- Docker

## Decisões de Arquitetura
- Padrão MVC utilizado para estruturação do código.
- Banco de dados PostgreSQL configurado para arquitetura multi-tenant.
- Uso de Docker para containerização do ambiente de desenvolvimento.

## Status
- **Feito**: Definição da stack, escopo inicial e criação da estrutura de documentação.
- **Falta Fazer**: Configuração do Docker, implementação do MVC, modelagem do banco e desenvolvimento das features de gestão.
