<?php

namespace Dynaccount;

require_once 'Dynaccount_account_API.php';

class Webshop_API extends Account_API {
	
	/**
	*	Get draft where to book accountings
	*/
	
	public function get_draft_id(string $draft_name): int{
		$result = $this->get('draft', 0, [
			'id'
		], [
			'name' => $draft_name
		]);
		
		if(!empty($result['error'])){
			throw new Error(implode(', ', $result['error']));
		}
		elseif(!empty($result['result'][0])){
			return $result['result'][0]['id'];
		}
		else{
			throw new Error("Booking draft '$draft_name' not found in Dynaccount");
		}
	}
	
	/**
	*	Get debtor group where the debtors (customers) are stored
	*/
	
	public function get_debtor_group(string $debtor_group_name): array{
		$result = $this->get('debtor_group', 0, [
			'payment_name',
			'vatcode_name',
			'ref_currency_name',
			'payment_method',
			'bank_name',
			'lang'
		], [
			'name' => $debtor_group_name
		]);
		
		if(!empty($result['error'])){
			throw new Error(implode(', ', $result['error']));
		}
		elseif(!empty($result['result'][0])){
			return $result['result'][0];
		}
		else{
			throw new Error("Debtor group '$debtor_group_name' not found in Dynaccount");
		}
	}
	
	/**
	*	Get the debtor (customer) or create it if it doesn't exists
	*/
	
	public function get_debtor(Array $debtor): array{
		$result = $this->get('debtor', 0, [
			'id'
		], [
			'module_id_' => $debtor['module_id_']
		]);
		
		if(!empty($result['error'])){
			throw new Error(implode(', ', $result['error']));
		}
		else{
			$result = $this->put('debtor', $result['result'][0]['id'] ?? 0, array_merge([
				'module_id_'				=> '',
				'module_group_name'			=> '',
				'vatcode_name'				=> '',
				'payment_name'				=> '',
				'discount_percent'			=> '',
				'payment_method'			=> 'BANK_ACCOUNT',
				'bank_name'					=> '',
				'name'						=> '',
				'attention_name'			=> '',
				'attention_position'		=> '',
				'attention_phone'			=> '',
				'attention_email'			=> '',
				'address'					=> '',
				'zip'						=> '',
				'city'						=> '',
				'ref_country_name'			=> '',
				'phone'						=> '',
				'email'						=> '',
				'email_invoice'				=> '',
				'email_statement'			=> '',
				'vatno'						=> '',
				'ean'						=> '',
				'ean_contact'				=> '',
				'lang'						=> 'da',
				'ref_currency_name'			=> '',
				'dlv_attention'				=> '',
				'dlv_name'					=> '',
				'dlv_address'				=> '',
				'dlv_zip'					=> '',
				'dlv_city'					=> '',
				'dlv_ref_country_name'		=> '',
				'dlv_phone'					=> '',
				'dlv_email'					=> '',
				'note'						=> '',
				'credit_max'				=> '',
				'invoicing_module_id_'		=> '',
				'invoicing_module_attention'=> ''
			], $debtor));
			
			if(!empty($result['error'])){
				throw new Error(implode(', ', $result['error']));
			}
			elseif(!empty($result['result'][0])){
				return $result['result'][0];
			}
			else{
				throw new Error("Debtor '".$debtor['module_id_']."' not found in Dynaccount");
			}
		}
	}
	
	/**
	*	Creates order
	*/
	
	public function put_debtor_order(array $order): array{
		$result = $this->put('debtor_order', 0, array_merge([
			'module_id_'			=> '',
			'dimension_name'		=> '',
			'time'					=> '',
			'currency_name'			=> '',
			'time_delivery'			=> '',
			'discount_percent'		=> '',
			'requisition'			=> '',
			'lang'					=> 'da',
			'attention'				=> '',
			'address'				=> '',
			'zip'					=> '',
			'city'					=> '',
			'ref_country_name'		=> '',
			'dlv_attention'			=> '',
			'dlv_name'				=> '',
			'dlv_address'			=> '',
			'dlv_zip'				=> '',
			'dlv_city'				=> '',
			'dlv_ref_country_name'	=> '',
			'txt'					=> ''
		], $order));
		
		if(!empty($result['error'])){
			throw new Error(implode(', ', $result['error']));
		}
		elseif(!empty($result['result'][0])){
			return $result['result'][0];
		}
		else{
			throw new Error("Debtor order was not created");
		}
	}
	
	/**
	*	Add products to order
	*/
	
	public function put_debtor_order_product(array $product): array{
		$result = $this->put('debtor_order_product', 0, array_merge([
			'order_id'			=> '',
			'product_id_'		=> '',
			'vatcode_name'		=> '',
			'product_type'		=> '',
			'qty'				=> '',
			'discount_type'		=> '',
			'discount_value'	=> '',
			'price'				=> '',
			'name'				=> '',
			'txt'				=> ''
		], $product));
		
		if(!empty($result['error'])){
			throw new Error(implode(', ', $result['error']));
		}
		elseif(!empty($result['result'][0])){
			return $result['result'][0];
		}
		else{
			throw new Error("Debtor order product was not created");
		}
	}
	
	/**
	*	Book accountings
	*/
	
	public function put_enclosure(int $draft_id, Array $enclosure, Array $accounting, Array $accounts, int $debtor_number){
		$result = $this->put('enclosure', 0, array(
			'time'				=> $enclosure['time'],
			'draft_id'			=> $draft_id,
			'dimension_name'	=> '',
			'enc_id_'			=> '',
			'txt'				=> $enclosure['txt'],
			'note'				=> ''
		));
		
		if(!empty($result['error'])){
			throw new Error(implode(', ', $result['error']));
		}
		elseif(!empty($result['result'][0])){
			$enclosure_id 		= $result['result'][0]['id'];
			
			$amount_sales		= $accounting['total_products'] * -1;
			$amount_shipping	= $accounting['total_shipping'] * -1;
			$amount_discounts	= $accounting['total_discounts'] * -1;
			$amount_vat			= $accounting['total_vat'] * -1;
			$amount_bank		= $accounting['total_paid'] * -1;
			
			try{
				$rows = [];
				
				if($amount_sales){
					$rows[] = $this->put_accounting($enclosure_id, 'LEDGER', $accounts['sales'], $amount_sales);
				}
				
				if($amount_shipping){
					$rows[] = $this->put_accounting($enclosure_id, 'LEDGER', $accounts['shipping'], $amount_shipping);
				}
				
				if($amount_discounts){
					$rows[] = $this->put_accounting($enclosure_id, 'LEDGER', $accounts['discounts'], $amount_discounts);
				}
				
				if($amount_vat){
					$rows[] = $this->put_accounting($enclosure_id, 'LEDGER', $accounts['vat'], $amount_vat);
				}
				
				if($amount_bank){
					$rows[] = $this->put_accounting($enclosure_id, 'DEBTOR_INVOICE', $debtor_number, $amount_bank);
				}
				
				$result = $this->insert_bulk('accounting', $rows);
				
				if(!empty($result['error'])){
					$errors = [];
					foreach($result['error'] as $k => $v){
						$errors[] = "Line $k: ".implode(', ', $v);
					}
					
					throw new Error(implode(', ', $errors));
				}
				elseif(!empty($result['result'][0])){
					return $result['result'][0];
				}
			}
			catch(Error $e){
				$this->delete('enclosure', $enclosure_id);
				
				throw new Error($e->getMessage());
			}
		}
	}
	
	private function put_accounting(int $enclosure_id, string $type, int $account_id_, float $amount){
		$accountoff_id_ = '';
		if($type != 'LEDGER'){
			$accountoff_id_ = $account_id_;
			$account_id_ = '';
		}
		
		return [
			'enclosure_id'		=> $enclosure_id,
			'type'				=> $type,
			'account_id_'		=> $account_id_,
			'accountoff_id_'	=> $accountoff_id_,
			'vatcode_name'		=> '',
			'dimension_name'	=> '',
			'dimensionoff_name'	=> '',
			'invoice_id_'		=> '',
			'invoice_time_due'	=> '',
			'fi_payment_id_'	=> '',
			'currency_name'		=> '',
			'amount'			=> $amount,
			'currency_rate'		=> ''
		];
	}
}