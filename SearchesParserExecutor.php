<?php

spl_autoload_register(function($className) {
    require $className . '.php';
});

try {
    $searches = new SearchesParser('searches.csv');
    $searches->parse();

    $searches->renderResult();
} catch (Exception $e) {
    printf("Error on line %d with message: %s \n", $e->getLine(), $e->getMessage());
}
