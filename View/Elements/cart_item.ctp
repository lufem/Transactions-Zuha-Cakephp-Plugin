<?php 
$foreignKey = !empty($product['Gallery']['id']) ? $transactionItem['foreign_key'] : $product['Parent']['id'];

echo __('<h5>%s</h5>', $this->Html->link($transactionItem['name'], '/products/products/view/'.$transactionItem['foreign_key'], null, __('Are you sure you want to leave this page?')));

echo '<table class="table table-hover"><tr><td>';

$minQty = !empty($product['Product']['cart_min']) ? $product['Product']['cart_min'] : 0;
$maxQty = !empty($product['Product']['cart_max']) ? $product['Product']['cart_max'] : null;

echo $this->Form->input("TransactionItem.{$i}.quantity", array(
    'label' => false,
	'class' => 'TransactionItemCartQty span5',
    'div' => false,
    'value' => $transactionItem['quantity'],
    'min' => $minQty, 'max' => $maxQty,
    'size' => 1,
    'after' => __(' %s', $this->Html->link('<i class="icon-trash"></i>', array('plugin' => 'transactions', 'controller' => 'transaction_items', 'action' => 'delete', $transactionItem['id']), array('title' => 'Remove from cart', 'escape' => false)))
    ));

$transactionItemCartPrice = $transactionItem['price'] * $transactionItem['quantity']; ?>

</td><td>

    <div class="TransactionItemCartPrice">
        $<span class="floatPrice"><?php echo number_format($transactionItemCartPrice, 2); ?></span>
    	<span class="priceOfOne"><?php echo number_format($transactionItem['price'], 2) ?></span>
    </div>
    
</td></tr></table>
