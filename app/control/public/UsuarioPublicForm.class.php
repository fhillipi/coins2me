<?php
/**
 * UsuarioPublicForm Form
 * @author  <FHILLIPI>
 */
class UsuarioPublicForm extends TPage
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        $html = new THtmlRenderer('app/resources/public.html');

        // replace the main section variables
        //$html->enableSection('main', array());
        
        //$panel = new TPanelGroup('Public!');
        //$panel->add($html);
        //$panel->style = 'margin: 100px';
        
        // add the template to the page
        //parent::add( $panel );
        
        
        
        // creates the form
        $this->form = new TQuickForm('form_Usuario');
        $this->form->class = 'tform'; // change CSS class
        $this->form = new BootstrapFormWrapper($this->form);
        $this->form->style = 'display: table;width:100%'; // change style
        
        // define the form title
        $this->form->setFormTitle('Usuario');
        


        // create the form fields
        $CODIGO = new TEntry('CODIGO');
        //$fkSYSTEM_USER = new TEntry('fkSYSTEM_USER');
        $EMAIL = new TEntry('EMAIL');
        $PASSWORD = new TEntry('PASSWORD');
        $DATA_NASCIMENTO = new TDate('DATA_NASCIMENTO');
        $STATUS = new TEntry('STATUS');
        //$PASSWORD->setValue = sha1($PASSWORD->getValue);
        //var_dump($PASSWORD->getValue);

        // add the fields
        //$this->form->addQuickField('Codigo', $CODIGO,  '50%' );
        //$this->form->addQuickField('Fksystem User', $fkSYSTEM_USER,  '50%' );
        $this->form->addQuickField('Email', $EMAIL,  '50%' );
        $this->form->addQuickField('Password', $PASSWORD->sha1($PASSWORD->getValue),  '50%' );
        $this->form->addQuickField('Data Nascimento', $DATA_NASCIMENTO,  '50%' );
        $this->form->addQuickField('Status', $STATUS,  '50%' );




        if (!empty($CODIGO))
        {
            $CODIGO->setEditable(FALSE);
        }
        
        /** samples
         $this->form->addQuickFields('Date', array($date1, new TLabel('to'), $date2)); // side by side fields
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( 100, 40 ); // set size
         **/
         
        // create the form actions
        $btn = $this->form->addQuickAction(_t('Save'), new TAction(array($this, 'onSave')), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addQuickAction(_t('New'),  new TAction(array($this, 'onClear')), 'bs:plus-sign green');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add(TPanelGroup::pack('Cadastro de Usuário', $this->form));
        //$container->add($html);
        parent::add($container);
    }

    /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        try
        {
            TTransaction::open('app'); // open a transaction
            
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
            $this->form->validate(); // validate form data
            
            $object = new Usuario;  // create an empty object
            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            // get the generated CODIGO
            $data->CODIGO = $object->CODIGO;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(TRUE);
    }
    
    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open('app'); // open a transaction
                $object = new Usuario($key); // instantiates the Active Record
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
}
