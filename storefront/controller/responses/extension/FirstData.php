<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2013 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  Lincence details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/
if ( !defined ( 'DIR_CORE' )) {
	header ( 'Location: static_pages/' );
}

class ControllerResponsesExtensionFirstData extends AController {
	public $data = array();
	public function main() {
    	$this->data['button_confirm'] = $this->language->get('button_confirm');
		$this->data['button_back'] = $this->language->get('button_back');

		if (!$this->config->get('FirstData_test')) {
    		$this->data['action'] = 'https://www.ipg-online.com/connect/gateway/processing';
  		} else {
			$this->data['action'] = 'https://test.ipg-online.com/connect/gateway/processing';
		}		

//deal with order amounts
		$display_totals = $this->cart->buildTotalDisplay();
		//$this->data['totals'] = $display_totals['total_data'];
		$totals =  $display_totals['total_data'];
		$this->data['display_totals'] =  $display_totals['total'];
		//$order_total = str_replace("£", "", $totals[3]['text']);
		$order_total = round($display_totals['total'],2);

		if (!$this->config->get('FirstData_test')) {
			$this->data['order_total'] = round($order_total,2);
  		} else {
			$this->data['order_total'] = round($order_total);
		}


		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		
		$this->data['business'] = $this->config->get('FirstData_email');
		$this->data['item_name'] = html_entity_decode($this->config->get('store_name'), ENT_QUOTES, 'UTF-8');				
		$this->data['currency_code'] = $order_info['currency'];
		$this->data['amount'] = $this->currency->format($order_info['total'], $order_info['currency'], $order_info['value'], FALSE);
		$this->data['first_name'] = html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');	
		$this->data['last_name'] = html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');	
		$this->data['address1'] = html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8');	
		$this->data['address2'] = html_entity_decode($order_info['payment_address_2'], ENT_QUOTES, 'UTF-8');	
		$this->data['city'] = html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8');	
		$this->data['zip'] = html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');	
		$this->data['country'] = $order_info['payment_iso_code_2'];
		$this->data['notify_url'] = $this->html->getURL('extension/FirstData/callback');
		$this->data['email'] = $order_info['email'];
		$this->data['oid'] = $this->session->data['order_id'];
		//$this->data['invoice'] = $this->session->data['order_id'] . ' - ' . html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8') . ' ' . html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
		$this->data['lc'] = $this->session->data['language'];

		if ( has_value($this->config->get('FirstData_logoimg')) ) {

			if ( strpos($this->config->get('FirstData_logoimg'), 'http://') || strpos($this->config->get('FirstData_logoimg'), 'https://') ) {
				$this->data['logoimg'] = $this->config->get('FirstData_logoimg');
			} else {
				$this->data['logoimg'] = HTTPS_SERVER . $this->config->get('FirstData_logoimg');
			}
		}

		if ( has_value($this->config->get('FirstData_cartbordercolor')) ) {
			$this->data['cartbordercolor'] = $this->config->get('FirstData_cartbordercolor');
		}

		$this->load->library('encryption');
		$encryption = new AEncryption($this->config->get('encryption_key'));

		$this->data['products'] = array();
		$products = $this->cart->getProducts();
		foreach ($products as $product) {
			$option_data = array();

			foreach ($product['option'] as $option) {
				if ($option['type'] != 'file') {
					$value = $option['value'];
				} else {
					$filename = $encryption->decrypt($option['value']);
					$value = mb_substr($filename, 0, mb_strrpos($filename, '.'));
				}

				$option_data[] = array(
					'name'  => $option['name'],
					'value' => (mb_strlen($value) > 20 ? mb_substr($value, 0, 20) . '..' : $value)
				);
			}

			$this->data['products'][] = array(
				'name'     => $product['name'],
				'model'    => $product['model'],
				'price'    => $this->currency->format($product['price'], false, false),
				'quantity' => $product['quantity'],
				'option'   => $option_data,
				'weight'   => $product['weight']
			);
		}


		$this->data['discount_amount_cart'] = 0;
		$totals = $this->cart->buildTotalDisplay();

		foreach($totals['total_data'] as $total){
			if(in_array($total['id'],array('subtotal','total'))){ continue;}
			if(in_array($total['id'],array('promotion','coupon'))){
			 	$total['value'] = $total['value']<0 ? $total['value']*-1 : $total['value'];
				$this->data['discount_amount_cart'] += $total['value'];
			}else{
			$this->data['products'][] = array(
							'name'     => $total['title'],
							'model'    => '',
							'price'    => $this->currency->format($total['value'], false, false),
							'quantity' => 1,
							'option'   => array(),
							'weight'   => 0
						);
			}
		}


		if (!$this->config->get('FirstData_transaction')) {
			$this->data['paymentaction'] = 'authorization';
		} else {
			$this->data['paymentaction'] = 'sale';
		}
		
		$this->data['return'] = $this->html->getSecureURL('checkout/success');
		
		if ($this->request->get['rt'] != 'checkout/guest_step_3') {
			$this->data['cancel_return'] = $this->html->getSecureURL('checkout/payment');
		} else {
			$this->data['cancel_return'] = $this->html->getSecureURL('checkout/guest_step_2');
		}

		
		$this->data['custom'] = $encryption->encrypt($this->session->data['order_id']);
		
		if ($this->request->get['rt'] != 'checkout/guest_step_3') {
			$this->data['back'] = $this->html->getSecureURL('checkout/payment');
		} else {
			$this->data['back'] = $this->html->getSecureURL('checkout/guest_step_2');
		}
		
		$this->view->batchAssign( $this->data ); 
		$this->processTemplate('responses/FirstData.tpl');
	}
	
	public function callback() {
		if (isset($this->request->post['oid'])) {
			$order_id = $this->request->post['oid'];
		} else {
			$order_id = 0;
		}

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id);
		$suspect = false;
		$message = '';
		if ($order_info) {

			if($this->request->post['status'] == "APPROVED"){
				$this->model_checkout_order->confirm($order_id, $this->config->get('FirstData_order_status_id'));
				echo "DONE!!";
			}
		}

	}
}
