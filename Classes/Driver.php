<?php
namespace DELIVERY\Driver;
use DELIVERY\User\User;
class Driver extends User{
    public function Login($Email, $Password ){}
    public function UpdateOrderStatus($OrderID, $Status){}
}