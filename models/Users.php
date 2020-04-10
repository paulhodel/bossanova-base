<?php

/**
 * (c) 2013 Bossanova PHP Framework
 * http://github.com/paulhodel/bossanova
 *
 * @author: Paul Hodel <paul.hodel@gmail.com>
 * @description: Model
 */
namespace models;

use bossanova\Model\Model;

class Users extends Model
{
    // Table configuration
    public $config = array(
        'tableName' => 'users',
        'primaryKey' => 'user_id',
        'sequence' => 'users_user_id_seq',
        'recordId' => 0
    );

    /**
     * Verify value exists in the user table
     *
     * @param  string $user_email
     * @return array  $row
     */
    public function exists($k, $v)
    {
        $v = $this->database->bind($v);

        $result = $this->database->Table($this->config->tableName)
            ->column($this->config->primaryKey)
            ->argument(1, "lower($k)", "lower($v)")
            ->execute();

        return $this->database->fetch_assoc($result);
    }

    /**
     * Logical delete a user based on the user_id
     *
     * @param  integer $user_id
     * @return array   $data
     */
    public function delete($user_id)
    {
        $this->database->table("users")
            ->column(array('user_status' => 0))
            ->argument(1, "user_id", $user_id)
            ->update()
            ->execute();

        return $this->hasSuccess();
    }

    /**
     * Get the current hash
     *
     * @param  string $user_email
     * @return array  $row
     */
    public function getUserPreviousPasswordHash($user_id, $user_password)
    {
        $user_password = $this->database->bind($user_password);

        $result = $this->database->table("users")
            ->argument(1, "user_id", $user_id)
            ->argument(2, "user_password", $user_password)
            ->execute();

        return $this->database->fetch_assoc($result) ? true : false;
    }

    /**
     * Get user by ident
     *
     * @param  string $ident - email or login
     * @return array  $row
     */
    public function getUserByIdent($ident)
    {
        $row = $this->getUserByEmail($ident);

        if (! $row) {
            $row = $this->getUserByLogin($ident);
        }

        return $row;
    }

    /**
     * Get user by email
     *
     * @param  string $user_email
     * @return array  $row
     */
    public function getUserByEmail($ident)
    {
        $ident = $this->database->bind(strtolower(trim($ident)));

        $result = $this->database->Table("users")
            ->argument(1, "lower(user_email)", "lower($ident)")
            ->execute();

        if ($row = $this->database->fetch_assoc($result)) {
            // Register user object
            $this->get($row['user_id']);
        }

        return $row;
    }

    /**
     * Get user by login
     *
     * @param  string $user_email
     * @return array  $row
     */
    public function getUserByLogin($ident)
    {
        $ident = $this->database->bind(strtolower(trim($ident)));

        $result = $this->database->Table("users")
            ->argument(1, "lower(user_login)", "lower($ident)")
            ->execute();

        if ($row = $this->database->fetch_assoc($result)) {
            // Register user object
            $this->get($row['user_id']);
        }

        return $row;
    }

    /**
     * Get user by hash
     *
     * @param  string $user_email
     * @return array  $row
     */
    public function getUserByHash($hash)
    {
        $hash = preg_replace("/[^a-zA-Z0-9]/", "", $hash);

        if ($hash && strlen($hash) == 128) {
            $hash = $this->database->bind(strtolower(trim($hash)));

            $result = $this->database->table("users")
                ->argument(1, "user_hash", $hash)
                ->execute();

            if ($row = $this->database->fetch_assoc($result)) {
                // Register user object
                $this->get($row['user_id']);
            }
        }

        return isset($row) ? $row : null;
    }

    /**
     * Update the password of a user based on a user_id
     *
     * @param integer $user_id
     * @return void
     */
    public function setPassword($user_id, $password, $hash = false)
    {
        if (isset($password) && $password) {
            if (! $hash) {
                $password = hash('sha512', $password);
            }
            // Update user password
            $salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
            $pass = hash('sha512', $password . $salt);

            // Columns
            $column = [];
            $column['user_salt'] = $salt;
            $column['user_password'] = $pass;
            $column = $this->database->bind($column);

            $this->database->table("users")
                ->column($column)
                ->argument(1, "user_id", $user_id)
                ->update()
                ->execute();
        }
    }

    /**
     * Set user log
     *
     * @return string $data - list of users
     */
    public function setLog($column)
    {
        $column = $this->database->bind($column);
        $column['access_date'] = "NOW()";

        $this->database->table('users_access')
            ->column($column)
            ->insert()
            ->execute();

        return $this->database->insert_id('users_access_user_access_id_seq');
    }
}
