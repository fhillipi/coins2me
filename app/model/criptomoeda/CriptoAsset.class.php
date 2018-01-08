<?php
/**
 * ExchangeCriptomoeda Active Record
 * @author  <Antonny FHILLIPI>
 */
class CriptoAsset extends TRecord
{
    const TABLENAME = 'cripto_asset';
    const PRIMARYKEY= 'CODIGO';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('fkCRIPTOMOEDA');
        parent::addAttribute('fkEXCHANGE');
    }


}
