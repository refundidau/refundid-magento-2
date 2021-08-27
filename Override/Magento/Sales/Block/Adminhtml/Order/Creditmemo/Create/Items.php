<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Refundid\CreditMemo\Override\Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create;

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

        $showRefundidButton = $this->shouldShowRefundidButton($order);

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



    public function shouldShowRefundidButton($order)
    {

        foreach ($order->getAllStatusHistory() as $orderComment) {
            $history[] = ['date' => $orderComment->getCreatedAt(), 'comment' => $orderComment->getComment()];
        }

        foreach ($order->getCreditmemosCollection() as $_memo) {
            foreach ($_memo->getCommentsCollection() as $_comment) {
                $history[] = ['date' => $_comment->getCreatedAt(), 'comment' => $_comment->getComment()];
            }
        }
        foreach ($order->getShipmentsCollection() as $_shipment) {
            foreach ($_shipment->getCommentsCollection() as $_comment) {
                $history[] = ['date' => $_comment->getCreatedAt(), 'comment' => $_comment->getComment()];
            }
        }
        foreach ($order->getInvoiceCollection() as $_invoice) {
            foreach ($_invoice->getCommentsCollection() as $_comment) {
                $history[] = ['date' => $_comment->getCreatedAt(), 'comment' => $_comment->getComment()];
            }
        }

        if (!isset($history)) return false;

        $requested = [];
        $approved = [];
        $rejects = [];
        $arrOrderstaus = [];
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

        if (!isset($requested) && !isset($arrOrderstaus)) return false;

        sort($requested);
        sort($arrOrderstaus);
        // Check for equality
        return ($requested != $arrOrderstaus);
    }
}
