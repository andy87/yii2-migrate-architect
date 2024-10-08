<?php

/**
 * @var $className string the new migration class name without namespace
 * @var $namespace string the new migration class namespace
 * @var $tableName string the name of the table
 * @var $columnListRemove string the list of columns to remove
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
    public int $scenario = self::SCENARIO_COLUMN_REMOVE;

    /** @var string Название таблицы */
    public string $tableName = '<?= $tableName ?>';

    /** @var array Список колонок для удаления */
    public array $columnListRemove = [
        //'column_name' => ['key_data']
        //'parent_id' => ['user' => 'id']
        //'category_id' => 'category_id'
        <?= $columnListRemove ?>
    ];



    /**
     * @return array
     */
    public function rollBackKeys(): array
    {
        return [
            // @see $this->foreignKeyList
            <?= $columnListRemove ?>
        ];
    }

    /**
     * @return array
     */
    public function rollBackColumns(): array
    {
        return [
            // 'key' => 'type',
            <?= $columnListRemove ?>
        ];
    }

}
