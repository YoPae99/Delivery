<?php
namespace DELIVERY\Order;
class Orders{
    private $OrderID;
    private $ClientID;
    private $Status;

    //COnstructor
    public function __construct($ID, $ClientID){
        $this->OrderID = $ID;
        $this->ClientID = $ClientID;
        $this->Status = 'Pending';
    }
}