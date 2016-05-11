<?php


class AuthCest
{
    private $token;

    public function _before(ApiTester $I)
    {
    }

    public function _after(ApiTester $I)
    {
    }

    // tests
    public function tryToTest(ApiTester $I)
    {
    }

    public function loginAndGetTokenFailWhenSomeParametersMissing(ApiTester $I) {
        $I->wantTo("Try to login by sending bad request");
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/oauth2/login', json_encode(array()));
        $I->seeResponseCodeIs(400);
    }

    public function loginAndGetTokenFailWhenInvalidUserCredentialsProvided(ApiTester $I)
    {
        $I->wantTo("Login in to API to get an active token");
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/oauth2/login', json_encode(array('grant_type' => 'password', 'username' => 'waheed.alkhateeb@gmail.com', 'password' => 'wrongPassword', 'client_id' => 'testclient', 'client_secret' => 'testpass')));
        $I->seeResponseCodeIs(401);//unauthorized
    }

    public function loginAndGetTokenSucceed(ApiTester $I) {
        $I->wantTo("Login in to API to get an active token");
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/oauth2/login', json_encode(array('grant_type' => 'password', 'username' => 'waheed.alkhateeb@gmail.com', 'password' => 'analretentive', 'client_id' => 'testclient', 'client_secret' => 'testpass')));
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->canSeeResponseContains('access_token');
        $response = $I->grabResponse();
        $response = json_decode($response);
        $I->canSeeResponseContains('"scope":"default"');
        $I->seeResponseMatchesJsonType([
            'scope' => 'string',
            'access_token' => 'string',
            'refresh_token' => 'string|null',
            'expires_in' => 'integer'
        ]);

        $this->token = $response->access_token;
    }

    /**
     *
     * @depends loginAndGetTokenSucceed
     *
     */
    public function getCurrentUserReturnsUserDetails(ApiTester $I) {
        $I->amBearerAuthenticated($this->token);
        $I->wantTo("Get the current user details(me)");
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/v1/users/me');
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseContains('id');
        $I->canSeeResponseContains('firstname');
        $I->canSeeResponseContains('lastname');
        $I->canSeeResponseContains('email');
        $I->canSeeResponseContains('created_at');
    }

    // More test methods go here

}
