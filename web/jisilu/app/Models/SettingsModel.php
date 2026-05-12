<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingsModel extends Model
{
    protected $table            = 'app_settings';
    protected $primaryKey       = 'key';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $allowedFields    = ['key', 'value', 'updated_at'];

    /**
     * 获取所有配置并整合为 key-value 数组
     */
    public function getAllSettings(): array
    {
        $data = $this->findAll();
        $settings = [];
        foreach ($data as $row) {
            $settings[$row['key']] = $row['value'];
        }
        return $settings;
    }

    /**
     * 获取单个配置值
     */
    public function getSetting(string $key, $default = null)
    {
        $row = $this->find($key);
        return $row ? $row['value'] : $default;
    }

    /**
     * 保存或更新配置
     */
    public function setSetting(string $key, $value)
    {
        return $this->replace([
            'key'   => $key,
            'value' => (string) $value,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
