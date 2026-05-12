<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\SettingsModel;

class SetCronKey extends BaseCommand
{
    protected $group       = 'Tasks';
    protected $name        = 'task:set_cron_key';
    protected $description = 'Sets or updates the cron secret key in the database.';
    protected $usage       = 'task:set_cron_key [key]';
    protected $arguments   = [
        'key' => 'The new secret key to set',
    ];

    public function run(array $params)
    {
        $key = $params[0] ?? null;

        if (empty($key)) {
            $key = CLI::prompt('Enter new cron secret key');
        }

        if (empty($key)) {
            CLI::error('Key cannot be empty.');
            return;
        }

        $model = new SettingsModel();
        
        if ($model->setSetting('cron_secret_key', $key)) {
            CLI::write('Cron secret key updated successfully.', 'green');
        } else {
            CLI::error('Failed to update key.');
        }
    }
}
