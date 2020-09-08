<?php

class Confirm extends \Magento\Newsletter\Controller\Subscriber\Confirm
{
    public function execute() {
        $id = (int)$this->getRequest()->getParam('id');

        $this->autoGenerateCouponCode($id);

        return parent::execute();
    }

    public function autoGenerateCouponCode($id) {
        $couponCode = $this->randomCouponString(7);
        $coupon = [
            'name' => 'Discount-For-' . $id,
            'desc' => 'Discount coupon code for user ' . $id,
            'start' => date('Y-m-d'),
            'max_redemptions' => 1,
            'discount_type' => 'cart_fixed',
            'discount_amount' => 5,
            'code' => $couponCode,

        ];

        // VERY bad practice. I'd normally do this via DI but didn't want to extend the __construct currently and
        // this gets the idea across.
        $object = \Magento\Framework\App\ObjectManager::getInstance();

        $cartRule = $object->create('Magento\SalesRule\Model\Rule'); // Would always use __construct DI here but used this for simplicity
        $cartRule->setName($coupon['name'])
            ->setDescription($coupon['desc'])
            ->setFromDate($coupon['start'])
            ->setToDate($coupon['end'])
            ->setUsesPerCustomer($coupon['max_redemptions'])
            ->setCustomerGroupIds(array('1','2','3')) //select customer group
            ->setIsActive(1)
            ->setSimpleAction($coupon['discount_type'])
            ->setDiscountAmount($coupon['discount_amount'])
            ->setDiscountQty(1)
            ->setApplyToShipping($coupon['flag_is_free_shipping'])
            ->setTimesUsed($coupon['redemptions'])
            ->setWebsiteIds(array('1'))
            ->setCouponType(2)
            ->setCouponCode($coupon['code'])
            ->setUsesPerCoupon(1)
            ->save();

        $couponCode = $this->autoGenerateCouponCode();

        // Sets it to the session
        // Again - would normally set this in the __construct but was pressed for time
        $object->create('Magento\Checkout\Model\Session')->getQuote()->setCouponCode($couponCode)
            ->collectTotals()
            ->save();
    }

    public function randomCouponString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }

}