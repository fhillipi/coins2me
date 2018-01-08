<?php
/**
 * Usuario Active Record
 * @author  <your-name-here>
 */
class Usuario extends TRecord
{
    const TABLENAME = 'usuario';
    const PRIMARYKEY= 'CODIGO';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $carteiras;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
       // parent::addAttribute('fkSYSTEM_USER');
        parent::addAttribute('EMAIL');
        parent::addAttribute('PASSWORD');
        parent::addAttribute('DATA_NASCIMENTO');
        parent::addAttribute('STATUS');
    }
    


    
    /**
     * Method addCarteira
     * Add a Carteira to the Usuario
     * @param $object Instance of Carteira
     */
    public function addCarteira(Carteira $object)
    {
        $this->carteiras[] = $object;
    }
    
    /**
     * Method getCarteiras
     * Return the Usuario' Carteira's
     * @return Collection of Carteira
     */
    public function getCarteiras()
    {
        return $this->carteiras;
    }

    /**
     * Reset aggregates
     */
    public function clearParts()
    {
        $this->carteiras = array();
    }

    /**
     * Load the object and its aggregates
     * @param $id object ID
     */
    public function load($id)
    {
    
        // load the related Carteira objects
        $repository = new TRepository('Carteira');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('fkUSUARIO', '=', $id));
        $this->carteiras = $repository->load($criteria);
    
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
    
        // delete the related Carteira objects
        $criteria = new TCriteria;
        $criteria->add(new TFilter('fkUSUARIO', '=', $this->id));
        $repository = new TRepository('Carteira');
        $repository->delete($criteria);
        // store the related Carteira objects
        if ($this->carteiras)
        {
            foreach ($this->carteiras as $carteira)
            {
                unset($carteira->id);
                $carteira->fkusuario = $this->id;
                $carteira->store();
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
        // delete the related Carteira objects
        $repository = new TRepository('Carteira');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('fkUSUARIO', '=', $id));
        $repository->delete($criteria);
        
    
        // delete the object itself
        parent::delete($id);
    }


}
