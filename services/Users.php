<?php

/**
 * (c) 2013 Bossanova PHP Framework 5
 * http://github.com/paulhodel/bossanova
 *
 * @author: Paul Hodel <paul.hodel@gmail.com>
 * @description: Users Services
 */
namespace services;

use bossanova\Services\Services;

class Users extends Services
{
    private $model = null;
    private $permissions = null;

    public function __construct()
    {
        parent::__construct();

        $this->model = new \models\Users();
        $this->permissions = new \models\Permissions();
    }

    /**
     * Select
     *
     * @param  integer $user_id
     * @return array   $data
     */
    public function select($id)
    {
        $data = [];

        if ((int)$id > 0) {
            $data = $this->model->getById($id);

            if (count($data) > 0) {
                if (! $this->permissions->isAllowedHierarchy($data['permission_id'])) {
                    $data = [
                        'error' => 1,
                        'message' => '^^[You do not have permission to load this record]^^'
                    ];
                } else {
                    // Extra information
                    if (isset($data['user_json']) && $data['user_json']) {
                        $data['user_json'] = json_decode($data['user_json'], true);
                    }
                }
            } else {
                $data = [
                    'error' => 1,
                    'message' => '^^[No record found]^^'
                ];
            }
        }

        return $data;
    }

    /**
     * Insert
     *
     * @param  array  $data
     * @return array  $data
     */
    public function insert($row)
    {
        // Permission is a mandatory field
        if (isset($row['permission_id'])) {
            if ($row['permission_id'] && ! $this->permissions->isAllowedHierarchy($row['permission_id'])) {
                $data = [
                    'error' => 1,
                    'message' => '^^[Permission denied]^^'
                ];
            } else {
                // Avoid duplicate user email
                $user = $this->model->exists('user_email', trim($row['user_email']));

                if ((isset($user['user_id']) && $user['user_id'])) {
                    $data = [
                        'error' => 1,
                        'message' => '^^[This email is already registered to another account]^^'
                    ];
                } else {
                    $user = $this->model->exists('user_login', trim($row['user_login']));

                    if ((isset($user['user_id']) && $user['user_id'])) {
                        $data = [
                            'error' => 1,
                            'message' => '^^[This login is already registered to another account]^^'
                        ];
                    } else {
                        // Password
                        if (! isset($row['user_password']) || ! $row['user_password']) {
                            $row['user_password'] = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 6)), 0, 6);
                        }

                        $salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
                        $pass = hash('sha512', hash('sha512', $row['user_password']) . $salt);
                        $row['user_salt'] = $salt;
                        $row['user_password'] = $pass;

                        // Avoid errors
                        if (isset($row['user_id'])) {
                            unset($row['user_id']);
                        }

                        // Extra information
                        if (isset($row['user_json'])) {
                            $row['user_json'] = json_encode($row['user_json']);
                        }

                        // Add a new record
                       $id = $this->model->column($row)->insert();

                        if (isset($id) && $id) {
                            // Loading recovery email body
                            $registrationFile = defined('EMAIL_REGISTRATION_FILE') ?
                                EMAIL_REGISTRATION_FILE : "resources/texts/registration.txt";

                            // Loading registration text template
                            $content = file_get_contents($registrationFile);

                            // Replace macros
                            $content = $this->mail->replaceMacros($content, $row);
                            $content = $this->mail->translate($content);

                            // Destination
                            $to = [
                                $row['user_email'],
                                $row['user_name']
                            ];

                            // From
                            $from = [
                                MS_CONFIG_FROM,
                                MS_CONFIG_NAME
                            ];

                            // Send email
                            $this->mail->sendmail($to, EMAIL_REGISTRATION_SUBJECT, $content, $from);

                            $data = [
                                'success' => 1,
                                'message' => '^^[The user has been successfully created. An email
                                    has been sent to your email address and should be confirmed]^^'
                            ];
                        }
                    }
                }
            }
        } else {
            $data = [
                'error' => 1,
                'message' => '^^[Permission is a mandatory field]^^'
            ];
        }

        return $data;
    }

    /**
     * Update
     *
     * @param  array  $data
     * @return array  $data
     */
    public function update($id, $row)
    {
        $data = $this->model->getById($id);

        if (! isset($data['permission_id'])) {
            $data['permission_id'] = 0;
        }

        if (count($data) > 0) {
            if (! $this->permissions->isAllowedHierarchy($data['permission_id'])) {
                $data = [
                    'error' => 1,
                    'message' => '^^[Permission denied]^^',
                ];
            } else {
                // Avoid duplicate user email
                $user = $this->model->exists('user_email', trim($row['user_email']));

                if ((isset($user['user_id']) && $user['user_id'] && $user['user_id'] != $id)) {
                    $data = [
                        'error' => 1,
                        'message' => '^^[This email is already in registered in another account]^^'
                    ];

                } else {
                    if (isset($row['user_login']) && $row['user_login']) {
                        $user = $this->model->exists('user_login', trim($row['user_login']));
                    }

                    if ((isset($user['user_id']) && $user['user_id'] && $user['user_id'] != $id)) {
                        $data = [
                            'error' => 1,
                            'message' => '^^[This login is already in registered in another account]^^'
                        ];
                    } else {
                        // Password
                        if (isset($row['user_password']) && ! $row['user_password']) {
                            unset($row['user_password']);
                        }

                        if (isset($row['user_password'])) {
                            // Update password information
                            $salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
                            $pass = hash('sha512', hash('sha512', $row['user_password']) . $salt);
                            $row['user_salt'] = $salt;
                            $row['user_password'] = $pass;
                        }

                        // Extra information
                        if (isset($row['user_json'])) {
                            $row['user_json'] = json_encode($row['user_json']);
                        }

                        $this->model->column($row)->update($id);

                        $data = [
                            'success' => 1,
                            'message' => '^^[Successfully saved]^^'
                        ];
                    }
                }
            }
        } else {
            $data = [
                'error' => 1,
                'message' => '^^[No record found]^^'
            ];
        }

        return $data;
    }

    /**
     * Logical delete a user based on the user_id
     *
     * @param  integer $user_id
     * @return array   $data
     */
    public function delete($user_id)
    {
        $data = $this->model->getById($user_id);

        if (count($data) > 0) {
            if (! $this->permissions->isAllowedHierarchy($data['permission_id'])) {
                $data = [ 'error' => 1, 'message' => '^^[Permission denied]^^' ];
            } else {
                $this->model->delete($user_id);
                $data = [ 'success' => 1, 'message' => '^^[Successfully deleted]^^' ];
            }
        } else {
            $data = [ 'error' => 1, 'message' => '^^[No record found]^^' ];
        }

        return $data;
    }

    public function grid()
    {
        $data = $this->model->grid();

        return $data;
    }

    /**
     * Select Permissions
     *
     * @param  integer $user_id
     * @return array   $data
     */
    public function getPermissions($id = null)
    {
        $permissions = $this->permissions->combo();

        return count($permissions) > 0 ? $permissions : [ 'error' => 1, 'message' => '^^[No record found]^^' ];
    }

    /**
     * Check existing user
     *
     */
    public function getById($id)
    {
        $data = $this->model->getById($id);

        return $data;
    }

    /**
     * Get user data
     *
     */
    public function getUserDataById($user_id)
    {
        $data = $this->model->getById($user_id);

        // Prepare json information
        if (isset($data['user_json']) && $data['user_json']) {
            $data['user_json'] = json_decode($data['user_json'], true);
        }

        // Get user image
        $data['user_image'] = $this->getPhoto($user_id, (int) $data['user_gender']);

        // Flags
        $data['user_message'] = isset($data['user_message']) ? $data['user_message'] : 0;
        $data['user_request'] = isset($data['user_request']) ? $data['user_request'] : 0;

        // Get user parent
        if (isset($data['parent_id']) && $data['parent_id'] > 0) {
            // Get parent information
            $parent = $this->model->getById($data['parent_id']);
            // Identification
            $data['parent_name'] = $parent['user_name'];
            $data['parent_login'] = $parent['user_login'];
        }

        return $data;
    }

    public function getPhoto($user_id, $gender = 1)
    {
        if (file_exists('public/media/profile/' . $user_id . '.jpg')) {
            $image = '/media/profile/' . $user_id  . '.jpg';
        } else {
            $image = "/templates/default/img/nophoto{$gender}.jpg";
        }

        return $image;
    }

    public function delPhoto($user_id)
    {
        $filename = 'public/media/profile/' . $user_id  . '.jpg';
        return unlink($filename);
    }

    public function setPhoto($user_id, $image)
    {
        $filename = 'public/media/profile/' . $user_id  . '.jpg';
        return file_put_contents($filename, base64_decode($image));
    }

    public function getUsersByParentId($user_id)
    {
        $data = $this->model->getUsersByParentId($user_id);

        return $data;
    }
}