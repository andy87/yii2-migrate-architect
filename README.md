
<h1 align="center">Yii2 migrate architect</h1>

Yii2 migrate architect - библиотека для фреймворка Yii2 упрощающая написание кода миграций. 

Цель: сделать простой и быстрый инструмент добавления миграций.

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

В конфигурационном файле `config/console.php` добавить контроллер:  
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
Пример с кастомизацией:
- **directoryTemplateMigrations** _путь к шаблонам миграций_
- **migrateTemplateMapping** _маппинг шаблонов миграций_
- **snippetsMigrationFilename** _шаблоны имён файлов миграции_

```php
use andy87\yii2\architect\components\controllers\ArchitectController;

return [
    // ...
    'controllerMap' => [
        // ...
    
        'architect' => [
            'class' => ArchitectController::class,
            'directoryTemplateMigrations' => '@app/path/to/migrations/template/',
            'migrateTemplateMapping' => [
                ArchitectController::SCENARIO_COLUMN_ADD => 'create_table_template',
                ArchitectController::SCENARIO_UPDATE => 'update_table_template',
                //,,,
            ],
            'snippetsMigrationFilename' => [
                ArchitectController::SCENARIO_COLUMN_ADD => 'create_table__%s',
                ArchitectController::SCENARIO_UPDATE => 'update_table__%s',
                //,,,
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

```
> php yii architect                             
Yii Migration Tool (based on Yii v2.0.51-dev)

Select action:
 1. Create migration
 2. Apply migrations
 3. Down migrations
 4. Run migrations
 0. Exit
--------------------
 variant: 2
`Create migration`:
 1. Create table
 2. Update column
 3. Add column
 4. Rename column
 5. Remove column
 0. Exit
--------------------
action: 1

Table name: product
Create new migration '../console/migrations\m240628_072029_create_table__product.php'? (yes|no) [no]:y
New migration created successfully.

```

___

## Простые примеры миграций

### CreateTable.

#### Создание таблицы
Колонки: `id`, `created_at`, `updated_at` создадутся автоматически
```php
<?php

use andy87\yii2\architect\CreateTable;

/**
 * Class m240626_210742_create_table__role
 */
class m240626_210742_create_table__role extends CreateTable
{
    public string $scenario = self::SCENARIO_CREATE;

    /** @var string Название таблицы */
    public string $tableName = '{{%role}}';
    
    /** 
     * @var array Список для генерации внешних ключей.
     * Для примера добавлены два внешних ключа.
     */
    public array $foreignKeyList = [
        'module' => 'id', // fk--role-module_id--module-id
        'sub_module_id' => ['module' => 'id'], // fk--role-sub_module_id--module-id  
    ];

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
            'module_id' => $this->integer(4)->notNull(),
            'sub_module_id' => $this->integer(4)->notNull(),
        ];
    }
}
```
Отмена создания колонок, происходит через назначение значения `false` или `null` для колонки.
Для примера: `id`, `updated_at` не будут созданы
```php
<?php

use andy87\yii2\architect\CreateTable;

/**
 * Class m240626_210741_create_table__log
 */
class m240626_210741_create_table__log extends CreateTable
{
    public string $scenario = self::SCENARIO_CREATE;

    /** @var string Название таблицы */
    public string $tableName = 'log';
    
    /**
     * @return array
     */
    public function columns(): array
    {
        return  [
            self::COLUMN_ID => false,
            'text' => $this->string(32)->notNull()->unique(),
            self::COLUMN_UPDATED_AT => null,
        ];
    }
}
```

### UpdateTable.

#### Добавление колонки
Для добавления колонки, необходимо переопределить метод `columnsListAdd`, вернув массив с описанием колонок.
```php
<?php

use andy87\yii2\architect\UpdateTable;


/**
 * Class m240626_210729_update_table__user
 */
class m240626_210729_update_table__user extends UpdateTable
{
    public string $scenario = self::SCENARIO_COLUMN_ADD;
    
    /** @var string Название таблицы */
    public string $tableName = 'user';

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
Для добавления колонок и внешних ключей, необходимо переопределить методы `columnsListAdd` и `foreignKeyList`, вернув массивы с описанием колонок и внешних ключей.
```php
<?php

use andy87\yii2\architect\UpdateTable;


/**
 * Class m240626_210728_update_table__category
 */
class m240626_210728_update_table__category extends UpdateTable
{
    public string $scenario = self::SCENARIO_COLUMN_ADD;

    /** @var string Название таблицы */
    protected string $tableName = 'category';

    /** @var array Мэппинг внешних ключей */
    protected array $foreignKeyList = [
        'user'      => 'id', // fk--category-user_id--user-id
        'author_id' => ['user' => 'id'], // fk--category-author_id--user-id
        'parent_id' => ['category' => 'id'], // fk--category-parent_id--category-id
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

#### Редактирование таблицы
Для редактирования колонки, необходимо переопределить метод `columnsListUpdate`, вернув массив с описанием колонок.
```php
<?php

use andy87\yii2\architect\UpdateTable;
use app\common\models\sources\Role;

/**
 * Class m240626_210729_update_table__user
 */
class m240626_210729_update_table__user extends UpdateTable
{
    public string $scenario = self::SCENARIO_UPDATE;

    /** @var string Название таблицы */
    protected string $tableName = 'user';

    /**
     * Список колонок для изменения
     *
     * @return array
     */
    public function columnsListUpdate(): array
    {
        return [
            'auth_key' => $this->string(64)->notNull()->unique(),
            'author_id' => $this->integer(4)->notNull()->unique(),
        ];
    }
}
```

#### Переименование колонки
Для переименования колонки, необходимо переопределить свойство `renameColumnList`, вернув массив с мэппингом старых и новых названий колонок.
```php
<?php

use app\common\models\sources\Role;
use andy87\yii2\architect\UpdateTable;

/**
 * Class m240626_210725_update_table__product
 */
class m240626_210725_update_table__product extends UpdateTable
{
    public string $scenario = self::SCENARIO_COLUMN_RENAME;

    /** @var string Название таблицы */
    protected string $tableName = 'product';

    /** @var array Список колонок для переименования */
    protected array $renameColumnList = [
        'old_price' => 'price',
        'new_price' => 'price_new',
    ];
}
```
#### Удаление колонок
Для удаления колонок, необходимо переопределить свойство `removeColumnList`, вернув массив с названиями колонок для удаления в ключе, а в значении массив указывающий на связи через внешние ключи.
```php
<?php

use app\common\models\sources\Role;
use andy87\yii2\architect\UpdateTable;

/**
 * Class m240626_210735_update_table__service
 */
class m240626_210735_update_table__service extends UpdateTable
{
    public string $scenario = self::SCENARIO_COLUMN_REMOVE;

    /** @var string Название таблицы */
    protected string $tableName = 'service';

    /** @var array Список колонок для удаления */
    protected array $removeColumnList = [
        'comments' => null,
        'property' => null,
        'user_id' => ['user' => 'id'],
    ];
}
```

[Packagist](https://packagist.org/packages/andy87/yii2-migrate-architect)
