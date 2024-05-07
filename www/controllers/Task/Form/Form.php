<?php

namespace Controllers\Task\Form;

use Exception;

class Form
{
    private $validActions = array('create', 'update', 'env', 'duplicate', 'delete', 'removeEnv', 'rebuild');

    /**
     *  Return the task form to the user according to his selection
     */
    public function get(string $action, array $repos_array)
    {
        if (!in_array($action, $this->validActions)) {
            throw new Exception('Task action is invalid');
        }

        if ($action == 'update') {
            $title = '<h3>UPDATE</h3>';
        }
        if ($action == 'env') {
            $title = '<h3>NEW ENVIRONMENT</h3>';
        }
        if ($action == 'duplicate') {
            $title = '<h3>DUPLICATE</h3>';
        }
        if ($action == 'delete') {
            $title = '<h3>DELETE</h3>';
        }
        if ($action == 'rebuild') {
            $title = '<h3>REBUILD REPO</h3>';
        }

        $content = $title . '<form class="task-form" autocomplete="off">';
        $totalReposArray = count($repos_array);

        foreach ($repos_array as $repo) {
            $myrepo = new \Controllers\Repo\Repo();
            $repoId = \Controllers\Common::validateData($repo['repo-id']);
            $snapId = \Controllers\Common::validateData($repo['snap-id']);
            $envId  = null;

            /**
             *  If an environment points to the snapshot (snapId), retrieve the envId from the repo array
             */
            if (!empty($repo['envId'])) {
                $envId = \Controllers\Common::validateData($repo['env-id']);
            }

            /**
             *  Check that the Ids are numeric
             */
            if (!is_numeric($repoId)) {
                throw new Exception("Repo Id is invalid");
            }
            if (!is_numeric($snapId)) {
                throw new Exception("Snapshot Id is invalid");
            }
            if (!empty($envId)) {
                if (!is_numeric($envId)) {
                    throw new Exception("Environment Id is invalid");
                }
            }

            /**
             *  Check that the Ids exist in the database
             */
            if (!$myrepo->existsId($repoId)) {
                throw new Exception("Repo Id does not exist");
            }
            if (!$myrepo->existsSnapId($snapId)) {
                throw new Exception("Snapshot Id does not exist");
            }
            if (!is_null($envId)) {
                if (!$myrepo->existsEnvId($envId)) {
                    throw new Exception("Environment Id does not exist");
                }
            }

            /**
             *  Retrieve all repo data from the Ids
             */
            $myrepo->getAllById($repoId, $snapId, $envId);

            /**
             *  Retrieve the package type of the repo
             */
            $packageType = $myrepo->getPackageType();

            /**
             *  Build the form from a template
             */
            ob_start();

            echo '<div class="task-form-params" repo-id="' . $repoId . '" snap-id="' . $snapId . '" env-id="' . $envId . '" action="' . $action . '">';
            echo '<table class="task-table">';

            /**
             *  Include form template
             */
            include(ROOT . '/views/includes/forms/tasks/' . $action . '.inc.php');

            echo '</table>';
            echo '</div>';

            /**
             *  Print a <hr> to separate when there are multiple repos to be processed
             */
            if ($totalReposArray > 1) {
                echo '<br><hr><br>';
            }
            $totalReposArray--;

            $content .= ob_get_clean();
        }

        ob_start();

        /**
         *  Include schedule task template
         */
        include(ROOT . '/views/includes/forms/tasks/schedule.inc.php');

        $content .= ob_get_clean();

        $content .= '<br><button class="task-confirm-btn btn-large-red">Execute now</button></form><br><br>';

        return $content;
    }

    /**
     *  Validate the task form filled by the user
     *  @param array $tasksParams
     */
    public function validate(array $tasksParams) : void
    {
        foreach ($tasksParams as $task) {
            /**
             *  Retrieve action
             */
            if (empty($task['action'])) {
                throw new Exception('No action has been specified');
            }

            /**
             *  Generate controller name
             */
            $controllerPath = '\Controllers\Task\Form\\' . ucfirst($task['action']);

            /**
             *  Check if class exists, otherwise the action might be invalid
             */
            if (!class_exists($controllerPath)) {
                throw new Exception('Invalid action: ' . $task['action']);
            }

            /**
             *  Validate form by calling the controller
             */
            $controller = new $controllerPath();
            $controller->validate($task);
        }
    }
}