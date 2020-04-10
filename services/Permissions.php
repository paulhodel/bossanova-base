<?php

/**
 * (c) 2013 Bossanova PHP Framework 5
 * http://github.com/paulhodel/bossanova
 *
 * @author: Paul Hodel <paul.hodel@gmail.com>
 * @description: Permissions Services
 */
namespace services;

use bossanova\Services\Services;
use bossanova\Common\Ident;

class Permissions extends Services
{
    use Ident;

    public $hierarchy = [ // TODO: resolver isso de vez
        1 => 'Diamond',
        2 => 'Gold',
        3 => 'Silver',
        4 => 'Bronze',
        5 => 'Copper',
        6 => 'None'
    ];

    /**
     * Select
     *
     * @param  integer $user_id
     * @return array   $data
     */
    public function select($id)
    {
        $data = $this->model->select($id);

        return $data;
    }

    /**
     * Insert
     *
     * @param  array  $row
     * @return array  $data
     */
    public function insert($row)
    {
        // Only can create groups with the same or better hierarchy
        $cur_user_permission_hierarchy = $this->model->getHierarchy($this->getGroup());
        $new_user_permission_hierarchy = $row['permission_order'];

        if ($cur_user_permission_hierarchy <= $new_user_permission_hierarchy) {

        }
        $row['permission_routes'] = json_encode($row['permission_routes']);

        $data = $this->model->column($row)->insert();

        if ($data) {
            $data = [
                'data' => $data,
                'message' => '^^[Sucessfully saved]^^'
            ];
        } else {
            $data = [
                'error' => 1,
                'message' => '^^[It was not possilble to save this record]^^: ' . $this->model->getError()
            ];
        }

        return $data;
    }

    /**
     * Update
     *
     * @param  int $id
     * @param  array  $row
     * @return array  $data
     */
    public function update($id, $row)
    {
        $row['permission_routes'] = json_encode($row['permission_routes']);

        $data = $this->model->column($row)->update($id);

        if ($data) {
            $data = [
                'data' => $data,
                'message' => '^^[Sucessfully saved]^^'
            ];
        } else {
            $data = [
                'error' => 1,
                'message' => '^^[It was not possilble to save this record]^^: ' . $this->model->getError()
            ];
        }

        return $data;
    }

    /**
     * Delete
     *
     * @param  integer $user_id
     * @return array   $data
     */
    public function delete($id)
    {
        $data = $this->model->delete($id);

        if ($data) {
            $data = [
                'data' => $data,
                'message' => '^^[Sucessfully deleted]^^'
            ];
        } else {
            $data = [
                'error' => 1,
                'message' => '^^[It was not possilble to delete this record]^^: ' . $this->model->getError()
            ];
        }

        return $data;
    }

    public function grid()
    {
        $data = $this->model->grid();

        // Convert to grid
        $grid = new \services\Grid();
        $data = $grid->get($data);

        return $data;
    }

    /**
     * Get the permissions by permission_id
     * @param integer $id
     * @return array $permissions
     */
    public function getPermissionsById($id)
    {
        // Get restrictions
        $restriction = \bossanova\Config\Config::get('permissions');

        // Permissions container
        $permissions = [];

        // Load permission information for this permission_id
        $row = $this->model->getById((int)$id);

        if (isset($row['permission_id']) && $row['permission_id'] > 0) {
            // If the user_id is a superuser register all restrited routes as permited
            if (isset($row['permission_superuser']) && $row['permission_superuser'] == 1) {
                foreach ($restriction as $k => $v) {
                    $k = str_replace('-', '_', $k);
                    $permissions[$k] = 1;
                }
            } else {
                // All route permited for his permission_id
                if ($permission_routes = json_decode($row['permission_routes'], true)) {
                    foreach ($permission_routes as $k => $v) {
                        $k = str_replace('-', '_', $k);
                        $permissions[$k] = 1;
                    }

                    // All route permited defined in the config.inc.php
                    foreach ($restriction as $k => $v) {
                        if (isset($v['permission']) && $v['permission'] == 1) {
                            $k = str_replace('-', '_', $k);
                            $permissions[$k] = 1;
                        }
                    }
                }
            }
        }

        return $permissions;
    }

    public function isPermissionsSuperUser($id)
    {
        $row = $this->model->getById((int) $id);

        if (isset($row['permission_id'])) {
            return isset($row['permission_superuser']) && $row['permission_superuser'] == 1 ? 1 : 0;
        }

        return 0;
    }
}
