<?php

return [

    /*
    |--------------------------------------------------------------------------
    | System Messages
    |--------------------------------------------------------------------------
    */

    'register' => [
        'error' => 'Houve um erro na tentativa de registro, tente novamente mais tarde.',
        'success' => 'Mais um passo! Para confirmar sua conta, abra o e-mail que acabamos de enviar para você e clique em "confirmar conta"',
        'confirm' => 'Sua conta foi confirmada com sucesso.',
        'confirm_error' => 'Seu usuário já foi ativado anteriormente.',
    ],
    'auth' => [
        'invalid_data' => 'Dados inválidos.',
        'invalid_data_try_again' => 'Dados inválidos. Tente novamente.',
        'unverified_user' => 'Email ainda não verificado, você deve confirmar sua conta para logar.',
        'passport_error' => 'Problemas no servidor de autenticação (passport). Tente Novamente mais tarde.',
        'logout_success' => 'Você deslogou com sucesso.',
        'invalid_code' => 'O código de resgate é inválido ou já foi usado.',
        'current_password_not_match' => 'A senha atual informada é inválida.',
        'password_same_as_current' => 'A senha informada não pode ser igual a anterior.',
    ],
    'user_forgot_password' => [
        'success' => 'Foi enviado um email de recuperação.',
    ],
    'user_reset_password' => [
        'success' => 'Sua senha foi criada com sucesso.',
        'mail_token_invalid' => 'O token enviado é inválido.'
    ],
    'unauthorized' => 'Acesso Não Autorizado.',
    'created_success' => 'Criado com Sucesso.',
    'updated_success' => 'Atualizado com Sucesso.',
    'deleted_success' => 'Excluído com Sucesso.',
    'restore_success' => 'Restaurado com Sucesso.',
    'category_delete_not_allowed' => "Não é possível excluir, existem categorias relacionadas a este item.",
    'redeem_code_delete_not_allowed' => "Não é possível excluir, existe um usuário relacionado a este item.",
    'language_not_accepted' => 'Linguagem não permitida no sistema.',
    'language_required' => 'Linguagem obrigatória no sistema não informada:',
];
