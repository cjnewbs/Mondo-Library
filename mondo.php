<?php

class mondo extends curl {

    public $_clientID = '';
    
    public $_redirectURI = '';
    
    public $_clientSecret = '';
    
    public $_authPageURI = '';

    public $_tokenExchangeURI = '';
    
    public $_state = '';
    
    public $_incomingState = '';
    
    public $_tempToken = '';
    
    public function OAuthCSRF($len){

        // if no len specified default to 50 chars
        if (!isset($len)){
            $len = 50;
        }

        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $output = '';
        for ($i=0; $i < $len; $i++) {
            $output = $output . $chars[rand(0, strlen($chars))];
        }
        
        return $output;
    }

    public function whoami(){

    }
    
    public function getBalance(){
   
    }

    public function accountName(){

    }

    public function accountCreationDate(){

    }

    public function accountID(){

    }

    public function spentToday(){

    }

    public function webhookList(){

    }

    public function webhookRegister(){


    }
    public function webhookDelete(){

    }

}