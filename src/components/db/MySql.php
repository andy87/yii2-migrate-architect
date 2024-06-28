<?php

namespace andy87\yii2\architect\components\db;

/**
 * Параметры для базы MySQL
 */
abstract class MySql
{
    /** @var string Yii2 драйвер */
    public const DRIVER = 'mysql';


    /** @var string Кодировка */
    public const CHARACTER = 'utf8mb4';

    /** @var string Сравнение */
    public const COLLATE = 'utf8mb4_unicode_ci';

    /** @var string Движок */
    public const ENGINE = 'InnoDB';



    /**
     * @return string
     */
    public static function getOptions(): string
    {
        return sprintf('CHARACTER SET %s COLLATE %s ENGINE=%s',self::CHARACTER,self::COLLATE,self::ENGINE);
    }
}