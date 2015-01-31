<?php

require_once __DIR__.'/../vendor/autoload.php';

use BrianReeve\AssetProxy\AssetLoader;

$assetLoader = new AssetLoader(__DIR__.'/assets/');

$asset = $assetLoader->getAsset('laptop.jpg');

echo 'Mime: '.$asset->getMimeType();
echo "\n";