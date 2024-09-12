<?php

/**
 * @var $className string the new migration class name without namespace
 * @var $namespace string the new migration class namespace
 * @var $tableName string the name of the table
 * @var $columnListRename string the list of columns to rename
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
    public int $scenario = self::SCENARIO_COLUMN_RENAME;

    /** @var string Название таблицы */
    public string $tableName = '<?= $tableName ?>';



    /** @var array Список колонок для переименования */
    public array $columnListRename = [
        //'old_column' => 'new_column',
        <?= $columnListRename ?>
    ];
}
