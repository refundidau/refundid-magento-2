<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Refundid\CreditMemo\Override\Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create;

use Magento\Sales\Block\Adminhtml\Order\View\Tab\History;
use Refundid\CreditMemo\Util\RefundidUtils;

/**
 * Adminhtml credit memo items grid
 *
 * @api
 * @since 100.0.2
 */
class Items extends \Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items
{

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Data $salesData,
        History $history,
        array $data = []
    ) {
        $this->history = $history;
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $salesData, $data);
    }

    /**
     * Prepare child blocks
     *
     * @return $this
     */
    protected function _prepareLayout()
    {

        $order = $this->getOrder();

        $onclick = "submitAndReloadArea($('creditmemo_item_container'),'" . $this->getUpdateUrl() . "')";

        $this->addChild(
            'update_button',
            \Magento\Backend\Block\Widget\Button::class,
            ['label' => __('Update Qty\'s'), 'class' => 'update-button', 'onclick' => $onclick]
        );

        $showRefundidButton = RefundidUtils::hasRefundidOrder($order);

        if ($this->getCreditmemo()->canRefund()) {
            if ($this->getCreditmemo()->getInvoice() && $this->getCreditmemo()->getInvoice()->getTransactionId()) {
                if (!$showRefundidButton) $this->addNormalSubmitButton();
            }
        }

        if ($showRefundidButton) $this->addRefundidButton();
        else $this->addOfflineSubmitButton();
    }

    private function addNormalSubmitButton()
    {
        $this->addChild(
            'submit_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Refund'),
                'class' => 'save submit-button refund primary',
                'onclick' => 'disableElements(\'submit-button\');submitCreditMemo()'
            ]
        );
    }

    private function addOfflineSubmitButton()
    {
        $this->addChild(
            'submit_offline',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Refund Offline'),
                'class' => 'save submit-button primary',
                'onclick' => 'disableElements(\'submit-button\');submitCreditMemoOffline()'
            ]
        );
    }


    private function addRefundidButton()
    {
        $this->addChild(
            'submit_offline',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Refund to Refundid'),
                'class' => 'save submit-button primary',
                'onclick' => 'window.open("https://merchant.refundid.com/")'
            ]
        );
    }

}
