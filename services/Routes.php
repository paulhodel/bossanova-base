<?php

/**
 * (c) 2013 Bossanova PHP Framework 5
 * http://github.com/paulhodel/bossanova
 *
 * @author: Paul Hodel <paul.hodel@gmail.com>
 * @description: Routes Services
 */
namespace services;

class Routes
{
    public function __construct()
    {
        $this->model = new \models\Routes();
    }

    /**
     * Select
     *
     * @param  integer $user_id
     * @return array   $data
     */
    public function select($id)
    {
        $nodes = new \models\Nodes();

        $row = $this->model->getById($id);

        if ($row['extra_config']) {
            $extra_config = json_decode($row['extra_config']);

            foreach ($extra_config as $k => $v) {
                if ($extra_config[$k]->node_id) {
                    $node = $nodes->getById($extra_config[$k]->node_id);
                    $node = json_decode($node['node_json'], true);
                    $extra_config[$k]->title = $node['title'];
                }
            }

            $row['extra_config'] = $extra_config;
        }

        return $row;
    }

    public function insert($row)
    {
        $row['extra_config'] = $this->getExtraConfig();

        $data = $this->model->column($row)->insert();

        if (! $data) {
            $data = [
                'error' => 1,
                'message' => '^^[It was not possible to save this record]^^'
            ];
        } else {
            $data = [
                'id' => $data,
                'success' => 1,
                'message' => '^^[Successfully saved]^^'
            ];
        }

        return $data;
    }

    public function update($id, $row)
    {
        $row['extra_config'] = $this->getExtraConfig();

        $data = $this->model->column($row)->update($id);

        if (! $data) {
            $data = [
                'error' => 1,
                'message' => '^^[It was not possible to save this record]^^'
            ];
        } else {
            $data = [
                'success' => 1,
                'message' => '^^[Successfully saved]^^'
            ];
        }

        return $data;
    }

    public function delete($id)
    {
        $data = $this->model->delete($id);

        if (! $data) {
            $data = [
                'error' => 1,
                'message' => '^^[It was not possible to delete this record]^^'
            ];
        } else {
            $data = [
                'success' => 1,
                'message' => '^^[Successfully deleted]^^'
            ];
        }

        return $data;
    }

    public function getExtraConfig()
    {
        $extra_config = [];

        if (isset($_POST['extra_config']['node_id']) && count($_POST['extra_config']['node_id'])) {
            foreach ($_POST['extra_config']['node_id'] as $k => $v) {
                $extra_config[$k]['node_id'] = $_POST['extra_config']['node_id'][$k];
                $extra_config[$k]['module_name'] = $_POST['extra_config']['module_name'][$k];
                $extra_config[$k]['controller_name'] = $_POST['extra_config']['controller_name'][$k];
                $extra_config[$k]['method_name'] = $_POST['extra_config']['method_name'][$k];
                $extra_config[$k]['template_area'] = $_POST['extra_config']['template_area'][$k];
            }
        }

        return json_encode($extra_config);
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
     * Return all modules available in the application dir
     *
     * @return string $data All modules found in the application folder
     */
    public function getModules()
    {
        // Keep all to be translated text references
        $data = [];

        $i = 0;

        // Search all folders reading all files
        if ($dh = opendir("modules")) {
            while (false !== ($file = readdir($dh))) {
                if (substr($file, 0, 1) != '.') {
                    if (is_dir('modules/' . $file)) {
                        $data[$i]['id'] = $file;
                        $data[$i]['name'] = $file;
                        $i ++;
                    }
                }
            }

            closedir($dh);
        }

        return $data;
    }

    /**
     * Return all methods available in the module or controllers
     *
     * @return string $data All methods found in a given module or controler
     */
    public function getMethodsByModule($module, $controller)
    {
        // Keep all to be translated text references
        $data = [];

        // Module
        $file = 'modules/' . ucfirst($module);

        // Controller
        if ($controller = ucfirst($controller)) {
            $file .= '/controllers/' . $controller;
        } else {
            $file .= '/' . ucfirst($module);
        }

        // Extension
        $file .= '.class.php';

        $i = 0;

        // Load methods
        if (file_exists($file)) {
            $a = file_get_contents($file);

            preg_match_all('/public? function (.*?)\(\)/', $a, $b);

            foreach ($b[1] as $k => $v) {
                $v = trim($v);

                if (substr($v, 0, 2) != '__') {
                    $data[$i]['id'] = $v;
                    $data[$i]['name'] = $v;

                    $i++;
                }
            }
        }

        return $data;
    }

    /**
     * Return all controllers available in the selected module
     *
     * @return string $data All controllers found in a given module folder
     */
    public function getControllersByModule($module)
    {
        // Keep all to be translated text references
        $data = [];

        // Module
        $file = ucfirst($module);

        $i = 0;

        // Search all folders reading all files
        if (is_dir('modules/' . $file . '/controllers')) {
            if ($dh = opendir('modules/' . $file . '/controllers')) {
                while (false !== ($file = readdir($dh))) {
                    if (substr($file, 0, 1) != '.') {
                        $data[$i]['id'] = substr($file, 0, - 10);
                        $data[$i]['name'] = substr($file, 0, - 10);

                        $i ++;
                    }
                }

                closedir($dh);
            }
        }

        return $data;
    }

    /**
     * Search for the template files
     *
     * @return array $templates - all templates found
     */
    public function getTemplates()
    {
        $data = [];

        $i = 0;

        // Format grid json data
        foreach ($this->searchTemplates('public/templates') as $k => $v) {
            $v = substr($v, 17);

            $data[$i]['id'] = $v;
            $data[$i]['name'] = $v;

            $i ++;
        }

        return $data;
    }

    /**
     * Internal search for the template files
     *
     * @return array $templates - all templates found
     */
    private function searchTemplates($folder)
    {
        // Keep all to be translated text references
        $templates = [];

        // Search all folders reading all files
        if ($dh = opendir($folder)) {
            while (false !== ($file = readdir($dh))) {
                if (substr($file, 0, 1) != '.') {
                    if (is_dir($folder . '/' . $file)) {
                        if (($file != 'css') && ($file != 'js') && ($file != 'doc') && ($file != 'img')) {
                            $templates = array_merge($templates, $this->searchTemplates($folder . '/' . $file));
                        }
                    } else {
                        if (substr($file, - 4) == 'html') {
                            $templates[] = $folder . '/' . $file;
                        }
                    }
                }
            }

            closedir($dh);
        }

        return $templates;
    }

    /**
     * Get all object ids from a HTML file
     *
     * @param string $template
     * @return array
     */
    public function getObjectIdsByTemplate($template)
    {
        $data = [];

        if (file_exists("public/templates/" . $template)) {
            $template = file_get_contents("public/templates/" . $template);
            preg_match_all("/<(.*)id=[\"'](.*?)[\"'](.*)>/", $template, $test);

            // Format grid json data
            $i = 0;
            foreach ($test[2] as $k => $v) {
                $data[$i]['id'] = $v;
                $data[$i]['name'] = $v;

                $i++;
            }
        }

        return $data;
    }

    /**
     * All dictionary files in the resources/locales
     *
     * @return string $json - list of locales
     */
    private function getLocales()
    {
        // Keep all to be translated text references
        $data = [];

        $i = 0;

        // Search all folders reading all files
        if ($dh = opendir('resources/locales')) {
            while (false !== ($file = readdir($dh))) {
                // Get all dictionaries
                if (substr($file, - 4) == '.csv') {
                    if (! isset($locales[substr($file, 0, - 4)])) {
                        $locales[substr($file, 0, - 4)] = substr($file, 0, - 4);
                    }
                }
            }

            closedir($dh);
        }

        // Change for the correct format
        if ($locales) {
            foreach ($locales as $k => $v) {
                $data[$i]['id'] = $k;
                $data[$i]['name'] = $v;

                $i ++;
            }
        }

        return $data;
    }

}
