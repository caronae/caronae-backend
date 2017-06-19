# Caronaê - Backend

[![CircleCI](https://circleci.com/gh/macecchi/caronae-backend/tree/develop.svg?style=svg&circle-token=9c47c2e35ff1feee8355437fe8c1d1ae7fc326d3)](https://circleci.com/gh/macecchi/caronae-backend/tree/develop)

Este é o backend no Caronaê, baseado no framework Laravel, em PHP. O backend é
composto da API mobile e da área administrativa.


## Instalação

Para instalar o ambiente de desenvolvimento, você precisa ter configurado na 
sua máquina:

- PHP 7.x
- Composer
- PostgreSQL

Comece fazendo o **clone** deste repositório para seu computador e selecionando 
o diretório dele. Todos os comandos abaixo serão executados dentro desse diretório.

Verifique que o **PostgreSQL** está sendo executado e **crie uma tabela e um 
usuário** para serem usados pelo backend.

```
createuser caronae
createdb -O caronae -E utf8 caronae
```

Em seguida, copie o arquivo `.env.example` para `.env` e atualize os respectivos 
campos com as configurações de conexão com o banco. Caso você tenha usado os dados
acima para configurar o banco, basta copiar o arquivo.

```
cp .env.example .env
```

Instale as dependências do projeto utilizando o **[Composer](https://getcomposer.org)**:

```
composer install
```

Para inicializar o banco de dados local, execute o *migrate* usando o **artisan**:

```
php artisan migrate
```

Em seguida, gere uma chave que será usada para salvar informações sensíveis no 
banco de dados:

```
php artisan key:generate
```

Agora basta executar o seguinte comando para iniciar o servidor:

```
php artisan serve
```

Pronto! Você já deve conseguir acessar o projeto localmente através do endereço
mostrado ao executar o comando acima.


### Populando o banco de dados

Para criar um administrador padrão, bairros e alguns usuários e caronas de exemplo,
execute o comando abaixo:

```
php artisan db:seed
```

Pronto! Agora você já pode fazer login na área administrativa utilizando o usuário
padrão.

| E-mail    | Senha    |
|-----------|----------|
| 1@1.com   | 1234     |


## Testes

Parte do código possui cobertura de testes, que ficam dentro da pasta **tests**.

Os testes são executados em um banco de dados separado para não haver interferência
do seu ambiente local. Portanto, é necessário criar uma tabela **caronae_testing**.

Para executá-los, basta utilizar o **PHPUnit**:

```
vendor/bin/phpunit
```
