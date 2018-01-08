<?php
/**
 * Exchange Active Record
 * @author  <Antonny FHILLIPI>
 */
class Exchange extends TRecord
{
    const TABLENAME = 'exchange';
    const PRIMARYKEY= 'CODIGO';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $criptomoedas;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('DESCRIÇÃO');
        parent::addAttribute('URL');
        parent::addAttribute('API_PUBLIC');
        parent::addAttribute('STATUS');
    }

    
    /**
     * Method addCriptomoeda
     * Add a Criptomoeda to the Exchange
     * @param $object Instance of Criptomoeda
     */
    public function addCriptomoeda(Criptomoeda $object)
    {
        $this->criptomoedas[] = $object;
    }
    
    /**
     * Method getCriptomoedas
     * Return the Exchange' Criptomoeda's
     * @return Collection of Criptomoeda
     */
    public function getCriptomoedas()
    {
        return $this->criptomoedas;
    }

    /**
     * Reset aggregates
     */
    public function clearParts()
    {
        $this->criptomoedas = array();
    }

    /**
     * Load the object and its aggregates
     * @param $id object ID
     */
    public function load($id)
    {
    
        // load the related Criptomoeda objects
        $repository = new TRepository('CriptoAsset');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('CODIGO', '=', $id));
        $cripto_assets = $repository->load($criteria);
        if ($cripto_assets)
        {
            foreach ($cripto_assets as $cripto_asset)
            {
                $criptomoeda = new Criptomoeda( $cripto_asset->fkCRIPTOMOEDA );
                $this->addCriptomoeda($criptomoeda);
            }
        }
    
        // load the object itself
        return parent::load($id);
    }

    /**
     * Store the object and its aggregates
     */
    public function store()
    {
        // store the object itself
        parent::store();
    
        // delete the related CriptoAsset objects
        $criteria = new TCriteria;
        $criteria->add(new TFilter('fkEXCHANGE', '=', $this->id));
        $repository = new TRepository('CriptoAsset');
        $repository->delete($criteria);
        // store the related ExchangeCriptomoeda objects
        if ($this->criptomoedas)
        {
            foreach ($this->criptomoedas as $criptomoeda)
            {
                $cripto_asset = new CriptoAsset;
                $cripto_asset->fkCRIPTOMOEDA = $criptomoeda->id;
                $cripto_asset->fkEXCHANGE = $this->id;
                $cripto_asset->store();
            }
        }
    }

    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        $id = isset($id) ? $id : $this->id;
        // delete the related CriptoAsset objects
        $repository = new TRepository('CriptoAsset');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('fkEXCHANGE', '=', $id));
        $repository->delete($criteria);
        
    
        // delete the object itself
        parent::delete($id);
    }


}
