<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

class ModelPaymentEverypay extends Model
{
    public function getMethod($address, $total)
    {
        $this->language->load('payment/everypay');

        if(DEVELOPER_DEBUG){
            return array(
                'code' => 'everypay',
                'title' => $this->language->get('text_title'),
                'terms' => '',
                'sort_order' => $this->config->get('everypay_sort_order'),
            );
        }else{
            return array();
        }
    }
}
