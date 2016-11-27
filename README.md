# Caronaê - Backend

[![CircleCI](https://circleci.com/gh/macecchi/caronae-backend/tree/develop.svg?style=svg&circle-token=9c47c2e35ff1feee8355437fe8c1d1ae7fc326d3)](https://circleci.com/gh/macecchi/caronae-backend/tree/develop)

Este é o backend no Caronaê, baseado no framework Laravel, em PHP. O backend é
composto da API mobile e da área administrativa.

Abaixo estão algumas informações importantes sobre o projeto,
que devem ser lidas para que modificações no projeto sejam
mais fáceis.


## Instalação

Para instalar o ambiente de desenvolvimento, você precisa ter configurado na 
sua máquina:

- PHP 5.6 ou superior
- PostgreSQL

Outras configurações também são suportadas, porém é recomendado utilizar as 
configurações acima, que são similares às do servidor de produção.

Comece fazendo o clone do repositório do backend (este repositório) para seu
computador e selecionando o diretório dele. Todos os comandos abaixo serão
executados dentro do diretório do backend.

Verifique que o banco de dados está sendo executado e crie uma tabela e um 
usuário para serem usados pelo backend. Em seguida, copie o arquivo `.env.example` 
para `.env` e atualize os respectivos campos com as configurações de conexão com
o banco de dados.

Caso vá testar notificações, configure também no arquivo `.env` a constante 
`GCM_API_KEY`, que é a chave de acesso ao projeto do GCM, utilizado para envio 
das notificações.

Agora, você precisará configurar as dependências do projeto, que podem ser 
instaladas usando o **Composer**:

```
composer install
```

Para executar a configuração do banco de dados, que está versionado na 
forma de migrations, é necessário executar o *migrate* usando o **Artisan**:

```
php artisan migrate
```

Uma vez configuradas as dependências, banco de dados e as configurações do projeto,
execute o seguinte comando para iniciar o servidor:

```
php artisan serve
```

Pronto! Você já deve conseguir acessar o projeto localmente através do endereço
mostrado ao executar o comando acima.


### Populando o banco de dados

Apesar de o servidor já estar funcionando, o banco de dados encontra-se inicialmente
vazio, ou seja, não há sequer um administrador cadastrado para acessar a área 
administrativa.

Para popular o banco, execute o comando abaixo, que irá criar um administrador padrão,
cadastrar as configurações dos bairros e criar alguns usuários de exemplo:

```
php artisan db:seed
```

Pronto! Agora você já pode fazer login na área administrativa utilizando o usuário
padrão.

| E-mail    | Senha    |
|-----------|----------|
| 1@1.com   | 1234     |



## Testes

Parte do backend possui testes que garantem o correto funcionamento de sistema, 
que ficam dentro da pasta **tests**.

Para executá-los, basta utilizar o **PHPUnit**, que vem instalado como uma das dependências do projeto. Basta executar o comando `vendor/bin/phpunit`.


## Bibliotecas usadas

Abaixo seguem as bibliotecas mais importantes usadas nesse projeto:

- Bootstrap 3 (http://getbootstrap.com/)
  Para o front-end da parte administrativa.

- Laravel 5.1 LTS (https://laravel.com/docs/5.1)
  Framework usada no back-end.

- Datatables 1.10.10 (https://datatables.net/manual/index)
  Para implementar todas as tabelas da área administrativa.

- Laravel Excel 2.1 (http://www.maatwebsite.nl/laravel-excel/docs)
  Usada para a exportação das tabelas nos formatos ".xlsx" e ".csv".

- JWT 0.5 (https://github.com/tymondesigns/jwt-auth)
  Usada para realizar autenticação pelos apps mobile.

## Adicionando colunas novas na área administrativa

O Datatables cria a tabela baseado em um request AJAX feito ao sistema.
Veja as rotas cujas URLs terminam em ".json" para saber que rota corresponde
a que action.

Para adicionar uma nova coluna, é preciso adicionar uma tag 'td' na view,
fazer com que a action retorne os dados com esse novo campo e depois modificar
a configuração do Datatables para mostrar essa coluna.
Ver: "views/resources/users/index.blade.php" para um exemplo.

Se quer que a nova coluna seja exportada via excel, olhe as rotas que terminam
com ".excel". Veja o metodo "indexExcel" na classe "UserController" para um exemplo.


## Banco de Dados

**Atenção!**

Não altere o banco diretamente. Use o sistema de migrations presente
no Laravel para realizar mudanças. Assim, elas ficam documentadas e
podem ser facilmente replicadas no caso de fazer deploy em outros servidores.

Cuidado também ao modificar o banco via migrations e apagar os dados já presentes
acidentalmente. Sempre faça um backup antes e verifique antes de comittar 
qualquer modificação se não está sobrescrevendo outras migrations.

--

A seguir segue a documentação do banco de dados:

- admin: Tabela que contém os usuários que podem logar na área administrativa.
  - id: id do usuario.
  - name: Nome do usuario.
  - email: Email do usuario.
  - password: Senha do usuario. Encriptada com o algoritmo Bcrypt.
  - remember_token: Não é usado. Vem por padrão no Laravel.
  - updated_at: Não é usado. Vem por padrão no Laravel.
  - created_at: Não é usado. Vem por padrão no Laravel.

- migrations: Tabela usada pelo sistema de migrations do Laravel. Não mexer.

- neighborhoods: Tabela que guarda todos os bairros do Rio de Janeiro. Ela contém apenas
                 os bairros conhecidos pelo app. É possível que nas caronas existam bairros/zonas
                 que não estejam listadas aqui.
  - id: id do bairro.
  - name: Nome do bairro.
  - zone: Zona da cidade do Rio de Janeiro a qual o bairro pertence.
  - distance: Distância média do bairro até o Fundão.

- password_resets: Tabela usada pelo sistema de recuperção de senhas do Laravel. Não mexer.

- users: Tabela que contém os usuários dos aplicativos do Caronae.
  - id: id do usuario.
  - name: Nome do usuario.
  - profile: Tipo de membro da comunidade acadêmica(Ex: aluno, professor, funcionário, etc..).
  - course: Curso que o usuario está fazendo na UFRJ.
  - phone_number: Numero de telefone do usuario.
  - email: Email do usuario.
  - car_owner: Se possui carro ou não.
  - car_model: Modelo do carro que possui.
  - car_color: Cor do carro que possui.
  - car_plate: Numero da placa do carro que possui.
  - token: Usado para login de usuario via app.
  - remember_token: ??
  - created_at: ??
  - updated_at: ??
  - location: ??
  - gcm_token: ??
  - face_id: ??
  - profile_pic_url: ??
  - face_token: ??
  - deleted_at: Se null, o usuario está ativo. Se possui uma data, o usuario foi bloqueado a partir dessa data.

- rides: Tabela que contém todas as caronas.
  - id: Id da carona.
  - myzone: Zona da cidade do Rio de Janeiro da qual o bairro em 'neighborhood' pertence. Pode conter
            valores que não estão listados na tabela 'neighborhoods'.
  - neighborhood: Bairro da cidade do Rio de Janeiro para o qual está indo ou do qual está saindo. Pode conter
                  valores que não estão listados na tabela 'neighborhoods'.
  - going: TRUE, se está indo para o fundão. FALSE, se está saindo.
  - place: ??
  - route: Descrição livre dos pontos por onde essa carona passa.
  - routine_id: ??
  - hub: Lugar no fundão para o qual está indo ou do qual está saindo.
  - slots: Quantidade de vagas disponiveis na carona.
  - mytime: Horário em que a carona vai acontecer.
  - mydate: Data em que a carona vai acontecer.
  - description: Informações gerais sobre a carona.
  - created_at: ??
  - updated_at: ??
  - week_days: ??
  - repeats_until: ??
  - done: Se a carona já aconteceu ou não. O seu preenchimento é feito quando o motorista diz que a carona aconteceu, e não automaticamente.
  - deleted_at: ??

- ride_user: Tabela de relacionamento entre usuarios e caronas.
  - id: Id da relação.
  - user_id: Id do usuario relacionado.
  - ride_id: Id da carona relacionada.
  - created_at: ??
  - updated_at: ??
  - status: Determina se o usuario relacionado é motorista da carona(valor "driver"), se pediu para entrar na carona
            mas ainda não foi aceito ("pending"), se foi aceito(valor "accepted"), se foi aceito mas saiu(valor "quit")
            ou se foi recusado(valor "refused").
  - feedback: Feedback do caronista sobre a carona. Pode ser positivo(valor "good") ou negativo(valor "bad").
              Pode ser vazio caso o usuario relacionado seja motorista da carona ou o feedback não tenha sido dado.



