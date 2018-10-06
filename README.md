# Caronaê - Backend

[![CircleCI](https://circleci.com/gh/caronae/caronae-backend.svg?style=svg)](https://circleci.com/gh/caronae/caronae-backend)

Backend do Caronaê, baseado no [Laravel](https://github.com/laravel/laravel). O backend é
composto por uma API REST usada pelos apps e pela área administrativa, usada internamente.

O Caronaê é um sistema de código aberto, seguro e prático de caronas compartilhadas, criado com o objetivo de ser replicado em diferentes instituições e feito exclusivamente para a comunidade acadêmica das instituições integrantes da Rede Caronaê. Para conhecer mais sobre o projeto, visite nosso [site](https://caronae.org).

## Instalação

O backend do Caronaê executa em um ambiente com PHP 7, PostgreSQL e Redis.

O jeito mais fácil de executar este projeto localmente é utilizando nossas imagens 
Docker. No diretório `docker` há uma configuração do [Docker Compose](https://docs.docker.com/compose/overview/).
Para iniciar o projeto junto com as dependências, execute:

```bash
cd docker
docker-compose up
```

Todos os comandos deste README devem ser executados de dentro do container do backend.
Você pode criar uma sessão dentro do container do caronae-backend através do comando abaixo:

```bash
docker exec -it caronae-backend sh
```


### Instalando dependências

Para instalar todas as ferramentas, incluindo as bibliotecas de teste (para o restante da configuração),
execute o comando abaixo de dentro do container:

```bash
composer install
```


### Populando o banco de dados

Há um seed do banco que cria um banco de dados limitado para desenvolvimento local.
Para usá-lo, execute o comando abaixo de dentro do container:

_Importante: o comando abaixo apaga todas as informações do banco de dados antes de
inserir os novos dados._

```bash
php artisan migrate:refresh --seed
```

Pronto! Agora você já pode fazer login na área administrativa utilizando o usuário
padrão.

* URL: [localhost:8000/admin](http://localhost:8000/admin)
* E-mail: user@example.com
* Senha: 123456


## Testes

Este projeto possui alguns testes unitários e de integração, que ficam dentro da
pasta **tests**.

Os testes são executados em uma tabela separada do banco de dados. Portanto, é necessário criar uma tabela **caronae_testing**.
Para criá-la, execute o comando abaixo de dentro do container:

```
createdb -h $DB_HOST -U $DB_USERNAME -O $DB_USERNAME -E utf8 caronae_testing
```

Para executar os testes, execute o **PHPUnit**:

```
./vendor/bin/phpunit
```
