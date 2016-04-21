<?php

class mondo extends curl {

    public $_clientID;
    public $_redirectURI;
    public $_clientSecret;
    public $_authPageURI;
    public $_tokenExchangeURI;
    public $_state;
    public $_incomingState;
    public $_tempToken;
    public $_accessToken;
    public $_refreshToken;
    public $_userID;
    public $_accountID;
    public $_accountName;
    public $_accountCreated;
    public $_balanceBalance;
    public $_balanceTodaySpend;
    
    public function saveSession(){

        $this->_state = $_SESSION['state'];

        $this->_incomingState = $_GET['state'];

        $this->_tempToken = $_GET['code'];
    }
    
    public function loadSession($verbose = false){

        $this->_accessToken = $_SESSION['access_token'];
        $this->_refreshToken = $_SESSION['refresh_token'];
        $this->_userID = $_SESSION['user_id'];
        if ($verbose === true){
            //for debugging
            echo "Access Token: $this->_accessToken <br>";
            echo "Refresh Token: $this->_refreshToken <br>";
            echo "User ID: $this->_userID <br>";
        }
        
    }
    
    public function OAuthCSRF($len = 50){

        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $output = '';
        for ($i=0; $i < $len; $i++) {
            $output = $output . $chars[rand(0, strlen($chars)-1)];
        }
        return $output;
    }

    public function checkAuthStatus(){
        $response = $this->get('https://api.getmondo.co.uk/ping/whoami', $this->_accessToken);
        $response = json_decode($response,true);
        if ($response['authenticated'] === true){
            $response = true;
        }else{
            $response = false;
        }
        return$response;
    }


    public function accountPreCache(){

        $response = $this->get('https://api.getmondo.co.uk/accounts',$this->_accessToken);
        $response = json_decode($response, true);

        $this->_accountID = $response['accounts'][0]['id'];
        $this->_accountName = $response['accounts'][0]['description'];
        $this->_accountCreated = $response['accounts'][0]['created'];
    }

    public function getAccountID(){
        if ($this->_accountID == ''){
            $this->accountPreCache();
        }
        return $this->_accountID;
    }

    public function getAccountName(){
        if ($this->_accountName == ''){
            $this->accountPreCache();
        }
        return $this->_accountName;
    }

    public function getAccountCreated(){
        if ($this->_accountCreated == ''){
            $this->accountPreCache();
        }
        return $this->_accountCreated;
    }

    public function balancePreCache(){
        if ($this->_accountID == ''){
            $this->accountPreCache();
        }
        $response = $this->get("https://api.getmondo.co.uk/balance?account_id=$this->_accountID",$this->_accessToken);
        $response = json_decode($response, true);

        $this->_balanceBalance = $response['balance'];
        $this->_balanceTodaySpend = $response['spend_today'];
    }

    public function getBalance(){
        if ($this->_balanceBalance == ''){
            $this->balancePreCache();
        }
        $balance = number_format(($this->_balanceBalance/100), 2, '.', ',');
        return "$balance";
    }
    
    public function getTodaySpend(){
        if ($this->_balanceTodaySpend == ''){
            $this->balancePreCache();
        }
        $spent_today = number_format(($this->_balanceTodaySpend/100), 2, '.', ',');
        return "$spent_today";
    }

}