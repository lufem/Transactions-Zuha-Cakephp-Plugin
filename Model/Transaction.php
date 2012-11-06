<?php
App::uses('TransactionsAppModel', 'Transactions.Model');
/**
 * Transaction Model
 *
 * @property TransactionShipment $TransactionShipment
 * @property TransactionPayment $TransactionPayment
 * @property TransactionShipment $TransactionShipment
 * @property TransactionCoupon $TransactionCoupon
 * @property Customer $Customer
 * @property Contact $Contact
 * @property Assignee $Assignee
 * @property TransactionItem $TransactionItem
 * @property TransactionPayment $TransactionPayment
 */
class Transaction extends TransactionsAppModel {
 public $name = 'Transaction';
	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasOne associations
 *
 * @var array
 */
//	public $hasOne = array(
//		'TransactionShipment' => array(
//			'className' => 'Transactions.TransactionShipment',
//			'foreignKey' => 'transaction_id',
//			'conditions' => '',
//			'fields' => '',
//			'order' => ''
//		)
//	);

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
//		'TransactionPayment' => array(
//			'className' => 'Transactions.TransactionPayment',
//			'foreignKey' => 'transaction_payment_id',
//			'conditions' => '',
//			'fields' => '',
//			'order' => ''
//		),
//		'TransactionShipment' => array(
//			'className' => 'Transactions.TransactionShipment',
//			'foreignKey' => 'transaction_shipment_id',
//			'conditions' => '',
//			'fields' => '',
//			'order' => ''
//		),
//		'TransactionCoupon' => array(
//			'className' => 'Transactions.TransactionCoupon',
//			'foreignKey' => 'transaction_coupon_id',
//			'conditions' => '',
//			'fields' => '',
//			'order' => ''
//		),
		'Customer' => array(
			'className' => 'Users.User',
			'foreignKey' => 'customer_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Contact' => array(
			'className' => 'Contacts.Contact',
			'foreignKey' => 'contact_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Assignee' => array(
			'className' => 'Users.User',
			'foreignKey' => 'assignee_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'TransactionItem' => array(
			'className' => 'Transactions.TransactionItem',
			'foreignKey' => 'transaction_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'TransactionPayment' => array(
			'className' => 'Transactions.TransactionPayment',
			'foreignKey' => 'transaction_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'TransactionShipment' => array(
			'className' => 'Transactions.TransactionShipment',
			'foreignKey' => 'transaction_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	
	/**
	 * The checkout page has options.
	 * This function's job is to get those options.
	 * @return array
	 */
	public function gatherCheckoutOptions() {
	    $options['ssl'] = defined('__TRANSACTIONS_SSL') ? unserialize(__TRANSACTIONS_SSL) : null;
	    $options['trustLogos'] = !empty($ssl['trustLogos']) ? $ssl['trustLogos'] : null;
	    $options['enableShipping'] = defined('__TRANSACTIONS_ENABLE_SHIPPING') ? __TRANSACTIONS_ENABLE_SHIPPING : false;
	    $options['fedexSettings'] = defined('__TRANSACTIONS_FEDEX') ? unserialize(__TRANSACTIONS_FEDEX) : null;
	    $options['paymentMode'] = defined('__TRANSACTIONS_DEFAULT_PAYMENT') ? __TRANSACTIONS_DEFAULT_PAYMENT : null;
	    $options['paymentOptions'] = defined('__TRANSACTIONS_ENABLE_PAYMENT_OPTIONS') ? unserialize(__TRANSACTIONS_ENABLE_PAYMENT_OPTIONS) : null;

	    if (defined('__TRANSACTIONS_ENABLE_SINGLE_PAYMENT_TYPE')) {
		  $options['singlePaymentKeys'] = $this->Session->read('OrderPaymentType');
		  if (!empty($options['singlePaymentKeys'])) {
			  $options['singlePaymentKeys'] = array_flip($options['singlePaymentKeys']);
			  $options['paymentOptions'] = array_intersect_key($options['paymentOptions'], $options['singlePaymentKeys']);
		  }
		}

	    $options['defaultShippingCharge'] = defined('__TRANSACTIONS_FLAT_SHIPPING_RATE') ? __TRANSACTIONS_FLAT_SHIPPING_RATE : 0;
	    
	    return $options;
	}
	
	
	/**
	 * This function returns the UUID to use for a User by first checking the Auth Session, then by checking for a Transaction Guest session,
	 * and finally, creating a Transaction Guest session if necessary.
	 *  
	 * @todo This should probably be in the User model in some fashion..
	 * 
	 * @return string The UUID of the User
	 */
	public function getCustomersId() {
	  $authUserId = CakeSession::read('Auth.User.id');
	  $transactionGuestId = CakeSession::read('Transaction._guestId');
	  if ($authUserId) {
		$userId = $authUserId;
	  } elseif($transactionGuestId) {
		$userId = $transactionGuestId;
	  } else {
		$userId = String::uuid();
		CakeSession::write('Transaction._guestId', $userId);
	  }

	  // Assign their Guest Cart to their Logged in self, if neccessary
	  $this->reassignGuestCart($transactionGuestId, $authUserId);
	  
	  return $userId;
	}

	
//	/** MOVED TO TransactionsAppModel
//	
//	 * This function is meant to transfer a cart when a guest shopper logs in.
//	 * After doing so, it deletes their Transaction._guestId session.
//	 * 
//	 * @param mixed $fromId
//	 * @param mixed $toId
//	 * @return boolean
//	 * @throws Exception 
//	 */
//	public function reassignGuestCart($fromId, $toId) {
//	  if($fromId && $toId) {
//		if ($this->updateAll(array('customer_id' => $toId), array('customer_id' => $fromId))) {
//		  return true;
//		} else {
//		  throw new Exception(__d('transactions', 'Guest cart merge failed'));
//		}
//	  }
//	}
	

/**
 * We could do all sorts of processing in here
 * @param string $userId
 * @return boolean|array
 */
	public function processCart($userId) {
	    
	    $theCart = $this->find('first', array(
		  'conditions' => array('customer_id' => $userId),
		  'contain' => array(
			  'TransactionItem',
			  'TransactionShipment',  // saved shipping addresses
			  'TransactionPayment',   // saved billing addresses
			  'Customer'			  // customer's user data
			  )
		));
	    
	    if(!$theCart) {
		  return FALSE;
	    }
	    
	    // figure out the subTotal
	    $subTotal = 0;
	    foreach($theCart['TransactionItem'] as $txnItem) {
		  $subTotal += $txnItem['price'] * $txnItem['quantity'];
	    }
	    
	    $theCart['Transaction']['order_charge'] = $subTotal;
	    
	    return $theCart;
	}
	
	
	/**
	 * Combine the pre-checkout and post-checkout Transactions.
	 * 
	 * @todo Handle being passed empty carts
	 * @param integer $userId
	 * @param array $data
	 * @return type
	 */
	public function finalizeTransactionData($submittedTransaction) {
		$userId = $this->getCustomersId();
		// get their current transaction (pre checkout page)
		$currentTransaction = $this->find('first', array(
		    'conditions' => array('customer_id' => $userId),
		    'contain' => array(
			  'TransactionItem',
			  'TransactionShipment',  // saved shipping addresses
			  'TransactionPayment',	  // saved billing addresses
			  'Customer'			  // customer's user data
			  )
		));

		if(!$currentTransaction) {
			throw new Exception('Transaction missing.');
		}
		
	    // update quantities
		foreach($submittedTransaction['TransactionItem'] as $submittedTxnItem) {
		    if($submittedTxnItem['quantity'] > 0) {
			  foreach($currentTransaction['TransactionItem'] as $currentTxnItem) {
				  if($currentTxnItem['id'] == $submittedTxnItem['id']) {
					$currentTxnItem['quantity'] = $submittedTxnItem['quantity'];
					$finalTxnItems[] = $currentTxnItem;
				  }
			  }
		    }
		}
			
		// unset the submitted TransactionItem's. They will be replaced after the merge.
		unset($submittedTransaction['TransactionItem']);
		
		// combine the Current and Submitted Transactions
		$officialTransaction = Set::merge($currentTransaction, $submittedTransaction);
		$officialTransaction['TransactionItem'] = $finalTxnItems;
		
		// figure out the subTotal
		$officialTransaction['Transaction']['order_charge'] = 0;
		foreach($officialTransaction['TransactionItem'] as $txnItem) {
		    $officialTransaction['Transaction']['order_charge'] += $txnItem['price'] * $txnItem['quantity'];
		}
				
		// return the official transaction
		return $officialTransaction;
	}
	
	
	
	public function finalizeUserData($transaction) {

	  // ensure their 'Customer' data has values when they are not logged in
	  if($transaction['Customer']['id'] == NULL) {
		//$transaction['Customer']['id'] = $transaction['Transaction']['customer_id'];
		$transaction['Customer']['first_name'] = $transaction['TransactionPayment'][0]['first_name'];
		$transaction['Customer']['last_name'] = $transaction['TransactionPayment'][0]['last_name'];
		$transaction['Customer']['email'] = $transaction['TransactionPayment'][0]['email']; // required
		$transaction['Customer']['username'] = $transaction['TransactionPayment'][0]['email']; // required
		//$transaction['Customer']['phone'] = $transaction['TransactionPayment']['phone'];
		
		// generate a temporary password: ANNNN
		$transaction['Customer']['password'] = chr(97 + mt_rand(0, 25)) . rand(1000, 9999); // required
		
		// set their User Role Id
		$transaction['Customer']['user_role_id'] = (defined('__APP_DEFAULT_USER_REGISTRATION_ROLE_ID')) ? __APP_DEFAULT_USER_REGISTRATION_ROLE_ID : 3 ;
	  }
	  
	  //$transaction['TransactionPayment']['user_id'] = $transaction['Transaction']['customer_id'];
	  // copy Payment data to Shipment data if neccessary
	  if($transaction['TransactionPayment'][0]['shipping'] == '0') {
		$transaction['TransactionShipment'][0]['transaction_id'] = $transaction['Transaction']['id'];
		$transaction['TransactionShipment'][0]['first_name'] = $transaction['TransactionPayment'][0]['first_name'];
		$transaction['TransactionShipment'][0]['last_name'] = $transaction['TransactionPayment'][0]['last_name'];
		$transaction['TransactionShipment'][0]['email'] = $transaction['TransactionPayment'][0]['email'];
		$transaction['TransactionShipment'][0]['street_address_1'] = $transaction['TransactionPayment'][0]['street_address_1'];
		$transaction['TransactionShipment'][0]['street_address_2'] = $transaction['TransactionPayment'][0]['street_address_2'];
		$transaction['TransactionShipment'][0]['city'] = $transaction['TransactionPayment'][0]['city'];
		$transaction['TransactionShipment'][0]['state'] = $transaction['TransactionPayment'][0]['state'];
		$transaction['TransactionShipment'][0]['zip'] = $transaction['TransactionPayment'][0]['zip'];
		$transaction['TransactionShipment'][0]['country'] = $transaction['TransactionPayment'][0]['country'];
		//$transaction['TransactionShipment']['phone'] = $transaction['TransactionPayment']['phone'];
		//$transaction['TransactionShipment']['user_id'] = $transaction['TransactionPayment']['user_id'];
	  }
	  
	  return $transaction;
	}

	
	/**
	 * 
	 * @param array $transactions Multiple transactions
	 * @return array A single Transaction
	 */
	public function combineTransactions($transactions) {

	  foreach($transactions as $transaction) {
		foreach($transaction['TransactionItem'] as $transactionItem) {
		  if( ! isset($finalTransactionItems[$transactionItem['foreign_key']]) ) {
			$finalTransactionItems[$transactionItem['foreign_key']] = $transactionItem;
		  } else {
			$finalTransactionItems[$transactionItem['foreign_key']]['quantity'] += $transactionItem['quantity'];
		  }
		}
	  }

	  // reset the keys back to 0,1,2,3..
	  $finalTransaction['TransactionItem'] = array_values($finalTransactionItems);

	  // reuse the Transaction data from the 1st Transaction (ideally, the newest Transaction)
	  $finalTransaction['Transaction'] = $transactions[0]['Transaction'];

	  return $finalTransaction;
	}
	
	
	/**
	 * An array of options for select inputs
	 *
	 */
	public function statuses() {
	    $statuses = array();
	    foreach (Zuha::enum('ORDER_TRANSACTION_STATUS') as $status) {
		  $statuses[Inflector::underscore($status)] = $status;
	    }
	    return Set::merge(array('failed' => 'Failed', 'paid' => 'Paid', 'shipped' => 'Shipped'), $statuses);
	}
	
	
}
