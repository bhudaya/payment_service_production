<?php

namespace Iapps\PaymentService\PaymentRequest;

use Iapps\Common\Core\IappsBasicBaseService;
use Iapps\PaymentService\Common\PaymentEventProducer;
use Iapps\PaymentService\PaymentMode\PaymentModeType;

class TMoneyInquireTransactionStatusService extends IappsBasicBaseService{

    public function process()
    {
        $TMoneyPaymentReqServ = PaymentRequestServiceFactory::build(PaymentModeType::BANK_TRANSFER_TMONEY);
        $TMoneyPaymentReqServ->setUpdatedBy($this->getUpdatedBy());
        $TMoneyPaymentReqServ->setIpAddress($this->getIpAddress());
        $this->_inquiryRequest($TMoneyPaymentReqServ);

        return true;
    }

    protected function _inquiryRequest(TMoneyPaymentRequestService $paymentRequestServ)
    {
        $i = 0;
        if ($requests = $paymentRequestServ->findPendingRequest()){
            foreach ($requests->result as $req) {
                if ($req instanceof PaymentRequest) {
                    if(!empty($req->getReferenceID())){
                      if ($response = $paymentRequestServ->reprocessRequest($req)) {
                            PaymentEventProducer::publishPaymentRequestChanged($req->getModuleCode(), $req->getTransactionID(), $req->getPaymentCode());
                      }
                    }
                }
                $i++;
            }
        }
    }
}