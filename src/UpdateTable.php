<?php declare(strict_types=1);

namespace andy87\yii2\architect;

use Exception;
use yii\console\ExitCode;

/**
 * Class UpdateTable
 *
 * @package andy87\yii2\architect
 */
abstract class UpdateTable extends components\migrations\Architect
{
    /** @var array Действие добавление колонки */
    private const ACTION_ADD = 1;

    /** @var array Действие редактирование колонки */
    private const ACTION_EDIT = 2;



    /** @var array Список колонок для удаления */
    public array $columnListRemove = [];

    /** @var array Список колонок для переименования */
    public array $columnListRename = [];



    /**
     * Список колонок пользователя для редактирования
     *
     * @return array
     */
    public function columnsListUpdate(): array
    {
        return [];
    }

    /**
     * Список колонок пользователя для добавления
     *
     * @return array
     */
    public function columnsListAdd(): array
    {
        return [];
    }


    /**
     * Список колонок для отката внешних ключей
     *
     * @return array
     */
    public function rollBackKeys(): array
    {
        return [];
    }

    /**
     * Список колонок для отката
     *
     * @return array
     */
    public function rollBackColumns(): array
    {
        return [];
    }

    /**
     * Применение миграций
     *
     * @return int
     *
     * @throws Exception
     */
    final public function safeUp(): int
    {
        switch ($this->scenario)
        {
            case self::SCENARIO_COLUMN_REMOVE:
                $this->processRemove( $this->columnListRemove );
                break;

            case self::SCENARIO_COLUMN_RENAME:
                $this->processRename( $this->columnListRename);
                break;

            case self::SCENARIO_UPDATE:
                $this->processUpdate( $this->columnsListUpdate() );
                break;

            case self::SCENARIO_COLUMN_ADD:
                $this->processAdd( $this->columnsListAdd() );
                break;
        }

        return ExitCode::OK;
    }

    /**
     * Откат миграций
     *
     * @return int
     *
     * @throws Exception
     */
    final public function safeDown(): int
    {
        switch ($this->scenario)
        {
            case self::SCENARIO_COLUMN_REMOVE:
                $this->processRemove( $this->columnListRemove, self::COMMAND_DOWN );
                break;

            case self::SCENARIO_COLUMN_RENAME:
                $this->processRename( $this->columnListRename, self::COMMAND_DOWN);
                break;

            case self::SCENARIO_UPDATE:
                $this->processUpdate( $this->columnsListUpdate(), self::COMMAND_DOWN );
                break;

            case self::SCENARIO_COLUMN_ADD:
                $this->processAdd( $this->columnsListAdd(), self::COMMAND_DOWN);
                break;
        }

        return ExitCode::OK;
    }

    /**
     * Удаление колонок.
     *
     * Задаётся для применения миграции:
     *  - массив `columnListRemove` - какие колонки надо удалить
     *  - массив `foreignKeyList` - какие ключи удалить/откатить
     *
     * Задаётся для отката миграции:
     *  - метод `rollBackColumns` - удалённые колонки и их типы
     *
     * @param array $columns
     * @param string $command
     *
     * @return void
     *
     * @throws \yii\console\Exception
     */
    private function processRemove( array $columns, string $command = self::COMMAND_UP ): void
    {
        if ( count($columns) )
        {
            $tableName = $this->prepareTableName();

            if( $command == self::COMMAND_UP)
            {
                $this->prepareForeignKeys($this->foreignKeyList, self::COMMAND_DOWN);

                foreach ($columns as $column => $columnOrParams ) {
                    $this->dropColumn($tableName, $column);
                }

            } elseif ( $command == self::COMMAND_DOWN ) {

                if ( count($rollBackColumns = $this->rollBackColumns()) )
                {
                    $this->processAdd( $rollBackColumns );

                    $this->prepareForeignKeys($this->foreignKeyList);
                }
            }
        }
    }

    /**
     * Переименование колонок.
     *
     * Задаётся для применения миграции:
     * - массив `columnListRename` - какие колонки надо переименовать
     *      ['старое_название' => 'новое_название']
     *
     * @param string $command
     * @param array $columns
     *
     * @return void
     */
    private function processRename(array $columns, string $command = self::COMMAND_UP ): void
    {
        $columns = ($command == self::COMMAND_UP ) ? $columns : array_flip($columns);

        foreach ( $columns as $oldName => $newName )
        {
            $this->renameColumn($this->tableName, $oldName, $newName);
        }
    }

    /**
     * Редактирование колонок.
     *
     * Задаётся для применения миграции:
     *  - метод `columnsListUpdate` - какие колонки надо изменить
     *
     * @param array $columns
     * @param string $command
     *
     * @return void
     * @throws \yii\console\Exception
     */
    private function processUpdate(array $columns, string $command = self::COMMAND_UP ): void
    {
        $tableName = $this->prepareTableName();

        if( $command == self::COMMAND_UP)
        {
            if ( count($columns) )
            {
                $this->prepareColumn(self::ACTION_EDIT, $tableName, $columns );
            }

            if ( count($this->columnListRename) )
            {
                $this->processRename($this->columnListRename );
            }

            if ( count($columnsListAdd = $this->columnsListAdd()) )
            {
                $this->prepareColumn(self::ACTION_ADD, $tableName, $columnsListAdd);
            }

            $this->prepareForeignKeys($this->foreignKeyList);

        } elseif ( $command == self::COMMAND_DOWN ) {

            $this->prepareForeignKeys( $this->foreignKeyList, self::COMMAND_DOWN );

            if ( count($rollBackColumns = $this->rollBackColumns()) ) {
                $this->prepareColumn( self::ACTION_EDIT, $tableName, $rollBackColumns );
            }

            if ( count($columnsListAdd = $this->columnsListAdd()) )
            {
                $columnsListAdd = array_keys($columnsListAdd);

                foreach ($columnsListAdd as $column ) {
                    $this->dropColumn($tableName, $column);
                }
            }

            if ( count($this->columnListRename) )
            {
                $this->processRename($this->columnListRename, self::COMMAND_DOWN );
            }

            $this->prepareForeignKeys($this->rollBackKeys());
        }
    }

    /**
     * Добавление колонок.
     *
     * Задаётся для применения миграции:
     *  - метод `columnsListAdd` - какие колонки надо добавить
     *  - массив `foreignKeyList` - какие ключи добавить
     *
     * @param string $command
     * @param array $columns
     *
     * @return void
     *
     * @throws \yii\console\Exception
     */
    private function processAdd( array $columns, string $command = self::COMMAND_UP ): void
    {
        if ( count($columns) )
        {
            $tableName = $this->prepareTableName();

            if ( $command == self::COMMAND_UP )
            {
                $this->prepareColumn(self::ACTION_ADD, $tableName, $columns );

                $this->prepareForeignKeys($this->foreignKeyList);

            } elseif ( $command == self::COMMAND_DOWN ) {

                $this->prepareForeignKeys( $this->foreignKeyList, self::COMMAND_DOWN );

                foreach ($columns as $column => $columnOrParams ) {
                    $this->dropColumn($tableName, $column);
                }
            }
        }
    }

    /**
     * @param int $action
     * @param string $tableName
     * @param array $columns
     *
     * @return void
     */
    private function prepareColumn(int $action, string $tableName, array $columns ): void
    {
        if ( count($columns) )
        {
            foreach ( $columns as $column => $config )
            {
                match ($action)
                {
                    self::ACTION_EDIT => $this->alterColumn( $tableName, $column, $config ),
                    self::ACTION_ADD => $this->addColumn( $tableName, $column, $config ),
                };
            }
        }
    }
}