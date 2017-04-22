<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backpack Crud Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the CRUD interface.
    | You are free to change them to anything
    | you want to customize your views to better match your application.
    |
    */

    // Create form
    'add'                 => 'Adicionar',
    'back_to_all'         => 'Voltar para todos ',
    'cancel'              => 'Cancelar',
    'add_a_new'           => 'Criar ',
    'user' => 'usuário',

        // Create form - advanced options
        'after_saving'            => 'Após a criação',
        'go_to_the_table_view'    => 'ir para a lista',
        'let_me_add_another_item' => 'adicionar um novo item',
        'edit_the_new_item'       => 'editar o novo item',

    // Edit form
    'edit'                 => 'Editar',
    'save'                 => 'Salvar',

    // Revisions
    'revisions'            => 'Revisions',
    'no_revisions'         => 'No revisions found',
    'created_this'          => 'created this',
    'changed_the'          => 'changed the',
    'restore_this_value'   => 'Restore this value',
    'from'                 => 'from',
    'to'                   => 'to',
    'undo'                 => 'Undo',
    'revision_restored'    => 'Revision successfully restored',

    // CRUD table view
    'all'                       => 'Todos ',
    'in_the_database'           => 'no banco de dados',
    'list'                      => 'Lista',
    'actions'                   => 'Ações',
    'preview'                   => 'Preview',
    'delete'                    => 'Deletar',
    'admin'                     => 'Admin',
    'details_row'               => 'This is the details row. Modify as you please.',
    'details_row_loading_error' => 'There was an error loading the details. Please retry.',

        // Confirmation messages and bubbles
        'delete_confirm'                              => 'Você tem certeza que deseja deletar este item?',
        'delete_confirmation_title'                   => 'Item Deletado',
        'delete_confirmation_message'                 => 'O item foi deletado com sucesso.',
        'delete_confirmation_not_title'               => 'NÃO deletado',
        'delete_confirmation_not_message'             => 'Houve um erro. O item pode não ter sido deletado.',
        'delete_confirmation_not_deleted_title'       => 'Não deleted',
        'delete_confirmation_not_deleted_message'     => 'Nada feito. O item está seguro.',

        // DataTables translation
        'emptyTable'     => 'Não há dados disponíveis na tabela',
        'info'           => 'Mostrando registros de _START_ a _END_ de _TOTAL_',
        'infoEmpty'      => 'Mostrando registros de 0 a 0 de 0',
        'infoFiltered'   => '(filtered from _MAX_ total entries)',
        'infoPostFix'    => '',
        'thousands'      => ',',
        'lengthMenu'     => '_MENU_ registros por página',
        'loadingRecords' => 'Carregando...',
        'processing'     => 'Processando...',
        'search'         => 'Pesquisa: ',
        'zeroRecords'    => 'Nenhuma correspondência encontrada',
        'paginate'       => [
            'first'    => 'Primeiro',
            'last'     => 'Último',
            'next'     => 'Próximo',
            'previous' => 'Anterior',
        ],
        'aria' => [
            'sortAscending'  => ': activate to sort column ascending',
            'sortDescending' => ': activate to sort column descending',
        ],

    // global crud - errors
    'unauthorized_access' => 'Unauthorized access - you do not have the necessary permissions to see this page.',
    'please_fix' => 'Please fix the following errors:',

    // global crud - success / error notification bubbles
    'insert_success' => 'O item foi adicionado com sucesso.',
    'update_success' => 'O item foi alterado com sucesso.',

    // CRUD reorder view
    'reorder'                      => 'Reorder',
    'reorder_text'                 => 'Use drag&drop to reorder.',
    'reorder_success_title'        => 'Done',
    'reorder_success_message'      => 'Your order has been saved.',
    'reorder_error_title'          => 'Error',
    'reorder_error_message'        => 'Your order has not been saved.',

    // CRUD yes/no
    'yes' => 'Sim',
    'no' => 'Não',

    // Fields
    'browse_uploads' => 'Browse uploads',
    'clear' => 'Clear',
    'page_link' => 'Page link',
    'page_link_placeholder' => 'http://example.com/your-desired-page',
    'internal_link' => 'Internal link',
    'internal_link_placeholder' => 'Internal slug. Ex: \'admin/page\' (no quotes) for \':url\'',
    'external_link' => 'External link',
    'choose_file' => 'Choose file',

    //Table field
    'table_cant_add' => 'Cannot add new :entity',
    'table_max_reached' => 'Maximum number of :max reached',

];
