<?php
namespace DELIVERY\Order;
class Orders{
    private $OrderId;
    private $ClientID;
    private $DriverId;
    private $Address;
    private $Status;

    //COnstructor
    public function __construct($ID, $ClientID){
        $this->OrderId = $ID;
        $this->ClientID = $ClientID;
        $this->Status = 'Pending';
    }
}