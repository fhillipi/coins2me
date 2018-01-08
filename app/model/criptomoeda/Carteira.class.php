<?php
/**
 * Carteira Active Record
 * @author  <your-name-here>
 */
class Carteira extends TRecord
{
    const TABLENAME = 'carteira';
    const PRIMARYKEY= 'CODIGO';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $usuario;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('fkUSUARIO');
        parent::addAttribute('DESCRICAO');
        parent::addAttribute('STATUS');
        
        //parent::addAttribute('fkusuario');
    }

    
    /**
     * Method set_usuario
     * Sample of usage: $carteira->usuario = $object;
     * @param $object Instance of Usuario
     */
    public function set_usuario(Usuario $object)
    {
        $this->usuario = $object;
        $this->fkUSUARIO = $object->id;
    }
    
    /**
     * Method get_usuario
     * Sample of usage: $carteira->usuario->attribute;
     * @returns Usuario instance
     */
    public function get_usuario()
    {
        // loads the associated object
        if (empty($this->usuario))
            $this->usuario = new Usuario($this->fkUSUARIO);
    
        // returns the associated object
        return $this->usuario;
    }
    


}
