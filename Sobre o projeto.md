## Sobre o projeto

Abaixo estão algumas informações importantes sobre o projeto,
que devem ser lidas para que modificações no projeto sejam
mais fáceis.

# Bibliotecas usadas

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

# Adicionando colunas novas na área administrativa

O Datatables cria a tabela baseado em um request AJAX feito ao sistema.
Veja as rotas cujas URLs terminam em ".json" para saber que rota corresponde
a que action.

Para adicionar uma nova coluna, é preciso adicionar uma tag 'td' na view,
fazer com que a action retorne os dados com esse novo campo e depois modificar
a configuração do Datatables para mostrar essa coluna.
Ver: "views/resources/users/index.blade.php" para um exemplo.

Se quer que a nova coluna seja exportada via excel, olhe as rotas que terminam
com ".excel". Veja o metodo "indexExcel" na classe "UserController" para um exemplo.

# Banco de Dados

/!\ Atenção /!\

Não altere o banco diretamente. Use o sistema de migrations presente
na Laravel para realizar mudanças. Assim, elas ficam documentadas e
podem ser facilmente replicadas no caso de fazer deploy em outros servidores.
Cuidado também ao modificar o banco via migrations e apagar os dados já presentes
acidentalmente. Sempre faça um backup antes.

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

- neighborhoods: Tabela que guarda todos os bairros do Rio de Janeiro.
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
  - myzone: Zona da cidade do Rio de Janeiro da qual o bairro em 'neighborhood' pertence.
  - neighborhood: Bairro da cidade do Rio de Janeiro para o qual está indo ou do qual está saindo.
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
            mas ainda não foi aceito ("pending"), se foi aceito(valor "accepted") ou se foi recusado(valor "??").
  - feedback: Feedback do caronista sobre a carona. Pode ser positivo(valor "good") ou negativo(valor "bad").
              Pode ser vazio caso o usuario relacionado seja motorista da carona ou o feedback não tenha sido dado.



