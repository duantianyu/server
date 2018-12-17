<?php

namespace App;

class Helpers
{

    /**
     * get the amount of memory allocated to PHP
     * @return string
     */
    static function getMemoryUsage()
    {
        $size = memory_get_usage(true);
        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];

        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $unit[$i];
    }

    /**
     * Send a get request through a proxy
     * @param string $url
     * @param bool $need_proxy
     * @return mixed
     */
    static function getByCurl($url, $need_proxy = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.87 Safari/537.36 OPR/54.0.2952.54');

        if ($need_proxy) {
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
            curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:1088');
            //curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5)
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
        }

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }


    /**
     * Post request
     * @param string $remote_server
     * @param string(json) $post_string
     * @param bool $need_proxy
     * @return mixed
     */
    static function requestByCurl($remote_server, $post_string, $need_proxy = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remote_server);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json;charset=utf-8']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 线下环境不用开启curl证书验证, 未调通情况可尝试添加该代码
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        if ($need_proxy) {
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
            curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:1088');
            //curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5)
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
        }

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }


    /**
     * Send multi get requests through a proxy
     * @param array $url
     * @param bool $need_proxy
     * @return array
     */
    static function getByCurlMulti(array $url, $need_proxy = false)
    {
        $ch_arr = [];
        $mh = curl_multi_init();
        foreach ($url as $k => $val) {
            $ch_arr[$k] = curl_init();
            curl_setopt($ch_arr[$k], CURLOPT_URL, $val);
            curl_setopt($ch_arr[$k], CURLOPT_HEADER, 0);
            curl_setopt($ch_arr[$k], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_arr[$k], CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch_arr[$k], CURLOPT_TIMEOUT, 15);
            curl_setopt($ch_arr[$k], CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.87 Safari/537.36 OPR/54.0.2952.54');
            if ($need_proxy) {
                curl_setopt($ch_arr[$k], CURLOPT_HTTPPROXYTUNNEL, 0);
                curl_setopt($ch_arr[$k], CURLOPT_PROXY, '127.0.0.1:1088');
                curl_setopt($ch_arr[$k], CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
            }

            curl_multi_add_handle($mh, $ch_arr[$k]);
        }
        //do curl
        $active = null;
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) == -1) {
                usleep(100);
            }
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
        //close curl
        foreach ($ch_arr as $val) {
            curl_multi_remove_handle($mh, $val);
        }
        //close
        curl_multi_close($mh);
        //get result
        foreach ($ch_arr as $val) {
            $response[] = curl_multi_getcontent($val);
        }

        return $response;
    }

}
