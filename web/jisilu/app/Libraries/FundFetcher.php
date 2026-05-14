<?php
namespace App\Libraries;

class FundFetcher
{
    /**
     * Fetch fund data from multiple sources (Tiantian, Sina, Eastmoney)
     * Replicates the `fetchFundFromMultipleSources` logic from Node.js
     *
     * @param string $code
     * @return array|null
     */
    public function fetchFund($code)
    {
        $result = [
            'name'           => '',
            'stockName'      => '',
            'price'          => 0,
            'marketPrice'    => 0,
            'netValue'       => 0,
            'purchaseStatus' => '',
            'purchaseFee'    => '',
            'source'         => '',
            'volume'         => 0
        ];

        // 1. Tiantian Fund
        try {
            $url  = "https://fundgz.1234567.com.cn/js/{$code}.js?rt=" . time() . "000";
            $text = $this->curlGet($url);
            if (preg_match('/jsonpgz\((.*)\)/', $text, $matches)) {
                $data = json_decode($matches[1], true);
                if ($data) {
                    $result['name']     = $data['name'] ?? '';
                    $result['price']    = floatval($data['gsz'] ?? 0);
                    $result['netValue'] = floatval($data['dwjz'] ?? 0);
                    $result['source']   = 'Tiantian';
                }
            }
        } catch (\Exception $e) {
            log_message('error', "Tiantian fetch failed for {$code}");
        }

        // 2. Sina Off-exchange (Fallback)
        if (empty($result['name']) || empty($result['netValue'])) {
            try {
                $url  = "https://hq.sinajs.cn/list=f_{$code}";
                $text = $this->curlGet($url, ['Referer: https://finance.sina.com.cn'], true); // true for GBK to UTF-8
                if (preg_match('/"(.*)"/', $text, $matches)) {
                    $parts = explode(',', $matches[1]);
                    if (count($parts) > 1) {
                        if (empty($result['name']))
                            $result['name'] = $parts[0];
                        if (empty($result['netValue']))
                            $result['netValue'] = floatval($parts[1]);
                        if (empty($result['price']))
                            $result['price'] = $result['netValue'];
                        $result['source'] = $result['source'] ?: 'Sina-Off';
                    }
                }
            } catch (\Exception $e) {
                log_message('error', "Sina Off-exchange fetch failed for {$code}");
            }
        }

        // 3. Sina On-exchange
        try {
            $prefix = (strpos($code, '50') === 0) ? 'sh' : 'sz';
            $url    = "https://hq.sinajs.cn/list={$prefix}{$code}";
            $text   = $this->curlGet($url, ['Referer: https://finance.sina.com.cn'], true); // GBK decode
            if (preg_match('/"(.*)"/', $text, $matches)) {
                $parts = explode(',', $matches[1]);
                if (count($parts) > 1) {
                    $result['stockName']   = $parts[0];
                    $result['marketPrice'] = floatval($parts[3] ?? 0);
                    $result['volume']      = floatval($parts[9] ?? 0);
                    if (empty($result['name']))
                        $result['name'] = $parts[0];
                }
            }
        } catch (\Exception $e) {
            log_message('error', "Sina On-exchange fetch failed for {$code}");
        }

        // 4. Eastmoney
        try {
            $commonHeaders = [
                'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1',
                'Referer: https://fund.eastmoney.com/',
                'Host: fundmobapi.eastmoney.com',
                'Accept: application/json, text/plain, */*',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
                'Origin: https://fund.eastmoney.com',
                'Connection: keep-alive'
            ];

            $url      = "https://fundmobapi.eastmoney.com/FundMNewApi/FundMNBaseInfo?pageIndex=1&pageSize=200&appType=ttjj&product=EFund&plat=Android&deviceid=1&Version=1&Fcode={$code}";
            $infoData = json_decode($this->curlGet($url, $commonHeaders), true);

            if ($infoData && isset($infoData['Datas'])) {
                $d = $infoData['Datas'];
                if (!empty($d['SGZT'])) {
                    $result['purchaseStatus'] = $d['SGZT'];
                    if (mb_strpos($result['purchaseStatus'], '限') !== false) {
                        try {
                            $limitHeaders = $commonHeaders;
                            $limitHeaders = array_filter($limitHeaders, function ($v) {
                                return strpos($v, 'Host:') === false; });
                            $limitUrl     = "https://api.fund.eastmoney.com/Fund/GetSingleFundInfo?fcode={$code}&fileds=FCODE,RZDF,SHORTNAME,FUNDTYPE,DTZT,ISSALES,ISBUY,RISKLEVEL,MINSG,MINDT,MAXSG,RATE,SYL_1N";

                            $limitData = json_decode($this->curlGet($limitUrl, $limitHeaders), true);
                            if ($limitData && isset($limitData['Data']['MAXSG'])) {
                                $maxSg = floatval($limitData['Data']['MAXSG']);
                                if ($maxSg > 0) {
                                    $formattedLimit           = $maxSg >= 10000 ? ($maxSg / 10000) . "万" : $maxSg . "元";
                                    $result['purchaseStatus'] = $formattedLimit;
                                }
                            }
                        } catch (\Exception $e) {
                            log_message('error', "Failed to fetch limit for {$code}");
                        }
                    }
                }

                if (empty($result['name']) && !empty($d['SHORTNAME']))
                    $result['name'] = $d['SHORTNAME'];
                if (empty($result['price']) && !empty($d['DWJZ']))
                    $result['price'] = floatval($d['DWJZ']);
                if (empty($result['netValue']) && !empty($d['DWJZ']))
                    $result['netValue'] = floatval($d['DWJZ']);
                if (empty($result['purchaseFee']) && !empty($d['RATE']))
                    $result['purchaseFee'] = $d['RATE'];
            }

            // Fallback old API
            if (empty($result['netValue'])) {
                $url  = "https://fundmobapi.eastmoney.com/FundMApi/FundVarietieFundamental.ashx?FundCode={$code}";
                $data = json_decode($this->curlGet($url, $commonHeaders), true);
                if ($data && isset($data['Datas'])) {
                    $d = $data['Datas'];
                    if (empty($result['name']) && !empty($d['SHORTNAME']))
                        $result['name'] = $d['SHORTNAME'];
                    if (empty($result['price']) && !empty($d['DWJZ']))
                        $result['price'] = floatval($d['DWJZ']);
                    if (empty($result['netValue']) && !empty($d['DWJZ']))
                        $result['netValue'] = floatval($d['DWJZ']);
                    if (empty($result['purchaseFee']) && !empty($d['SGRATE']))
                        $result['purchaseFee'] = $d['SGRATE'];
                }
            }

            // Fee fallback
            if (empty($result['purchaseFee'])) {
                $url       = "https://fundmobapi.eastmoney.com/FundMApi/FundTradeInfo.ashx?FCODE={$code}&deviceid=Wap&plat=Wap&product=EFund&version=2.0.0&_=" . time() . "000";
                $tradeData = json_decode($this->curlGet($url, $commonHeaders), true);
                if ($tradeData && isset($tradeData['Datas']['BUY_FEE'])) {
                    $result['purchaseFee'] = $tradeData['Datas']['BUY_FEE'];
                }
            }
        } catch (\Exception $e) {
            log_message('error', "Eastmoney lookup failed for {$code}");
        }

        return !empty($result['name']) ? $result : null;
    }

    private function curlGet($url, $headers = [], $isGbk = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $startTime   = microtime(true);
        $output      = curl_exec($ch);
        $elapsedTime = microtime(true) - $startTime;
        $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // 记录每次请求的耗时，方便排查哪个接口最慢（如果是调试可以改为 'info' 或 'debug'）
        // 这里使用 'error' 或 'warning' 级别是为了确保它能被写入日志文件（通常阈值较低）
        if ($elapsedTime > 0.5) {
            log_message('info', sprintf("FundFetcher: [%.3fs] HTTP %d - %s", $elapsedTime, $httpCode, $url));
        }

        curl_close($ch);

        if ($output && $isGbk) {
            $output = mb_convert_encoding($output, 'UTF-8', 'GBK');
        }

        return $output;
    }
}
