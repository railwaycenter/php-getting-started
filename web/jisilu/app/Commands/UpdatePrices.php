<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\LofFundsModel;
use App\Libraries\FundFetcher;

class UpdatePrices extends BaseCommand
{
    /**
     * The Command's Group
     * @var string
     */
    protected $group = 'Tasks';

    /**
     * The Command's Name
     * @var string
     */
    protected $name = 'task:update_prices';

    /**
     * The Command's Description
     * @var string
     */
    protected $description = 'Updates LOF funds prices and premium rates from multiple sources.';

    /**
     * The Command's Usage
     * @var string
     */
    protected $usage = 'task:update_prices';

    public function run(array $params)
    {
        CLI::write('Starting background price update...', 'green');
        
        // 我们改为复用 Api 控制器中封装好的内部逻辑
        // 这符合 DRY (Don't Repeat Yourself) 原则
        $api = new \App\Controllers\Api();
        
        $syncNamesOnly = in_array('--sync-names', $params);
        if ($syncNamesOnly) {
            CLI::write('Mode: Syncing Names only.', 'yellow');
        }

        $count = $api->runUpdatePricesInternal($syncNamesOnly);
        
        CLI::write("Updated {$count} funds successfully.", 'green');
        CLI::write('Background price update finished.', 'green');
    }
}
