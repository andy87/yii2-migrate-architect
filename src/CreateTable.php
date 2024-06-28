<?php declare(strict_types=1);

namespace andy87\yii2\architect;

use yii\db\ColumnSchemaBuilder;
use yii\console\{ ExitCode, Exception };

/**
 * Class CreateTable
 * 
 * @package andy87\yii2\architect
 */
abstract class CreateTable extends components\migrations\Architect
{
    /** @var ?string Комментарий таблицы */
    protected string $tableComment = '';



    /**
     * Применение миграций
     *
     * @return int
     *
     * @throws Exception
     */
    final public function safeUp(): int
    {
        $columns = $this->prepareColumns(
            $this->columns()
        );

        $this->createTable( $this->tableName, $columns, $this->getTableOptions() );

        if ( strlen($this->tableComment) > 0 ) {
            $this->addCommentOnTable($this->tableName, $this->tableComment);
        }

        return parent::safeUp();
    }

    /**
     * Получение списка колонок
     *
     * @return array
     */
    abstract public function columns(): array;

    /**
     * @param array $columns
     *
     * @return array
     */
    private function prepareColumns( array $columns ): array
    {
        foreach ( $this->columnLabels as $key => $label )
        {
            if (!isset($columns[$key]))
            {
                switch ($key)
                {
                    case self::COLUMN_ID:
                        $columns = array_merge(
                            [self::COLUMN_ID => $this->primaryKey()->comment($this->columnLabels[self::COLUMN_ID]) ], 
                            $columns
                        );
                        break;

                    case self::COLUMN_CREATED_AT:
                    case self::COLUMN_UPDATED_AT:
                    $columns[$key] = $this->getSchemaDateTime($label);
                        break;
                }
            } elseif (!$columnList[$key]) {

                unset($columnList[$key]);
            }
        }

        return $columnList;
    }

    /**
     * @param string $comment
     *
     * @return ColumnSchemaBuilder
     */
    protected function getSchemaDateTime(string $comment): ColumnSchemaBuilder
    {
        $type = null;

        $expression = [
            self::DATETIME_TIMESTAMP => 'CURRENT_TIMESTAMP',
            self::DATETIME_DATETIME => 'NOW()',
        ];
        $expression = $expression[self::DATETIME];

        $type = match (self::DATETIME)
        {
            self::DATETIME_TIMESTAMP => $this->timestamp(),
            self::DATETIME_DATETIME => $this->dateTime(),
        };
        
        return $type
            ->defaultExpression($expression)
            ->append("ON UPDATE $expression")
            ->comment($comment);
    }
    


    /**
     * @return int
     */
    final public function safeDown(): int
    {
        if (parent::safeDown() === ExitCode::OK)
        {
            $this->dropTable($this->tableName);

            return ExitCode::OK;
        }

        return ExitCode::DATAERR;
    }
}