<?php
Class curl
{
//all this class + method is responsible for is abstracting the curl process away from the mondo library
    public $_rootCApath = '';

    public function get($endpoint, $bearer_token = null, $post_data = null, $DELETE = false)
    {

        $ch = curl_init();

        //provide url variable to curl object
        curl_setopt($ch, CURLOPT_URL, $endpoint);

        // Set so curl_exec returns the result instead of outputting it.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


        //configure CURL to verify remote host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        if (!is_null($bearer_token)) {
            $bearer_header = array("Authorization: Bearer $bearer_token");

            curl_setopt($ch, CURLOPT_HTTPHEADER, $bearer_header);
        }
        
        if (!is_null($post_data))
        {
            // tell curl to expect post fields
            curl_setopt($ch, CURLOPT_POST, true);

            // This is the fields to post in the form of an array.
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

        }

        //this was added specifically for the deleteHook() method as it requires a DELETE HTTP request
        if ($DELETE === true) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        // CA root keys for verification
        curl_setopt($ch, CURLOPT_CAPATH, $this->_rootCApath);
        
        // Get the response and close the channel.
        $response = curl_exec($ch);

        //check if curl request completed MUST USE ===
        if($response === false) {

            //return error if curl didn't complete
            $response = 'CURL_ERROR: ' . curl_error($ch);

        }

        //$this->_statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $response;
    }
}