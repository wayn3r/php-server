<?php

namespace Helpers;

class Http {
    public static function get(string $url, array $params = []): \Core\HttpResponse {
        if ($params) {
            $url .= '?';
            foreach ($params as $param => $value) {
                $url .= $param . '=' . $value . '&';
            }
            $url = rtrim($url, '&');
        }
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ]
        );

        $response = curl_exec($curl);
        $respInfo = curl_getinfo($curl);
        $response = new \Core\HttpResponse(
            $respInfo["http_code"],
            json_decode($response),
            curl_error($curl)
        );
        curl_close($curl);
        return $response;
    }
    public static function post(string $url, array $data) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ]
        ));
        $response = curl_exec($curl);
        $respInfo = curl_getinfo($curl);
        $response = new \Core\HttpResponse(
            $respInfo["http_code"],
            json_decode($response),
            curl_error($curl)
        );
        curl_close($curl);
        return $response;
    }
}
