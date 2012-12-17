<?php
App::uses('TransactionsAppModel', 'Transactions.Model');
/**
 * TransactionTax Model
 *
 * @property Transaction $Transaction
 */
class TransactionTax extends TransactionsAppModel {
    
public $name = 'TransactionTax';  
/**
 * Display field
 *
 * @var string
 */
    public $displayField = 'name';
    
/**
 * Acts as
 * 
 * @var array
 */
    public $actsAs = array('Tree');
    
/**
 * Order
 * 
 * @var array
 */
    public $order = array('name' => 'ASC');
    
/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
#		'name' => array(
#			'notempty' => array(
#				'rule' => array('notempty'),
#				//'message' => 'Your custom message here',
#				//'allowEmpty' => false,
#				//'required' => false,
#				//'last' => false, // Stop validation after this rule
#				//'on' => 'create', // Limit validation to 'create' or 'update' operations
#			),
#		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'Transaction' => array(
			'className' => 'Transactions.Transaction',
			'foreignKey' => 'tax_id',
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
    	'Children' => array(
			'className' => 'Transactions.TransactionTax',
			'foreignKey' => 'parent_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => 'SELECT COUNT(*) FROM transaction_taxes as TransactionTax WHERE TransactionTax.id = Transaction.parent_id',
		    ),
	    );
    
    public $belongsTo = array(
		'Parent' => array(
			'className' => 'Transactions.TransactionTax',
			'foreignKey' => 'parent_id',
			'conditions' => '',
			'fields' => '',
			'order' => '',
            'counterCache' => 'children'
	    	),
        );
    
/**
 * Constructor
 */
	public function __construct($id = false, $table = null, $ds = null) {
    	parent::__construct($id, $table, $ds);
		$this->order = array("{$this->alias}.name");
	}
    
/**
 * Before save callback
 *
 * @var array $options
 */
    public function beforeSave($options = array()) {
        $this->data = $this->_cleanData($this->data);
        return true;
    }
    
/**
 * After save callback
 *
 * @var bool $created
 */
    public function afterSave($created) {
        if ($created) {
            $subData = $this->_subRegionData($this->data);
            if (!empty($subData)) {
                $id = $this->id;
                if ($this->saveAll($subData)) {
                    $this->id = $id; // reset to first id so that other functions get the id of the first one they are trying to save
                }
            }
        }
    }
    
    protected function _subRegionData($data) {
        $subRegions = false;
        if (!empty($data['TransactionTax']['code']) && $list = $this->_lists($data['TransactionTax']['code'])) {
            $i=0;
            foreach ($this->$list() as $code => $name) {
                $subRegions[$i]['TransactionTax']['parent_id'] = $this->id;
                $subRegions[$i]['TransactionTax']['name'] = $name;
                $subRegions[$i]['TransactionTax']['label'] = $this->_labels($code);
                $subRegions[$i]['TransactionTax']['code'] = $code;
                $subRegions[$i]['TransactionTax']['rate'] = '0.00';
                $i++;
            }
        }
        return $subRegions;
    }
    
/**
 * Which list to use
 */
    protected function _lists($code) {
        $list = false;
        if ($code == 'US') {
            $list = 'states';
        }
        if ($code == 'CA') {
            $list = 'provinces';
        }
        if ($code == 'AU') {
            $list = 'territories';
        }
        return $list;
    }
    
/**
 * Labels by Code
 */
    protected function _labels($code) {
        $label = 'State Tax';
        $labels = array('VAT' => array_flip(array_merge($this->territories(), $this->provinces()))); // an array of non "State Tax" Labels
        if (in_array($code, $labels['VAT'])) {
            $label = 'VAT';
        }
        
        return $label;
    }
    
/**
 * Clean data
 * 
 */
    protected function _cleanData($data) {
        if (!empty($data['TransactionTax']['code']) && empty($data['TransactionTax']['name'])) {
            $countries = $this->countries();
            $data['TransactionTax']['name'] = $countries[$data['TransactionTax']['code']];
        }
        return $data;
    }


	protected function _applyTax($data) {
        $debug($data);
        break;
	}
    
    public function countries($filter = false) {
        
        $countries =  array(
            'ZZ' => 'Everywhere Else',
            'EU*' => 'European Countries',
            'NA*' => 'North American Countries',
            'GB' => 'United Kingdom',
            'US' => 'United States',
            'null' => '-----------',
            'AF' => 'Afghanistan',
            'AX' => 'Aland Islads',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AI' => 'Anguilla',
            'AG' => 'Antigua And Barbuda',
            'AR' => 'Argentina',
            'AM' => 'Armenia',
            'AW' => 'Aruba',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'AZ' => 'Azerbaijan',
            'BS' => 'Bahamas',
            'BH' => 'Bahrain',
            'BD' => 'Bangladesh',
            'BB' => 'Barbados',
            'BY' => 'Belarus',
            'BE' => 'Belgium',
            'BZ' => 'Belize',
            'BJ' => 'Benin',
            'BM' => 'Bermuda',
            'BT' => 'Bhutan',
            'BO' => 'Bolivia',
            'BA' => 'Bosnia And Herzegovina',
            'BW' => 'Botswana',
            'BV' => 'Bouvet Island',
            'BR' => 'Brazil',
            'IO' => 'British Indian Ocean Territory',
            'BN' => 'Brunei Darussalam',
            'BG' => 'Bulgaria',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'KH' => 'Cambodia',
            'CM' => 'Cameroon',
            'CA' => 'Canada',
            'CV' => 'Cape Verde',
            'KY' => 'Cayman Islands',
            'CF' => 'Central African Republic',
            'TD' => 'Chad',
            'CL' => 'Chile',
            'CN' => 'China',
            'CX' => 'Christmas Island',
            'CC' => 'Cocos (Keeling) Islands',
            'CO' => 'Colombia',
            'KM' => 'Comoros',
            'CG' => 'Congo',
            'CD' => 'Congo, The Democratic Republic Of The',
            'CK' => 'Cook Islands',
            'CR' => 'Costa Rica',
            'CI' => 'CÔTe D\'Ivoire',
            'HR' => 'Croatia',
            'CU' => 'Cuba',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DK' => 'Denmark',
            'DJ' => 'Djibouti',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'EC' => 'Ecuador',
            'EG' => 'Egypt',
            'SV' => 'El Salvador',
            'GQ' => 'Equatorial Guinea',
            'ER' => 'Eritrea',
            'EE' => 'Estonia',
            'ET' => 'Ethiopia',
            'ZZ' => 'Everywhere Else',
            'FK' => 'Falkland Islands (Malvinas)',
            'FO' => 'Faroe Islands',
            'FJ' => 'Fiji',
            'FI' => 'Finland',
            'FR' => 'France',
            'GF' => 'French Guiana',
            'PF' => 'French Polynesia',
            'TF' => 'French Southern Territories',
            'GA' => 'Gabon',
            'GM' => 'Gambia',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GR' => 'Greece',
            'GL' => 'Greenland',
            'GD' => 'Grenada',
            'GP' => 'Guadeloupe',
            'GT' => 'Guatemala',
            'GG' => 'Guernsey',
            'GN' => 'Guinea',
            'GW' => 'Guinea Bissau',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HM' => 'Heard Island And Mcdonald Islands',
            'VA' => 'Holy See (Vatican City State)',
            'HN' => 'Honduras',
            'HK' => 'Hong Kong',
            'HU' => 'Hungary',
            'IS' => 'Iceland',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IR' => 'Iran, Islamic Republic Of',
            'IQ' => 'Iraq',
            'IE' => 'Ireland',
            'IM' => 'Isle Of Man',
            'IL' => 'Israel',
            'IT' => 'Italy',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JE' => 'Jersey',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'Kenya',
            'KI' => 'Kiribati',
            'KP' => 'Korea, Democratic People\'s Republic Of',
            'KV' => 'Kosovo',
            'KW' => 'Kuwait',
            'KG' => 'Kyrgyzstan',
            'LA' => 'Lao People\'s Democratic Republic',
            'LV' => 'Latvia',
            'LB' => 'Lebanon',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libyan Arab Jamahiriya',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MO' => 'Macao',
            'MK' => 'Macedonia, The Former Yugoslav Republic Of',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius',
            'YT' => 'Mayotte',
            'MX' => 'Mexico',
            'MD' => 'Moldova, Republic Of',
            'MC' => 'Monaco',
            'MN' => 'Mongolia',
            'ME' => 'Montenegro',
            'MS' => 'Montserrat',
            'MA' => 'Morocco',
            'MZ' => 'Mozambique',
            'MM' => 'Myanmar',
            'NA' => 'Namibia',
            'NR' => 'Nauru',
            'NP' => 'Nepal',
            'NL' => 'Netherlands',
            'AN' => 'Netherlands Antilles',
            'NC' => 'New Caledonia',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'NU' => 'Niue',
            'NF' => 'Norfolk Island',
            'NO' => 'Norway',
            'OM' => 'Oman',
            'PK' => 'Pakistan',
            'PS' => 'Palestinian Territory, Occupied',
            'PA' => 'Panama',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'PN' => 'Pitcairn',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'QA' => 'Qatar',
            'RE' => 'Reunion',
            'RO' => 'Romania',
            'RU' => 'Russia',
            'RW' => 'Rwanda',
            'BL' => 'Saint BarthÉLemy',
            'SH' => 'Saint Helena',
            'KN' => 'Saint Kitts And Nevis',
            'LC' => 'Saint Lucia',
            'MF' => 'Saint Martin',
            'PM' => 'Saint Pierre And Miquelon',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'ST' => 'Sao Tome And Principe',
            'SA' => 'Saudi Arabia',
            'SN' => 'Senegal',
            'RS' => 'Serbia',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SG' => 'Singapore',
            'SK' => 'Slovakia',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia',
            'ZA' => 'South Africa',
            'GS' => 'South Georgia And The South Sandwich Islands',
            'KR' => 'South Korea',
            'ES' => 'Spain',
            'LK' => 'Sri Lanka',
            'VC' => 'St. Vincent',
            'SD' => 'Sudan',
            'SR' => 'Suriname',
            'SJ' => 'Svalbard And Jan Mayen',
            'SZ' => 'Swaziland',
            'SE' => 'Sweden',
            'CH' => 'Switzerland',
            'SY' => 'Syria',
            'TW' => 'Taiwan',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania, United Republic Of',
            'TH' => 'Thailand',
            'TL' => 'Timor Leste',
            'TG' => 'Togo',
            'TK' => 'Tokelau',
            'TO' => 'Tonga',
            'TT' => 'Trinidad and Tobago',
            'TN' => 'Tunisia',
            'TR' => 'Turkey',
            'TM' => 'Turkmenistan',
            'TC' => 'Turks And Caicos Islands',
            'TV' => 'Tuvalu',
            'UG' => 'Uganda',
            'UA' => 'Ukraine',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'UM' => 'United States Minor Outlying Islands',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VE' => 'Venezuela',
            'VN' => 'Viet Nam',
            'VG' => 'Virgin Islands, British',
            'WF' => 'Wallis And Futuna',
            'EH' => 'Western Sahara',
            'YE' => 'Yemen',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
            );
        if ($filter) {
            $countries = array_diff_key($countries, $this->find('list', array('conditions' => array('TransactionTax.parent_id' => null), 'fields' => array('TransactionTax.code', 'TransactionTax.name'))));
        }
        return $countries;
    }
    
/**
 * States
 *
 */
	public function states() {
		return array(
			'US-AL' => 'Alabama',
			'US-AK' => 'Alaska',
			'US-AZ' => 'Arizona',
			'US-AR' => 'Arkansas',
            'US-AS' => 'American Samoa',
			'US-CA' => 'California',
			'US-CO' => 'Colorado',
			'US-CT' => 'Connecticut',
			'US-DE' => 'Delaware',
			'US-FL' => 'Florida',
			'US-GA' => 'Georgia',
            'US-GU' => 'Guam',
			'US-HI' => 'Hawaii',
			'US-ID' => 'Idaho',
			'US-IL' => 'Illinois',
			'US-IN' => 'Indiana',
			'US-IA' => 'Iowa',
			'US-KS' => 'Kansas',
			'US-KY' => 'Kentucky',
			'US-LA' => 'Louisiana',
			'US-ME' => 'Maine',
			'US-MD' => 'Maryland',
			'US-MA' => 'Massachusetts',
			'US-MI' => 'Michigan',
			'US-MN' => 'Minnesota',
			'US-MS' => 'Mississippi',
			'US-MO' => 'Missouri',
            'US-MP' => 'Northern Mariana Islands',
			'US-MT' => 'Montana',
			'US-NE' => 'Nebraska',
			'US-NV' => 'Nevada',
			'US-NH' => 'New Hampshire',
			'US-NJ' => 'New Jersey',
			'US-NM' => 'New Mexico',
			'US-NY' => 'New York',
			'US-NC' => 'North Carolina',
			'US-ND' => 'North Dakota',
			'US-OH' => 'Ohio',
			'US-OK' => 'Oklahoma',
			'US-OR' => 'Oregon',
			'US-PA' => 'Pennsylvania',
            'US-PR' => 'Puerto Rico',
			'US-RI' => 'Rhode Island',
			'US-SC' => 'South Carolina',
			'US-SD' => 'South Dakota',
			'US-TN' => 'Tennessee',
			'US-TX' => 'Texas',
			'US-UT' => 'Utah',
            'US-UM' => 'United States Minor Outlying Islands',
			'US-VT' => 'Vermont',
			'US-VA' => 'Virginia',
            'US-VI' => 'Virgin Islands, U.S.',
			'US-WA' => 'Washington',
			'US-WV' => 'West Virginia',
			'US-WI' => 'Wisconsin',
			'US-WY' => 'Wyoming',
			);
	}
    
/**
 * Canadian Provinces
 * 
 */
    public function provinces() {
        return array(
            'CA-AB' => 'Alberta',
            'CA-BC' => 'British Columbia',
            'CA-MB' => 'Manitoba',
            'CA-NB' => 'New Brunswick',
            'CA-NF' => 'Newfoundland',
            'CA-NT' => 'Northwest Territories',
            'CA-NS' => 'Nova Scotia',
            'CA-ON' => 'Ontario',
            'CA-PE' => 'Prince Edward Island',
            'CA-QC' => 'Quebec',
            'CA-SK' => 'Saskatchewan',
            'CA-YT' => 'Yukon',
            );
    }
    
/**
 * Australian Territories
 * 
 */
    public function territories() {
        return array(
            'AU-AAT' => 'Australian Antarctic Territory',
            'AU-ACT' => 'Australian Capital Territory',
            'AU-JBT' => 'Jervis Bay Territory',
            'AU-NSW' => 'New South Wales',
            'AU-NT' => 'Northern Territory',
            'AU-QLD' => 'Queensland', 
            'AU-SA' => 'South Australia',
            'AU-TAS' => 'Tasmania',
            'AU-VIC' => 'Victoria',
            'AU-WA' => 'Western Australia',
            );
    }
    
/**
 * Types of Tax Calculations
 * 
 * @return array
 */
	public function types($parent = null) {
        $message = !empty($parent) ? $parent['TransactionTax']['rate'] . '% ' .$parent['TransactionTax']['name'] . ' rate' : __('parent rate');
		return array(
			'added' => __('Added to %s', $message), // added to [Parent.rate]% [Parent.name]
			'instead' => __('Instead of %s', $message), // Instead of [Parent.rate]% [Parent.name] 
    		'compound' => __('Compounded on top of %s', $message), // Instead of [Parent.rate]% [Parent.name] 
			);
	}

}