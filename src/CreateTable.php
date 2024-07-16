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
    /** @var string Комментарий таблицы */
    public string $tableComment = '';



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
     * Получение списка колонок пользователя
     *
     * @return array
     */
    abstract public function columns(): array;

    /**
     * Добавление в список колонок стандартные поля: ID, created_at, updated_at
     *
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
            } elseif (!$columns[$key]) {

                unset($columns[$key]);
            }
        }

        return $columns;
    }

    /**
     * Получение опций для создаваемых колонок: created_at, updated_at
     *
     * @param string $comment
     *
     * @return ColumnSchemaBuilder
     */
    protected function getSchemaDateTime(string $comment): ColumnSchemaBuilder
    {
        $type = match (static::DATETIME)
        {
            self::DATETIME_TIMESTAMP => $this->timestamp(),
            self::DATETIME_DATETIME => $this->dateTime(),
        };

        $expression = match(self::DATETIME)
        {
            self::DATETIME_TIMESTAMP => 'CURRENT_TIMESTAMP',
            self::DATETIME_DATETIME => 'NOW()',
        };

        return $type
            ->defaultExpression($expression)
            ->append("ON UPDATE $expression")
            ->comment($comment);
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
        if (parent::safeDown() === ExitCode::OK)
        {
            $this->dropTable($this->tableName);

            return ExitCode::OK;
        }

        return ExitCode::DATAERR;
    }
}