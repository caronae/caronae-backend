# Caronaê - Backend

[![CircleCI](https://circleci.com/gh/caronae/caronae-backend.svg?style=svg)](https://circleci.com/gh/caronae/caronae-backend)

Backend do Caronaê, baseado no [Laravel](https://github.com/laravel/laravel). O backend é
composto da API mobile e da área administrativa.


## Instalação

O backend do Caronaê executa em um ambiente com PHP 7.1, PostgreSQL e Redis.

O jeito mais fácil de executar este projeto localmente é utilizando nossas imagens 
Docker. A documentação encontra-se no repositório [caronae-docker](https://github.com/caronae/caronae-docker).

**Todos os comandos abaixo devem ser executados de dentro do container Docker.
Você pode criar uma sessão dentro do container do caronae-backend através do comando abaixo:**

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

Para executar os testes, basta utilizar o **PHPUnit**:

```
./vendor/bin/phpunit
```
