jQuery(document).ready(function($) {
  register_ban_button_action();

  // make the button work on subsequent result pages
  $('#crudTable').on('draw.dt', function () {
     register_ban_button_action();
  }).dataTable();

  function register_ban_button_action() {
    $("[data-button-type=ban], [data-button-type=unban]").unbind('click');
    $("[data-button-type=ban], [data-button-type=unban]").click(function(e) {
      e.preventDefault();
      var ban_button = $(this);
      var ban_url = $(this).attr('href');
      var ban_type = $(this).data('button-type');
      var ban_action = (ban_type == 'ban') ? 'banido' : 'desbanido';

      var confirm_message;
      if (ban_type == 'ban') {
        confirm_message = 'Você tem certeza que deseja banir este usuário? Todas as caronas relacionadas serão excluídas permanentemente.';
      } else {
        confirm_message = 'Você tem certeza que deseja desbanir este usuário?';
      }

      if (confirm(confirm_message)) {
          $.ajax({
              url: ban_url,
              type: 'POST',
              success: function(result) {
                  // Show an alert with the result
                  new PNotify({
                      title: "Usuário " + ban_action,
                      text: "O usuário foi " + ban_action + " com sucesso.",
                      type: "success"
                  });

                  var new_label, new_url, new_type;
                  if (ban_type == 'ban') {
                    new_label = ban_button.html().replace('Banir', 'Desbanir');
                    new_url = ban_url.replace('ban', 'unban');
                    new_type = 'unban';
                  } else {
                    new_label = ban_button.html().replace('Desbanir', 'Banir');
                    new_url = ban_url.replace('unban', 'ban');
                    new_type = 'ban';
                  }
                  
                  ban_button.html(new_label);
                  ban_button.attr('href', new_url);
                  ban_button.data('button-type', new_type);
                  // delete the row from the table
                  //ban_button.parentsUntil('tr').parent().remove();
              },
              error: function(result) {
                  // Show an alert with the result
                  new PNotify({
                      title: "NÃO " + ban_action,
                      text: "Houve um erro. O usuário pode não ter sido " + ban_action + ".",
                      type: "warning"
                  });
              }
          });
      }
    });
  }

});
