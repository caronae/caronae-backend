# Caronaê - Backend

[![CircleCI](https://circleci.com/gh/caronae/caronae-backend.svg?style=svg)](https://circleci.com/gh/caronae/caronae-backend)

Backend do Caronaê, baseado no [Laravel](https://github.com/laravel/laravel). O backend é
composto por uma API REST usada pelos apps e pela área administrativa, usada internamente.

O Caronaê é um sistema de código aberto, seguro e prático de caronas compartilhadas, criado com o objetivo de ser replicado em diferentes instituições e feito exclusivamente para a comunidade acadêmica das instituições integrantes da Rede Caronaê. Para conhecer mais sobre o projeto, visite nosso [site](https://caronae.org).

## Instalação

O backend do Caronaê executa em um ambiente com PHP 7, PostgreSQL e Redis.

O jeito mais fácil de executar este projeto localmente é utilizando nossas imagens 
**Docker**. Você não precisa ter nada instalado na sua máquina além do [Docker](https://www.docker.com/) e [Docker Compose](https://docs.docker.com/compose/overview/).

No diretório `docker` há uma configuração do [Docker Compose](https://docs.docker.com/compose/overview/).
Para rodar o projeto junto com todas as dependências necessárias, execute:

```bash
cd docker
docker-compose up
```

Você pode criar uma terminal dentro do container do caronae-backend através do comando abaixo:

```bash
docker exec -it caronae-backend sh
```

### Populando o banco de dados

Quando você executa o projeto pela primeira vez, o banco de dados está vazio. Porém
há um [seed](https://laravel.com/docs/5.7/seeding) que popula um banco de dados com dados aleatórios, perfeito para desenvolvimento local.
Para usá-lo, execute o comando abaixo:

_Importante: o comando abaixo apaga todos os dados existentes antes de inserir os novos dados._

```bash
docker exec -it caronae-backend php artisan migrate:refresh --seed
```

Pronto! Agora você já pode fazer login na área administrativa utilizando o usuário padrão.

* URL: [localhost:8000/admin](http://localhost:8000/admin)
* E-mail: user@example.com
* Senha: 123456


## Testes

Este projeto possui alguns testes unitários e de integração, que ficam dentro da
pasta **tests**. Eles verificam o comportamento da aplicação a fim de evitar que mudanças no código
quebrem alguma funcionalidade existente. Para ler mais sobre testes no Laravel, consulte a
[documentação oficial](https://laravel.com/docs/5.7/testing).

Existe um arquivo de configuração do Docker Compose feito só pra poder rodar os testes.
Você pode executá-lo da sua máquina usando o comando abaixo de dentro da pasta `docker`:

```bash
docker-compose -f docker-compose.test.yml up --build --exit-code-from caronae-backend-tests
```
