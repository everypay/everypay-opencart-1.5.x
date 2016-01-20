<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

class ModelPaymentEverypay extends Model
{
    public function getMethod($address, $total)
    {
        $this->language->load('payment/everypay');

        return array(
            'code' => 'everypay',
            'title' => $this->language->get('text_title'),
            'terms' => '',
            'sort_order' => $this->config->get('everypay_sort_order'),
        );
    }
}
