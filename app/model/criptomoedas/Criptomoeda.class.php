<?php
/**
 * Criptomoeda Active Record
 * @author  <your-name-here>
 */
class Criptomoeda extends TRecord
{
    const TABLENAME = 'criptomoeda';
    const PRIMARYKEY= 'CODIGO';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('SIGLA');
        parent::addAttribute('NOME');
        parent::addAttribute('UTF');
    }


}
