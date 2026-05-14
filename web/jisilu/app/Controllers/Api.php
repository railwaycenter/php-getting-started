<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\LofFundsModel;

class Api extends ResourceController
{
    public function health()
    {
        return $this->respond(['status' => 'ok', 'env' => 'codeigniter']);
    }

    public function getLofData()
    {
        $category = $this->request->getGet('category');
        $search   = trim($this->request->getGet('search'));

        $model = new LofFundsModel();

        try {
            $funds = $model->getActiveFunds($category, $search);

            $allStatsQuery  = $model->select('premium_rate, volume')->where('is_deleted', false)->findAll();
            $totalCount     = 0;
            $averagePremium = 0;
            $totalVolume    = 0;

            if ($allStatsQuery && count($allStatsQuery) > 0) {
                $totalCount = count($allStatsQuery);
                // PHP 8+ strictness: array_column fails if input is empty or invalid
                $premiumColumn = array_column($allStatsQuery, 'premium_rate');
                $volumeColumn  = array_column($allStatsQuery, 'volume');

                if (!empty($premiumColumn)) {
                    $premiumSum     = array_sum($premiumColumn);
                    $averagePremium = $premiumSum / $totalCount;
                }

                if (!empty($volumeColumn)) {
                    $totalVolume = array_sum($volumeColumn);
                }
            }

            // CamelCase mapping for the frontend compatibility
            $mappedFunds = array_map(function ($f) {
                return [
                    'id'             => $f['id'],
                    'code'           => $f['code'],
                    'name'           => $f['name'],
                    'stockName'      => $f['stock_name'],
                    'price'          => (float) $f['price'],
                    'marketPrice'    => (float) $f['market_price'],
                    'netValue'       => (float) $f['net_value'],
                    'premiumRate'    => (float) $f['premium_rate'],
                    'volume'         => (float) $f['volume'],
                    'turnoverRate'   => (float) $f['turnover_rate'],
                    'alertThreshold' => (float) $f['alert_threshold'],
                    'lastUpdate'     => $f['last_update'],
                    'category'       => $f['category'],
                    'purchaseStatus' => $f['purchase_status'],
                    'purchaseFee'    => $f['purchase_fee']
                ];
            }, $funds);

            return $this->respond([
                'list'  => $mappedFunds,
                'stats' => [
                    'totalCount'     => $totalCount,
                    'averagePremium' => $averagePremium,
                    'totalVolume'    => $totalVolume
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return $this->failServerError($e->getMessage() ?: "服务器内部错误");
        }
    }

    public function addFund()
    {
        $json = $this->request->getJSON();
        if (!$json || !isset($json->code) || !isset($json->name) || !isset($json->category)) {
            return $this->failValidationError("缺少必填字段");
        }

        $model = new LofFundsModel();
        // Check if exists
        $existing = $model->where('code', $json->code)->first();

        // classification function logic
        $categoryToUse = $this->classifyFund($json->code, $json->name);

        $p           = isset($json->price) ? (float) $json->price : 1.0;
        $mp          = isset($json->marketPrice) ? (float) $json->marketPrice : $p;
        $nv          = isset($json->netValue) ? (float) $json->netValue : 1.0;
        $at          = isset($json->alertThreshold) ? (float) $json->alertThreshold : 10.0;
        $vol         = isset($json->volume) ? (float) $json->volume : 0;
        $premiumRate = $nv > 0 ? (($mp - $nv) / $nv) * 100 : 0;

        if ($existing) {
            if ($existing['is_deleted']) {
                $updatePayload = [
                    'is_deleted'   => false,
                    'name'         => $json->name,
                    'stock_name'   => $json->stockName ?? null,
                    'price'        => $p,
                    'market_price' => $mp,
                    'net_value'    => $nv,
                    'premium_rate' => $premiumRate,
                    'volume'       => $vol,
                    'last_update'  => date('Y-m-d H:i:s'),
                    'category'     => $categoryToUse
                ];
                if (isset($json->purchaseStatus))
                    $updatePayload['purchase_status'] = $json->purchaseStatus;
                if (isset($json->purchaseFee))
                    $updatePayload['purchase_fee'] = $json->purchaseFee;

                $model->update($existing['id'], $updatePayload);
                return $this->respond(['success' => true, 'message' => "记录已恢复"]);
            } else {
                return $this->failResourceExists("记录已存在");
            }
        } else {
            // Because TS lacked auto ID generation in insert except for whatever Supabase did,
            // we will let the database create the ID or simulate UUID
            $insertPayload = [
                'code'            => $json->code,
                'name'            => $json->name,
                'stock_name'      => $json->stockName ?? null,
                'price'           => $p,
                'market_price'    => $mp,
                'net_value'       => $nv,
                'premium_rate'    => $premiumRate,
                'volume'          => $vol,
                'turnover_rate'   => 0,
                'alert_threshold' => $at,
                'last_update'     => date('Y-m-d H:i:s'),
                'category'        => $categoryToUse,
                'is_deleted'      => false
            ];
            if (isset($json->purchaseStatus))
                $insertPayload['purchase_status'] = $json->purchaseStatus;
            if (isset($json->purchaseFee))
                $insertPayload['purchase_fee'] = $json->purchaseFee;

            $model->insert($insertPayload);
            return $this->respondCreated(['success' => true]);
        }
    }

    public function batchAddFunds()
    {
        $json = $this->request->getJSON();
        if (!$json || !isset($json->codes) || !is_array($json->codes) || empty($json->codes)) {
            return $this->failValidationError("缺少或无效的代码列表");
        }

        $fetcher = new \App\Libraries\FundFetcher();
        $model   = new LofFundsModel();

        $successCount = 0;
        $errorCount   = 0;

        foreach ($json->codes as $code) {
            try {
                $data = $fetcher->fetchFund($code);
                if ($data) {
                    $p           = $data['price'] ?: ($data['netValue'] ?: 1.0);
                    $mp          = $data['marketPrice'] ?: $p;
                    $nv          = $data['netValue'] ?: $p;
                    $premiumRate = $nv > 0 ? (($mp - $nv) / $nv) * 100 : 0;
                    $category    = $this->classifyFund($code, $data['name']);

                    $insertPayload = [
                        'code'            => $code,
                        'name'            => $data['name'],
                        'stock_name'      => $data['stockName'],
                        'price'           => $p,
                        'market_price'    => $mp,
                        'net_value'       => $nv,
                        'premium_rate'    => $premiumRate,
                        'volume'          => $data['volume'] ?: 0,
                        'turnover_rate'   => 0,
                        'alert_threshold' => 10.0,
                        'last_update'     => date('Y-m-d H:i:s'),
                        'category'        => $category,
                        'is_deleted'      => false
                    ];
                    if (!empty($data['purchaseStatus']))
                        $insertPayload['purchase_status'] = $data['purchaseStatus'];
                    if (!empty($data['purchaseFee']))
                        $insertPayload['purchase_fee'] = $data['purchaseFee'];

                    if ($model->insert($insertPayload)) {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                } else {
                    $errorCount++;
                }
                // Delay to avoid rate limiting
                usleep(500000); // 500ms
            } catch (\Exception $e) {
                log_message('error', "Batch add failed for {$code}: " . $e->getMessage());
                $errorCount++;
            }
        }

        return $this->respond(['success' => true, 'successCount' => $successCount, 'errorCount' => $errorCount]);
    }

    public function updateFund($id = null)
    {
        $json = $this->request->getJSON();
        if (!$id || !$json)
            return $this->failValidationError();

        $model = new LofFundsModel();

        $p           = isset($json->price) ? (float) $json->price : 0;
        $mp          = isset($json->marketPrice) ? (float) $json->marketPrice : $p;
        $nv          = isset($json->netValue) ? (float) $json->netValue : 0;
        $at          = isset($json->alertThreshold) ? (float) $json->alertThreshold : 10.0;
        $vol         = isset($json->volume) ? (float) $json->volume : 0;
        $premiumRate = $nv > 0 ? (($mp - $nv) / $nv) * 100 : 0;

        $categoryToUse = isset($json->category) && !empty($json->category)
            ? $json->category
            : $this->classifyFund($json->code, $json->name);

        $updatePayload = [
            'code'            => $json->code,
            'name'            => $json->name,
            'stock_name'      => $json->stockName ?? null,
            'category'        => $categoryToUse,
            'price'           => $p,
            'market_price'    => $mp,
            'net_value'       => $nv,
            'premium_rate'    => $premiumRate,
            'alert_threshold' => $at,
            'volume'          => $vol,
            'last_update'     => date('Y-m-d H:i:s')
        ];
        if (isset($json->purchaseStatus))
            $updatePayload['purchase_status'] = $json->purchaseStatus;
        if (isset($json->purchaseFee))
            $updatePayload['purchase_fee'] = $json->purchaseFee;

        $model->update($id, $updatePayload);
        return $this->respond(['success' => true]);
    }

    public function deleteFund($id = null)
    {
        if (!$id)
            return $this->failValidationError();
        $model = new LofFundsModel();
        $model->update($id, ['is_deleted' => true]);
        return $this->respondDeleted(['success' => true]);
    }

    public function getSettings()
    {
        $settingsModel = new \App\Models\SettingsModel();
        $settings      = $settingsModel->getAllSettings();

        return $this->respond([
            'appTitle'                => $settings['app_title'] ?? "集思录 LOF 数据仿制版",
            'frontendRefreshInterval' => (int) ($settings['frontend_refresh_interval'] ?? 10),
            'batchSize'               => (int) ($settings['batch_size'] ?? 50),
            'customCategories'        => $settings['custom_categories'] ?? 'LOF:普通LOF,IndexLOF:指数LOF,StockLOF:股票型LOF'
        ]);
    }

    public function saveSettings()
    {
        $json = $this->request->getJSON();
        if (!$json)
            return $this->failValidationError();

        $settingsModel = new \App\Models\SettingsModel();

        if (isset($json->appTitle)) {
            $settingsModel->setSetting('app_title', $json->appTitle);
        }
        if (isset($json->frontendRefreshInterval)) {
            $settingsModel->setSetting('frontend_refresh_interval', $json->frontendRefreshInterval);
        }
        if (isset($json->batchSize)) {
            $settingsModel->setSetting('batch_size', (int) $json->batchSize);
        }
        if (isset($json->customCategories)) {
            $settingsModel->setSetting('custom_categories', $json->customCategories);
        }

        return $this->respond(['success' => true]);
    }

    public function login()
    {
        $json     = $this->request->getJSON();
        $password = $json->password ?? '';

        $settingsModel = new \App\Models\SettingsModel();
        $hash          = $settingsModel->getSetting('admin_password_hash');

        if ($hash && password_verify($password, $hash)) {
            // 生成动态随机 Token
            $dynamicToken = bin2hex(random_bytes(16));
            $settingsModel->setSetting('admin_active_token', $dynamicToken);

            return $this->respond(['success' => true, 'token' => $dynamicToken]);
        }

        return $this->failUnauthorized("密码错误或管理员未初始化");
    }

    public function getStats()
    {
        $model = new LofFundsModel();
        $funds = $model->select('premium_rate, volume')->where('is_deleted', false)->findAll();

        $totalCount     = count($funds);
        $averagePremium = 0;
        $totalVolume    = 0;
        if ($totalCount > 0) {
            $premiumSum     = array_sum(array_column($funds, 'premium_rate'));
            $averagePremium = $premiumSum / $totalCount;
            $totalVolume    = array_sum(array_column($funds, 'volume'));
        }

        return $this->respond([
            'totalCount'     => $totalCount,
            'averagePremium' => $averagePremium,
            'totalVolume'    => $totalVolume
        ]);
    }

    public function fundLookup($code = null)
    {
        if (!$code)
            return $this->failValidationError();

        $fetcher = new \App\Libraries\FundFetcher();
        $data    = $fetcher->fetchFund($code);

        if ($data) {
            $category     = $this->classifyFund($code, $data['name']);
            $responseData = array_merge($data, [
                'category'   => $category,
                'lastUpdate' => date('c')
            ]);
            return $this->respond($responseData);
        } else {
            return $this->failNotFound("未找到该基金，请检查代码是否正确（通常LOF以16或50开头）");
        }
    }

    public function syncNames()
    {
        // ... 原有逻辑 ... (略，实际执行时会保留完整代码)
        // 实际上我们可以通过 runUpdatePricesInternal 来涵盖名字同步
        $result = $this->runUpdatePricesInternal(true);
        return $this->respond(['success' => true, 'count' => $result]);
    }

    /**
     * Val.town 专用定时触发接口
     */
    public function cronUpdate()
    {
        $startTime = microtime(true);
        // 关键修复：延长脚本执行时间至 300 秒，因为采集基金数据需要较长时间
        // set_time_limit(300);

        $providedKey = $this->request->getGet('key');
        if (!$providedKey)
            return $this->failUnauthorized("缺少密钥");

        $settingsModel = new \App\Models\SettingsModel();
        $savedKey      = $settingsModel->getSetting('cron_secret_key');

        if (empty($savedKey) || $providedKey !== $savedKey) {
            return $this->failUnauthorized("密钥无效或未设置");
        }

        $batchSize = (int) $settingsModel->getSetting('batch_size', 50);
        $result    = $this->runUpdatePricesInternal(false, $batchSize, true);

        $executionTime = round(microtime(true) - $startTime, 2);

        return $this->respond([
            'success'        => true,
            'summary'        => "成功处理了 {$result['count']} 条数据",
            'execution_time' => "总耗时 {$executionTime} s",
            'timestamp'      => date('Y-m-d H:i:s', time() + 8 * 3600), // 修正为北京时间 (UTC+8)
            'updated_list'   => $result['details']
        ]);
    }

    /**
     * 核心采集逻辑封装 (供 CLI 和 API 复用)
     * @param bool $syncNamesOnly 是否仅同步名称
     * @param int|null $limit 限制采集条数
     * @param bool $returnDetails 是否返回详细列表
     * @return int|array 成功更新条目或详情数组
     */
    public function runUpdatePricesInternal($syncNamesOnly = false, $limit = null, $returnDetails = false)
    {
        $model   = new LofFundsModel();
        $fetcher = new \App\Libraries\FundFetcher();

        $builder = $model->where('is_deleted', false)
            ->orderBy('last_update', 'ASC'); // 轮询逻辑：优先抓取最久未更新的数据

        if ($limit) {
            $builder->limit($limit);
        }

        $funds         = $builder->findAll();
        $successCount  = 0;
        $details       = [];
        $loopStartTime = microtime(true); // 记录循环开始时间

        foreach ($funds as $f) {
            // 防超时机制：如果执行时间超过 25 秒（预留时间防止被强制中断），则安全退出
            if (microtime(true) - $loopStartTime > 25) {
                log_message('warning', "runUpdatePricesInternal: Execution time approaching 30s limit, breaking loop early.");
                break;
            }

            try {
                // 采集时增加微小延迟，避免被封禁
                usleep(300000);

                $data = $fetcher->fetchFund($f['code']);
                if ($data) {
                    $nv          = $data['netValue'] ?: ($f['net_value'] ?: 1);
                    $price       = $data['price'] ?: $nv;
                    $marketPrice = $data['marketPrice'] ?: $price;
                    $premiumRate = $nv > 0 ? (($marketPrice - $nv) / $nv) * 100 : 0;

                    $payload = [
                        'last_update' => date('Y-m-d H:i:s')
                    ];

                    if ($syncNamesOnly) {
                        $payload['name']       = $data['name'];
                        $payload['stock_name'] = $data['stockName'];
                    } else {
                        $payload['price']        = $data['price'];
                        $payload['market_price'] = $data['marketPrice'];
                        $payload['net_value']    = $nv;
                        $payload['premium_rate'] = $premiumRate;
                        $payload['volume']       = $data['volume'];
                        if (!empty($data['purchaseStatus']))
                            $payload['purchase_status'] = $data['purchaseStatus'];
                        if (!empty($data['purchaseFee']))
                            $payload['purchase_fee'] = $data['purchaseFee'];
                    }

                    $model->update($f['id'], $payload);
                    $successCount++;
                    if ($returnDetails) {
                        $details[] = [
                            'code'    => $f['code'],
                            'name'    => $data['name'],
                            'price'   => $marketPrice,
                            'premium' => round($premiumRate, 2) . '%'
                        ];
                    }
                }
            } catch (\Exception $e) {
                log_message('error', "Update Logic failed for {$f['code']}: " . $e->getMessage());
            }
        }
        return $returnDetails ? ['count' => $successCount, 'details' => $details] : $successCount;
    }

    // Helper methods
    private function failValidationError($message = "请求数据无效")
    {
        return $this->fail($message, 400);
    }

    private function classifyFund($code, $name)
    {
        $indexKeywords = [
            '指数',
            '300',
            '500',
            '1000',
            '50',
            '800',
            '红利',
            '价值',
            '成长',
            '恒生',
            '纳斯达克',
            '标普',
            '道琼斯',
            '中证',
            '上证',
            '深证',
            '创业板',
            '科创',
            '全指',
            '细分',
            '等权',
            '策略'
        ];

        $stockKeywords = [
            '股票',
            '精选',
            '核心',
            '优选',
            '主题',
            '行业',
            '领先',
            '配置',
            '混合',
            '灵活',
            '积极',
            '动力',
            '回报',
            '机遇'
        ];

        foreach ($indexKeywords as $k) {
            if (mb_strpos($name, $k) !== false)
                return 'IndexLOF';
        }

        foreach ($stockKeywords as $k) {
            if (mb_strpos($name, $k) !== false)
                return 'StockLOF';
        }

        if (strpos($code, '50') === 0)
            return 'StockLOF';

        return 'LOF';
    }
}
