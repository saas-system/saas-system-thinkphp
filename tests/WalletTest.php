<?php


use PHPUnit\Framework\TestCase;

((new \think\App())->http)->run();

class WalletTest extends TestCase
{
    public function testData()
    {
        $tradeAmount = 2.1;
        $tradInfo    = [
            'trade_title'   => '测试',
            'memo'          => '测试',
            'trade_amount'  => $tradeAmount,
            'business_type' => 1,
            'pay_type'      => 1,
            'user_id'       => 1,
        ];

        $fromUserId = 1;
        $fromRole   = 'U';
        $toRole     = 'A';

        $fromAccountId = 1;
        $toAccountId   = 2;

        $tenantId  = '226ba257-d398-40d1-980b-2081598a8898';
        $tenantPre = ''; // 可为空

        $walletService = new \Sxqibo\FastWallet\service\WalletService($tenantId, $tenantPre);
        $formWallet    = $walletService->saveWallet('-' . $tradeAmount, $fromAccountId, $fromUserId, $fromRole);
        $toWallet      = $walletService->saveWallet($tradeAmount, $toAccountId, $tenantId, $toRole);

        $walletService->saveWalletFlows($formWallet, $toWallet, $tradInfo, '', '');
    }
}
