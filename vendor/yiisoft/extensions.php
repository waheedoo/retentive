<?php

$vendorDir = dirname(__DIR__).'/../application/api/modules';

return array (
  'filsh/yii2-oauth2-server' =>
  array (
    'name' => 'filsh/yii2-oauth2-server',
    'version' => '2.0.0.0',
    'alias' =>
    array (
      '@filsh/yii2/oauth2server' => $vendorDir . '/filsh/yii2-oauth2-server',
    ),
    'bootstrap' => 'filsh\\yii2\\oauth2server\\Bootstrap',
  ),
);
