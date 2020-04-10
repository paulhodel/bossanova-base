<?php

namespace tests\modules\Admin;

use bossanova\Test\Test;

use modules\Admin\Admin;

class AdminTest extends Test
{
    protected $stack;

    protected function setUp() : void
    {
        $this->stack = new Admin;
    }

    public function testMenu()
    {
        $config = $this->stack->getConfiguration();

        $value = $config['extra_config'][1]->method_name == 'menu' ? true : false;

        $this->assertTrue($value);
    }
}