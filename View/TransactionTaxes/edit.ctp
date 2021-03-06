<div class="transactionTax form">
    <?php echo $this->Form->create('TransactionTax');?>
    <fieldset>
    <?php
        echo $this->Form->input('TransactionTax.id');
    	echo $this->Form->input('TransactionTax.label', array('type' => 'hidden', 'value' => 'National Tax'));
		echo $this->Form->input('TransactionTax.code', array('label' => 'Region', 'empty' => '-- Please select a region --'));
		echo $this->Form->input('TransactionTax.rate', array('label' => 'Tax Rate %'));
	?>
	</fieldset>
    <?php echo $this->Form->end(__('Save Region'));?>
</div>

<?php 
// set the contextual menu items
$this->set('context_menu', array('menus' => array(
    array(
		'heading' => 'Products',
		'items' => array(
			$this->Html->link(__('Dashboard'), array('plugin' => 'products', 'controller' => 'products', 'action' => 'dashboard')),
			)
		),
	array(
		'heading' => 'Taxes',
		'items' => array(
			$this->Html->link(__('List'), array('action' => 'index')),
			)
		),
	))); ?>