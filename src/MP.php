<?php

namespace rodggall\MercadoPago;

use Exception;

$GLOBALS["LIB_LOCATION"]=dirname(__FILE__);

class MP{
    
    const version="0.5.2";
    
    private $client_id;
    private $client_secret;
    private $ll_access_token;
    private $access_data;
    private $sandbox=FALSE;
    
    function __construct(){
        
        $confVal=func_num_args();
        
        if($confVal>2||$confVal<1){
            throw new MercadoPagoException("Argumentos Invalidos. Se requiere CLIENT_ID y CLIENT_SECRET, o ACCESS_TOKEN");
        }
        elseif($confVal==1){
            $this->ll_access_token=func_get_arg(0);
        }
        elseif($confVal==2){
            $this->client_id = func_get_arg(0);
            $this->client_secret = func_get_arg(1);
        }
    }
    public function sandbox_mode($prueba=NULL){
        if(!is_null($prueba)){
            $this->sandbox = $prueba===TRUE;
        }
        return $this->sandbox;
    }
    public function getAccessToken(){
        if (isset ($this->ll_access_token) && !is_null($this->ll_access_token)) {
            return $this->ll_access_token;
        }
        app_client_values = array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'client_credentials'
        );

        $access_data = MPRestClient::post(array(
            "uri" => "/oauth/token",
            "data" => $app_client_values,
            "headers" => array(
                "content-type" => "application/x-www-form-urlencoded"
            )
        ));

        if ($access_data["status"] != 200) {
            throw new MercadoPagoException ($access_data['response']['message'], $access_data['status']);
        }

        $this->access_data = $access_data['response'];

        return $this->access_data['access_token'];
    }
    public function getPayment($id){
        $uri_prefix = $this->sandbox ? "/sandbox" : "";
            
        $request = array(
            "uri" => $uri_prefix."/collections/notifications/{$id}",
            "params" => array(
                "access_token" => $this->getAccessToken()
            )
        );

        $payment_info = MPRestClient::get($request);
        return $payment_info;
    }
    public function getAuthorizedPayment($id){
        $request = array(
            "uri" => "/authorized_payments/{$id}",
            "params" => array(
                "access_token" => $this->getAccessToken()
            )
        );

        $authorized_payment_info = MPRestClient::get($request);
        return $authorized_payment_info;
    }
    public function refundPayment($id){
        $request = array(
            "uri" => "/collections/{$id}",
            "params" => array(
                "access_token" => $this->getAccessToken()
            ),
            "data" => array(
                "status" => "refunded"
            )
        );

        $response = MPRestClient::put($request);
        return $response;
    }
    public function cancelPayment($id){
        $request = array(
            "uri" => "/collections/{$id}",
            "params" => array(
                "access_token" => $this->getAccessToken()
            ),
            "data" => array(
                "status" => "cancelled"
            )
        );

        $response = MPRestClient::put($request);
        return $response;
    }
    public function cancelPreapprovalPayment($id){
        $request = array(
            "uri" => "/preapproval/{$id}",
            "params" => array(
                "access_token" => $this->getAccessToken()
            ),
            "data" => array(
                "status" => "cancelled"
            )
        );

        $response = MPRestClient::put($request);
        return $response;
    }
    public function searchPayment($filters, offset=0, $limit=0){
        $filters["offset"] = $offset;
        $filters["limit"] = $limit;

        $uri_prefix = $this->sandbox ? "/sandbox" : "";
            
        $request = array(
            "uri" => $uri_prefix."/collections/search",
            "params" => array_merge ($filters, array(
                "access_token" => $this->getAccessToken()
            ))
        );

        $collection_result = MPRestClient::get($request);
        return $collection_result;
    }
    public function createPreference($preference){
         $request = array(
            "uri" => "/checkout/preferences",
            "params" => array(
                "access_token" => $this->getAccessToken()
            ),
            "data" => $preference
        );

        $preference_result = MPRestClient::post($request);
        return $preference_result;
    }
    public function updatePreference($id,$preference){
        
    }
}