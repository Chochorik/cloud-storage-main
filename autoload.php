<?php

function loadEntities($className) : void
{
    require_once './' . $className . '.php';
}

spl_autoload_register('loadEntities');


