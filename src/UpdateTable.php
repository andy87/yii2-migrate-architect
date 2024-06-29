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
    /** @var array Действие редактирование колонки */
    private const ACTION_EDIT = 0;

    /** @var array Действие добавление колонки */
    private const ACTION_ADD = 1;


    /** @var array Список колонок для удаления */
    public array $columnListRemove = [];

    /** @var array Список колонок для переименования */
    public array $columnListRename = [];



    /**
     * @return array
     */
    public function columnsListEdit(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function columnsListAdd(): array
    {
        return [];
    }


    /**
     * @return array
     */
    public function rollBackKeys(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function rollBackColumns(): array
    {
        return [];
    }

    /**
     * @return int
     * @throws Exception
     */
    final public function safeUp(): int
    {
        if ( count($this->columnListRemove) )
        {
            $this->prepareForeignKeys(self::COMMAND_DOWN, $this->foreignKeyList );

            $this->processRemove( $this->columnListRemove );
        }

        $this->processRename($this->columnListRename);

        $this->processEdit ($this->columnsListEdit() );

        $this->processAdd( self::COMMAND_UP, $this->columnsListAdd() );

        return parent::safeUp();
    }

    /**
     * @return int
     *
     * @throws Exception
     */
    final public function safeDown(): int
    {
        if ( parent::safeDown() == ExitCode::OK )
        {
            $this->processRename($this->columnListRename, self::COMMAND_DOWN );

            if ( count($addColumnKeys = $this->columnsListAdd()) )
            {
                $addColumnKeys = array_keys($addColumnKeys);

                $this->processRemove( $addColumnKeys );
            }

            if ( count($rollBackColumns = $this->rollBackColumns()) ) {
                $this->processAdd( self::COMMAND_DOWN, $rollBackColumns );
            }

            if ( count($rollBackKeys = $this->rollBackKeys()) ) {
                $this->prepareForeignKeys(self::COMMAND_UP, $rollBackKeys );
            }

            return ExitCode::OK;
        }

        return ExitCode::DATAERR;
    }

    /**
     * @param array $columns
     *
     * @return void
     *
     * @throws \yii\console\Exception
     */
    private function processRemove( array $columns ): void
    {
        if ( count($columns) )
        {
            $tableName = $this->getTableName();

            foreach ($columns as $column => $columnOrParams )
            {
                if ( is_string($columnOrParams) || is_array($columnOrParams) )
                {
                    [$column, $refTableName, $refColumnName] = $this->getForeignData($tableName, $columnOrParams);

                    $foreignKeyName = $this->generateForeignKeyName($tableName, $column, $refTableName, $refColumnName);

                    $this->dropForeignKey($foreignKeyName, $tableName);
                }

                $this->dropColumn($tableName, $column);
            }
        }
    }

    /**
     * @param string $command
     * @param array $columns
     *
     * @return void
     */
    private function processRename(array $columns,  string $command = self::COMMAND_UP ): void
    {
        $columns = ($command == self::COMMAND_UP ) ? $columns : array_flip($columns);

        $tableName = $this->getTableName();

        foreach ( $columns as $oldName => $newName )
        {
            $this->renameColumn($tableName, $oldName, $newName);
        }
    }

    /**
     * @param array $columns
     *
     * @return void
     */
    private function processEdit(array $columns): void
    {
        $tableName = $this->getTableName();

        $this->processUpdate(self::ACTION_EDIT, $tableName, $columns );
    }

    /**
     * @param string $command
     * @param array $columns
     *
     * @return void
     *
     * @throws \yii\console\Exception
     */
    private function processAdd( string $command, array $columns ): void
    {
        if ( count($columns) )
        {
            $tableName = $this->getTableName();

            if ($command === self::COMMAND_UP )
            {
                foreach ($columns as $column => $params )
                {
                    $this->addColumn($tableName, $column, $params);
                }
            } elseif ($command === self::COMMAND_DOWN) {

                if ( count($this->foreignKeyList) ) {
                    $this->prepareForeignKeys(self::COMMAND_DOWN, $this->foreignKeyList);
                }

                $columns = array_keys($columns);

                $this->processRemove($columns);
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
    private function processUpdate( int $action, string $tableName, array $columns ): void
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