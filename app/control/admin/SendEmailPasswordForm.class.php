     <?php
    class SendEmailPasswordForm extends TPage
    {
        protected $form; // form
        
        function __construct($param)
        {
            parent::__construct();
            $table = new TTable;
            $table->width = '100%';
            // creates the form
            $this->form = new TForm('form_send_email_pass');
            $this->form->class = 'tform';
            $this->form->style = 'max-width: 450px; margin:auto; margin-top:120px;';
            // add the notebook inside the form
            $this->form->add($table);
            // create the form fields
            $email = new TEntry('email');
            
            // define the sizes
            $email->setSize('70%', 40);
            $email->style = 'height:35px; font-size:14px;float:left;border-bottom-left-radius: 0;border-top-left-radius: 0;';
            $row=$table->addRow();
            $row->addCell( new TLabel('Resetar senha') )->colspan = 2;
            $row->class='tformtitle';
            $email->placeholder = 'user@email.com';
            $email->setLabel('Email');
            
            $email->addValidation('Email', new TEmailValidator);
            $envelope = '<span style="float:left;width:35px;margin-left:45px;height:35px;" class="input-group-addon"><span class="glyphicon glyphicon-envelope"></span></span>';
            $container1 = new TElement('div');
            $container1->add($envelope);
            $container1->add($email);
            $row=$table->addRow();
            $row->addCell($container1)->colspan = 2;
            
            // create an action button (save)
            $recuperar_button=new TButton('recovery');
            // define the button action
            $recuperar_button->setAction(new TAction(array($this, 'onReset')), _t('Send'));
            $recuperar_button->class = 'btn btn-success';
            $recuperar_button->style = 'font-size:18px;width:90%;padding:10px';
            $row=$table->addRow();
            $row->class = 'tformaction';
            $cell = $row->addCell( $recuperar_button );
            $cell->colspan = 2;
            $cell->style = 'text-align:center';
            
            $login = new TActionLink('Login', new TAction(array($this, 'onLogin')) );
            $login->style = 'font-size:16px;width:90%;padding:10px';
            
            $row = $table->addRow();
            $cell = $row->addCell( $login );
            
            $this->form->setFields(array($email, $recuperar_button));
            // add the form to the page
            parent::add($this->form);
        }
        
        public function onLogin()
        {
            AdiantiCoreApplication::gotoPage('LoginForm');
        }
        public function onReset()
        {
            try
            {
                TTransaction::open('permission');
                $data = $this->form->getData('StdClass');
                $this->form->validate();
                $user = SystemUser::newFromEmail( $data->email );
                TTransaction::close();
                // valida se existe usuário com email digitado
                if ($user)
                {
                
                    try
                    {
                        TTransaction::open('permission');
                        $object = new SystemUser($user->id);
                        $object->reset_pass = 'Y'; //parâmetro que diz que há uma requisição de reset de senha
                        $object->data_pass = time(); //variável para validação de link
                        $object->uid_pass = uniqid(rand(), true); //variável para validação de link
                        $object->store();
                        
                        // monta parâmetros GET com criptografia
                        $url = sprintf( 'id=%s&email=%s&uid=%s&key=%s',$object->id, md5($object->email), md5($object->uid_pass), md5($object->data_pass) );
                        // monta link que será enviado para o email do usuário
                        $link = ($_SERVER['HTTPS']=='on' ? "https" : "http") . '://'. $_SERVER['HTTP_HOST'] . '/index.php?class=ResetPasswordForm&'. $url;
                        
                        // pega parâmetros para envio de email
                        $prefs = SystemPreference::getAllPreferences();
                        TTransaction::close();
                        
                        $mail = new TMail;
                        $mail->setFrom($prefs['mail_from'], 'Falko ERP');
                        $mail->setSubject('Reset de senha');
                        $mail->setHtmlBody("Você solicitou o reset de senha, por favor clique no link abaixo para cadastrar sua nova senha!<br><br>
                                            Usuário: $object->login<br>
                                            <a href='$link'>Resetar senha</a><br><br>
                                            Atenciosamente,<br>
                                            <b>Falko ERP</b>");
                        $mail->addAddress($object->email);
                        $mail->SetUseSmtp();
                        $mail->SetSmtpHost($prefs['smtp_host'], $prefs['smtp_port']);
                        $mail->SetSmtpUser($prefs['smtp_user'], $prefs['smtp_pass']);
                        $mail->send();
                        
                        new TMessage('info', 'Você receberá um email com as instruções para redefinição de senha.');
                    }
                    catch (Exception $e)
                    {
                        // shows the exception error message
                        new TMessage('error', $e->getMessage());
                        
                        // undo all pending operations
                        TTransaction::rollback();
                    }
                }
                else
                {
                    throw new Exception(_t('User not found'));
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

