<?php declare(strict_types=1);

namespace andy87\yii2\architect\components\migrations;

use andy87\yii2\architect\components\interfaces\ArchitectInterface;
use yii\db\Migration;
use yii\console\{ ExitCode, Exception };
use andy87\yii2\architect\components\db\MySql;

/**
 * Class Architector
 * 
 * @package andy87\yii2\architect\components\migrations
 */
abstract class Architect extends Migration implements ArchitectInterface
{
    /** @var string Настройка типа для dateTime колонок*/
    protected const DATETIME = self::DATETIME_DATETIME;


    /** @var string Флаг использования `Timestamp` схемы */
    protected const DATETIME_TIMESTAMP = 'timestamp';

    /** @var string Флаг использования `DateTime` схемы */
    protected const DATETIME_DATETIME = 'datetime';


    /** @var string Аттрибут "ID" */
    protected const COLUMN_ID = 'id';
    
    /** @var string Аттрибут "Дата создания" */
    protected const COLUMN_CREATED_AT = 'created_at';

    /** @var string Аттрибут "Дата обновления" */
    protected const COLUMN_UPDATED_AT = 'updated_at';

    /** @var string Аттрибут "Дата удаления" */
    protected const COMMAND_UP = 'up';

    /** @var string Аттрибут "Дата удаления" */
    protected const COMMAND_DOWN = 'down';


    /** @var int Сценарий */
    public int $scenario;

    /** @var string Имя таблицы */
    public string $tableName;

    /** @var array Список колонок, созданных по умолчанию */
    protected array $columnLabels = [
        self::COLUMN_ID => 'ID',
        self::COLUMN_CREATED_AT => 'Дата создания',
        self::COLUMN_UPDATED_AT => 'Дата обновления',
    ];



    /**
     * @var array Список внешних ключей
     *
     *  $foreignKeyList = ['user' => 'id'];
     *      // fk--{{tableName}}-user_id--user-id
     *
     *  $foreignKeyList = [
     *      'city' => 'id',
     *      'responsible_id' => ['user' => 'id']
     *  ];
     *      // fk--{{tableName}}-city_id--city-id
     *      // fk--{{tableName}}-responsible_id--user-id
     **/
    protected array $foreignKeyList = [];



    /**
     * Применение миграций
     *
     * @return int
     *
     * @throws Exception
     */
    public function safeUp(): int
    {
        $this->prepareForeignKeys( $this->foreignKeyList );

        return ExitCode::OK;
    }

    /**
     * Откат миграций
     *
     * @return int
     *
     * @throws Exception
     */
    public function safeDown(): int
    {
        $this->prepareForeignKeys($this->foreignKeyList, self::COMMAND_DOWN );

        return ExitCode::OK;
    }

    /**
     * @param string $command
     * @param array $keys
     *
     * @return void
     *
     * @throws Exception
     */
    protected function prepareForeignKeys( array $keys, string $command = self::COMMAND_UP ): void
    {
        if ( count($keys) )
        {
            switch ($command)
            {
                case self::COMMAND_UP:
                    $this->constructForeignKeys($keys);
                    break;

                case self::COMMAND_DOWN:
                    $this->dropForeignKeys($keys);
                    break;
            }
        }
    }

    /**
     * @param array $keys
     *
     * @return void
     *
     * @throws Exception
     */
    protected function constructForeignKeys( array $keys ): void
    {
        if ( count($keys) )
        {
            $tableName = $this->prepareTableName();

            foreach ( $keys as $TableNameOrColumnName => $columnOrParams )
            {
                [$column, $refTableName, $refColumnName] = $this->getForeignData($TableNameOrColumnName, $columnOrParams);

                $this->addForeignKey(
                    $this->generateForeignKeyName($tableName, $column, $refTableName, $refColumnName ),
                    $tableName, $column, $refTableName, $refColumnName
                );
            }
        }
    }

    /**
     * @param array $keys
     *
     * @return void
     *
     * @throws Exception
     */
    protected function dropForeignKeys( array $keys ): void
    {
        if ( count($keys) )
        {
            $tableName = $this->prepareTableName();

            foreach ( $keys as $TableNameOrColumnName => $columnOrParams )
            {
                [$column, $refTableName, $refColumnName] = $this->getForeignData($TableNameOrColumnName, $columnOrParams);

                $this->dropForeignKey(
                    $this->generateForeignKeyName($tableName, $column, $refTableName, $refColumnName ),
                    $tableName
                );
            }
        }
    }

    /**
     * @param string $tableName
     * @param string $column
     * @param string $refTableName
     * @param string $refColumnName
     *
     * @return string
     */
    protected function generateForeignKeyName( string $tableName, string $column, string $refTableName, string $refColumnName ): string
    {
        return sprintf("fk--%s-%s--%s-%s", $tableName, $column, $refTableName, $refColumnName);
    }

    /**
     * @param string|int $TableNameOrColumnName
     * @param string|array $columnOrParams
     *
     * @return array
     *
     * @throws Exception
     */
    protected function getForeignData( string|int $TableNameOrColumnName, string|array $columnOrParams): array
    {
        if ( is_string($columnOrParams) )
        {
            $refTableName = $TableNameOrColumnName;
            $refColumnName = $columnOrParams;

            $column = sprintf("%s_%s", $refTableName, $refColumnName);

        }elseif ( is_array($columnOrParams) ) {

            $column = $TableNameOrColumnName;

            switch (count($columnOrParams))
            {
                case 1:
                    $refTableName = array_key_first($columnOrParams);
                    $refColumnName = $columnOrParams[$refTableName];
                    break;

                default:
                    [$refTableName, $refColumnName] = $columnOrParams;
            }

        } else {

            throw new Exception('Foreign config error');
        }

        return [ $column, $refTableName, $refColumnName ];
    }

    /**
     * Опции для таблицы
     *
     * @return ?string
     */
    protected function getTableOptions(): ?string
    {
        return match ($this->db->driverName) {
            MySql::DRIVER => MySql::getOptions(),
            default => null,
        };
    }

    /**
     * @return string
     */
    protected function prepareTableName(): string
    {
        $tableName = $this->db->quoteSql($this->tableName);

        return trim($tableName, '`');
    }
}