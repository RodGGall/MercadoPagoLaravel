<?php

namespace MercadoPagoLaravel;

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
        $app_client_values = array(
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
    public function get($request, $params = null, $authenticate = true) {
        if (is_string ($request)) {
            $request = array(
                "uri" => $request,
                "params" => $params,
                "authenticate" => $authenticate
            );
        }

        $request["params"] = isset ($request["params"]) && is_array ($request["params"]) ? $request["params"] : array();

        if (!isset ($request["authenticate"]) || $request["authenticate"] !== false) {
            $request["params"]["access_token"] = $this->getAccessToken();
        }

        $result = MPRestClient::get($request);
        return $result;
    }
    public function post($request, $data = null, $params = null) {
        if (is_string ($request)) {
            $request = array(
                "uri" => $request,
                "data" => $data,
                "params" => $params
            );
        }

        $request["params"] = isset ($request["params"]) && is_array ($request["params"]) ? $request["params"] : array();

        if (!isset ($request["authenticate"]) || $request["authenticate"] !== false) {
            $request["params"]["access_token"] = $this->getAccessToken();
        }

        $result = MPRestClient::post($request);
        return $result;
    }
    public function put($request, $data = null, $params = null) {
        if (is_string ($request)) {
            $request = array(
                "uri" => $request,
                "data" => $data,
                "params" => $params
            );
        }

        $request["params"] = isset ($request["params"]) && is_array ($request["params"]) ? $request["params"] : array();

        if (!isset ($request["authenticate"]) || $request["authenticate"] !== false) {
            $request["params"]["access_token"] = $this->getAccessToken();
        }

        $result = MPRestClient::put($request);
        return $result;
    }
    public function delete($request, $params = null) {
        if (is_string ($request)) {
            $request = array(
                "uri" => $request,
                "params" => $params
            );
        }

        $request["params"] = isset ($request["params"]) && is_array ($request["params"]) ? $request["params"] : array();

        if (!isset ($request["authenticate"]) || $request["authenticate"] !== false) {
            $request["params"]["access_token"] = $this->getAccessToken();
        }

        $result = MPRestClient::delete($request);
        return $result;
    }
}
class MPRestClient {
    const API_BASE_URL = "https://api.mercadopago.com";

    private static function buildRequest($request) {
        if (!extension_loaded ("curl")) {
            throw new MercadoPagoException("No existe la extension Curl. Instale la extension Curl o habilitela en el archivo php.ini.");
        }

        if (!isset($request["method"])) {
            throw new MercadoPagoException("No ingreso el metodo http");
        }

        if (!isset($request["uri"])) {
            throw new MercadoPagoException("No ingreso unaURI valida");
        }

        $headers = array("accept: application/json");
        $json_content = true;
        $form_content = false;
        $default_content_type = true;

        if (isset($request["headers"]) && is_array($request["headers"])) {
            foreach ($request["headers"] as $h => $v) {
                $h = strtolower($h);
                $v = strtolower($v);

                if ($h == "content-type") {
                    $default_content_type = false;
                    $json_content = $v == "application/json";
                    $form_content = $v == "application/x-www-form-urlencoded";
                }

                array_push ($headers, $h.": ".$v);
            }
        }
        if ($default_content_type) {
            array_push($headers, "content-type: application/json");
        }

        $connect = curl_init();

        curl_setopt($connect, CURLOPT_USERAGENT, "MercadoPago PHP SDK v" . MP::version);
        curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connect, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($connect, CURLOPT_CAINFO, $GLOBALS["LIB_LOCATION"] . "/cacert-2018-01-17.pem");
        curl_setopt($connect, CURLOPT_CUSTOMREQUEST, $request["method"]);
        curl_setopt($connect, CURLOPT_HTTPHEADER, $headers);

        if (isset ($request["params"]) && is_array($request["params"]) && count($request["params"]) > 0) {
            $request["uri"] .= (strpos($request["uri"], "?") === false) ? "?" : "&";
            $request["uri"] .= self::buildQuery($request["params"]);
        }
        curl_setopt($connect, CURLOPT_URL, self::API_BASE_URL . $request["uri"]);

        if (isset($request["data"])) {
            if ($json_content) {
                if (gettype($request["data"]) == "string") {
                    json_decode($request["data"], true);
                } else {
                    $request["data"] = json_encode($request["data"]);
                }

                if(function_exists('json_last_error')) {
                    $json_error = json_last_error();
                    if ($json_error != JSON_ERROR_NONE) {
                        throw new MercadoPagoException("JSON Error [{$json_error}] - Data: ".$request["data"]);
                    }
                }
            } else if ($form_content) {
                $request["data"] = self::buildQuery($request["data"]);
            }

            curl_setopt($connect, CURLOPT_POSTFIELDS, $request["data"]);
        }

        return $connect;
    }

    private static function exec($request) {

        $connect = self::buildRequest($request);

        $api_result = curl_exec($connect);
        $api_http_code = curl_getinfo($connect, CURLINFO_HTTP_CODE);

        if ($api_result === FALSE) {
            throw new MercadoPagoException (curl_error ($connect));
        }

        $response = array(
            "status" => $api_http_code,
            "response" => json_decode($api_result, true)
        );

        if ($response['status'] >= 400) {
            $message = $response['response']['message'];
            if (isset ($response['response']['cause'])) {
                if (isset ($response['response']['cause']['code']) && isset ($response['response']['cause']['description'])) {
                    $message .= " - ".$response['response']['cause']['code'].': '.$response['response']['cause']['description'];
                } else if (is_array ($response['response']['cause'])) {
                    foreach ($response['response']['cause'] as $cause) {
                        $message .= " - ".$cause['code'].': '.$cause['description'];
                    }
                }
            }

            throw new MercadoPagoException ($message, $response['status']);
        }

        curl_close($connect);

        return $response;
    }

    private static function buildQuery($params) {
        if (function_exists("http_build_query")) {
            return http_build_query($params, "", "&");
        } else {
            foreach ($params as $name => $value) {
                $elements[] = "{$name}=" . urlencode($value);
            }

            return implode("&", $elements);
        }
    }

    public static function get($request) {
        $request["method"] = "GET";

        return self::exec($request);
    }

    public static function post($request) {
        $request["method"] = "POST";

        return self::exec($request);
    }

    public static function put($request) {
        $request["method"] = "PUT";

        return self::exec($request);
    }

    public static function delete($request) {
        $request["method"] = "DELETE";

        return self::exec($request);
    }
}

class MercadoPagoException extends Exception {
    public function __construct($message, $code = 500, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
