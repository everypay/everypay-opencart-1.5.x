<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

class ControllerPaymentEverypay extends Controller
{
    public function index()
    {
        $this->language->load('payment/everypay');
        $data['button_confirm'] = $this->language->get('button_confirm');

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $data['public_key'] = $this->config->get('everypay_public_key');
        $data['currency_code'] = $order_info['currency_code'];
        $data['total'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) * 100;
        $data['merchant_order_id'] = $this->session->data['order_id'];
        $data['card_holder_name'] = $order_info['payment_firstname'].' '.$order_info['payment_lastname'];
        $data['email'] = $order_info['email'];
        $data['phone'] = $order_info['telephone'];
        $data['name'] = $this->config->get('config_name');
        $data['lang'] = $this->session->data['language'];
        $data['sandbox'] = $this->config->get('everypay_sandbox');
        $data['sandbox_warning'] = $this->language->get('text_sandbox_warning');
        $data['return_url'] = $this->url->link('payment/everypay/callback', '', 'SSL');
        $data['installments'] = $this->getInstallments($order_info['total']);

        if (file_exists(DIR_TEMPLATE.$this->config->get('config_template').'/template/payment/everypay.tpl')) {
            $this->template = $this->config->get('config_template').'/template/payment/everypay.tpl';
        } else {
            $this->template = 'default/template/payment/everypay.tpl';
        }

        $this->data = $data;

        $this->render();
    }


    public function callback()
    {
        $this->language->load('payment/everypay');
        $this->data['title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));
        $this->data['language'] = $this->language->get('code');
        $this->data['direction'] = $this->language->get('direction');
        $this->data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));
        $this->data['text_response'] = $this->language->get('text_response');
        $this->data['text_success'] = $this->language->get('text_success');
        $this->data['text_success_wait'] = sprintf($this->language->get('text_success_wait'), $this->url->link('checkout/success'));
        $this->data['text_failure'] = $this->language->get('text_failure');
        $this->data['text_failure_wait'] = sprintf($this->language->get('text_failure_wait'), $this->url->link('checkout/checkout', '', 'SSL'));

        $this->load->model('checkout/order');
        if (isset($this->request->request['everypayToken']) && isset($this->request->request['merchant_order_id'])) {
            $everypayToken = $this->request->request['everypayToken'];
            $merchant_order_id = $this->request->request['merchant_order_id'];

            $order_info = $this->model_checkout_order->getOrder($merchant_order_id);
            $amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) * 100;

            $success = false;
            $error = '';

            try {
                $phone = str_replace(['+', '-', ' '], null, $order_info['telephone']);
                $ch = $this->getCurlHandle($everypayToken, $amount, $order_info['email'], $phone);

                //execute post
                $result = curl_exec($ch);
                $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($result === false) {
                    $success = false;
                    $error = 'Curl error: '.curl_error($ch);
                } else {
                    $response_array = json_decode($result, true);
                    //Check success response
                    if ($http_status === 200 and isset($response_array['error']) === false) {
                        $success = true;
                    } else {
                        $success = false;

                        if (!empty($response_array['error']['code'])) {
                            $error = $response_array['error']['code'].':'.$response_array['error']['message'];
                        } else {
                            $error = 'EVERYPAY_ERROR:Invalid Response <br/>'.$result;
                        }
                    }
                }

                //close connection
                curl_close($ch);
            } catch (Exception $e) {
                $success = false;
                $error = 'OPENCART_ERROR:Request to EveryPay Failed';
            }

            if ($success === true) {
                $this->model_checkout_order->confirm($this->request->post['merchant_order_id'], $this->config->get('config_order_status_id'));

                $message = 'Everypay transaction id: ' . $response_array['token'];
                $this->model_checkout_order->update($this->request->post['merchant_order_id'], $this->config->get('config_order_status_id'), $message, true);

                $this->data['continue'] = $this->url->link('checkout/success');

                $this->renderSuccess();
            } else {
                $this->data['continue'] = $this->url->link('checkout/failure');
                $this->renderFailure();
            }
        } else {
            $this->data['continue'] = $this->url->link('checkout/cart');

            $this->renderFailure();
        }

        $this->response->setOutput($this->render());
    }

    private function getCurlHandle($token, $amount, $email, $phone)
    {
        $sandbox = $this->config->get('everypay_sandbox');
        $url = 1 == $sandbox
            ? 'https://sandbox-api.everypay.gr/payments'
            : 'https://api.everypay.gr/payments';
        $secret_key = $this->config->get('everypay_secret_key');
        $data = array(
            'amount' => $amount,
            'token' => $token,
            'payee_email' => $email,
            'payee_phone' => $phone,
        );
        if (false !== $max = $this->getInstallments($amount)) {
            $data['max_installments'] = $max;
        }
        $fields_string =http_build_query($data);

        //cURL Request
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, $secret_key.':');
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        return $ch;
    }

    private function getInstallments($total)
    {
        $total = round($total / 100, 2);
        $inst = htmlspecialchars_decode($this->config->get('everypay_installments'));
        if ($inst) {
            $installments = json_decode($inst, true);
            foreach ($installments as $i) {
                if ($total >= $i['from'] && $total <= $i['to']) {
                    return $i['max'];
                }
            }
        }

        return false;
    }

    private function renderSuccess()
    {
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/everypay_success.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/everypay_success.tpl';
        } else {
            $this->template = 'default/template/payment/everypay_success.tpl';
        }
    }

    private function renderFailure()
    {
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/everypay_failure.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/everypay_failure.tpl';
        } else {
            $this->template = 'default/template/payment/everypay_failure.tpl';
        }
    }
}
