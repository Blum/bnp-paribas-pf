<?php

namespace Gentor\BnpPF\Service;

use Illuminate\Support\Facades\Http;

/**
 * Class Client
 *
 * @package Gentor\BnpPF\Service
 */
class Client
{
    /**
     * Test endpoint
     */
    const TEST_ENDPOINT = 'https://ws-test.pbpf.bg/ServicesPricing/';

    /**
     * Live endpoint
     */
    const LIVE_ENDPOINT = 'https://ws.pbpf.bg/ServicesPricing/';

    /**
     * @var
     */
    protected $certificate;

    /**
     * @var
     */
    protected $key;

    /**
     * @var
     */
    protected $password;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var
     */
    protected $curl;

    /**
     * @var
     */
    protected $curlOptions;

    /**
     * Client constructor.
     *
     * @param      $certificate
     * @param      $password
     * @param bool $testMode
     */
    public function __construct($certificate, $key, $password, $testMode = false)
    {
        $this->certificate = $certificate;
        $this->key = $key;
        $this->password = $password;
        $this->url = $testMode ? static::TEST_ENDPOINT : static::LIVE_ENDPOINT;
    }

    /**
     * @param $urlParams
     *
     * @return mixed
     * @throws \Gentor\BnpPF\Service\Error
     */
    public function getResult($urlParams)
    {
        // $this->setCurlOptions($urlParams);

        // $result = curl_exec($this->curl);

        // $code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        // $error = curl_error($this->curl);

        // curl_close($this->curl);

        // if (200 != $code) {
        //     throw new Error($error, $code);
        // }

        $response = Http::withOptions([
            'ssl_key' => [$this->key, $this->password],
            'cert' => [$this->certificate, $this->password],
            "verify" => false,
        ])->get($this->url . $urlParams);

        return $this->xml2obj($response);
    }

    /**
     * @param $urlParams
     */
    protected function setCurlOptions($urlParams)
    {
        $this->curl = curl_init();

        $this->curlOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_ENCODING => '',
            CURLOPT_USERAGENT => 'MerchantPos',
            CURLOPT_AUTOREFERER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_NOBODY => false,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSLCERT => $this->certificate,
            CURLOPT_SSLCERTTYPE => 'PEM',
            CURLOPT_SSLCERTPASSWD => $this->password,
            CURLOPT_SSLVERSION => 1,
            CURLOPT_URL => $this->url . $urlParams,
        ];

        curl_setopt_array($this->curl, $this->curlOptions);
    }

    /**
     * @param $obj
     * @param $result
     */
    protected function normalizeSimpleXML($obj, & $result)
    {
        $data = $obj;
        if (is_object($data)) {
            $data = get_object_vars($data);
        }
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $res = null;
                $this->normalizeSimpleXML($value, $res);
                if (($key == '@attributes') && ($key)) {
                    $result = $res;
                } else {
                    $result[$key] = $res;
                }
            }
        } else {
            $result = $data;
        }
    }

    /**
     * @param $xml
     *
     * @return mixed
     */
    protected function xml2obj($xml)
    {
        $result = [];
        $this->normalizeSimpleXML(simplexml_load_string($xml), $result);

        return json_decode(json_encode($result));
    }
}
