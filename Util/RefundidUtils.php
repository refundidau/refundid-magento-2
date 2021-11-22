<?php
/**
 * Magento 2 extensions for Refundid
 *
 * @author Refundid
 */

namespace Refundid\CreditMemo\Util;

use Refundid\CreditMemo\Util\RefundidUtils;

class RefundidUtils{
    public static function hasRefundidOrder($order)
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
        $arrOrderstatus = [];
        foreach ($history as $comment) {
            if ($comment['comment']) {
                if (preg_match('/\bRefundid approved by merchant\b/', $comment['comment'])) {

                    preg_match_all('#\((.*?)\)#', $comment['comment'], $ordermatch);

                    if (isset($ordermatch[1])) {
                        foreach ($ordermatch[1] as $order) {
                            $approvedId = explode("-", $order);
                            $approved[] = $approvedId[1];
                        }
                    }
                }
                if (preg_match('/\bThis order has been refunded by\b/', $comment['comment'])) {

                    preg_match_all('#\((.*?)\)#', $comment['comment'], $refundmatch);

                    if (isset($refundmatch[1])) {
                        foreach ($refundmatch[1] as $refund) {
                            $requestId = explode("-", $refund);
                            $requested[$requestId[1]] = True;
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

        $arrOrderstatus = array_merge($approved, $rejects);

        if (!isset($requested) && !isset($arrOrderstatus)) return false;

        sort($requested);
        sort($arrOrderstatus);
        // Check for equality
        return ($requested != $arrOrderstatus);
    }
}
