<?php

namespace andy87\yii2\architect\components\interfaces;

interface ArchitectInterface
{
    public const EXIT_CODE = 0;

    public const ACTION_SETUP = 1;
    public const ACTION_CREATE = 2;
    public const ACTION_APPLY = 3;
    public const ACTION_DOWN = 4;
    public const ACTION_EXIT = 0;


    public const MIGRATE_CREATE = 1;
    public const MIGRATE_UPDATE = 2;
    public const MIGRATE_ADD = 3;
    public const MIGRATE_RENAME = 4;
    public const MIGRATE_REMOVE = 5;
}