     <?php
    class ResetPasswordForm extends TPage
    {
        protected $form; // form
        
        /**
         * Class constructor
         * Creates the page and the registration form
         */
        function __construct($param)
        {
            parent::__construct();
            
            $valido = TRUE;
            if (isset($_GET['id']))
            {
                $id = $_GET['id'];
                $email = $_GET['email'];
                $uid = $_GET['uid'];
                $data = $_GET['key'];
                
                // checa se id do usuário existe
                TTransaction::open('permission');
                $user = new SystemUser($id);
                TTransaction::close();
            
                if ($user instanceof SystemUser)
                {
                    if ($user->active == 'N') //não permite que usuário inativo altera a senha
                    {
                        $valido = FALSE;
                    }
                    else if ($user->reset_pass != 'Y') //valida se há uma requisição de reset de senha
                    {
                        $valido = FALSE;
                    }
                    else if (md5($user->email) != $email) //valida se email criptografado é igual ao do link acessado
                    {
                        $valido = FALSE;
                    }
                    else if (md5($user->uid_pass) != $uid) //valida se uid criptografado é igual ao do link acessado
                    {
                        $valido = FALSE;
                    }
                    else if (md5($user->data_pass) != $data) //valida se email criptografado é igual ao do link acessado
                    {
                        $valido = FALSE;
                    }
                }
                else
                {
                    $valido = FALSE;
                }
            }
            // se não é valido, leva usuário para tela de login
            if (!$valido && isset($_GET['id']))
            {
                new TMessage('error', _t('User not found'), new TAction(array($this, 'onLogin')) );
                return false;
            }
            $table = new TTable;
            $table->width = '100%';
            // creates the form
            $this->form = new TForm('form_reset_password');
            $this->form->class = 'tform';
            $this->form->style = 'max-width: 450px; margin:auto; margin-top:120px;';
            // add the notebook inside the form
            $this->form->add($table);
            // create the form fields
            $user_id = new THidden('user_id');
            $user_id->setValue($user->id);
            $senha = new TPassword('senha');
            $senha_confirm = new TPassword('senha_confirm');
            
            $senha->placeholder = 'Nova senha';
            $senha_confirm->placeholder = 'Digite novamente';
            
            // define the sizes
            $senha->setSize('70%', 40);
            $senha_confirm->setSize('70%', 40);
            $senha->style = 'height:35px; font-size:14px;float:left;border-bottom-left-radius: 0;border-top-left-radius: 0;';
            $senha_confirm->style = 'height:35px; font-size:14px;float:left;border-bottom-left-radius: 0;border-top-left-radius: 0;';
            $row=$table->addRow();
            $row->addCell( new TLabel('Nova senha') )->colspan = 2;
            $row->class='tformtitle';
            
            $senha->setLabel('Senha');
            $senha_confirm->setLabel('Senha_confirm');
            
            $senha->addValidation('Senha', new TRequiredValidator);
            $senha_confirm->addValidation('Senha_confirm', new TRequiredValidator);
            $locker = '<span style="float:left;width:35px;margin-left:45px;height:35px;" class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>';
            $container1 = new TElement('div');
            $container1->add($locker);
            $container1->add($senha);
            $container1->add($user_id);
            
            $container2 = new TElement('div');
            $container2->add($locker);
            $container2->add($senha_confirm);
            $row=$table->addRow();
            $row->addCell($container1)->colspan = 2;
            $row=$table->addRow();
            $row->addCell($container2)->colspan = 2;
            
            // create an action button (save)
            $recuperar_button=new TButton('recovery');
            // define the button action
            $recuperar_button->setAction(new TAction(array($this, 'onReset')), _t('Save'));
            $recuperar_button->class = 'btn btn-success';
            $recuperar_button->style = 'font-size:18px;width:90%;padding:10px';
            $row=$table->addRow();
            $row->class = 'tformaction';
            $cell = $row->addCell( $recuperar_button );
            $cell->colspan = 2;
            $cell->style = 'text-align:center';
            
            $this->form->setFields(array($senha, $senha_confirm, $user_id, $recuperar_button));
            // add the form to the page
            parent::add($this->form);
        }
        
        public function onLogin()
        {
            TApplication::loadPage('LoginForm', '', $_REQUEST);
        }
        /**
         * Reset password
         */
        public function onReset()
        {
            try
            {
                $data = $this->form->getData('StdClass');
                $this->form->validate();
                //valida se senha e senha de confirmação foram digitadas iguais
                if ($data->senha != $data->senha_confirm)
                {
                    new TMessage('error', 'Senhas não conferem, por favor digite as senhas iguais.');
                }
                else
                {
                    // se passou pelas validações, instancia o usuário
                    TTransaction::open('permission');
                    $object = new SystemUser($data->user_id);
                    if ($object)
                    {
                        // grava nova senha e limpa os outros campos, assim é garantido que o link é válido para apenas uma utilização
                        $object->password = password_hash($data->senha, PASSWORD_DEFAULT);
                        $object->reset_pass = NULL;
                        $object->data_pass = NULL;
                        $object->uid_pass = NULL;
                        $object->store();
                        
                        new TMessage('info', 'Senha alterada com sucesso!', new TAction(array($this, 'onLogin')) );
                    }
                    else
                    {
                        new TMessage('error', _t('User not found'), new TAction(array($this, 'onLogin')) );
                    }
                    TTransaction::close();
                }
            }
            catch (Exception $e)
            {
                new TMessage('error',$e->getMessage());
                TTransaction::rollback();
            }
        }
    }
    ?> 

