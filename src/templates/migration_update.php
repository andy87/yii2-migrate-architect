<?php

/**
 * @var $className string the new migration class name without namespace
 * @var $namespace string the new migration class namespace
 * @var $tableName string the name of the table
 */

echo "<?php\n";
if (!empty($namespace)) {
    echo "\nnamespace {$namespace};\n";
}
?>

use andy87\yii2\architect\UpdateTable;

/**
 * Class <?= $className . "\n" ?>
 */
class <?= $className ?> extends UpdateTable
{
    /** @var int Сценарий */
    public int $scenario = self::SCENARIO_UPDATE;

    /** @var string Название таблицы */
    public string $tableName = '<?= $tableName ?>';


    /** @var array Список колонок для удаления */
    public array $columnListRemove = [];



    /** @var array Список внешних ключей */
    public array $foreignKeyList = [
        // 'table' => 'id', // for column: table_id
        // 'column_id' => ['table' => 'column'],
    ];


    /**
     * @return array
     */
    public function columnsListEdit(): array
    {
        return [
            //'column' => 'type',
        ];
    }

    /**
     * @return array
     */
    public function rollBackKeys(): array
    {
        return [
            // @see $this->foreignKeyList
        ];
    }

    /**
     * @return array
     */
    public function rollBackColumns(): array
    {
        return [
            // 'key' => 'type',
        ];
    }
}
