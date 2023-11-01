<?php
/**
 * @name EOLINKER ams open source，EOLINKER open source version
 * @link https://global.eolinker.com/
 * @package EOLINKER
 * @author www.eolinker.com eoLinker Ltd.co 2015-2018
 * 
 * eoLinker is the world's leading and domestic largest online API interface management platform, providing functions such as automatic generation of API documents, API automated testing, Mock testing, team collaboration, etc., aiming to solve the problem of low development efficiency caused by separation of front and rear ends.
 * If you have any problems during the process of use, please join the user discussion group for feedback, we will solve the problem for you with the fastest speed and best service attitude.
 *
 * 
 *
 * Website：https://global.eolinker.com/
 * Slack：eolinker.slack.com
 * facebook：@EoLinker
 * twitter：@eoLinker
 */

class ProxyModule
{
    /**
     * proxy to host
     * @param string $method 
     * @param string $URL 
     * @param string $headers 
     * @param string $param 
     * @return bool|array
     */
    public function proxyToDesURL($method, $URL, &$headers = NULL, &$param = NULL)
    {
        
        $require = curl_init($URL);

        
        $isHttps = substr($URL, 0, 8) == "https://" ? TRUE : FALSE;

       
        switch ($method) {
            case 'GET' :
                curl_setopt($require, CURLOPT_CUSTOMREQUEST, "GET");
                break;
            case 'POST' :
                {
                    curl_setopt($require, CURLOPT_CUSTOMREQUEST, "POST");
                    break;
                }
            case 'DELETE' :
                curl_setopt($require, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'HEAD' :
                curl_setopt($require, CURLOPT_CUSTOMREQUEST, 'HEAD');
                
                curl_setopt($require, CURLOPT_NOBODY, TRUE);
                break;
            case 'OPTIONS' :
                curl_setopt($require, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
                break;
            case 'PATCH' :
                curl_setopt($require, CURLOPT_CUSTOMREQUEST, 'PATCH');
                break;
            case 'PUT' :
                curl_setopt($require, CURLOPT_CUSTOMREQUEST, 'PUT');
                break;
            default :
                return FALSE;
        }
        if ($param) {
            if (is_array($param)) {
                $str = '';
                foreach ($param as $key => $value) {
                    $str .= $key . '=' . urlencode($value) . '&';
                }
                $param = substr($str, 0, -1);
            }
            curl_setopt($require, CURLOPT_POSTFIELDS, $param);
        }

        if ($isHttps) {
            
            curl_setopt($require, CURLOPT_SSL_VERIFYPEER, FALSE);
            
            curl_setopt($require, CURLOPT_SSL_VERIFYHOST, TRUE);
        }

        if ($headers) {
            
            curl_setopt($require, CURLOPT_HTTPHEADER, $headers);
        }

        
        curl_setopt($require, CURLOPT_RETURNTRANSFER, TRUE);

        
        //curl_setopt($require, CURLOPT_FOLLOWLOCATION, TRUE);

        
        curl_setopt($require, CURLOPT_HEADER, TRUE);

        $time = date("Y-m-d H:i:s", time());

        
        $response = $this->curl_redirect_exec($require);

        
        $httpCode = curl_getinfo($require, CURLINFO_HTTP_CODE);

        
        $deny = curl_getinfo($require, CURLINFO_TOTAL_TIME) * 1000;

        
        $headerSize = curl_getinfo($require, CURLINFO_HEADER_SIZE);

        if ($response) {
            
            $header = substr($response, 0, $headerSize);

            
            $body = substr($response, $headerSize);

           
            $bodyTemp = json_encode(array(0 => $body));
            $bodyTemp = str_replace('\ufeff', '', $bodyTemp);
            $bodyTemp = json_decode($bodyTemp, TRUE);
            $body = trim($bodyTemp[0]);

            
            $header_rows = array_filter(explode(PHP_EOL, $header), "trim");
            $respondHeaders = array();
            foreach ($header_rows as $row) {
                $keylen = strpos($row, ':');
                if ($keylen) {
                    $respondHeaders[] = array(
                        'key' => substr($row, 0, $keylen),
                        'value' => trim(substr($row, $keylen + 1))
                    );
                }
            }

            
            curl_close($require);
            return array(
                'testTime' => $time,
                'testDeny' => $deny,
                'testHttpCode' => $httpCode,
                'testResult' => array(
                    'headers' => $respondHeaders,
                    'body' => $body
                )
            );
        } else {
            if (curl_errno($require)) {
                $error = curl_error($require);
                
                curl_close($require);
                return array(
                    'testTime' => $time,
                    'testDeny' => $deny,
                    'testHttpCode' => 500,
                    'testResult' => array(
                        'headers' => array(),
                        'body' => $error)
                );
            }
            
            curl_close($require);
            return NULL;
        }
    }

    /**
     * Redirect
     * @param $ch
     * @return bool|mixed
     */
    private function curl_redirect_exec($ch)
    {
        static $curl_loops = 0;
        static $curl_max_loops = 20;

        if ($curl_loops++ >= $curl_max_loops) {
            $curl_loops = 0;
            return FALSE;
        }
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        list($header,) = explode("\n\n", $response, 2);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code == 301 || $http_code == 302) {
            $matches = array();
            preg_match('/Location:(.*?)\n/', $header, $matches);
            $url = @parse_url(trim(array_pop($matches)));
            if (!$url) {
                $curl_loops = 0;
                return $response;
            }
            $new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query'] ? '?' . $url['query'] : '');
            if ($url['scheme'] == 'https') {
                
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, TRUE);
            }
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_URL, $new_url);
            return $this->curl_redirect_exec($ch);
        } else {
            $curl_loops = 0;
            return $response;
        }
    }
}

?>