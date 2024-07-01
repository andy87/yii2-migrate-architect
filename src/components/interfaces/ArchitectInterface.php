<?php

namespace andy87\yii2\architect\components\interfaces;

interface ArchitectInterface
{
    public const EXIT_CODE = 0;

    public const ACTION_CREATE = 1;
    public const ACTION_APPLY = 2;
    public const ACTION_DOWN = 3;
    public const ACTION_SETUP = 4;

    public const ACTION_EXIT = 0;


    public const SCENARIO_CREATE = 1;
    public const SCENARIO_UPDATE = 2;
    public const SCENARIO_COLUMN_ADD = 3;
    public const SCENARIO_COLUMN_RENAME = 4;
    public const SCENARIO_COLUMN_REMOVE = 5;

    public const PROMPT_ACTION = 1;

    public const PROMPT_MIGRATION = 2;
}