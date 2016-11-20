<?php
return [
    'id' => 'app-api',
	'name' => '',

    'controllerNamespace' => 'api\controllers',
	'defaultRoute' => 'product',

    'components' => [
		'urlManager' => [
			'enablePrettyUrl' => true,
			'rules' => [
				// you can add all the actions that are related to auth to this controller (default)
				//e.g: resetPassword, login, logout
				//'POST /oauth2/<action:\w+>' => 'oauth2/default/<action>',
				'POST /oauth2/login' 	=> 'oauth2/default/login',
				'GET  /oauth2/tokenInfo'=> 'oauth2/default/token',
				'DELETE /oauth2/logout' => 'oauth2/default/logout',

				[
					'class' => 'yii\rest\UrlRule',
					'controller' => 'v1/product',
					'extraPatterns' => [
						'GET custom' => 'custom',
						'GET protected' => 'protected',
						// an example about endpoint that can be accessed only by a user with role=admin
						'GET administrator' => 'administrator'
					],
				],
				[
					'class' => 'yii\rest\UrlRule',
					'controller' => 'v1/user',
					'except' => ['delete', 'create'], //403 forbidden when requesting these actions
					'extraPatterns' => [
						'POST register'	=> 'register',
						'GET <id:\d+|me>'	 => 'view',
						'PUT <id:\d+|me>'	=> 'update',
						'POST sendResetPassword' => 'sendResetPassword',
						'POST  resetPassword' => 'resetPassword',

					],
				],
			]
		],
		'request' => [
			'parsers' => [
				'application/json' => 'yii\web\JsonParser',
			]
		],
		'response' => [
//			'class' => 'yii\web\Response',
//			'formatters' => [
//				yii\web\Response::FORMAT_HTML => '\api\components\HtmlResponseFormatter',
//			],
			'format' => yii\web\Response::FORMAT_JSON,
			'charset' => 'UTF-8',
			'on beforeSend' => function (\yii\base\Event $event) {
				/** @var \yii\web\Response $response */
				$response = $event->sender;
				// catch situation, when no controller hasn't been loaded
				// so no filter wasn't loaded too. Need to understand in which format return result
				if(empty(Yii::$app->controller)) {
					$content_neg = new \yii\filters\ContentNegotiator();
					$content_neg->response = $response;
					$content_neg->formats = Yii::$app->params['formats'];
					$content_neg->negotiate();

					//set response output for 404 scenarios:
					if($response->statusCode == '404') {
						$response->data = [
							'error' => 'Not found!',
							'code'	=> '404'
						];
					}
				}
				if ($response->data !== null && Yii::$app->request->get('suppress_response_code')) {
					$response->data = [
						'success' => $response->isSuccessful,
						'data' => $response->data,
					];
					$response->statusCode = 200;
				}
			},
		],
		'user' => [
			'identityClass' => 'api\models\User',
			'loginUrl' => null,
			//'enableSession' => false
        ],
    ],
    'params' => [],
];
