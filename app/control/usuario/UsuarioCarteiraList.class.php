<?php
/**
 * UsuarioCarteiraList Listing
 * @author  <your name here>
 */
class UsuarioCarteiraList extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $formgrid;
    private $loaded;
    private $deleteButton;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new TQuickForm('form_search_Carteira');
        $this->form->class = 'tform'; // change CSS class
        $this->form = new BootstrapFormWrapper($this->form);
        $this->form->style = 'display: table;width:100%'; // change style
        $this->form->setFormTitle('Carteira');
        

        // create the form fields
        $CODIGO = new TEntry('CODIGO');
        $fkUSUARIO = new TEntry('fkUSUARIO');
        $DESCRICAO = new TEntry('DESCRICAO');


        // add the fields
        $this->form->addQuickField('Código', $CODIGO,  '100%' );
        $this->form->addQuickField('Usuário', $fkUSUARIO,  '100%' );
        $this->form->addQuickField('Descricão', $DESCRICAO,  '100%' );

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Carteira_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addQuickAction(_t('New'),  new TAction(array('CarteiraForm', 'onEdit')), 'bs:plus-sign green');
        
        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_CODIGO = new TDataGridColumn('CODIGO', 'Código da Carteira', 'center');
        $column_fkUSUARIO = new TDataGridColumn('fkUSUARIO', 'Código do Usuário', 'center');
        $column_fkUSUARIO2 = new TDataGridColumn('usuario->EMAIL', 'Usuário', 'center');
        $column_DESCRICAO = new TDataGridColumn('DESCRICAO', 'Descricão', 'left');
        $column_STATUS = new TDataGridColumn('STATUS', 'Status', 'left');


        // add the columns to the DataGrid
        
        $this->datagrid->addColumn($column_fkUSUARIO);
        $this->datagrid->addColumn($column_fkUSUARIO2);
        $this->datagrid->addColumn($column_CODIGO);
        $this->datagrid->addColumn($column_DESCRICAO);
        $this->datagrid->addColumn($column_STATUS);

        
        // create_VIEW action
         // creates two datagrid actions
        $action_view = new TDataGridAction(array($this, 'onView'));
        $action_view->setLabel('View');
        $action_view->setImage('fa:search blue');
        //$action_view->setField('name');
        //$this->datagrid->addAction($action_view);
        
        // create EDIT action
        $action_edit = new TDataGridAction(array('CarteiraForm', 'onEdit'));
        //$action_edit->setUseButton(TRUE);
        //$action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('fa:pencil-square-o blue fa-lg');
        $action_edit->setField('CODIGO');
        $this->datagrid->addAction($action_edit);
        
        // create DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        //$action_del->setUseButton(TRUE);
        //$action_del->setButtonClass('btn btn-default');
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('fa:trash-o red fa-lg');
        $action_del->setField('CODIGO');
        $this->datagrid->addAction($action_del);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        


        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add(TPanelGroup::pack('Lista de Carteiras / Usuários', $this->form));
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        
        parent::add($container);
    }
    
    /**
     * Inline record editing
     * @param $param Array containing:
     *              key: object ID value
     *              field name: object attribute to be updated
     *              value: new attribute content 
     */
    public function onInlineEdit($param)
    {
        try
        {
            // get the parameter $key
            $field = $param['field'];
            $key   = $param['key'];
            $value = $param['value'];
            
            TTransaction::open('app'); // open a transaction with database
            $object = new Carteira($key); // instantiates the Active Record
            $object->{$field} = $value;
            $object->store(); // update the object in the database
            TTransaction::close(); // close the transaction
            
            $this->onReload($param); // reload the listing
            new TMessage('info', "Record Updated");
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Register the filter in the session
     */
    public function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters
        TSession::setValue('UsuarioCarteiraList_filter_CODIGO',   NULL);
        TSession::setValue('UsuarioCarteiraList_filter_fkUSUARIO',   NULL);
        TSession::setValue('UsuarioCarteiraList_filter_DESCRICAO',   NULL);

        if (isset($data->CODIGO) AND ($data->CODIGO)) {
            $filter = new TFilter('CODIGO', 'like', "%{$data->CODIGO}%"); // create the filter
            TSession::setValue('UsuarioCarteiraList_filter_CODIGO',   $filter); // stores the filter in the session
        }


        if (isset($data->fkUSUARIO) AND ($data->fkUSUARIO)) {
            $filter = new TFilter('fkUSUARIO', 'like', "%{$data->fkUSUARIO}%"); // create the filter
            TSession::setValue('UsuarioCarteiraList_filter_fkUSUARIO',   $filter); // stores the filter in the session
        }


        if (isset($data->DESCRICAO) AND ($data->DESCRICAO)) {
            $filter = new TFilter('DESCRICAO', 'like', "%{$data->DESCRICAO}%"); // create the filter
            TSession::setValue('UsuarioCarteiraList_filter_DESCRICAO',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('Carteira_filter_data', $data);
        
        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
    
    /**
     * Load the datagrid with data
     */
    public function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'app'
            TTransaction::open('app');
            
            // creates a repository for Carteira
            $repository = new TRepository('Carteira');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'CODIGO';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue('UsuarioCarteiraList_filter_CODIGO')) {
                $criteria->add(TSession::getValue('UsuarioCarteiraList_filter_CODIGO')); // add the session filter
            }


            if (TSession::getValue('UsuarioCarteiraList_filter_fkUSUARIO')) {
                $criteria->add(TSession::getValue('UsuarioCarteiraList_filter_fkUSUARIO')); // add the session filter
            }


            if (TSession::getValue('UsuarioCarteiraList_filter_DESCRICAO')) {
                $criteria->add(TSession::getValue('UsuarioCarteiraList_filter_DESCRICAO')); // add the session filter
            }

            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }
            
            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            
            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit
            
            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * Ask before deletion
     */
    public function onDelete($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'Delete'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
    }
    
    /**
     * Delete a record
     */
    public function Delete($param)
    {
        try
        {
            $key=$param['key']; // get the parameter $key
            TTransaction::open('app'); // open a transaction with database
            $object = new Carteira($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database
            TTransaction::close(); // close the transaction
            $this->onReload( $param ); // reload the listing
            new TMessage('info', AdiantiCoreTranslator::translate('Record deleted')); // success message
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    

    
    /**
     * method onView()
     * Executed when the user clicks at the view button
     */
    function onView($param)
    {
        // get the parameter and shows the message
        $name = $param['name'];
        new TMessage('info', "The name is : $name");
    }
    

    
    /**
     * method show()
     * Shows the page
     */
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR !(in_array($_GET['method'],  array('onReload', 'onSearch')))) )
        {
            if (func_num_args() > 0)
            {
                $this->onReload( func_get_arg(0) );
            }
            else
            {
                $this->onReload();
            }
        }
        parent::show();
    }
}
