<?php

/**
 * Stores
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    collections
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
class Stores extends BaseStores
{
  
  /**
   * Genero lo slug su base nome ed indirizzo
   */
  public function getSlug(){
    return Doctrine_Inflector::urlize($this->getName() . ' ' . $this->getAddress());
  }

  /**
   * Getter per la colonna brand.
   * 
   * Viene aggiunta la stringa 'Diesel'  alle righe che 
   * contengono il valore "Kid"
   */
  public function getBrand()
  {
    $type = $this->_get('brand');
    if ($type == 'Kid' )
    {
      $type = 'Diesel ' . $type;
    }  
    return $type;
  }
  
  /**
   * Ritorna il valore da utilizzare come "type" dello store
   * 
   * Il type dello store visualizzato all'utente reale non è il type
   * vero ma una variante calcolata in base ad un array di mappa
   * 
   */
  public function getPublicType()
  {
    $typesMap = $this->getTable()->getTypesMap();
    
    $typesMap['Kid Outlet'] = 'Kid Outlet';
    $typesMap['Diesel Outlet'] = 'Diesel Outlet';
    $realType = $this->getType();
    return $typesMap[$realType];
  }
  
  public function getCity()
  {
    $city = parent::_get('city');
    return ucwords(strtolower($city));
  }
  
  public function getTypesArray() {
    $types = $this->getStoreTypes();
    $res = array();
    foreach ($types as $t) {
      $res[] = $t->getId();
    }//foreach
    return $res;
  }//getTypesArray
  
  public function getStoreNews() {
    return Doctrine_Query::create()->from('StoreNews sn') 
    ->addWhere ('sn.store_id = ?', array(self::_get('id')))
    ->orderBy('sn.created_at desc')
    ->execute();
  }
  
}