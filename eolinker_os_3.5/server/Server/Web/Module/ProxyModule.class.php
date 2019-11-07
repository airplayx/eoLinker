<?php

/**
 * @name eolinker ams open source，eolinker开源版本
 * @link https://www.eolinker.com/
 * @package eolinker
 * @author www.eolinker.com 广州银云信息科技有限公司 2015-2017
 * eoLinker是目前全球领先、国内最大的在线API接口管理平台，提供自动生成API文档、API自动化测试、Mock测试、团队协作等功能，旨在解决由于前后端分离导致的开发效率低下问题。
 * 如在使用的过程中有任何问题，欢迎加入用户讨论群进行反馈，我们将会以最快的速度，最好的服务态度为您解决问题。
 *
 * eoLinker AMS开源版的开源协议遵循Apache License 2.0，如需获取最新的eolinker开源版以及相关资讯，请访问:https://www.eolinker.com/#/os/download
 *
 * 官方网站：https://www.eolinker.com/
 * 官方博客以及社区：http://blog.eolinker.com/
 * 使用教程以及帮助：http://help.eolinker.com/
 * 商务合作邮箱：market@eolinker.com
 * 用户讨论QQ群：284421832
 */
class ProxyModule
{
    /**
     * 转发请求到目的主机
     * @param string $method 请求方法
     * @param string $URL 请求路径
     * @param string $headers 请求头部
     * @param string $param 请求参数
     * @return bool|array
     */
    public function proxyToDesURL($method, $URL, &$headers = NULL, &$param = NULL)
    {
        //初始化请求
        $require = curl_init($URL);

        //判断是否HTTPS
        $isHttps = substr($URL, 0, 8) == "https://" ? TRUE : FALSE;

        //设置请求方式
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
                //HEAD请求返回结果不包含BODY
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
            //跳过证书检查
            curl_setopt($require, CURLOPT_SSL_VERIFYPEER, FALSE);
            //检查证书中是否设置域名
            curl_setopt($require, CURLOPT_SSL_VERIFYHOST, TRUE);
        }

        if ($headers) {
            //设置请求头
            curl_setopt($require, CURLOPT_HTTPHEADER, $headers);
        }

        //返回结果不直接输出
        curl_setopt($require, CURLOPT_RETURNTRANSFER, TRUE);

        //重定向
        //curl_setopt($require, CURLOPT_FOLLOWLOCATION, TRUE);

        //把返回头包含再输出中
        curl_setopt($require, CURLOPT_HEADER, TRUE);

        $time = date("Y-m-d H:i:s", time());

        //发送请求
        $response = $this->curl_redirect_exec($require);

        //获取返回结果状态码
        $httpCode = curl_getinfo($require, CURLINFO_HTTP_CODE);

        //获取传输总耗时
        $deny = curl_getinfo($require, CURLINFO_TOTAL_TIME) * 1000;

        //获取头部长度
        $headerSize = curl_getinfo($require, CURLINFO_HEADER_SIZE);

        if ($response) {
            //返回头部字符串
            $header = substr($response, 0, $headerSize);

            //返回体
            $body = substr($response, $headerSize);

            //过滤隐藏非法字符
            $bodyTemp = json_encode(array(0 => $body));
            $bodyTemp = str_replace('\ufeff', '', $bodyTemp);
            $bodyTemp = json_decode($bodyTemp, TRUE);
            $body = trim($bodyTemp[0]);

            //将返回结果头部转成数组
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

            //关闭请求
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
                //关闭请求
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
            //关闭请求
            curl_close($require);
            return NULL;
        }
    }

    /**
     * 重定向处理
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
                //跳过证书检查
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                //检查证书中是否设置域名
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