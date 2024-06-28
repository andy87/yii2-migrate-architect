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
    /** @var string Название таблицы */
    protected string $tableName = '<?= $tableName ?>';


    /** @var array Список колонок для удаления */
    public array $columnListRemove = [];


    /** @var array Список внешних ключей */
    public array $foreignKeyList = [
        // 'table' => 'id', // for column: table_id
        // 'column_id' => ['table' => 'column'],
    ];


    /**
     * {@inheritdoc}
     */
    public function columnsListAdd(): array
    {
        return [
            //'column' => 'type',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function columnsListEdit(): array
    {
        return [
            //'column' => 'type',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rollBackKeys(): array
    {
        return [
            // @see $this->foreignKeyList
        ];
    }

    /**
    * {@inheritdoc}
    */
    public function rollBackColumns(): array
    {
        return [
            // 'key' => 'type',
        ];
    }
}
