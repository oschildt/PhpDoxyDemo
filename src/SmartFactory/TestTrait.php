<?php
namespace SmartFactory;

/**
* TestTrait for handling the XML API requests.
*/
trait TestTrait
{
    /**
     * This is a funct sayHello
     *
     * @param int $hello
     * Des
     *
     * @return void
     */
    public function sayHello($hello)
    {
        parent::sayHello();
        echo 'World!';
    }

    /**
     * This is a funct 2 sayGoodbuy
     *
     * @param int $buy
     * Des
     *
     * @return void
     */
    public function sayGoodbuy($buy)
    {
        parent::sayGoodbuy();
        echo 'World!';
    }
}
