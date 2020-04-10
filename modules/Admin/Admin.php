<?php

namespace modules\Admin;

use bossanova\Module\Module;

class Admin extends Module
{
    public function __construct()
    {
        $content = new \stdClass;
        $content->module_name = 'admin';
        $content->method_name = 'logo';
        $content->template_area = 'logo';
        $this->setContent($content);

        $content = new \stdClass;
        $content->module_name = 'admin';
        $content->method_name = 'menu';
        $content->template_area = 'menu';
        $this->setContent($content);

        $content = new \stdClass;
        $content->module_name = 'admin';
        $content->method_name = 'topmenu';
        $content->template_area = 'topmenu';
        $this->setContent($content);
    }


    public function __default()
    {
        $this->setView('admin');
    }

    public function logo()
    {
        return $this->loadView('logo');
    }

    public function menu()
    {
        return $this->loadView('menu');
    }

    public function topmenu()
    {
        return $this->loadView('topmenu');
    }
}