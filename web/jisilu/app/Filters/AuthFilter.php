<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $token = $request->getHeaderLine('Authorization');
        
        $settingsModel = new \App\Models\SettingsModel();
        $activeToken = $settingsModel->getSetting('admin_active_token');
        
        $expectedToken = "Bearer " . $activeToken;
        
        if (empty($activeToken) || $token !== $expectedToken) {
            $response = service('response');
            $response->setJSON(['error' => '未授权访问，请重新登录']);
            return $response->setStatusCode(401);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing here
    }
}
