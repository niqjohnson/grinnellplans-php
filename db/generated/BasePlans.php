<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BasePlans extends Doctrine_Record
{
  public function setTableDefinition()
  {
    $this->setTableName('plans');
    $this->hasColumn('id', 'integer', 20, array('type' => 'integer', 'autoincrement' => true, 'primary' => true, 'length' => '20'));
    $this->hasColumn('user_id', 'integer', 4, array('type' => 'integer', 'length' => '4'));
    $this->hasColumn('plan', 'string', 2147483647, array('type' => 'string', 'length' => '2147483647'));
  }

}