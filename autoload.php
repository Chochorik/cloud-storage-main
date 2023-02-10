<?php

function loadEntities($className)
{
    require_once './' . $className . '.php';
}

spl_autoload_register('loadEntities');


