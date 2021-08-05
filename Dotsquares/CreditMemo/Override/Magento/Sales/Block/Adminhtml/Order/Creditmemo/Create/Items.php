<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Dotsquares\CreditMemo\Override\Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create;

use Magento\Sales\Block\Adminhtml\Order\View\Tab\History;

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
        $buttonShow = $this->showButtonNew($order);
        if ($this->getCreditmemo()->canRefund()) {
            if ($this->getCreditmemo()->getInvoice() && $this->getCreditmemo()->getInvoice()->getTransactionId()) {
                if (!$buttonShow) {
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
            }
            if ($buttonShow) {
                $this->addChild(
                    'submit_offline',
                    \Magento\Backend\Block\Widget\Button::class,
                    [
                        'label' => __('Refund to refundid'),
                        'class' => 'save submit-button primary',
                        'onclick' => 'window.open("https://refundidmerchants.24livehost.com/")'
                    ]
                );
            } else {
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
        } else {
            if ($buttonShow) {
                $this->addChild(
                    'submit_offline',
                    \Magento\Backend\Block\Widget\Button::class,
                    [
                        'label' => __('Refund to refundid'),
                        'class' => 'save submit-button primary',
                        'onclick' => 'window.open("https://refundidmerchants.24livehost.com/")'
                    ]
                );
            } else {
                $this->addChild(
                    'submit_button',
                    \Magento\Backend\Block\Widget\Button::class,
                    [
                        'label' => __('Refund Offline'),
                        'class' => 'save submit-button primary',
                        'onclick' => 'disableElements(\'submit-button\');submitCreditMemoOffline()'
                    ]
                );
            }
        }
    }
    public function showButtonNew($order)
    {

        foreach ($order->getAllStatusHistory() as $orderComment) {
            $history[] = ['date' => $orderComment->getCreatedAt(), 'comment' =>
            $orderComment->getComment()];
        }

        foreach ($order->getCreditmemosCollection() as $_memo) {
            foreach ($_memo->getCommentsCollection() as $_comment) {
                $history[] = ['date' => $_comment->getCreatedAt(), 'comment' =>
                $_comment->getComment()];
            }
        }
        foreach ($order->getShipmentsCollection() as $_shipment) {
            foreach ($_shipment->getCommentsCollection() as $_comment) {
                $history[] = ['date' => $_comment->getCreatedAt(), 'comment' =>
                $_comment->getComment()];
            }
        }
        foreach ($order->getInvoiceCollection() as $_invoice) {
            foreach ($_invoice->getCommentsCollection() as $_comment) {
                $history[] = ['date' => $_comment->getCreatedAt(), 'comment' =>
                $_comment->getComment()];
            }
        }
        $requested = [];
        $approved = [];
        $rejects = [];
        $arrOrderstaus = [];
        if (isset($history)) {
            foreach ($history as $comment) {
                if ($comment['comment']) {
                    if (preg_match('/\bRefundid approved by merchant\b/', $comment['comment'])) {

                        preg_match_all('#\((.*?)\)#', $comment['comment'], $odermatch);

                        if (isset($odermatch[1])) {
                            foreach ($odermatch[1] as $oder) {
                                $approvedId = explode("-", $oder);
                                $approved[] = $approvedId[1];
                            }
                        }
                    }
                    if (preg_match('/\bThis order has been refunded by\b/', $comment['comment'])) {

                        preg_match_all('#\((.*?)\)#', $comment['comment'], $refundmatch);

                        if (isset($refundmatch[1])) {
                            foreach ($refundmatch[1] as $refund) {
                                $requestId = explode("-", $refund);
                                $requested[] = $requestId[1];
                            }
                        }
                    }

                    if (preg_match('/\bRefundid rejected\b/', $comment['comment'])) {

                        preg_match_all('#\((.*?)\)#', $comment['comment'], $rejectedmatch);

                        if (isset($rejectedmatch[1])) {
                            foreach ($rejectedmatch[1] as $refund) {
                                $rejectId = explode("-", $refund);
                                $rejects[] = $rejectId[1];
                            }
                        }
                    }
                }
            }
            $arrOrderstaus = array_merge($approved, $rejects);

            if (isset($requested) || isset($arrOrderstaus)) {
                sort($requested);
                sort($arrOrderstaus);

                // Check for equality
                if ($requested == $arrOrderstaus) {
                    return false;
                } else {
                    return true;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
