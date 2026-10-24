# Zerei

Plataforma web para registrar os jogos que você zerou ao longo do ano, acompanhar o histórico e manter uma lista de jogos que pretende zerar (backlog).

## Funcionalidades

- **Jogos zerados:** registre cada zerada com data, plataforma, nota (1 a 5 estrelas) e nível de conclusão (história, 100% ou platina). Um mesmo jogo pode ter várias zeradas, para quem gosta de rejogar.
- **Backlog:** mantenha a lista de jogos que quer zerar e acompanhe o status de cada um (Backlog → Jogando → Zerado/Abandonado).
- **Contagem por ano:** veja quantos jogos você zerou em cada ano.
- **Linha do tempo:** acompanhe as zeradas mês a mês, em cada ano.
- **Capas dos jogos:** busca de capas pela [RAWG](https://rawg.io) ou por link `https` informado à mão.
- **Multiusuário:** cada pessoa enxerga e altera apenas os próprios dados.
- **Tema escuro e claro**, com layout responsivo para desktop e celular.

## Tecnologias

- [Laravel 13](https://laravel.com) com Blade (monolito fullstack, sem Livewire/Inertia)
- [Tailwind CSS 4](https://tailwindcss.com) e [Alpine.js](https://alpinejs.dev)
- [Laravel Breeze](https://laravel.com/docs/starter-kits#laravel-breeze) para autenticação
- SQLite
- [Pest](https://pestphp.com) para testes
- [Laravel Pint](https://laravel.com/docs/pint) para formatação

## Requisitos

- PHP 8.3 ou superior
- [Composer](https://getcomposer.org)
- Node.js e npm

## Instalação

```bash
git clone [<url-do-repositorio>](https://github.com/vinialves34/zerei.git) zerei
cd zerei
composer setup
```

O script `composer setup` instala as dependências, cria o `.env`, gera a chave da aplicação, roda as migrations e compila os assets.

### Configuração da RAWG (opcional)

As capas dos jogos vêm da API da RAWG. Para habilitar a busca de capas, crie uma chave em [rawg.io/apidocs](https://rawg.io/apidocs) e informe no `.env`:

```dotenv
RAWG_API_KEY=sua-chave
```

Sem a chave, a aplicação funciona normalmente: a busca da RAWG não retorna resultados e a capa pode ser informada por link. Para vincular capas a jogos já cadastrados (por título exato):

```bash
php artisan games:fetch-covers
```

## Desenvolvimento

```bash
composer run dev
```

Sobe o servidor da aplicação e o Vite com hot reload.

## Testes e formatação

```bash
composer test            # suíte completa
vendor/bin/pint          # formata o código
```

## Arquitetura

O fluxo de uma requisição é **Rota → Controller → Service → Model**:

| Camada | Local | Responsabilidade |
| --- | --- | --- |
| Controller | `app/Http/Controllers` | Preocupações de HTTP: autoriza, valida e devolve a resposta |
| Form Request | `app/Http/Requests` | Validação e autorização da entrada |
| Service | `app/Services` | Regra de negócio, consultas e transações |
| Model | `app/Models` | Relacionamentos, casts e scopes |
| Policy | `app/Policies` | Permissão por recurso (dados de outro usuário retornam 404) |
| Enum | `app/Enums` | Valores fixos e rótulos |

O teste `tests/Unit/ArchitectureTest.php` falha se essas fronteiras forem quebradas. Mais detalhes sobre decisões de projeto, domínio e convenções estão no [CLAUDE.md](CLAUDE.md).

## Licença

Projeto de código aberto sob a licença [MIT](https://opensource.org/licenses/MIT).
