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

namespace Adyen\Payment\Model\Method;

/**
 * Class Sepa
 * @package Adyen\Payment\Model\Method
 */
class Sepa extends \Magento\Payment\Model\Method\AbstractMethod
{

    const METHOD_CODE = 'adyen_sepa';

    /**
     * @var string
     */
    protected $_code = self::METHOD_CODE;

    /**
     * @var \Adyen\Payment\Model\Api\PaymentRequest
     */
    protected $_paymentRequest;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Request object
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canCaptureOnce = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_isGateway = true;
    protected $_canUseInternal = true;


    /**
     * Sepa constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Adyen\Payment\Model\Api\PaymentRequest $paymentRequest
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Adyen\Payment\Model\Api\PaymentRequest $paymentRequest,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_paymentRequest = $paymentRequest;
        $this->_urlBuilder = $urlBuilder;
        $this->_request = $request;
    }



    /**
     * Assign data to info model instance
     *
     * @param \Magento\Framework\DataObject|mixed $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);

        // set data to additionalInformation
        $infoInstance = $this->getInfoInstance();
        $infoInstance->setAdditionalInformation('account_name', $data['account_name']);
        $infoInstance->setAdditionalInformation('iban', $data['iban']);
        $infoInstance->setAdditionalInformation('country', $data['country']);
        $infoInstance->setAdditionalInformation('accept_sepa', $data['accept_sepa']);
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)authorize
     */
    public function validate()
    {
        $infoInstance = $this->getInfoInstance();
        $iban = $infoInstance->getAdditionalInformation('iban');
        if(empty($iban) || !$this->validateIban($iban)){
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid Iban number.'));
        }
        return $this;
    }

    public function validateIban($iban) {

        $iban = strtolower(str_replace(' ','',$iban));
        $Countries = ['al'=>28,'ad'=>24,'at'=>20,'az'=>28,'bh'=>22,'be'=>16,'ba'=>20,'br'=>29,'bg'=>22,'cr'=>21,'hr'=>21,'cy'=>28,'cz'=>24,'dk'=>18,'do'=>28,'ee'=>20,'fo'=>18,'fi'=>18,'fr'=>27,'ge'=>22,'de'=>22,'gi'=>23,'gr'=>27,'gl'=>18,'gt'=>28,'hu'=>28,'is'=>26,'ie'=>22,'il'=>23,'it'=>27,'jo'=>30,'kz'=>20,'kw'=>30,'lv'=>21,'lb'=>28,'li'=>21,'lt'=>20,'lu'=>20,'mk'=>19,'mt'=>31,'mr'=>27,'mu'=>30,'mc'=>27,'md'=>24,'me'=>22,'nl'=>18,'no'=>15,'pk'=>24,'ps'=>29,'pl'=>28,'pt'=>25,'qa'=>29,'ro'=>24,'sm'=>27,'sa'=>24,'rs'=>22,'sk'=>24,'si'=>19,'es'=>24,'se'=>24,'ch'=>21,'tn'=>24,'tr'=>26,'ae'=>23,'gb'=>22,'vg'=>24];
        $Chars = ['a'=>10,'b'=>11,'c'=>12,'d'=>13,'e'=>14,'f'=>15,'g'=>16,'h'=>17,'i'=>18,'j'=>19,'k'=>20,'l'=>21,'m'=>22,'n'=>23,'o'=>24,'p'=>25,'q'=>26,'r'=>27,'s'=>28,'t'=>29,'u'=>30,'v'=>31,'w'=>32,'x'=>33,'y'=>34,'z'=>35];

        if(isset($Countries[substr($iban,0,2)]) && strlen($iban) == $Countries[substr($iban,0,2)]){

            $MovedChar = substr($iban, 4).substr($iban,0,4);
            $MovedCharArray = str_split($MovedChar);
            $NewString = "";

            foreach($MovedCharArray AS $key => $value){
                if(!is_numeric($MovedCharArray[$key])){
                    $MovedCharArray[$key] = $Chars[$MovedCharArray[$key]];
                }
                $NewString .= $MovedCharArray[$key];
            }

            if(bcmod($NewString, '97') == 1)
            {
                return true;
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }

    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$this->canAuthorize()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The authorize action is not available.'));
        }

        /*
         * do not send order confirmation mail after order creation wait for
         * Adyen AUTHORIISATION notification
         */
        $order = $payment->getOrder();
        $order->setCanSendNewEmailFlag(false);

        // do not let magento set status to processing
        $payment->setLastTransId($this->getTransactionId())->setIsTransactionPending(true);

        // DO authorisation
        $this->_processRequest($payment, $amount, "authorise");

        return $this;
    }

    protected function _processRequest(\Magento\Sales\Model\Order\Payment $payment, $amount, $request)
    {
        switch ($request) {
            case "authorise":
                $response = $this->_paymentRequest->fullApiRequest($payment, $this->_code);
                break;
        }

        if (!empty($response)) {
            $this->_processResponse($payment, $response);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Empty result.'));
        }
    }

    protected function _processResponse(\Magento\Payment\Model\InfoInterface $payment, $response)
    {
        $payment->setAdditionalInformation('3dActive', false);

        switch ($response['resultCode']) {
            case "Authorised":
                //$this->_addStatusHistory($payment, $responseCode, $pspReference, $this->_getConfigData('order_status'));
                $this->_addStatusHistory($payment, $response['resultCode'], $response['pspReference']);
                $payment->setAdditionalInformation('pspReference', $response['pspReference']);
                break;
            case "Refused":
                $errorMsg = __('The payment is REFUSED.');
                $this->_logger->critical($errorMsg);
                throw new \Magento\Framework\Exception\LocalizedException(__($errorMsg));
                break;
        }
    }

    protected function _addStatusHistory($payment, $responseCode, $pspReference)
    {
        $type = 'Adyen Result URL response:';
        $comment = __('%1 <br /> authResult: %2 <br /> pspReference: %3 <br /> paymentMethod: %4', $type, $responseCode, $pspReference, "");
        $payment->getOrder()->setAdyenResulturlEventCode($responseCode);
        $payment->getOrder()->addStatusHistoryComment($comment);
        return $this;
    }


    /**
     * Checkout redirect URL getter for onepage checkout (hardcode)
     *
     * @see \Magento\Checkout\Controller\Onepage::savePaymentAction()
     * @see \Magento\Quote\Model\Quote\Payment::getCheckoutRedirectUrl()
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        return $this->_urlBuilder->getUrl('checkout/onepage/success/',['_secure' => $this->_getRequest()->isSecure()]);
    }

    /**
     * Retrieve request object
     *
     * @return \Magento\Framework\App\RequestInterface
     */
    protected function _getRequest()
    {
        return $this->_request;
    }


}