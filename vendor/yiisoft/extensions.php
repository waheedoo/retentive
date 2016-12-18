<?php

$vendorDir = dirname(__DIR__);

return array (
  'filsh/yii2-oauth2-server' => 
  array (
    'name' => 'filsh/yii2-oauth2-server',
    'version' => '2.0.0.0',
    'alias' => 
    array (
      '@filsh/yii2/oauth2server' => $vendorDir . '/../application/api/modules/filsh/yii2-oauth2-server',
    ),
    'bootstrap' => 'filsh\\yii2\\oauth2server\\Bootstrap',
  ),
  'yiisoft/yii2-swiftmailer' => 
  array (
    'name' => 'yiisoft/yii2-swiftmailer',
    'version' => '9999999-dev',
    'alias' => 
    array (
      '@yii/swiftmailer' => $vendorDir . '/yiisoft/yii2-swiftmailer',
    ),
  ),
);
