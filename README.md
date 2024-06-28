
<h1 align="center">Yii2 migrate architect</h1>

<p align="center"><img src="assets/logo/yii2-migrate-architectLogo_256.png" style="width:256px; height: auto" alt="yii2-migrate-architect php curl facade"/></p>

Yii2 migrate architect - библиотека упрощающая написание кода миграций в Yii2

Цель: сделать простой и лёгкий в настройке компонента и запроса пакет.

P.S. я знаю про существование таких библиотек как: [Guzzle](https://github.com/guzzle/guzzle), [Client](https://github.com/yiisoft/yii2-httpclient) _(в моём любимом Yii2)_, но хотелось попробовать создать свою реализацию.  
Без "лишних" данных, вызовов и настроек, nullWarningStyle - только то, что нужно: сухо, лаконично, минималистично.  
_Разумеется, это не конкурент, а просто попытка создать что-то своё_

### Содержание:

- [Установка](#yii2-migrate-architect-setup)
- [Использование](#yii2-migrate-architect-use)

___

<h2 align="center"> <span id="yii2-migrate-architect-setup"></span>
    Установка
</h2>


<h3>Требования</h3> <span id="yii2-migrate-architect-setup-require"></span>

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
        ],
        // ...
    ],
    // ...
];
___



## Простые примеры <span id="yii2-migrate-architect-use"></span>

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
 
### CreateTable.

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
    public function сolumns(): array
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
    public function сolumns(): array
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


<p align="center">- - - - -</p>

___



<h2 align="center">Базовый класс</h2> <span id="yii2-migrate-architect-src-Operator"></span>

_use [andy87\knock_knock\core\Operator](src/core/Operator.php);_

PHP Фасад\Адаптер для отправки запросов через ext cURL

<h3>ReadOnly свойства:</h3> <span id="yii2-migrate-architect-src-Operator-readonly"></span>

- **commonRequest**
    - _Объект содержащий параметры, назначаемые всем исходящим запросам_
- **realRequest**
    - _Используемый запрос_
- **eventHandlers**
    - _Список обработчиков событий_
- **host**
    - _Хост, на который будет отправляться запросы_
- **logs**
    - _Список логов_

Возможности/фичи:
- Настройки параметров запросов
- Защита данных от перезаписи
- Обработчики событий
- Инкапсуляция
- Singleton
- логирование

#### ВАЖНЫЙ МОМЕНТ!
- В классах применяется инкапсуляция, поэтому для доступа к свойствам компонентов используются ReadOnly свойства.
- `CURL_OPTIONS` по умолчанию пустые! В большинстве случаев, для получения валидных ответов, требуется задать необходимые настройки.



<h2 align="center">"Получение" объекта/экземпляра класса</h2> <span id="yii2-migrate-architect-src-Operator-construct"></span>

Передавая параметры напрямую в конструктор:
```php
$operator = new Operator( $_ENV['API_HOST'], $commonRequestParams );
``` 
Применяя, паттерн Singleton:
```php
$operator = Operator::getInstance( $_ENV['API_HOST'], $commonRequestParams );
```
Методы возвращают объект(экземпляр класса `Operator`), принимая на вход два аргумента:
- `string $host` - хост
- `array $operatorConfig` - массив с настройками для всех исходящих запросов.

При создании объекта `Operator` будет вызван метод `init()`, который запускает пользовательские инструкции.  
После выполнения `init()` запускается обработчик события привязанный к ключу `EVENT_AFTER_CONSTRUCT`

<h2 align="center" id="yii2-migrate-architect-src-Operator-params">
  Общие настройки запросов
</h2>
Что бы указать настройки применяемые ко всем исходящим запросам,  
при создании объекта `Operator` передаётся массив (ключ - значение), с необходимыми настройками.

Пример настройки:
```php
// настройки для последующих исходящих запросов
$commonRequestParams = [
    Request::SETUP_PROTOCO => $_ENV['API_PROTOCOL'],
    Request::SETUP_CONTENT_TYPE => Request::CONTENT_TYPE_JSON,
    Request::SETUP_CURL_OPTIONS => [
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true
    ]
];
// Получаем компонент для отправки запросов
$operator = new Operator( $_ENV['API_HOST'], $commonRequestParams );

//Применяя, паттерн Singleton:
$operator = Operator::getInstance( $_ENV['API_HOST'], $commonRequestParams );
```
Доступные ключи для настройки(константы класса `Request`):

- `SETUP_PROTOCOL`
- `SETUP_HOST`
- `SETUP_METHOD`
- `SETUP_HEADERS`
- `SETUP_CONTENT_TYPE`
- `SETUP_DATA`
- `SETUP_CURL_OPTIONS`
- `SETUP_CURL_INFO`


<h2>Обработчики событий</h2> <span id="yii2-migrate-architect-src-events-setupEventHandlers"></span>

<h3>Список событий</h3> <span id="yii2-migrate-architect-src-events-list"></span>

- `EVENT_AFTER_CONSTRUCT` после создания объекта yii2-migrate-architect
- `EVENT_CREATE_REQUEST` после создания объекта запроса
- `EVENT_BEFORE_SEND` перед отправкой запроса
- `EVENT_CURL_Operator` перед отправкой curl запроса
- `EVENT_CREATE_RESPONSE` после создания объекта ответа
- `EVENT_AFTER_SEND` после получения ответа

<h5>Пример установки обработчиков событий</h5> <span id="yii2-migrate-architect-src-Handler-events-example"></span>

```php
$operator->setupEventHandlers([
    Operator::EVENT_AFTER_CONSTRUCT => function( Operator $operator ) {
        // ...
    },
    Operator::EVENT_CREATE_REQUEST => function( Operator $operator, Request $request ) {
        // ...
    },
    Operator::EVENT_BEFORE_SEND => function( Operator $operator, Request $request ) {
        // ...
    },
    Operator::EVENT_CURL_HANDLER => function( Operator $operator, resource $ch ) {
        // ...
    },
    Operator::EVENT_CREATE_RESPONSE => function( Operator $operator, Response $response ) {
        // ...
    },
    Operator::EVENT_AFTER_SEND => function( Operator $operator, Response $response ) {
        // ...
    }
]);
```
Первый аргумент - ключ события, второй - callback функция.

Все callback функции принимают первым аргументом объект/экземпляр класса `Operaotr`.  
Вторым аргументом передаётся объект/экземпляр класса в зависимости от события:
- `Request` - для событий `EVENT_CREATE_REQUEST`, `EVENT_BEFORE_SEND`
- `Response` - для событий `EVENT_CREATE_RESPONSE`, `EVENT_AFTER_SEND`


<p align="center">- - - - -</p>

___



<h1 align="center">Запрос</h1><span id="yii2-migrate-architect-src-Request"></span>

_use [andy87\knock_knock\core\Request](src/core/Request.php);_

Объект запроса, содержащий данные для отправки запроса.

<h3>ReadOnly свойства:</h3> <span id="yii2-migrate-architect-src-Request-readonly"></span>

- **protocol** - _протокол_
- **host** - _хост_
- **endpoint** - _конечная точка_
- **method** - _метод_
- **headers** - _заголовки_
- **contentType** - _тип контента_
- **data** - _данные_
- **curlOptions** - _опции cURL_
- **curlInfo** - _информация cURL_
- **params** - _параметры запроса_
- **url** - _полный URL_
- **params** - _все свойства в виде массива_
- **fakeResponse** - _установленные фэйковые данные ответа_
- **errors** - _лог ошибок_

<h3 align="center">Создание объекта запроса</h3> <span id="yii2-migrate-architect-src-Request-construct"></span>

Передавая параметры напрямую в конструктор:
```php
$request = new Request( 'info/me', [
    Request::METHOD => Method::POST,
    Request::DATA => [ 'client_id' => 34 ],
    Request::HEADERS => [ 'api-secret-key' => $_ENV['API_SECRET_KEY'] ],
    Request::CURL_OPTIONS => [ CURLOPT_TIMEOUT => 10 ],
    Request::CURL_INFO => [
        CURLINFO_CONTENT_TYPE,
        CURLINFO_HEADER_SIZE,
        CURLINFO_TOTAL_TIME
    ],
    Request::CONTENT_TYPE => ContentType::FORM_DATA,
]);
```
Методом, который вызывает _callback_ функцию, привязанную к ключу `EVENT_CREATE_REQUEST`
```php
$request = $operator->constructRequest(Method::GET, 'info/me', [
    Request::METHOD => Method::POST,
    Request::DATA => [ 'client_id' => 45 ],
    Request::HEADERS => [ 'api-secret-key' => $_ENV['API_SECRET_KEY'] ],
    Request::CURL_OPTIONS => [ CURLOPT_TIMEOUT => 10 ],
    Request::CURL_INFO => [
        CURLINFO_CONTENT_TYPE,
        CURLINFO_HEADER_SIZE,
        CURLINFO_TOTAL_TIME
    ],
    Request::CONTENT_TYPE => ContentType::FORM_DATA,
]);
```
Клонируя существующий объект запроса:
```php
$request = $operator->constructRequest(Method::GET, 'info/me');

$response = $operator->send($request);

//Клонирование объекта запроса (без статуса отправки)
$cloneRequest = $request->clone();

// Отправка клона запроса
$response = $operator->setupRequest( $cloneRequest )->send();
```

<h3>
    Назначение/Изменение/Получение отдельных параметров запроса (set/get)
</h3> <span id="yii2-migrate-architect-src-Request-setter-getter"></span>

Таблица set/get методов для взаимодействия с отдельными свойствами запроса

| Параметр        | Сеттер                                | Геттер                     | Информация                                                                                                                                                                   |
|-----------------|---------------------------------------|----------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Протокол        | setProtocol( string $protocol )       | getProtocol(): string      | <a href="https://curl.se/docs/protdocs.html" target="_blank">протоколы</a>                                                                                                   |
| Хост            | setHost( string $host )               | getHost(): string          | ---                                                                                                                                                                          |
| Endpoint        | setEndpoint( string $url )            | getEndpoint(): string      | ---                                                                                                                                                                          |
| Метод           | setMethod( string $method )           | getMethod(): string        | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods" target="_blank">методы</a>                                                                               |
| Заголовки       | setHeaders( array $headers )          | getHeaders(): array        | <a href="https://ru.wikipedia.org/wiki/%D0%A1%D0%BF%D0%B8%D1%81%D0%BE%D0%BA_%D0%B7%D0%B0%D0%B3%D0%BE%D0%BB%D0%BE%D0%B2%D0%BA%D0%BE%D0%B2_HTTP" target="_blank">заголовки</a> |
| Тип контента    | setContentType( string $contentType ) | getContentType(): string   | <a href="https://ru.wikipedia.org/wiki/%D0%A1%D0%BF%D0%B8%D1%81%D0%BE%D0%BA_MIME-%D1%82%D0%B8%D0%BF%D0%BE%D0%B2" target="_blank">Тип контента</a>                            |
| Данные          | setData( mixed $data )                | getData(): mixed           | ---                                                                                                                                                                          |
| Опции cURL      | setCurlOptions( array $curlOptions )  | getCurlOptions(): array    | <a href="https://www.php.net/manual/ru/function.curl-setopt.php" target="_blank">Опции cURL</a>                                                                              |
| Информация cURL | setCurlInfo( array $curlInfo )        | getCurlInfo(): array       | <a href="https://www.php.net/manual/ru/function.curl-getinfo.php" target="_blank">Информация cURL</a>                                                                        |
| Фэйковый ответ  | setFakeResponse( array $response )    | getFakeResponse(): array   |                                                                                                                                                                              |

```php
$request = $operator->constructRequest(Method::GET, 'info/me');

$request->setMethod( Method::GET );
$request->setData(['client_id' => 67]);
$request->setHeaders(['api-secret-key' => 'secretKey67']);
$request->setCurlOptions([
    CURLOPT_TIMEOUT => 10,
    CURLOPT_RETURNTRANSFER => true
]);
$request->setCurlInfo([
    CURLINFO_CONTENT_TYPE,
    CURLINFO_HEADER_SIZE,
    CURLINFO_TOTAL_TIME
]);
$request->setContentType( ContentType::JSON );

$protocol = $request->getPrococol(); // String
$host = $request->getHost(); // String
// ... аналогичным образом доступны и другие подобные методы для получения свойств запроса
```
<h3>Назначение запроса с переназначением свойств</h3> <span id="yii2-migrate-architect-src-Request-setupRequest"></span>

```php
$operator->setupRequest( $request, [
    Request::SETUP_HOST => $_ENV['API_HOST'],
    Request::SETUP_HEADERS => [
        'api-secret' => $_ENV['API_SECRET_KEY']
    ],
]);
```
`setupRequest( Request $request, array $options = [] ): self`


##### addError( string $error )
Добавление ошибки в лог ошибок
```php
$request = $operator->constructRequest(Method::GET, 'info/me');

$request->addError('Ошибка!');

```


<p align="center">- - - - -</p>

___



<h1 align="center">Ответ</h1><span id="yii2-migrate-architect-src-Response"></span>

_use [andy87\knock_knock\core\Response](src/core/Response.php);_

Объект ответа, содержащий данные ответа на запрос.
<h3>ReadOnly свойства</h3> <span id="yii2-migrate-architect-src-Response-readonly"></span>

- **content**
    - _данные ответа_
- **httpCode**
    - _код ответа_
- **request**
    - _объект запроса, содержащий данные о запросе_
- **curlOptions**
    - _быстрый доступ к request->curlOptions_
- **curlInfo**
    - _быстрый доступ к request->curlInfo_

<h3 align="center">Создание объекта ответа</h3> <span id="yii2-migrate-architect-src-Response-construct"></span>

Передавая параметры напрямую в конструктор:
```php
$response = new Response('{"id" => 806034, "name" => "and_y87"}', 200 );
```
Методом, который вызывает _callback_ функцию, привязанную к ключу `EVENT_CREATE_RESPONSE`
```php
$response = $operator->constructResponse([
    Response::CONTENT => [
        'id' => 806034,
        'name' => 'and_y87'
    ],
    Response::HTTP_CODE => 400,
], $request );
```
`constructResponse( array $responseParams, ?Request $request = null ): Response`

<h2>Отправка запроса</h2> <span id="yii2-migrate-architect-src-Handler-send"></span>

`send( ?Request $request = null ): Response`
Вызов возвращает объект/экземпляр класса `Response`.  
Срабатывают callback функции, привязанные к ключам:
- `EVENT_AFTER_SEND`
- `EVENT_CREATE_RESPONSE`
- `EVENT_BEFORE_SEND`
- `EVENT_CURL_HANDLER`

```php
$operator = new Operator( $_ENV['API_HOST'] );
$request = $operator->constructRequest(Method::GET, 'info/me');
$response = $operator->send($request);

// Аналог
$operator = new Operator( $_ENV['API_HOST'] );
$response = $operator->send( $operator->constructRequest(Method::GET, 'info/me') );
```
Нельзя повторно отправить запрос, выбрасывается исключение `RequestCompleteException`.
Для повторной отправки запроса, необходимо создать новый объект запроса и использовать его:
```php
$operator = new Operator( $_ENV['API_HOST'] );
$request = $operator->constructRequest(Method::GET, 'info/me');
$response = $operator->send($request);

// повторная отправка запроса
$response = $operator->send($request->clone());
```

<h2>Отправка запроса с фэйковым ответом</h2> <span id="yii2-migrate-architect-src-Handler-fakeResponse"></span>


```php
// параметры возвращаемого ответа
$fakeResponse = [
    Response::HTTP_CODE => 200,
    Response::CONTENT => '{"id" => 8060345, "nickName" => "and_y87"}'
];
$request->setFakeResponse( $fakeResponse );

$response = $operator->send( $request );
```
объект `$response` будет содержать в свойствах `content`, `httpCode` данные переданные в аргументе `$fakeResponse`

<h2>Данные в ответе</h2> <span id="yii2-migrate-architect-src-Response-setter"></span>

В созданный объект `Response`, чей запрос не был отправлен, разрешено задавать данные, используя методы группы `set`.
```php
$response = $operator->send($request);

$response
    ->setHttpCode(200)
    ->setContent('{"id" => 8060345, "nickName" => "and_y87"}');
```
**Внимание!** Если данные в объекте уже существуют, повторно задать их нельзя выбрасывается `ParamUpdateException`.  
В случае необходимости заменить данные, используется вызов метода `replace( string $key, mixed $value )` см. далее

<h3 id="yii2-migrate-architect-src-Response-replace">
    Подмена данных
</h3> <span></span>
Это сделано для явного действия, когда необходимо заменить данные в объекте `Response`.

```php
$response = $operator->send($request);

$response
    ->replace( Response::HTTP_CODE, 200 )
    ->replace( Response::CONTENT, '{"id" => 8060345, "nickName" => "and_y87"}' );
```

<h2>Данные запроса из ответа</h2> <span id="yii2-migrate-architect-src-Response-request"></span>

Для получения из объекта `Response` данных запроса, необходимо обратиться к ReadOnly свойству `request`  
и далее взаимодействовать с ним аналогично объекту `Request`
```php
$operator = new Operator( $_ENV['API_HOST'] );
$response = $operator->setRequest( $operator->constructRequest(Method::GET, 'info/me') )->send();

// Получение компонента запроса
$request = $response->request;

$method = $request->method; // получение метода запроса
```

Получения свойств cURL запроса
```php
$operator = new Operator( $_ENV['API_HOST'] );
$response = $operator->setRequest( $operator->constructRequest(Method::GET, 'info/me') )->send();

$response->request;

// Получение свойств через объект запроса
$curlOptions =  $response->request->curlOption;
$curlInfo =  $response->request->curlInfo;

//Вариант с использованием быстрого доступа
$curlOptions =  $response->curlOption;
$curlInfo =  $response->curlInfo;
```
<h3>asArray()</h3> <span id="yii2-migrate-architect-src-Response-asArray"></span>

Преобразование в массив.
- преобразование данных ответа на запрос `asArray()`
- преобразование всего объекта в массив `asArray(true)`
```php
$response = $operator->send($request)->asArray(); // $response
$array = $response->content; // Array$response
```
##### addError( string $error )
Добавление ошибки в лог ошибок
```php
$request = $operator->constructRequest(Method::GET, 'info/me');

$response = $operator->send($request);

$response->addError('Ошибка!');

```

<p align="center">- - - - -</p>

___

<p align="center">- - - - -</p>


<h1 align="center" >Дополнительные возможности</h1> <span id="yii2-migrate-architect-src-feature"></span>

<h3>SSL</h3> <span id="yii2-migrate-architect-src-ssl"></span>

Функционал включения/отключения SSL верификации в объектах `Operaotr` & `Request`.

В `curlOptions` добавляется ключ `CURLOPT_SSL_VERIFYPEER` и `CURLOPT_SSL_VERIFYHOST`.

`->disableSSL( bool $verifyPeer = false, int $verifyHost = 0 );`  
`->enableSSL( bool $verifyPeer = true, int $verifyHost = 2 );`

`Operaotr` - для всех запросов
```php
$operator = new Operator( $_ENV['API_HOST'] );
$operator->disableSSL();

$request = $operator->constructRequest(Method::GET, 'info/me');

$response = $operator->setupRequest( $request )->send();
```

`Request` - для конкретного запроса
```php
$operator = new Operator( $_ENV['API_HOST'] )->disableSSL();

$request = $operator->constructRequest(Method::GET, 'info/me');
$request->enableSSL();

$response = $operator->setupRequest( $request )->send();
```
<h3>Cookie</h3> <span id="yii2-migrate-architect-src-Cookie"></span>

В объекте `Operaotr` имеется функционал использования cookie.  
`Operaotr` - для всех запросов
```php
$operator = new Operator( $_ENV['API_HOST'] );

$cookie = $_ENV['COOKIE'];
$jar = $_ENV['COOKIE_JAR'];

$operator->useCookie( $cookie, $jar );
```  
`$operator->useCookie( string $cookie, string $jar, ?string $file = null )`  
по умолчанию `$file = null` и  `$file` приравнивается к `$jar`

<h3>Логирование</h3> <span id="yii2-migrate-architect-src-logs"></span>

Добавление сообщений во внутренний массив `logs`

```php
$operator = new Operator( $_ENV['API_HOST'] );

$operator->addLog( 'Какое то сообщение' );
```
`$operator->addLog( string $message )`


<p align="center">- - - - -</p>

___

<p align="center">- - - - -</p>


<h1 align="center">Расширения на основе базового класса</h1><span  id="yii2-migrate-architect-extends"></span>

<h3 align="center">
    <a href="docs/yii2-migrate-architect/yii2-migrate-architectOctopus.md" target="_blank">
        yii2-migrate-architectOctopus
        <br>
        <img src="assets/logo/yii2-migrate-architectOctopus_320.png" style="width:200px; height: auto" alt="yii2-migrate-architectOctopus php curl facade"/>
    </a>
</h3> <span id="yii2-migrate-architect-Octopus"></span>

Класс с функционалом простой реализации отправки запросов и минимальными настройками

<h4>Доступные методы.</h4> <span id="yii2-migrate-architect-Octopus-methods"></span>

| get() | post() | put() | patch() | delete() | head() | options() | trace() |
|-------|--------|-------|---------|----------|--------|-----------|---------|

<h4>Каждый метод принимает два аргумента:</h4> <span id="yii2-migrate-architect-Octopus-methods-args"></span>

| Аргумент  |   Тип   | Обязательный  | Описание                       |
|:----------|:-------:|:-------------:|:-------------------------------|
| $endpoint | string  |      Да       | URL запроса (без хоста)        |
| $params   |  array  |      Нет      | Данные запроса в виде массива  |
_P.S. host задаётся в конструкторе_

<h4>Простой пример использования</h4> <span id="yii2-migrate-architect-Octopus-methods-example"></span>

```php
$yii2-migrate-architectOctopus = new yii2-migrate-architectOctopus($_ENV['API_URL']);

$yii2-migrate-architectOctopus->get( '/profile', [ 'id' => 806034 ] ); //GET запрос

$yii2-migrate-architectOctopus->post( '/new', [  //POST запрос
    'name' => 'Новая новость',
    'content' => 'Текст новости' 
]);
```


<p align="center"> - - - - - </p>

<h3 align="center">
    <a href="docs/yii2-migrate-architect/yii2-migrate-architectSecurity.md" target="_blank">
        yii2-migrate-architectSecurity
        <br>
        <img src="assets/logo/yii2-migrate-architectSecurity_280.png" style="width:auto; height: 128px" alt="yii2-migrate-architectSecurity php curl facade"/>
    </a>
</h3> <span id="yii2-migrate-architect-security"></span>

Расширяет класс [yii2-migrate-architectOctopus](docs/yii2-migrate-architect/yii2-migrate-architectOctopus.md), предоставляя доступ к функционалу для простой и  
быстрой реализации авторизации, и настройки запросов.

```php
$yii2-migrate-architectSecurity = new yii2-migrate-architectSecurity($_ENV['API_URL']);

// Настройка параметров запроса по умолчанию
$yii2-migrate-architectSecurity
    ->disableSSL()
    ->setupAuthorization( yii2-migrate-architectSecurity::TOKEN_BEARER, 'token' )
    ->setupHeaders([ 'X-Api-Key' => $_ENV['X_API_KEY'] ])
    ->setupContentType( ContentType::JSON )
    ->on( Operator::EVENT_AFTER_SEND, function( Operator $operator, Response $response ) => 
    {
        $logFilePath = $_SERVER['DOCUMENT_ROOT'] . '/api_log.txt';

        file_put_contents( $logFilePath, $response->content, FILE_APPEND );
    });

// Получение ответа на запрос методом `patch`
$responsePatch = $yii2-migrate-architectSecurity->patch( 'product', [
    'price' => 1000
]);

$product = $responsePatch->asArray();

$price = $product['price'];

// Изменение типа контента на `application/json`, для следующего запроса
$yii2-migrate-architectSecurity->useContentType( ContentType::JSON );

// Отправка POST запроса и получение ответа
$responsePost = $yii2-migrate-architectSecurity->post( 'category', [
    'name' => 'Фреймворки'
]);

$response = json_decode( $responsePost->content );

$category_id = $response->id;

```


<p align="center">- - - - -</p>

___

<p align="center">- - - - -</p>


<h1>Custom реализация</h1> <span id="yii2-migrate-architect-Custom"></span>

Custom реализация Базового класса, к примеру с добавлением логирования работающим "под капотом"
```php
class yii2-migrate-architectYandex extends Operator
{
    private const LOGGER = 'logger';


    private string $host = 'https://api.yandex.ru/'

    private string $contentType = ContentType::JSON

    private YandexLogger $logger;



    /**
     * @return void
     */
    public function init(): void
    {
        $this->setupYandexLoggerEventHandlers();
    }
    
    /**
     * @param array $callbacks
     * 
     * @return self
     */
    private function setupYandexLoggerEventHandlers( array $callbacks ): self
    {
        $this->on( self::AFTER_CREATE_REQUEST, function( Request $request ) => 
        {
            $logData = $this->getLogDataByRequest( $request );

            $this->addYandexLog( $logData );
        };

        $this->on(self::EVENT_AFTER_SEND, function( Response $response ) => 
        {
            $logData = $this->getLogDataByRequest( $response->request );

            $this->addYandexLog( $logData );
        };
    }

    /**
      * @param Request $request
      * 
      * @return array
      */
    private function getLogDataByRequest( Request $request ): array
    {
        return $request->getParams();
    }

    /**
     * @param array $logData
     * 
     * @return void
     */
    private function addYandexLog( array $logData ): bool
    {
        return $logger->log( $logData );
    }
}

```
<h3>
    Пример использования custom реализации
</h3> <span id="yii2-migrate-architect-Custom-use"></span>

```php

$yii2-migrate-architectYandex = yii2-migrate-architectYandex::getInstanсe( $_ENV['API_HOST'], [
    yii2-migrate-architectYandex::LOGGER => new YandexLogger(),
]);

$response = $yii2-migrate-architectYandex->setupRequest( 'profile', [ 
    Request::METHOD => Method::PATCH,
    Request::DATA => [ 'city' => 'Moscow' ],
]); // Логирование `afterCreateRequest`

$response = $yii2-migrate-architectYandex->send(); // Логирование `afterSend`
```


<p align="center">- - - - -</p>

___

<p align="center">- - - - -</p>


<h2>Тесты</h2> <span id="yii2-migrate-architect-tests"></span>

- tests: 100+
- assertions: 350+

<h3>
    Запуск тестов:
</h3> <span id="yii2-migrate-architect-tests-run"></span>

Нативный
```bash
vendor/bin/phpunit
```  
Информационный
```bash
vendor/bin/phpunit --testdox
```  
С логированием
```bash
vendor/bin/phpunit --log-junit "tests/logs/phpunit.xml"
```

<h2>Лицензия</h2> <span id="yii2-migrate-architect-license"></span>

https://github.com/andy87/yii2-migrate-architect под лицензией CC BY-SA 4.0  
Для получения дополнительной информации смотрите http://creativecommons.org/licenses/by-sa/4.0/  
Свободно для не коммерческого использования  
С указанием авторства для коммерческого использования

<h2>Изменения</h2> <span id="yii2-migrate-architect-changelog"></span>

Для получения полной информации смотрите [CHANGELOG](docs/CHANGELOG.md)

<h3>
    Последние изменения
</h3> <span id="yii2-migrate-architect-changes"></span>

24/05/2024 - 99b  
26/05/2024 - v1.0.0  
25/05/2024 - v1.0.1  
04/06/2024 - v1.0.2  
09/06/2024 - v1.2.1  
09/06/2024 - v1.3.0  
13/06/2024 - v1.3.1

[Packagist](https://packagist.org/packages/andy87/yii2-migrate-architect)
