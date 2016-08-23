<?php
/**
 *                       ######
 *                       ######
 * ############    ####( ######  #####. ######  ############   ############
 * #############  #####( ######  #####. ######  #############  #############
 *        ######  #####( ######  #####. ######  #####  ######  #####  ######
 * ###### ######  #####( ######  #####. ######  #####  #####   #####  ######
 * ###### ######  #####( ######  #####. ######  #####          #####  ######
 * #############  #############  #############  #############  #####  ######
 *  ############   ############  #############   ############  #####  ######
 *                                      ######
 *                               #############
 *                               ############
 *
 * Adyen Payment module (https://www.adyen.com/)
 *
 * Copyright (c) 2015 Adyen BV (https://www.adyen.com/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>
 */

namespace Adyen\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;

class RefundResponseValidator extends AbstractValidator
{
    /**
     * @param array $validationSubject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate(array $validationSubject)
    {
        $responses = \Magento\Payment\Gateway\Helper\SubjectReader::readResponse($validationSubject);

        $isValid = true;
        $errorMessages = [];

        foreach ($responses as $response) {
            if ($response['response'] != '[refund-received]') {
                $errorMsg = __('Error with refund');
                $this->_logger->critical($errorMsg);
                $errorMessages[] = $errorMsg;
            }
        }

        return $this->createResult($isValid, $errorMessages);
    }
}