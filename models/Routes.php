<?php

/**
 * (c) 2013 Bossanova PHP Framework
 * http://www.bossanova-framework.com
 *
 * @author: Paul Hodel <paul.hodel@gmail.com>
 * @description: Model
 */
namespace models;

use bossanova\Model\Model;

class Routes extends Model
{
    // Table configuration
    public $config = array(
        'tableName' => 'routes',
        'primaryKey' => 'route_id',
        'sequence' => 'routes_route_id_seq',
        'recordId' => 0
    );

    /**
     * Return the record as an array
     *
     * @param  integer $id
     * @return object
     */
    public function getByRoute($url)
    {
        $url = $this->database->bind($url);

        $result = $this->database->table('routes')
            ->argument(1, "route", $url)
            ->execute();

        return $this->database->fetch_assoc($result);
    }
}
