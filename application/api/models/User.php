<?php
namespace api\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

use yii\helpers\ArrayHelper;
use yii\base\InvalidConfigException;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $role
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */
class User extends ActiveRecord implements \yii\web\IdentityInterface, \OAuth2\Storage\UserCredentialsInterface
{
    const STATUS_DELETED    = 0;
    const STATUS_ACTIVE     = 10;
    const STATUS_INACTIVE   = 100;

    const ROLE_USER  = 10;
    const ROLE_ADMIN = 200;

    /** @var string Plain password. Used for model validation. */
    public $password;
    public $password_repeat;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%users}}';
    }


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
				'class' => TimestampBehavior::className(),
				'value' => new Expression('NOW()'),
			]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {

        return [

            // email rules
            'emailRequired' => ['email', 'required', 'on' => ['register', 'connect', 'create', 'update']],
            'emailPattern'  => ['email', 'email'],
            'emailLength'   => ['email', 'string', 'max' => 255],
            'emailUnique'   => ['email', 'unique'],
            'emailTrim'     => ['email', 'trim'],

            // password rules
            'passwordRequired' => ['password', 'required', 'on' => ['register', 'create']],
            'passwordLength'   => ['password', 'string', 'min' => 6, 'on' => ['register', 'create']],

            // password repeat
            'repeatPasswordRequired' => ['password_repeat', 'required', 'on' => ['register', 'create']],
            ['password_repeat', 'compare', 'compareAttribute'=>'password', 'message'=>"Passwords don't match" ],

            //name required:
            [['firstname', 'lastname'], 'required', 'on' => ['register', 'create']],
            [['firstname', 'lastname'], 'string', 'max' => 25],
            [['password_reset_token'], 'string', 'max' => 255],

            ['status', 'default', 'value' => self::STATUS_ACTIVE], //user is active by default
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],

            ['role', 'default', 'value' => self::ROLE_USER],
            ['role', 'in', 'range' => [self::ROLE_USER, self::ROLE_ADMIN]],
        ];
    }


    /** @inheritdoc */
    public function scenarios()
    {
        //list all the fields that will be accepted and saved from the client
        return [
            'register' => ['email', 'password', 'password_repeat', 'password_reset_token', 'firstname', 'lastname'],
            'login'    => ['email', 'password'],
            'create'   => ['email', 'password', 'password_repeat', 'firstname', 'lastname'],
            'update'   => ['email', 'password']
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
		/** @var \filsh\yii2\oauth2server\Module $module */
		$module = Yii::$app->getModule('oauth2');
        //print_r($module->getServer()->getResourceController()->getToken());exit;
		$token = $module->getServer()->getResourceController()->getToken();

		return !empty($token['user_id'])
					? static::findIdentity($token['user_id'])
					: null;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $expire = Yii::$app->params['user']['passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function getRole()
    {
        return isset($this->role) ? $this->role : null;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

	public function checkUserCredentials($username, $password)
	{
		$user = static::findByEmail($username);
		if (empty($user)) {
			return false;
		}
		return $user->validatePassword($password);
	}

	public function getUserDetails($username)
	{
		$user = static::findByEmail($username);
		return ['user_id' => $user->getId()];
	}

    /** @inheritdoc */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->generateAuthKey();
            $this->setAttribute('registration_ip', \Yii::$app->request->userIP);
            $this->setAttribute('status', self::STATUS_ACTIVE);
            $this->setAttribute('role', self::ROLE_USER);
        }

        if (!empty($this->password)) {
            $this->setPassword($this->password);
        }

        return parent::beforeSave($insert);
    }

    /** @inheritdoc */
    public function save($runValidation = true, $attributeNames = null)
    {
        return parent::save($runValidation, $attributeNames); // TODO: Change the autogenerated stub
    }
}
