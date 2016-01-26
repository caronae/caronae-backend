<h1>Sistema Caronaê</h1>

<h2>Recuperação de senha</h2>

<p>Você está recebendo esse email porque requisitou uma nova senha.</p>

<a href="{{ action('Auth\PasswordController@getReset', $token) }}">
    Clique aqui para continuar a definir uma nova senha.
</a>

<p>Caso você não tenha requisitado esse email, ignore-o.</p>