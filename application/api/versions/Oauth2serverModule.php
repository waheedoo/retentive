<?php
namespace api\versions\Oauth2serverModule;
use \filsh\yii2\oauth2server\Module;

class Oauth2serverModule extends \filsh\yii2\oauth2server\Module
{
    /**
     * Gets Oauth2 Server
     *
     * @return \filsh\yii2\oauth2server\Server
     * @throws \yii\base\InvalidConfigException
     */
    public function getServer()
    {
        if(!$this->has('server')) {
            $storages = [];

            if($this->useJwtToken)
            {
                if(!array_key_exists('access_token', $this->storageMap) || !array_key_exists('public_key', $this->storageMap)) {
                    throw new \yii\base\InvalidConfigException('access_token and public_key must be set or set useJwtToken to false');
                }
                //define dependencies when JWT is used instead of normal token
                \Yii::$container->clear('public_key'); //remove old definition
                \Yii::$container->set('public_key', $this->storageMap['public_key']);
                \Yii::$container->set('OAuth2\Storage\PublicKeyInterface', $this->storageMap['public_key']);

                \Yii::$container->clear('access_token'); //remove old definition
                \Yii::$container->set('access_token', $this->storageMap['access_token']);
            }

//            foreach(array_keys($this->storageMap) as $name) {
//                $storages[$name] = \Yii::$container->get($name);
//            }

            foreach ($this->storageMap as $name => $class) {
                $storages[$name] = \Yii::$container->get($class);
            }

            $storages['access_token'] = \Yii::$container->get('filsh\yii2\oauth2server\storage\Pdo');
            $storages['authorization_code'] = \Yii::$container->get('filsh\yii2\oauth2server\storage\Pdo');
            $storages['client_credentials'] = \Yii::$container->get('filsh\yii2\oauth2server\storage\Pdo');
            $storages['client'] = \Yii::$container->get('filsh\yii2\oauth2server\storage\Pdo');
            $storages['refresh_token'] = \Yii::$container->get('filsh\yii2\oauth2server\storage\Pdo');
            $storages['scope'] = \Yii::$container->get('filsh\yii2\oauth2server\storage\Pdo');
            $storages['jwt_bearer'] = \Yii::$container->get('filsh\yii2\oauth2server\storage\Pdo');

            $grantTypes = [];
            foreach($this->grantTypes as $name => $options) {
                if(!isset($storages[$name]) || empty($options['class'])) {
                    throw new \yii\base\InvalidConfigException('Invalid grant types configuration.');
                }

                $class = $options['class'];
                unset($options['class']);

                $reflection = new \ReflectionClass($class);
                $config = array_merge([0 => $storages[$name]], [$options]);

                $instance = $reflection->newInstanceArgs($config);
                $grantTypes[$name] = $instance;
            }

            $server = \Yii::$container->get(Server::className(), [
                $this,
                $storages,
                array_merge(array_filter([
                    'use_jwt_access_tokens' => $this->useJwtToken,//ADDED
                    'token_param_name' => $this->tokenParamName,
                    'access_lifetime' => $this->tokenAccessLifetime,
                    /** add more ... */
                ]), $this->options),
                $grantTypes
            ]);

            $this->set('server', $server);
        }
        return $this->get('server');
    }
}