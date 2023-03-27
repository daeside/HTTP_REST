<?php

class HTTP
{
    public static function Get(array $settings)
    {
        return self::Request($settings, 'GET');
    }

    public static function Post(array $settings)
    {
        return self::Request($settings, 'POST');
    }

    public static function Patch(array $settings)
    {
        return self::Request($settings, 'PATCH');
    }

    public static function Put(array $settings)
    {
        return self::Request($settings, 'PUT');
    }

    public static function Delete(array $settings)
    {
        return self::Request($settings, 'DELETE');
    }

    private static function Request(array $settings, string $method)
    {
        $response = null;

        try
        {
            $client = curl_init();
            $settings = self::ValidateSettings(array_change_key_case($settings, CASE_UPPER), $method);
            curl_setopt($client, CURLOPT_URL, $settings['URI']);
            curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($client, CURLOPT_SSLVERSION, 6); // TLS v1.2
            $headers = self::SetHeaders($settings);
            curl_setopt($client, CURLOPT_HTTPHEADER, $headers);
            $client = self::SetHttpMethod($client, $settings['DATA'], $method);
            $response = curl_exec($client);
            $code = curl_getinfo($client, CURLINFO_HTTP_CODE);
            curl_close($client);
            $response = $settings['ERRORS'] || ( $code >= 200 && $code <= 299) ? $response : null;
        }
        catch(Exception $ex)
        {}
        return $response;
    }

    private static function ValidateSettings(array $settings, string $method) : array
    {
        $settings['URI'] = array_key_exists('URI', $settings) ? $settings['URI'] : '';
        $settings['DATA'] = array_key_exists('DATA', $settings) ? $settings['DATA'] : '';
        $settings['AUTHORIZATION'] = array_key_exists('AUTHORIZATION', $settings) ? $settings['AUTHORIZATION'] : [];
        $settings['CUSTOMHEADERS'] = array_key_exists('CUSTOMHEADERS', $settings) ? $settings['CUSTOMHEADERS'] : [];
        $settings['FORMAT'] = array_key_exists('FORMAT', $settings) ? $settings['FORMAT'] : '';
        $settings['ERRORS'] = array_key_exists('ERRORS', $settings) ? $settings['ERRORS'] : false;
        $settings['METHOD'] = $method;
        return $settings;
    }

    private static function SetHeaders(array $settings) : array
    {
        $headers = [];
        foreach($settings['CUSTOMHEADERS'] as $key=>$value)
        {
            array_push($headers, sprintf('%s:%s', $key, $value));
        }
        $headers = self::SetContentType($headers, $settings['FORMAT']);
        $headers = self::SetAuthorization($headers, $settings['AUTHORIZATION']);
        return $headers;
    }

    private static function SetAuthorization(array $headers, array $authorization) : array
    {
        if(!empty($authorization))
        {
            $key = array_keys($authorization)[0];
            $auth = sprintf('%s %s', $key, $authorization[$key]);
            array_push($headers, sprintf('Authorization:%s', $auth));
        }
        return $headers;
    }

    private static function SetContentType(array $headers, string $format) : array
    {
        $type = empty($format) ? 'text/plain' : sprintf('application/%s', strtolower($format));
        array_push($headers, sprintf('Content-Type:%s', $type));
        return $headers;
    }

    private static function SetHttpMethod($client, string $data, string $method)
    {
        curl_setopt($client, CURLOPT_CUSTOMREQUEST, $method);

        if($method === 'POST' || $method === 'PUT' || $method === 'PATCH')
        {
            curl_setopt($client, CURLOPT_POSTFIELDS, $data);
        }
        return $client;
    }
}