<?php

class mondo extends curl
{
    //the '_' prefix is being used to indicate that they are properties
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
    public $_txnData;
    
    public function saveSession()
    {

        $this->_state = $_SESSION['state'];

        $this->_incomingState = $_GET['state'];

        $this->_tempToken = $_GET['code'];
    }
    
    public function loadSession($verbose = false)
    {

        $this->_accessToken = $_SESSION['access_token'];
        $this->_refreshToken = $_SESSION['refresh_token'];
        $this->_userID = $_SESSION['user_id'];
        if ($verbose === true) {
            //for debugging
            echo "Access Token: $this->_accessToken <br>";
            echo "Refresh Token: $this->_refreshToken <br>";
            echo "User ID: $this->_userID <br>";
        }
    }

    public function purgeSession()
    {
        unset($_SESSION['state']);
        unset($_SESSION['access_token']);
        unset($_SESSION['refresh_token']);
        unset($_SESSION['user_id']);
        
    }
    
    public function OAuthCSRF($len = 50)
    {

        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $output = '';
        for ($i=0; $i < $len; $i++) {
            $output = $output . $chars[rand(0, strlen($chars)-1)];
        }
        return $output;
    }

    /**
     * @return bool
     * This method should be called by any page that attempts to get user data before requesting it.
     * if FALSE is returned then the current page should redirect the user to a page explaining what has ha
     */
    public function checkAuthStatus()
    {
        $response = $this->get('https://api.getmondo.co.uk/ping/whoami', $this->_accessToken);
        $response = json_decode($response,true);
        if ($response['authenticated'] === true){
            $response = true;
        } else {
            $response = false;
        }
        return $response;
    }

    /**
     * This method is automaticaly called by the getAccount family of methods if the account data has
     * not been cached yet for this page request.
     */
    protected function accountPreCache()
    {
        $response = $this->get('https://api.getmondo.co.uk/accounts',$this->_accessToken);
        $response = json_decode($response, true);

        //this always get the ['accounts']['0'] sub array as currently mondo only allows one 
        $this->_accountID = $response['accounts'][0]['id'];
        $this->_accountName = $response['accounts'][0]['description'];
        $this->_accountCreated = $response['accounts'][0]['created'];
    }

    public function getAccountID()
    {
        if ($this->_accountID == '') {
            $this->accountPreCache();
        }
        return $this->_accountID;
    }

    public function getAccountName()
    {
        if ($this->_accountName == '') {
            $this->accountPreCache();
        }
        return $this->_accountName;
    }

    public function getAccountCreated()
    {
        if ($this->_accountCreated == '') {
            $this->accountPreCache();
        }
        return $this->_accountCreated;
    }

    protected function balancePreCache()
    {
        if ($this->_accountID == '') {
            $this->accountPreCache();
        }
        $response = $this->get("https://api.getmondo.co.uk/balance?account_id=$this->_accountID",$this->_accessToken);
        $response = json_decode($response, true);

        $this->_balanceBalance = $response['balance'];
        $this->_balanceTodaySpend = $response['spend_today'];
    }

    public function getBalance()
    {
        if ($this->_balanceBalance == '') {
            $this->balancePreCache();
        }
        $balance = number_format(($this->_balanceBalance/100), 2, '.', ',');
        return "£$balance";
    }
    
    public function getTodaySpend()
    {
        if ($this->_balanceTodaySpend == '') {
            $this->balancePreCache();
        }
        $spent_today = number_format(($this->_balanceTodaySpend/100), 2, '.', ',');
        return "$spent_today";
    }

    public function registerHook($url)
    {
        if ($this->_accountID == '') {
            $this->accountPreCache();
        }
        $post_data = "account_id=$this->_accountID&url=$url";
        $response = $this->get('https://api.getmondo.co.uk/webhooks',$this->_accessToken, $post_data);
        return json_decode($response,true);
    }

    public function listHooks()
    {
        if ($this->_accountID == '') {
            $this->accountPreCache();
        }
        
        $response = $this->get("https://api.getmondo.co.uk/webhooks?account_id=$this->_accountID",$this->_accessToken);
        return json_decode($response,true);
    }

    public function deleteHook($id)
    {
        if ($this->_accountID == '') {
            $this->accountPreCache();
        }

        $response = $this->get("https://api.getmondo.co.uk/webhooks/$id",$this->_accessToken, null, true);

        if ($response == '{}' ) {
            return 'SUCCESS';
        } else {
            return 'FAILURE';
        }
    }


    public function getTransactionList()
    {
        $response = $this->get("https://api.getmondo.co.uk/transactions?account_id=$this->_accountID",$this->_accessToken);
        return json_decode($response, true);
    }

    public function getTransaction($ID)
    {
        if ($this->_txnData['id'] != $ID ) {
            $response = $this->get("https://api.getmondo.co.uk/transactions/$ID", $this->_accessToken);
            $this->_txnData = json_decode($response, true);
        }
        return $this;
    }

    //when the below methods are called at a minimum the 1st must be chained after the getTransaction($ID) method
    public function txnCreated()
    {
        if (!is_null($this->_txnData)) {
            return $this->_txnData['transaction']['created'];
        } else {
            return 'ERR:NO_TXNID_SUPPLIED';
        }
    }

    public function txnDescription()
    {
        if (!is_null($this->_txnData)) {
            return $this->_txnData['transaction']['description'];
        } else {
            return 'ERR:NO_TXNID_SUPPLIED';
        }
    }

    public function txnAmount($asNegative = false, $asCurrency = true)
    {
        if (!is_null($this->_txnData)) {
            $response = $this->_txnData['transaction']['amount'];
            if ($asNegative === false) {
                $response = str_replace('-', '', $response);
            }
            $response = number_format(($response/100), 2, '.', ',');
            if ($asCurrency === true) {
                $response = "£" . $response;
            }
            return $response;
        } else {
            return 'ERR:NO_TXNID_SUPPLIED';
        }
    }

    public function txnCategory()
    {
        if (!is_null($this->_txnData)) {
            return $this->_txnData['transaction']['category'];
        } else {
            return 'ERR:NO_TXNID_SUPPLIED';
        }
    }

    public function txnSettled()
    {
        if (!is_null($this->_txnData)) {
            return $this->_txnData['transaction']['settled'];
        } else {
            return 'ERR:NO_TXNID_SUPPLIED';
        }
    }
}