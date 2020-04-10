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

class Permissions extends Model
{
    // Table configuration
    public $config = array(
        'tableName' => 'permissions',
        'primaryKey' => 'permission_id',
        'sequence' => 'permissions_permission_id_seq',
        'recordId' => 0
    );

    public function select($id)
    {
        // Get permission data
        $row = $this->getById($id);

        // Allowed routes
        $allowedRoutes = json_decode($row['permission_routes'], true);

        // Get restrictions
        $restrictions = $this->getRestrictions();

        foreach ($restrictions as $k => $v) {
            $restrictions[$k]['checked'] = isset($allowedRoutes[$k]) && $allowedRoutes[$k] == 1 ? true : false;
        }

        // Defaults
        $row['permission_superuser'] = ($row['permission_superuser'] == '1') ? 1 : 0;
        $row['permission_order'] = ($row['permission_order']) ? $row['permission_order'] : 6;
        $row['permission_status'] = ($row['permission_status'] == '0') ? 0 : 1;

        // Full format
        $row['permission_routes'] = $restrictions;

        return $row;
    }

    /**
     * Get the hierarchy from this permission group
     * @param int $permission_id
     * @return integer|boolean
     */
    public function getHierarchy($permission_id)
    {
        $result = $this->database->table("permissions")
            ->column("permission_order")
            ->argument(1, "permission_id", $permission_id)
            ->select()
            ->execute();

        if ($row = $this->database->fetch_assoc($result)) {
            return $row['permission_order'];
        }

        return false;
    }

    /**
     * Logical delete of a record
     *
     * @param integer $permission_id
     *            - permission_id
     * @return array $data - message
     */
    public function delete($id, $currentUserPermissionId)
    {
        // Logical delete the record
        $this->database->table('permissions')
            ->column(array('permission_status' => 0))
            ->argument(1, 'permission_id', (int) $id)
            ->update()
            ->execute();

        return $this->hasSuccess();
    }

    /**
     * Check permission hierarchy
     *
     * @return bool
     */
    public function isAllowedHierarchy($id, $permission_id)
    {
        if (! $id) {
            // Permission to be defined
            $bool = true;
        } else {
            if ($permission_id == $id) {
                $bool = true;
            } else {
                $result = $this->database->table("permissions")
                    ->column("permission_order")
                    ->argument(1, "permission_id", $permission_id)
                    ->select()
                    ->execute();

                $row1 = $this->database->fetch_assoc($result);

                $result = $this->database->table("permissions")
                    ->column("permission_order")
                    ->argument(1, "permission_id", $id)
                    ->select()
                    ->execute();

                $row2 = $this->database->fetch_assoc($result);

                $bool = ($row1['permission_order'] > $row2['permission_order']) ? false : true;
            }
        }

        return $bool;
    }

    /**
     * Get all restricted areas from BF config file
     *
     * @return bool
     */
    public function getRestrictions()
    {
        $restrictions = \bossanova\Config\Config::get('restrictions');

        ksort($restrictions);

        return $restrictions;
    }

    /**
     * Populate permission combo
     *
     * @return string $data - list of permissions
     */
    public function combo($permission_id)
    {
        $data = [];

        // Get the permission level
        $result = $this->database->table("permissions")
        ->column("permission_order")
        ->argument(1, "permission_id", $permission_id)
        ->select()
        ->execute();

        $row = $this->database->fetch_assoc($result);

        // Get only the permissions with the same level or a lower importance
        if ($row['permission_order'] > 0) {
            $result = $this->database->table("permissions")
            ->column("permission_id, permission_name")
            ->argument(1, "permission_status", 1)
            ->argument(2, "permission_order", $row['permission_order'], ">=")
            ->order("permission_name")
            ->execute();
        } else {
            $result = $this->database->table("permissions")
            ->column("permission_id, permission_name")
            ->argument(1, "permission_status", 1)
            ->argument(2, "permission_id", $permission_id)
            ->order("permission_name")
            ->execute();
        }

        // Create the json
        while ($row = $this->database->fetch_assoc($result)) {
            $data[] = [
                'id' => $row['permission_id'],
                'name' => $row['permission_name'],
            ];
        }

        return $data;
    }
}
