<?php declare(strict_types=1);

namespace andy87\yii2\architect\components\controllers;

use Yii;
use yii\helpers\{ FileHelper, BaseConsole };
use andy87\yii2\architect\components\interfaces\ArchitectInterface;
use yii\console\{ ExitCode, Exception, controllers\MigrateController };

/**
 * Class MigrateController
 *
 * @package andy87\yii2\architect\components\controllers
 */
class ArchitectController extends MigrateController implements ArchitectInterface
{
    public $promptLabels = [
        'action' => [
            self::ACTION_SETUP => 'Setup migrations',
            self::ACTION_CREATE => 'Create migration',
            self::ACTION_APPLY => 'Apply migrations',
            self::ACTION_DOWN => 'Down migrations',
        ],
        'migrate' => [
            self::SCENARIO_CREATE => 'Create table',
            self::SCENARIO_UPDATE => 'Update column',
            self::SCENARIO_COLUMN_ADD => 'Add column',
            self::SCENARIO_COLUMN_RENAME => 'Rename column',
            self::SCENARIO_COLUMN_REMOVE => 'Remove column',
        ],
    ];

    public $defaultAction = 'index';
    public ?string $ns = null;

    public string $directoryTemplateMigrations = '@vendor/andy87/yii2-migrate-architect/src/templates/';

    public array $migrateTemplateMapping = [
        self::SCENARIO_CREATE => 'migration_create',
        self::SCENARIO_UPDATE => 'migration_update',
        self::SCENARIO_COLUMN_ADD => 'migration_update_add',
        self::SCENARIO_COLUMN_RENAME => 'migration_update_rename',
        self::SCENARIO_COLUMN_REMOVE => 'migration_update_remove',
    ];

    public array $snippetsMigrationFilename = [
        self::SCENARIO_CREATE  => 'create_table__%s',
        self::SCENARIO_UPDATE  => 'update_table__%s',
        self::SCENARIO_COLUMN_ADD     => 'columns_add__%s',
        self::SCENARIO_COLUMN_RENAME  => 'columns_rename__%s',
        self::SCENARIO_COLUMN_REMOVE  => 'columns_remove__%s',
    ];

    /**
     * @return int
     *
     * @throws Exception|Yii\base\Exception
     */
    public function actionIndex(): int
    {
        $this->displayPromptVariantList("Select action:", $this->promptLabels['action']);

        $action = (int) $this->prompt("\n variant:", ['required' => true]);

        match ($action)
        {
            self::ACTION_SETUP  => $this->processSetup(),
            self::ACTION_CREATE => $this->processCreate(),
            self::ACTION_APPLY  => $this->processApply(),
            self::ACTION_DOWN   => $this->processDown(),
            self::ACTION_EXIT   => exit("\n EXIT \n"),
            default => exit("\nUnknown action\n")
        };

        return ExitCode::OK;
    }

    /**
     * @return void
     */
    public function processSetup(): void
    {
        parent::actionUp();
    }


    /**
     * @return void
     *
     * @throws Exception|yii\base\Exception
     */
    public function processCreate(): void
    {
        $this->displayPromptVariantList("`Create migration`:", $this->promptLabels['migrate']);

        $action = (int) $this->prompt("\naction:", ['required' => true]);

        $fileNameTemplate = $this->snippetsMigrationFilename[$action];

        $templateFile = $this->migrateTemplateMapping[$action];

        $this->templateFile = Yii::getAlias($this->directoryTemplateMigrations) . "$templateFile.php";

        $tableName = $this->prompt("\nTable name:", ['required' => true]);
        $tableName = strtolower($tableName);

        $fileName = sprintf($fileNameTemplate, $tableName);

        $params = match($action)
        {
            self::SCENARIO_COLUMN_RENAME => ['columnListRename' => ''],
            self::SCENARIO_COLUMN_REMOVE => ['columnListRemove' => ''],
            default => []
        };

        if ( count($params) > 0 )
        {
            $this->stdout("\nSetup columns.");

            $done = false;

            while ($done == false)
            {
                switch ($action)
                {
                    case self::SCENARIO_COLUMN_RENAME:
                        $columnName = $this->prompt("\nOld column name:", ['required' => true]);
                        $newColumnName = $this->prompt("New column name:", ['required' => true]);
                        $params['columnListRename'] .= "\n        '$columnName' => '$newColumnName',";
                        break;

                    case self::SCENARIO_COLUMN_REMOVE:
                        $columnName = $this->prompt("\nRemove column name:", ['required' => true]);
                        $params['columnListRemove'] .= "\n        '$columnName' => null,";
                        break;
                }
                $done = !$this->confirm("\nContinue setup columns?");
            }

            $firstKey = array_key_first($params);

            if (isset($params[$firstKey])) $params[$firstKey] .= "\n";
        }

        $this->generateMigrateFile( $fileName, $tableName, $params );
    }

    /**
     * @return void
     */
    public function processApply(): void
    {
        $this->stdout("\nUp migration:");

        $limit = (int) $this->prompt("\n setup `limit` : ", [
            'required' => true,
            'default' => 1
        ]);

        if ($limit == self::EXIT_CODE) exit("\n EXIT \n");

        parent::actionUp($limit);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function processDown(): void
    {
        $this->stdout("\nDown migration:");

        $limit = (int) $this->prompt("\n setup `limit` : ", [
            'required' => true,
            'default' => 1
        ]);

        if ($limit == self::EXIT_CODE) exit("\n EXIT \n");
        parent::actionDown($limit);
    }

    /**
     * @param string $message
     * @param array $array
     *
     * @return void
     */
    protected function displayPromptVariantList(string $message, array $array): void
    {
        $this->stdout("$message");

        foreach ($array as $index => $label) {
            $this->stdout("\n $index. $label");
        }

        $exitCode = self::EXIT_CODE;
        $this->stdout("\n $exitCode. Exit");

        $this->stdout("\n--------------------");
    }

    /**
     * @throws Exception
     *
     * @throws yii\base\Exception
     */
    public function generateMigrateFile(string $name, string $tableName, array $params = [] ): int
    {
        if (!preg_match('/^[\w\\\\]+$/', $name)) {
            throw new Exception('The migration name should contain letters, digits, underscore and/or backslash characters only.');
        }

        $className = 'm' . gmdate('ymd_His') . "_$name";

        // Abort if name is too long
        $nameLimit = $this->getMigrationNameLimit();
        if ($nameLimit !== null && strlen($className) > $nameLimit) {
            throw new Exception('The migration name is too long.');
        }

        $migrationPath = reset($this->migrationPath);

        $file = $migrationPath . DIRECTORY_SEPARATOR . $className . '.php';
        if ($this->confirm("Create new migration '$file'?")) {
            $migrateParams = array_merge([
                'name' => $tableName,
                'className' => $className,
                'namespace' => $this->ns,
                'tableName' => $tableName
            ], $params);
            $content = $this->generateMigrationSourceCode($migrateParams);
            FileHelper::createDirectory($migrationPath);
            if (file_put_contents($file, $content, LOCK_EX) === false) {
                $this->stdout("Failed to create new migration.\n", BaseConsole::FG_RED);

                return ExitCode::IOERR;
            }

            FileHelper::changeOwnership($file, $this->newFileOwnership, $this->newFileMode);

            $this->stdout("New migration created successfully.\n", BaseConsole::FG_GREEN);
        }

        return ExitCode::OK;
    }
}