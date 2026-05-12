<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\SettingsModel;

class SetAdminPassword extends BaseCommand
{
    protected $group       = 'Tasks';
    protected $name        = 'task:set_admin_password';
    protected $description = 'Sets or updates the admin password in the database.';
    protected $usage       = 'task:set_admin_password [password]';
    protected $arguments   = [
        'password' => 'The new password to set',
    ];

    public function run(array $params)
    {
        $password = $params[0] ?? null;

        if (empty($password)) {
            $password = CLI::prompt('Enter new admin password');
        }

        if (empty($password)) {
            CLI::error('Password cannot be empty.');
            return;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $model = new SettingsModel();
        
        if ($model->setSetting('admin_password_hash', $hash)) {
            CLI::write('Admin password updated successfully.', 'green');
        } else {
            CLI::error('Failed to update password.');
        }
    }
}
