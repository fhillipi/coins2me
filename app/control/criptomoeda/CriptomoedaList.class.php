<?php
/**
 * CriptomoedaList Listing
 * @author  <your name here>
 */
class CriptomoedaList extends TPage
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
        $this->form = new TQuickForm('form_search_Criptomoeda');
        $this->form->class = 'tform'; // change CSS class
        $this->form = new BootstrapFormWrapper($this->form);
        $this->form->style = 'display: table;width:100%'; // change style
        $this->form->setFormTitle('Criptomoeda');


        // create the form fields
        $CODIGO = new TEntry('CODIGO');
        $SIGLA = new TEntry('SIGLA');
        $NOME = new TEntry('NOME');
        $UTF = new TEntry('UTF');


        // add the fields
        $this->form->addQuickField('Código', $CODIGO,  '100%' );
        $this->form->addQuickField('Sigla', $SIGLA,  '100%' );
        $this->form->addQuickField('Nome', $NOME,  '100%' );
        //$this->form->addQuickField('Utf', $UTF,  '100%' );


        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Criptomoeda_filter_data') );

        // add the search form actions
        $btn = $this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addQuickAction(_t('New'),  new TAction(array('CriptomoedaForm', 'onEdit')), 'bs:plus-sign green');

        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');


        // creates the datagrid columns
        $column_check = new TDataGridColumn('check', '', 'left');
        $column_CODIGO = new TDataGridColumn('CODIGO', 'Código', 'left');
        $column_SIGLA = new TDataGridColumn('SIGLA', 'Sigla', 'left');
        $column_NOME = new TDataGridColumn('NOME', 'Nome', 'left');
        $column_UTF = new TDataGridColumn('UTF', 'UTF', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_check);
        $this->datagrid->addColumn($column_CODIGO);
        $this->datagrid->addColumn($column_SIGLA);
        $this->datagrid->addColumn($column_NOME);
        //$this->datagrid->addColumn($column_UTF);


        // create EDIT action
        $action_edit = new TDataGridAction(array('CriptomoedaForm', 'onEdit'));
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

        $this->datagrid->disableDefaultClick();

        // put datagrid inside a form
        $this->formgrid = new TForm;
        $this->formgrid->add($this->datagrid);

        // creates the delete collection button
        $this->deleteButton = new TButton('delete_collection');
        $this->deleteButton->setAction(new TAction(array($this, 'onDeleteCollection')), AdiantiCoreTranslator::translate('Delete selected'));
        $this->deleteButton->setImage('fa:remove red');
        $this->formgrid->addField($this->deleteButton);

        $gridpack = new TVBox;
        $gridpack->style = 'width: 100%';
        $gridpack->add($this->formgrid);
        $gridpack->add($this->deleteButton)->style = 'background:whiteSmoke;border:1px solid #cccccc; padding: 3px;padding: 5px;';

        $this->transformCallback = array($this, 'onBeforeLoad');


        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add(TPanelGroup::pack('CriptoMoedas', $this->form));
        $container->add($gridpack);

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
            $object = new Criptomoeda($key); // instantiates the Active Record
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
        TSession::setValue('CriptomoedaList_filter_CODIGO',   NULL);
        TSession::setValue('CriptomoedaList_filter_SIGLA',   NULL);
        TSession::setValue('CriptomoedaList_filter_NOME',   NULL);
        TSession::setValue('CriptomoedaList_filter_UTF',   NULL);

        if (isset($data->CODIGO) AND ($data->CODIGO)) {
            $filter = new TFilter('CODIGO', 'like', "%{$data->CODIGO}%"); // create the filter
            TSession::setValue('CriptomoedaList_filter_CODIGO',   $filter); // stores the filter in the session
        }


        if (isset($data->SIGLA) AND ($data->SIGLA)) {
            $filter = new TFilter('SIGLA', 'like', "%{$data->SIGLA}%"); // create the filter
            TSession::setValue('CriptomoedaList_filter_SIGLA',   $filter); // stores the filter in the session
        }


        if (isset($data->NOME) AND ($data->NOME)) {
            $filter = new TFilter('NOME', 'like', "%{$data->NOME}%"); // create the filter
            TSession::setValue('CriptomoedaList_filter_NOME',   $filter); // stores the filter in the session
        }


        if (isset($data->UTF) AND ($data->UTF)) {
            $filter = new TFilter('UTF', 'like', "%{$data->UTF}%"); // create the filter
            TSession::setValue('CriptomoedaList_filter_UTF',   $filter); // stores the filter in the session
        }


        // fill the form with data again
        $this->form->setData($data);

        // keep the search data in the session
        TSession::setValue('Criptomoeda_filter_data', $data);

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

            // creates a repository for Criptomoeda
            $repository = new TRepository('Criptomoeda');
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


            if (TSession::getValue('CriptomoedaList_filter_CODIGO')) {
                $criteria->add(TSession::getValue('CriptomoedaList_filter_CODIGO')); // add the session filter
            }


            if (TSession::getValue('CriptomoedaList_filter_SIGLA')) {
                $criteria->add(TSession::getValue('CriptomoedaList_filter_SIGLA')); // add the session filter
            }


            if (TSession::getValue('CriptomoedaList_filter_NOME')) {
                $criteria->add(TSession::getValue('CriptomoedaList_filter_NOME')); // add the session filter
            }


            if (TSession::getValue('CriptomoedaList_filter_UTF')) {
                $criteria->add(TSession::getValue('CriptomoedaList_filter_UTF')); // add the session filter
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
            $object = new Criptomoeda($key, FALSE); // instantiates the Active Record
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
     * Ask before delete record collection
     */
    public function onDeleteCollection( $param )
    {
        $data = $this->formgrid->getData(); // get selected records from datagrid
        $this->formgrid->setData($data); // keep form filled

        if ($data)
        {
            $selected = array();

            // get the record id's
            foreach ($data as $index => $check)
            {
                if ($check == 'on')
                {
                    $selected[] = substr($index,5);
                }
            }

            if ($selected)
            {
                // encode record id's as json
                $param['selected'] = json_encode($selected);

                // define the delete action
                $action = new TAction(array($this, 'deleteCollection'));
                $action->setParameters($param); // pass the key parameter ahead

                // shows a dialog to the user
                new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
            }
        }
    }

    /**
     * method deleteCollection()
     * Delete many records
     */
    public function deleteCollection($param)
    {
        // decode json with record id's
        $selected = json_decode($param['selected']);

        try
        {
            TTransaction::open('app');
            if ($selected)
            {
                // delete each record from collection
                foreach ($selected as $id)
                {
                    $object = new Criptomoeda;
                    $object->delete( $id );
                }
                $posAction = new TAction(array($this, 'onReload'));
                $posAction->setParameters( $param );
                new TMessage('info', AdiantiCoreTranslator::translate('Records deleted'), $posAction);
            }
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }


    /**
     * Transform datagrid objects
     * Create the checkbutton as datagrid element
     */
    public function onBeforeLoad($objects, $param)
    {
        // update the action parameters to pass the current page to action
        // without this, the action will only work for the first page
        $deleteAction = $this->deleteButton->getAction();
        $deleteAction->setParameters($param); // important!

        $gridfields = array( $this->deleteButton );

        foreach ($objects as $object)
        {
            $object->check = new TCheckButton('check' . $object->CODIGO);
            $object->check->setIndexValue('on');
            $gridfields[] = $object->check; // important
        }

        $this->formgrid->setFields($gridfields);
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
