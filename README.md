
<h1 align="center">Yii2 migrate architect</h1>

Yii2 migrate architect - библиотека упрощающая написание миграций в Yii2.  
Предоставляя консольный интерфейс для создания файлов использующих упрощённую конструкцию для выполнения миграций.

Цель: сделать процесс написания миграций более быстрым, а файлы миграций более структурированными.

### Содержание:

- [Установка](#yii2-migrate-architect-setup)
- [Использование](#yii2-migrate-architect-use)

___

<h2 align="center"> <span id="yii2-migrate-architect-setup"></span>
    Установка
</h2>


<h3>Требования</h3> <span id="yii2-migrate-architect-setup-require"></span>

- php >=8.0
- Yii2

<h3>
    <a href="https://getcomposer.org/download/">Composer</a>
</h3> <span id="yii2-migrate-architect-setup-composer"></span>

## Добавление пакета в проект

<h3>Используя: консольные команды. <small><i>(Предпочтительней)</i></small></h3><span id="yii2-migrate-architect-setup-composer-cli"></span>

- используя composer, установленный локально
```bash
composer require andy87/yii2-migrate-architect
````  
- используя composer.phar
```bash
php composer.phar require andy87/yii2-migrate-architect
```
**Далее:** обновление зависимостей `composer install`


<h3>Используя: файл `composer.json`</h3><span id="yii2-migrate-architect-setup-composer-composer-json"></span>

Открыть файл `composer.json`  
В раздел, ключ `require` добавить строку  
`"andy87/yii2-migrate-architect": "*"`  
**Далее:** обновление зависимостей `composer install`

<p align="center">- - - - -</p>

В конфигурационном файле `config/web.php` добавить контроллер:  
`andy87\yii2\architect\components\controllers\ArchitectController`
```php
use andy87\yii2\architect\components\controllers\ArchitectController;

return [
    // ...
    'controllerMap' => [
        // ...
    
        'architect' => ArchitectController::class,
        // ...
    ],
    // ...
];

```
Кастомизация:
 - **ns** _namespace миграций_
 - **directoryTemplateMigrations** _путь к шаблонам миграций_
 - **migrateTemplateMapping** _маппинг шаблонов миграций_
 - **snippetsMigrationFilename** _шаблоны для генерации части имени файла миграпции_
```php
use andy87\yii2\architect\components\controllers\ArchitectController;

return [
    // ...
    'controllerMap' => [
        // ...
    
        'architect' => [
            'class' => ArchitectController::class,
            'ns' => 'name/space',
            'directoryTemplateMigrations' => '@app/path/to/migrations/template/',
            'migrateTemplateMapping' => [
                ArchitectController::MIGRATE_ADD => 'create_table_template',
                ArchitectController::MIGRATE_UPDATE => 'update_table_template',
            ],
            'snippetsMigrationFilename' => [
                ArchitectController::MIGRATE_ADD => 'create_table_%s',
                ArchitectController::MIGRATE_UPDATE => 'update_table_%s',
            ]
        ],
        // ...
    ],
    // ...
];
```
___

## Использование <span id="yii2-migrate-architect-use"></span>


Консольная команда:
```bash
  php yii architect
```
Запускает интерактивное меню для:
 - запуска миграций
 - создания миграций
   - с предустановленными шаблонами миграций использующих базовые классы:
     - `andy87\yii2\architect\CreateTable`
     - `andy87\yii2\architect\UpdateTable`

___

## Простые примеры миграций

### CreateTable.
columns
#### Создание таблицы
Колонки: `id`, `created_at`, `updated_at` создадутся автоматически
```php
<?php

use andy87\yii2\architect\CreateTable;

/**
 * Class m240626_210742_create_table__role
 */
class m240626_210742_add_table__role extends CreateTable
{
    /** @var string Название таблицы */
    protected string $tableName = '{{%role}}';
    
    /**
     * @return array
     */
    public function columns(): array
    {
        return  [
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'key' => $this->string(32)->notNull()->unique(),
            'name' => $this->string(64)->notNull()->unique(),
            'priority' => $this->integer(4)->defaultValue(1),
        ];
    }
}
```
Отмена создания колонок.  
для примера: `id`, `created_at`, `updated_at` не будут созданы
```php
<?php

use andy87\yii2\architect\CreateTable;

/**
 * Class m240626_210741_add_table__log
 */
class m240626_210741_add_table__log extends CreateTable
{
    /** @var string Название таблицы */
    protected string $tableName = '{{%log}}';
    
    /**
     * @return array
     */
    public function columns(): array
    {
        return  [
            self::COLUMN_ID => false,
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'key' => $this->string(32)->notNull()->unique(),
            'name' => $this->string(64)->notNull()->unique(),
            'priority' => $this->integer(4)->defaultValue(1),
            self::COLUMN_CREATED_AT => false,
            self::COLUMN_UPDATED_AT => null,
        ];
    }
}
```

### UpdateTable.

#### Добавление колонки 

```php
<?php

use andy87\yii2\architect\UpdateTable;


/**
 * Class m240626_210729_ext_table__user
 */
class m240626_210729_ext_table__user extends UpdateTable
{
    /** @var string Название таблицы */
    protected string $tableName = '{{%user}}';

    /**
     * Список колонок для добавления
     * 
     * @return array
     */
    public function columnsListAdd(): array
    {
        return [
            'verification_token' => $this->string()->defaultValue(null)
        ];
    }
}
```

#### Добавление колонок и внешних ключей

```php
<?php

use andy87\yii2\architect\UpdateTable;


/**
 * Class m240626_210728_ext_table__category
 */
class m240626_210728_ext_table__category extends UpdateTable
{
    /** @var string Название таблицы */
    protected string $tableName = 'category';

    /** @var array Мэппинг внешних ключей */
    protected array $foreignKeyList = [
        'user'      => 'id',
        'author_id' => ['user' => 'id'],
        'parent_id' => ['category' => 'id'],
    ];
    
    /**
     * Список колонок для добавления
     *
     * @return array
     */
    public function columnsListAdd(): array
    {
        return [
            'user_id'   => $this->integer(8)->notNull(),
            'author_id' => $this->integer(8)->notNull()->after('user_id'),
            'parent_id' => $this->integer(8)->null()->after('id'),
        ];
    }
}
```

#### Редактирование колонки

```php
<?php

use andy87\yii2\architect\UpdateTable;
use app\common\models\sources\Role;


/**
 * Class m240626_210729_update_table__user
 */
class m240626_210729_ext_table__user extends UpdateTable
{
    /** @var string Название таблицы */
    protected string $tableName = '{{%user}}';

    /**
     * Список колонок для изменения
     *
     * @return array
     */
    public function columnsListEdit(): array
    {
        return [
            'auth_key' => $this->string(64)->notNull()->unique(),
        ];
    }
}
```

#### Переименование колонки

```php
<?php

use app\common\models\sources\Role;
use andy87\yii2\architect\UpdateTable;

/**
 * Class m240626_210725_ext_table__product
 */
class m240626_210725_ext_table__product extends UpdateTable
{
    /** @var string Название таблицы */
    protected string $tableName = 'product';

    /** @var array Список колонок для переименования */
    protected array $renameColumnList = [
        'old_price' => 'price',
        'new_price' => 'price_new',
    ];
}
```
#### Переименование колонки

```php
<?php

use app\common\models\sources\Role;
use andy87\yii2\architect\UpdateTable;

/**
 * Class m240626_210735_ext_table__servcie
 */
class m240626_210735_ext_table__servcie extends UpdateTable
{
    /** @var string Название таблицы */
    protected string $tableName = 'servcie';

    /** @var array Список колонок для удаления */
    protected array $removeColumnList = [
        'comments',
        'property',
    ];
}
```

[Packagist](https://packagist.org/packages/andy87/yii2-migrate-architect)
