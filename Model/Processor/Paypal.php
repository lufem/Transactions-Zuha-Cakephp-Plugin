<?php
/**
 * Paypal Direct Payment API
 * 
 * @todo update support for ARB
 * @todo update support for Chained Payments
 */
App::import('Vendor', 'paypal', array('file'=>'paypal/paypal.php'));
class Paypal extends AppModel {
	
	public $useTable = false;

	public $paysettings = array();
	public $response = array();
	public $payInfo = array();
	public $recurring = false;
	
	public function __construct($id = false, $table = null, $ds = null) {
    	parent::__construct($id, $table, $ds);
		if (defined('__TRANSACTIONS_PAYPAL')) {
            $this->paysettings = unserialize(__TRANSACTIONS_PAYPAL);
		} else {
			throw new Exception('Payment configuration NOT setup, contact admin with error code : 947941');
		}
	}
	
	public function redirectUrl($data) {
		$redirectUrl = $this->paysettings['PAYPAL_URL'] . $data[''];
		return $redirectUrl;
	}
	
	
	public function pay($paymentInfo, $function = "DoDirectPayment") {
		$paypal = new PaypalApi();
		$this->payInfo = $paymentInfo ;
		$paypal->setPaySettings($this->paysettings);

		
		if ($paymentInfo['Transaction']['mode'] === 'PAYPAL.ACCOUNT') {
			// send parameters to PayPal
			$paymentInfo['Transaction']['returnUrl'] = 'http://ttysoon.localhost/transactions/transactions/success';
			$paymentInfo['Transaction']['cancelUrl'] = 'http://ttysoon.localhost/transactions/transactions/cart';
			$res = $paypal->SetExpressCheckout($paymentInfo);
			
			debug($res);
			
			if ($this->_responseIsGood($res)) {
				// At this point, we need to redirect to paypal to get authorization.
				// need to... do something here..
				// maybe just change the order status to pending or something..
				header('Location: ' . $this->paysettings['PAYPAL_URL'] . $res['TOKEN']);
				exit();
			} else {
				throw new Exception('<b>PayPal Error: </b> ' . $res['L_LONGMESSAGE0'], 1);
			}
		}
		elseif ($paymentInfo['Transaction']['mode'] === 'PAYPAL.CC') {
			$res = $paypal->DoDirectPayment($paymentInfo);
			debug($res);
			if ($this->_responseIsGood($res)) {
				// do stuff with $res
			} else {
				throw new Exception("Error Processing Request", 1);
			}
		}
		
debug($paymentInfo);
break;

		// if ($this->recurring && !empty($paymentInfo['Billing']['arb_profile_id'])) {
			// // if existing profile recurring id for arb, update the subscription 
			// $res = $paypal->UpdateRecurringPaymentsProfile($paymentInfo);
// 			
		// } elseif ($this->recurring) {
			// // create a new subscription of recurring type
			// $res = $paypal->CreateRecurringPaymentsProfile($paymentInfo);
// 			
		// } elseif ($function === "DoDirectPayment") {
			// $res = $paypal->DoDirectPayment($paymentInfo);
// 			
		// } elseif ($function === "SetExpressCheckout") {
			// $res = $paypal->SetExpressCheckout($paymentInfo);
// 			
		// } elseif ($function === "GetExpressCheckoutDetails") {
			// $res = $paypal->GetExpressCheckoutDetails($paymentInfo);
// 			
		// } elseif ($function === "DoExpressCheckoutPayment") {
			// $res = $paypal->DoExpressCheckoutPayment($paymentInfo);
// 			
		// } else {
			// $res = "Function Does Not Exist!";
		// }

		$this->_parsePaypalResponse($res);
	}


	/**
	 * Quick check for errors sent back from PayPal
	 *
	 * @todo Ideally, we should be writing out these errors to a log file.
	 * 
	 * @param array $response Associative array of info that came back from PayPal
	 * @return boolean
	 */
	protected function _responseIsGood($response) {
		if ($nvpReqArray['ACK'] === 'Failure' || $nvpReqArray['ACK'] === 'FailureWithWarning') {
			return false;
		} else {
			return true;
		}
	}

	/*
	* @params $data and $amount
	* it returns the response text if its successful
	*/
	public function chainedPayment($data, $amount ) {
		if (defined('__TRANSACTIONS_CHAINED_PAYMENT')) {
            	App::import('Component', 'Transactions.Chained');
	        	$component = new ChainedComponent();
	        	if (method_exists($component, 'initialize')) {
	            	$component->initialize($this->Controller);
		        }
		        if (method_exists($component, 'startup')) {
		            $component->startup($this->Controller);
		        }
            	$component->chainedSettings($data['Billing']);
    			$component->Pay($amount);
				if ($component->response['response_code'] == 1) {
					return " Payment has been transfered to its vendors" ;
				}
		}
	}


	public function recurring($val = false) {
		$this->recurring = $val;
	}

	/* 
	 * @params 
	 * $profileId: profile id of buyer
	 * $action: to suspend , cancel, reactivate the reccuring profile
	 */
	public function ManageRecurringPaymentsProfileStatus($profileId, $action) {
		$paypal = new Paypal();
		$paypal->setPaySettings($this->paysettings);
		$res = $paypal->ManageRecurringPaymentsProfileStatus($profileId, $action);
		
		$this->_parsePaypalResponse($res);
	}


/**
 * Parse the response from Paypal into a more readable array
 * makes doing validation changes easier.
 *
 */
	protected function _parsePaypalResponse($parsedResponse = null) {
		if ($parsedResponse) {
			$parsedResponse['reason_code'] = $parsedResponse['ACK'];
			switch ($parsedResponse['ACK']) {
				case 'Success' :
					$parsedResponse['reason_text'] = 'Successful Payment';
					if (defined('__TRANSACTIONS_CHAINED_PAYMENT')) {
						$parsedResponse['reason_text'] .= $this->chainedPayment($this->payInfo, $parsedResponse['AMT']) ;
					}
					$parsedResponse['response_code'] = 1;
					$parsedResponse['description'] = 'Transaction Completed';
					break;
				case 'SuccessWithWarning' :
					$parsedResponse['response_code'] = 1;
					$parsedResponse['reason_text'] = $parsedResponse['L_SHORTMESSAGE0'];
					$parsedResponse['description'] = $parsedResponse['L_LONGMESSAGE0'];
					break;
				case 'FailureWithWarning' :
				case 'Failure' :
					$parsedResponse['response_code'] = 3; // similar to authorize
					$parsedResponse['reason_text'] = $parsedResponse['L_SHORTMESSAGE0'];
					$parsedResponse['description'] = $parsedResponse['L_LONGMESSAGE0'];
					break;
			}
			if (isset($parsedResponse['AMT'])) {
				$parsedResponse['amount'] = $parsedResponse['AMT'];
			}
			if (isset($parsedResponse['TRANSACTIONID'])) {
				$parsedResponse['transaction_id'] = $parsedResponse['TRANSACTIONID'];
			}

			// if PROFILEID is set then it is recurring payment and it will get profile info
			if (isset($parsedResponse['PROFILEID'])) {
				$paypal = new Paypal();
				$paypal->setPaySettings($this->paysettings);

				$res = $paypal->GetRecurringPaymentsProfileDetails($parsedResponse['PROFILEID']);

				// recurrence type missing
				$parsedResponse['transaction_id'] = $res['PROFILEID'];
				$parsedResponse['description'] = $res['DESC'];
				$parsedResponse['is_arb'] = 1;
				$parsedResponse['arb_payment_start_date'] = $res['PROFILESTARTDATE'];
				$parsedResponse['arb_payment_end_date'] = $res['FINALPAYMENTDUEDATE'];
				$parsedResponse['amount'] = $res['AMT'];
				$parsedResponse['meta'] = "CORRELATIONID:{$res['CORRELATIONID']}, BUILD:{$res['BUILD']}, STATUS:{$res['STATUS']}".
					"BILLINGPERIOD:{$res['BILLINGPERIOD']}, BILLINGFREQUENCY:{$res['BILLINGFREQUENCY']}, TOTALBILLINGCYCLES:{$res['TOTALBILLINGCYCLES']}";
			}

			if (isset($parsedResponse['CVV2MATCH']) && isset($parsedResponse['CORRELATIONID'])) {
				$parsedResponse['meta'] = "CORRELATIONID:{$parsedResponse['CORRELATIONID']}, CVV2MATCH:{$parsedResponse['CVV2MATCH']}";
			}
		}
		$this->response = $parsedResponse;
	}

}
