<?php
/**
 * This is a file for the TestTrait.
 *
 * @package System
 */

namespace SmartFactory;

/**
* This is a test trait.
*/
trait TestTrait
{
    /**
     * This is a method sayHello.
     *
     * @param int $hello
     * Hello mode.
     *
     * @return void
     */
    public function sayHello($hello)
    {
        echo 'Hello!';
    }
    
    /**
     * This is a method sayGoodbye.
     *
     * @return void
     */
    public function sayGoodbye()
    {
        echo 'Good bye!';
    }
}
