<?php echo $this->Html->script('http://code.highcharts.com/highcharts.js', array('inline' => false)); ?>
<?php echo $this->Html->script('http://code.highcharts.com/modules/exporting.js', array('inline' => false)); ?>

<div class="products row-fluid">
    <div class="span8 pull-left first">
        <ul class="nav nav-tabs" id="myTab">
            <li><a href="#today" data-toggle="tab">Today</a></li>
            <li><a href="#thisWeek" data-toggle="tab">This Week</a></li>
            <li><a href="#thisMonth" data-toggle="tab">This Month</a></li>
            <li><a href="#thisYear" data-toggle="tab">This Year</a></li>
            <li><a href="#allTime" data-toggle="tab">All Time</a></li>
        </ul>
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade" id="today">
                <div class="row-fluid">
                    <div class="alert alert-success clearfix">
                        <h3 class="span6 pull-left"> <?php echo $statsSalesToday['count']; ?> Orders Today </h3>
                        <h3 class="span6 pull-left"> $<?php echo $statsSalesToday['value']; ?> Total Value </h3>
                    </div>

                    <?php
                    // vars for chart
                    $hour = array_fill(0, 24, 0);
                    foreach ($statsSalesToday as $order) {
                        if ($order['Transaction']) {
                            $hourKey = (int) date('H', strtotime($order['Transaction']['created']));
                            $hour[$hourKey]++;
                        }
                    } ?>
                    <script type="text/javascript">
                    $(function () {
                        $('#myTab a:first').tab('show');
                    });
                    var chart;
                    $(document).ready(function() {
                        chart = new Highcharts.Chart({
                            chart: {
                                renderTo: 'ordersToday',
                                type: 'spline'
                            },
                            credits: false,
                            title: {
                                text: false
                            },
                            subtitle: {
                                text: false
                            },
                            xAxis: {
                                type: 'datetime',
                                dateTimeLabelFormats: { // don't display the dummy year
                                    month: '%e. %b',
                                    year: '%b'
                                }
                            },
                            yAxis: {
                                title: {
                                    text: false
                                },
                                min: 0
                            },
                            tooltip: {
                                formatter: function() {
                                        return '<b>'+ this.series.name +'</b><br/>'+
                                        Highcharts.dateFormat('%e. %b', this.x) +': '+ this.y +' m';
                                }
                            },
        
                            series: [{
                                name: 'Leads',
                                // Define the data points. All series have a dummy year
                                // of 1970/71 in order to be compared on the same x axis. Note
                                // that in JavaScript, months start at 0 for January, 1 for February etc.
                                data: [
                                <?php
                                $i = 0;
                                while ($i < 24) { ?>
                                    [<?php echo $i ?>,   <?php echo $hour[$i] ? $hour[$i] : 0; ?>],
                                <?php ++$i; } ?>
                                ]
                            }]
                        });
                    });
                    </script>
                    <div id="ordersToday" style="min-width: 300px; height: 300px;"></div>
                </div>
            </div>
            <div class="tab-pane fade" id="thisWeek">
                <div>
                    <?php
                    echo
                    '<div class="alert alert-success">'
                    . '<h1>' . $statsSalesThisWeek['count'] . '</h1><b>Orders This Week</b>'
                    . '<h1>$' . $statsSalesThisWeek['value'] . '</h1><b>Total Value</b>'
                    . '</div>';
                    ?>
                </div>
            </div>
            <div class="tab-pane fade" id="thisMonth">
                <div>
                    <?php
                    echo
                    '<div class="alert alert-success">'
                    . '<h1>' . $statsSalesThisMonth['count'] . '</h1><b>Orders This Month</b>'
                    . '<h1>$' . $statsSalesThisMonth['value'] . '</h1><b>Total Value</b>'
                    . '</div>';
                    ?>
                </div>
            </div>
            <div class="tab-pane fade" id="thisYear">
                <div>
                    <?php
                    echo
                    '<div class="alert alert-success">'
                    . '<h1>' . $statsSalesThisYear['count'] . '</h1><b>Orders This Year</b>'
                    . '<h1>$' . $statsSalesThisYear['value'] . '</h1><b>Total Value</b>'
                    . '</div>';
                    ?>
                </div>
            </div>
            <div class="tab-pane fade" id="allTime">
                <div>
                    <?php
                    echo
                    '<div class="alert alert-success">'
                    . '<h1>' . $statsSalesAllTime['count'] . '</h1><b>Orders All Time</b>'
                    . '<h1>$' . $statsSalesAllTime['value'] . '</h1><b>Total Value</b>'
                    . '</div>';
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    

    <div class="tagProducts span3 pull-right last">
        <ul class="nav nav-list">
            <?php 
            $counts['open'] = !empty($counts['open']) ? __('<span class="badge badge-inverse">%s</span>', $counts['open']) : '<span class="badge">0</span>';
            $counts['shipped'] = !empty($counts['shipped']) ? __('<span class="badge badge-info">%s</span>', $counts['shipped']) : '<span class="badge">0</span>';
            $counts['paid'] = !empty($counts['paid']) ? __('<span class="badge badge-success">%s</span>', $counts['paid']) : '<span class="badge">0</span>';
            $counts['failed'] = !empty($counts['failed']) ? __('<span class="badge badge-important">%s</span>', $counts['failed']) : '<span class="badge">0</span>';
            foreach (array_reverse($transactionStatuses) as $key => $status) { ?>
                <li><?php echo $this->Html->link(__('%s %s Transactions', $counts[strtolower($status)], $status), array('admin' => true, 'plugin' => 'transactions', 'controller' => 'transactions', 'action' => 'index', 'filter' => 'status:' . $key, 'sort' => 'Transaction.created', 'direction' => 'desc'), array('escape' => false)); ?></li>

            <?php } ?>
            <li><?php echo $this->Html->link(__('%s In Cart Transactions', $counts['open']), array('admin' => true, 'plugin' => 'transactions', 'controller' => 'transactions', 'action' => 'index', 'filter' => 'status:open', 'sort' => 'Transaction.created', 'direction' => 'desc'), array('escape' => false)); ?></li>
            <li><?php echo $this->Html->link(__('My Assigned Transactions'), array('admin' => true, 'plugin' => 'transactions', 'controller' => 'transaction_items', 'action' => 'index', 'filter' => 'assignee_id:'.$this->Session->read('Auth.User.id'))); ?></li>
        </ul>
    </div>

</div>

<div class="products clear first pull-left row-fluid">
    <h3>Setup</h3>
    <div class="span3">
        <h5>Store</h5>
        <ul class="nav nav-list">
            <li>
            	<div class="btn-group">
            		<button class="btn btn-small"><?php echo $this->Html->link('Create a Product', array('plugin' => 'products', 'controller' => 'products', 'action' => 'add')); ?></button>
            		<button class="btn btn-small dropdown-toggle" data-toggle="dropdown">
            			<span class="caret"></span>
            		</button>
            		<ul class="dropdown-menu">
            			<li><?php echo $this->Html->link('Create an Membership Product', array('plugin' => 'products', 'controller' => 'products', 'action' => 'add', 'membership')); ?></li>
            			<li><?php echo $this->Html->link('Create an ARB Product', array('plugin' => 'products', 'controller' => 'products', 'action' => 'add', 'arb')); ?></li>
            			<?php echo CakePlugin::loaded('Auctions') ? __('<li>%s</li>', $this->Html->link('Create an Auction', array('plugin' => 'auctions', 'controller' => 'auctions', 'action' => 'add'))) : null; ?>
            			<!-- <li><?php echo $this->Html->link('Create a Virtual Product', array('plugin' => 'products', 'controller' => 'products', 'action' => 'add', 'virtual')); ?></li> -->
            		</ul>
            	</div>
            </li>
            <li><?php echo $this->Html->link('All Products', array('plugin' => 'products', 'controller' => 'products', 'action' => 'index')); ?></li>
            <li><?php echo $this->Html->link('Out Of Stock Products', array('plugin' => 'products', 'controller' => 'products', 'action' => 'index', 'filter' => 'stock:0')); ?></li>
        </ul>
    </div>
    <div class="span3">
        <h5>Brands</h5>
        <ul class="nav nav-list">
            <li><?php echo $this->Html->link('List All Brands', array('plugin' => 'products', 'controller' => 'product_brands', 'action' => 'index')); ?></li>
            <li><?php echo $this->Html->link('Add a Brand', array('plugin' => 'products', 'controller' => 'product_brands', 'action' => 'add')); ?></li>
        </ul>
    </div>
    <div class="span3">
        <h5>Attributes</h5>
        <ul class="nav nav-list">
            <li><?php echo $this->Html->link('Product Variations', array('plugin' => 'products', 'controller' => 'products', 'action' => 'categories')); ?></li>
        </ul>
        <h5>Categories</h5>
        <ul class="nav nav-list">
            <li><?php echo $this->Html->link('Product Categories', array('plugin' => 'products', 'controller' => 'products', 'action' => 'categories')); ?></li>
        </ul>
    </div>
    <div class="span2">
        <h5>Settings</h5>
        <ul class="nav nav-list">
            <li><?php echo $this->Html->link('List All', array('admin' => true, 'plugin' => null, 'controller' => 'settings', 'action' => 'index', 'start' => 'type:Transactions')); ?></li>
            <li><?php echo $this->Html->link('Emails', array('admin' => true, 'plugin' => 'transactions', 'controller' => 'transactions', 'action' => 'settings')); ?></li>
            <li><?php echo $this->Html->link('Tax Rates', array('admin' => true, 'plugin' => 'transactions', 'controller' => 'transaction_taxes', 'action' => 'index')); ?></li>
            <li><?php echo $this->Html->link('Status Types', array('admin' => true, 'plugin' => null, 'controller' => 'enumerations', 'action' => 'index', 'filter' => 'type:TRANSACTIONS_ITEM_STATUS')); ?></li>
            <li><?php echo $this->Html->link('Item Status Types', array('admin' => true, 'plugin' => null, 'controller' => 'enumerations', 'action' => 'index', 'start' => 'type:TRANSACTIONS_STATUS')); ?></li>
        </ul>
    </div>
</div>

<?php
// set contextual search options
$this->set('forms_search', array(
    'url' => '/products/products/index/', 
	'inputs' => array(
		array(
			'name' => 'contains:name', 
			'options' => array(
				'label' => '', 
				'placeholder' => 'Product Search',
				'value' => !empty($this->request->params['named']['contains']) ? substr($this->request->params['named']['contains'], strpos($this->request->params['named']['contains'], ':') + 1) : null,
				)
			),
		)
	));
	
// set the contextual breadcrumb items
$this->set('context_crumbs', array('crumbs' => array(
	$this->Html->link(__('Admin Dashboard'), '/admin'),
	$page_title_for_layout,
)));

// set the contextual menu items
$this->set('context_menu', array('menus' => array(
    array(
		'heading' => 'Products',
		'items' => array(
			$this->Html->link(__('Dashboard'), array('admin' => true, 'controller' => 'products', 'action' => 'dashboard'), array('class' => 'active')),
			)
		),
        array(
            'heading' => 'Products',
            'items' => array(
                $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')),
                $this->Html->link(__('List Transactions'), array('plugin' => 'transactions', 'controller' => 'transactions', 'action' => 'index')),
            )
        ),
        ))); ?>