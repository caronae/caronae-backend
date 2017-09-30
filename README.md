# Caronaê - Backend

[![CircleCI](https://circleci.com/gh/macecchi/caronae-backend/tree/develop.svg?style=svg&circle-token=9c47c2e35ff1feee8355437fe8c1d1ae7fc326d3)](https://circleci.com/gh/macecchi/caronae-backend/tree/develop)

Backend do Caronaê, baseado no [Laravel](https://github.com/laravel/laravel). O backend é
composto da API mobile e da área administrativa.


## Instalação

O backend do Caronaê executa em um ambiente com PHP 7.1, PostgreSQL e Redis.

O jeito mais fácil de executar este projeto localmente é utilizando nossas imagens 
Docker. A documentação encontra-se no repositório [caronae-docker](https://github.com/caronae/caronae-docker).


### Populando o banco de dados

Há um seed do banco que cria um banco de dados limitado de amostra para desenvolvimento
local. Para usá-lo, importe os seeds no seu banco.

```
php artisan db:seed
```

Pronto! Agora você já pode fazer login na área administrativa utilizando o usuário
padrão.

* E-mail: 1@1.com
* Senha: 1234


## Testes

Este projeto possui alguns testes unitários e de integração, que ficam dentro da
pasta **tests**.

Os testes são executados em uma tabela separada do banco de dados. Portanto, é necessário criar uma tabela **caronae_testing**:

```
createdb -O caronae -E utf8 caronae_testing
```

Para executar os testes, basta utilizar o **PHPUnit**:

```
vendor/bin/phpunit
```
