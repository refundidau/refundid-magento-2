<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Refundid\CreditMemo\Override\Magento\Sales\Block\Adminhtml\Order\View\Tab;

use Refundid\CreditMemo\Util\RefundidUtils;

/**
 * Order information tab
 *
 * @api
 * @author Refundid
 * @since 100.0.2
 */
class Info extends \Magento\Sales\Block\Adminhtml\Order\View\Tab\Info
{
    public function hasRefundidOrder()
    {
        return RefundidUtils::hasRefundidOrder($this->getOrder());
    }
}