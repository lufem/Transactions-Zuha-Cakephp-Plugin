<?php
App::uses('TransactionsAppController', 'Transactions.Controller');
/**
 * Transactions Controller
 *
 * @property Transaction $Transaction
 */
class TransactionsController extends TransactionsAppController {

	public $name = 'Transactions';
	
	public $uses = array('Transactions.Transaction');
	
	public $components = array('Ssl', 'Transactions.Payments');
	
	
	public function beforeFilter() {
		parent::beforeFilter();
	}
	
	
/**
 * checkout method
 * processes the order and payment
 *
 * @return void
 */
	public function checkout() {
	    if($this->request->data) {
		  try {
			  
			$data = $this->Transaction->finalizeTransactionData($this->request->data);
			$data = $this->Transaction->finalizeUserData($data);
			
			$data = $this->Payments->pay($data);
			debug($data);break;

			$data['Transaction']['status'] = 'paid';
			
			if(!$this->Auth->loggedIn()) {
				$this->Transaction->Customer->add($data);
				// Refactor their $data with their new Customer.id
				$data['Transaction']['customer_id'] = $this->Transaction->Customer->id;
				$data['Customer']['id'] = $this->Transaction->Customer->id;
				foreach($data['TransactionAddress'] as &$transactionAddress) {
					$transactionAddress['user_id'] = $this->Transaction->Customer->id;
				}
			}

	  		// need a valid Customer.id to proceed
			$this->Transaction->save($data);
			$this->TransactionAddress->save($data);
			if($data['Connection']) {
				$connection['Connection']['user_id'] = $data['Customer']['id'];
				$connection['Connection']['type'] = $data['Transaction']['mode'];
				$connection['Connection']['value'] = $data['Connection'];
				$this->Transaction->Customer->Connection->save($connection);
			}

			return $this->redirect(array('plugin' => 'transactions', 'controller' => 'transactions', 'action' => 'success'));
			
		  } catch (Exception $e) {
			  
			  $this->Session->setFlash(__d('transactions', $e->getMessage()));
			  $data['Transaction']['status'] = 'failed';
			  $this->Transaction->save($data);
			  
			  return $this->redirect(array('plugin' => 'transactions', 'controller' => 'transactions', 'action' => 'myCart'));
			  
//			debug($e);
//			debug($e->getMessage());
//			echo($exc->getMessage().'<pre>'.$exc->getTraceAsString().'</pre>');  
//			while($exc = $exc->getPrevious()) {
//				echo('Caused by: '.$exc->getMessage().'<pre>'.$exc->getTraceAsString().'</pre>');  
//			}
//			break;
		  }

		  
//		  
//		  if ($response['response_code'] != 1) {
//			// Transaction failed, back to the cart!
//			$officialTransaction['Transaction']['status'] = 'failed';
//			$this->Session->setFlash($response['reason_text'] . ' ' . $response['description']);
//			$url = array('plugin' => 'transactions', 'controller' => 'transactions', 'action' => 'myCart');
//		  } else {
//			// else redirect them to success page
//			$officialTransaction['Transaction']['status'] = 'paid';
//			$url = array('plugin' => 'transactions', 'controller' => 'transactions', 'action' => 'success');
//		  }
//		  
//		  // save the transaction stuff again
//		  $this->Transaction->saveAll($officialTransaction);
////		  $this->Transaction->TransactionPayment->save($officialTransaction);
////		  $this->Transaction->TransactionShipment->save($officialTransaction);
//		  
//		  // do the redirection
//		  $this->redirect($url);

	    } else {
		  $this->Session->setFlash(__d('transactions', 'Invalid transaction.'));
		  return $this->redirect($this->referer());
	    }
	}
	
/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->Transaction->recursive = 0;
		$this->set('transactions', $this->paginate());
	}

  /**
   * 
   * @param string $id
   * @throws NotFoundException
   */
	public function view($id = null) {
		$this->Transaction->id = $id;
		if (!$this->Transaction->exists()) {
			throw new NotFoundException(__d('transactions', 'Invalid transaction'));
		}
		$this->set('transaction', $this->Transaction->read(null, $id));
	}
	
	/**
	 * 
	 * @throws NotFoundException
	 */
	public function myCart() {
	  	// gather checkout options like shipping, payments, ssl, etc
		$options = $this->Transaction->gatherCheckoutOptions();

	    // ensure that SSL is on if it's supposed to be
		if ($options['ssl'] !== null && !strpos($_SERVER['HTTP_HOST'], 'localhost')) {
		  $this->Ssl->force();
		}
		
		// If they have two carts, we are going to ask the customer what to do with them
		// determine the user's "ID"
		$userId = $this->Transaction->getCustomersId();
		$numberOfCarts = $this->Transaction->find('count', array('conditions' => array('customer_id' => $userId)));
		if($numberOfCarts > 1) {

		  return $this->redirect(array('plugin'=>'transactions', 'controller'=>'transactions', 'action'=>'mergeCarts'));
		  
		} else {
		  // get their cart and process it
		  $this->request->data = $this->Transaction->processCart($userId);

		  if (!$this->request->data) {
			throw new NotFoundException(__d('transactions', 'Cart is empty'));
		  }

		  // set the variables to display in the cart
		  $this->set(compact('options'));
		  
		}
	}

	/**
	 * add method
	 *
	 * @return void
	 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Transaction->create();
			if ($this->Transaction->save($this->request->data)) {
				$this->Session->setFlash(__d('transactions', 'The transaction has been saved'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__d('transactions', 'The transaction could not be saved. Please, try again.'));
			}
		}
		$transactionPayments = $this->Transaction->TransactionPayment->find('list');
		$transactionShipments = $this->Transaction->TransactionShipment->find('list');
		//$transactionCoupons = $this->Transaction->TransactionCoupon->find('list');
		$customers = $this->Transaction->Customer->find('list');
		$contacts = $this->Transaction->Contact->find('list');
		$assignees = $this->Transaction->Assignee->find('list');
		$this->set(compact('transactionPayments', 'transactionShipments', 'transactionCoupons', 'customers', 'contacts', 'assignees'));
	}


	/**
	 * 
	 * @param string $id
	 * @throws NotFoundException
	 */
	public function edit($id = null) {
		$this->Transaction->id = $id;
		if (!$this->Transaction->exists()) {
			throw new NotFoundException(__d('transactions', 'Invalid transaction'));
		}
		if ( ($this->request->is('post') || $this->request->is('put')) && !empty($this->request->data)) {
			if ($this->Transaction->save($this->request->data)) {
				$this->Session->setFlash(__d('transactions', 'The transaction has been saved'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__d('transactions', 'The transaction could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->Transaction->read(null, $id);
		}
		$transactionAddresses = $this->Transaction->TransactionAddress->find('list');
		$transactionCoupons = $this->Transaction->TransactionCoupon->find('list');
		$customers = $this->Transaction->Customer->find('list');
		$contacts = $this->Transaction->Contact->find('list');
		$assignees = $this->Transaction->Assignee->find('list');
		$this->set(compact('transactionAddresses', 'transactionCoupons', 'customers', 'contacts', 'assignees'));
	}


	/**
	 * 
	 * @param string $id
	 * @throws MethodNotAllowedException
	 * @throws NotFoundException
	 */
	public function delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->Transaction->id = $id;
		if (!$this->Transaction->exists()) {
			throw new NotFoundException(__d('transactions', 'Invalid transaction'));
		}
		if ($this->Transaction->delete()) {
			$this->Session->setFlash(__d('transactions', 'Transaction deleted'));
			return $this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__d('transactions', 'Transaction was not deleted'));
		return $this->redirect(array('action' => 'index'));
	}
	
	
	/**
	 * 
	 */
	public function mergeCarts() {
	  // find their carts.
	  // there should only be 2
	  $transactions = $this->Transaction->find('all',array(
		  'conditions' => array(
			  'customer_id' => $this->Session->read('Auth.User.id'),
			  'status' => 'open'
			  ),
		  'contain' => array('TransactionItem'),
		  'order' => array('Transaction.modified' => 'desc')
	  ));
	  
	  $this->set('transactions', $transactions);
	  
	  // they have made a choice.  process it.
	  // choices are: '1', 'merge', or '2'
	  if(isset($this->request->params['named']['choice'])) {
		if(in_array($this->request->params['named']['choice'], array('1', 'merge', '2'))) {
		  switch ($this->request->params['named']['choice']) {
			case '1':
			  $this->Transaction->delete($transactions[1]['Transaction']['id']);
			  break;
			case '2':
			  $this->Transaction->delete($transactions[0]['Transaction']['id']);
			  break;
			case 'merge':
			  $transaction = $this->Transaction->combineTransactions($transactions);
			  $this->Transaction->delete($transactions[0]['Transaction']['id']);
			  $this->Transaction->delete($transactions[1]['Transaction']['id']);
			  $this->Transaction->saveAll($transaction);
			  break;
		  }
		  
		  $this->redirect(array('action' => 'myCart'));
		  
		}
	  }
	  
	}
	
}
