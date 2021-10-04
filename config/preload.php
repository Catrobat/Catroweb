<?php

/**
 * PHP preloading and Symfony => free performance boost!
 *
 *  - https://symfony.com/blog/php-preloading-and-symfony
 *
 *  Has to be configured on the server via php.ini files!
 *  ```
 *    opcache.preload=/path/to/project/config/preload.php
 *  ```
 */
if (file_exists(dirname(__DIR__).'/var/cache/prod/srcApp_KernelProdContainer.preload.php')) {
  require dirname(__DIR__).'/var/cache/prod/srcApp_KernelProdContainer.preload.php';
}

if (file_exists(dirname(__DIR__).'/var/cache/prod/App_KernelProdContainer.preload.php')) {
  require dirname(__DIR__).'/var/cache/prod/App_KernelProdContainer.preload.php';
}
