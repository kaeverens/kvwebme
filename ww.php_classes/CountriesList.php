<?php
class CountriesList{
	public $countries;
	private $_onChange;
	// { list of countries
	public $countriesList=array(
		array('iso'=>'AFG', 'name'=>'Afghanistan'),
		array('iso'=>'ALA', 'name'=>'Åland'),
		array('iso'=>'ALB', 'name'=>'Albania'),
		array('iso'=>'DZA', 'name'=>'Algeria'),
		array('iso'=>'ASM', 'name'=>'American Samoa'),
		array('iso'=>'AND', 'name'=>'Andorra'),
		array('iso'=>'AGO', 'name'=>'Angola'),
		array('iso'=>'AIA', 'name'=>'Anguilla'),
		array('iso'=>'ATG', 'name'=>'Antigua and Barbuda'),
		array('iso'=>'ARG', 'name'=>'Argentina'),
		array('iso'=>'ARM', 'name'=>'Armenia'),
		array('iso'=>'ABW', 'name'=>'Aruba'),
		array('iso'=>'AUS', 'name'=>'Australia'),
		array('iso'=>'AUT', 'name'=>'Austria'),
		array('iso'=>'AZE', 'name'=>'Azerbaijan'),
		array('iso'=>'BHS', 'name'=>'Bahamas'),
		array('iso'=>'BHR', 'name'=>'Bahrain'),
		array('iso'=>'BGD', 'name'=>'Bangladesh'),
		array('iso'=>'BRB', 'name'=>'Barbados'),
		array('iso'=>'BLR', 'name'=>'Belarus'),
		array('iso'=>'BEL', 'name'=>'Belgium'),
		array('iso'=>'BLZ', 'name'=>'Belize'),
		array('iso'=>'BEN', 'name'=>'Benin'),
		array('iso'=>'BMU', 'name'=>'Bermuda'),
		array('iso'=>'BTN', 'name'=>'Bhutan'),
		array('iso'=>'BOL', 'name'=>'Bolivia'),
		array('iso'=>'BIH', 'name'=>'Bosnia and Herzegovina'),
		array('iso'=>'BWA', 'name'=>'Botswana'),
		array('iso'=>'BRA', 'name'=>'Brazil'),
		array('iso'=>'VGB', 'name'=>'British Virgin Islands'),
		array('iso'=>'BRN', 'name'=>'Brunei Darussalam'),
		array('iso'=>'BGR', 'name'=>'Bulgaria'),
		array('iso'=>'BFA', 'name'=>'Burkina Faso'),
		array('iso'=>'BDI', 'name'=>'Burundi'),
		array('iso'=>'KHM', 'name'=>'Cambodia'),
		array('iso'=>'CMR', 'name'=>'Cameroon'),
		array('iso'=>'CAN', 'name'=>'Canada'),
		array('iso'=>'CPV', 'name'=>'Cape Verde'),
		array('iso'=>'CYM', 'name'=>'Cayman Islands'),
		array('iso'=>'CAF', 'name'=>'Central African Republic'),
		array('iso'=>'TCD', 'name'=>'Chad'),
		array('iso'=>'CHL', 'name'=>'Chile'),
		array('iso'=>'CHN', 'name'=>'China'),
		array('iso'=>'COL', 'name'=>'Colombia'),
		array('iso'=>'COM', 'name'=>'Comoros'),
		array('iso'=>'COG', 'name'=>'Congo, Republic of'),
		array('iso'=>'COD', 'name'=>'Congo, Democratic Republic of'),
		array('iso'=>'COK', 'name'=>'Cook Islands'),
		array('iso'=>'CRI', 'name'=>'Costa Rica'),
		array('iso'=>'CIV', 'name'=>'Cote d\'Ivoire'),
		array('iso'=>'HRV', 'name'=>'Croatia'),
		array('iso'=>'CUB', 'name'=>'Cuba'),
		array('iso'=>'CYP', 'name'=>'Cyprus'),
		array('iso'=>'CZE', 'name'=>'Czech Republic'),
		array('iso'=>'DNK', 'name'=>'Denmark'),
		array('iso'=>'DJI', 'name'=>'Djibouti'),
		array('iso'=>'DMA', 'name'=>'Dominica'),
		array('iso'=>'DOM', 'name'=>'Dominican Republic'),
		array('iso'=>'ECU', 'name'=>'Ecuador'),
		array('iso'=>'EGY', 'name'=>'Egypt'),
		array('iso'=>'SLV', 'name'=>'El Salvador'),
		array('iso'=>'GNQ', 'name'=>'Equatorial Guinea'),
		array('iso'=>'ERI', 'name'=>'Eritrea'),
		array('iso'=>'EST', 'name'=>'Estonia'),
		array('iso'=>'ETH', 'name'=>'Ethiopia'),
		array('iso'=>'FLK', 'name'=>'Falkland Islands (Malvinas)'),
		array('iso'=>'FRO', 'name'=>'Faroe Islands'),
		array('iso'=>'FJI', 'name'=>'Fiji'),
		array('iso'=>'FIN', 'name'=>'Finland'),
		array('iso'=>'FRA', 'name'=>'France'),
		array('iso'=>'GUF', 'name'=>'French Guiana'),
		array('iso'=>'PYF', 'name'=>'French Polynesia'),
		array('iso'=>'GAB', 'name'=>'Gabon'),
		array('iso'=>'GMB', 'name'=>'Gambia'),
		array('iso'=>'GEO', 'name'=>'Georgia'),
		array('iso'=>'DEU', 'name'=>'Germany'),
		array('iso'=>'GHA', 'name'=>'Ghana'),
		array('iso'=>'GIB', 'name'=>'Gibraltar'),
		array('iso'=>'GRC', 'name'=>'Greece'),
		array('iso'=>'GRL', 'name'=>'Greenland'),
		array('iso'=>'GRD', 'name'=>'Grenada'),
		array('iso'=>'GLP', 'name'=>'Guadeloupe'),
		array('iso'=>'GUM', 'name'=>'Guam'),
		array('iso'=>'GTM', 'name'=>'Guatemala'),
		array('iso'=>'GGY', 'name'=>'Guernsey'),
		array('iso'=>'GIN', 'name'=>'Guinea'),
		array('iso'=>'GNB', 'name'=>'Guinea-Bissau'),
		array('iso'=>'GUY', 'name'=>'Guyana'),
		array('iso'=>'HTI', 'name'=>'Haiti'),
		array('iso'=>'HND', 'name'=>'Honduras'),
		array('iso'=>'HKG', 'name'=>'Hong Kong'),
		array('iso'=>'HUN', 'name'=>'Hungary'),
		array('iso'=>'ISL', 'name'=>'Iceland'),
		array('iso'=>'IND', 'name'=>'India'),
		array('iso'=>'IDN', 'name'=>'Indonesia'),
		array('iso'=>'IRN', 'name'=>'Iran'),
		array('iso'=>'IRQ', 'name'=>'Iraq'),
		array('iso'=>'IRL', 'name'=>'Ireland'),
		array('iso'=>'IMN', 'name'=>'Isle of Man'),
		array('iso'=>'ISR', 'name'=>'Israel'),
		array('iso'=>'ITA', 'name'=>'Italy'),
		array('iso'=>'JAM', 'name'=>'Jamaica'),
		array('iso'=>'JPN', 'name'=>'Japan'),
		array('iso'=>'JEY', 'name'=>'Jersey'),
		array('iso'=>'JOR', 'name'=>'Jordan'),
		array('iso'=>'KAZ', 'name'=>'Kazakhstan'),
		array('iso'=>'KEN', 'name'=>'Kenya'),
		array('iso'=>'KIR', 'name'=>'Kiribati'),
		array('iso'=>'PRK', 'name'=>'Korea, North'),
		array('iso'=>'KOR', 'name'=>'Korea, South'),
		array('iso'=>'KWT', 'name'=>'Kuwait'),
		array('iso'=>'KGZ', 'name'=>'Kyrgyzstan'),
		array('iso'=>'LAO', 'name'=>'Laos'),
		array('iso'=>'LVA', 'name'=>'Latvia'),
		array('iso'=>'LBN', 'name'=>'Lebanon'),
		array('iso'=>'LSO', 'name'=>'Lesotho'),
		array('iso'=>'LBR', 'name'=>'Liberia'),
		array('iso'=>'LBY', 'name'=>'Libya'),
		array('iso'=>'LIE', 'name'=>'Liechtenstein'),
		array('iso'=>'LTU', 'name'=>'Lithuania'),
		array('iso'=>'LUX', 'name'=>'Luxembourg'),
		array('iso'=>'MAC', 'name'=>'Macau'),
		array('iso'=>'MKD', 'name'=>'Macedonia'),
		array('iso'=>'MDG', 'name'=>'Madagascar'),
		array('iso'=>'MWI', 'name'=>'Malawi'),
		array('iso'=>'MYS', 'name'=>'Malaysia'),
		array('iso'=>'MDV', 'name'=>'Maldives'),
		array('iso'=>'MLI', 'name'=>'Mali'),
		array('iso'=>'MLT', 'name'=>'Malta'),
		array('iso'=>'MHL', 'name'=>'Marshall Islands'),
		array('iso'=>'MTQ', 'name'=>'Martinique'),
		array('iso'=>'MRT', 'name'=>'Mauritania'),
		array('iso'=>'MUS', 'name'=>'Mauritius'),
		array('iso'=>'MYT', 'name'=>'Mayotte'),
		array('iso'=>'MEX', 'name'=>'Mexico'),
		array('iso'=>'FSM', 'name'=>'Micronesia'),
		array('iso'=>'MDA', 'name'=>'Moldova'),
		array('iso'=>'MCO', 'name'=>'Monaco'),
		array('iso'=>'MNG', 'name'=>'Mongolia'),
		array('iso'=>'MSR', 'name'=>'Montserrat'),
		array('iso'=>'MAR', 'name'=>'Morocco'),
		array('iso'=>'MOZ', 'name'=>'Mozambique'),
		array('iso'=>'MMR', 'name'=>'Myanmar'),
		array('iso'=>'NAM', 'name'=>'Namibia'),
		array('iso'=>'NRU', 'name'=>'Nauru'),
		array('iso'=>'NPL', 'name'=>'Nepal'),
		array('iso'=>'NLD', 'name'=>'Netherlands'),
		array('iso'=>'ANT', 'name'=>'Netherlands Antilles'),
		array('iso'=>'NCL', 'name'=>'New Caledonia'),
		array('iso'=>'NZL', 'name'=>'New Zealand'),
		array('iso'=>'NIC', 'name'=>'Nicaragua'),
		array('iso'=>'NIU', 'name'=>'Niue'),
		array('iso'=>'NER', 'name'=>'Niger'),
		array('iso'=>'NGA', 'name'=>'Nigeria'),
		array('iso'=>'NFK', 'name'=>'Norfolk Island'),
		array('iso'=>'MNP', 'name'=>'Northern Mariana Islands'),
		array('iso'=>'NOR', 'name'=>'Norway'),
		array('iso'=>'OMN', 'name'=>'Oman'),
		array('iso'=>'PAK', 'name'=>'Pakistan'),
		array('iso'=>'PLW', 'name'=>'Palau'),
		array('iso'=>'PSE', 'name'=>'Palestinian Territory, Occupied'),
		array('iso'=>'PAN', 'name'=>'Panama'),
		array('iso'=>'PNG', 'name'=>'Papua New Guinea'),
		array('iso'=>'PRY', 'name'=>'Paraguay'),
		array('iso'=>'PER', 'name'=>'Peru'),
		array('iso'=>'PHL', 'name'=>'Philippines'),
		array('iso'=>'PCN', 'name'=>'Pitcairn Island'),
		array('iso'=>'POL', 'name'=>'Poland'),
		array('iso'=>'PRT', 'name'=>'Portugal'),
		array('iso'=>'PRI', 'name'=>'Puerto Rico'),
		array('iso'=>'QAT', 'name'=>'Qatar'),
		array('iso'=>'REU', 'name'=>'Reunion'),
		array('iso'=>'ROU', 'name'=>'Romania'),
		array('iso'=>'RUS', 'name'=>'Russia'),
		array('iso'=>'RWA', 'name'=>'Rwanda'),
		array('iso'=>'BLM', 'name'=>'Saint Barthelemy'),
		array('iso'=>'SHN', 'name'=>'Saint Helena'),
		array('iso'=>'KNA', 'name'=>'Saint Kitts and Nevis'),
		array('iso'=>'LCA', 'name'=>'Saint Lucia'),
		array('iso'=>'MAF', 'name'=>'Saint Martin'),
		array('iso'=>'SPM', 'name'=>'Saint Pierre and Miquelon'),
		array('iso'=>'VCT', 'name'=>'Saint Vincent and the Grenadines'),
		array('iso'=>'WSM', 'name'=>'Samoa'),
		array('iso'=>'SMR', 'name'=>'San Marino'),
		array('iso'=>'STP', 'name'=>'Sao Tome and Principe'),
		array('iso'=>'SAU', 'name'=>'Saudia Arabia'),
		array('iso'=>'SEN', 'name'=>'Senegal'),
		array('iso'=>'SCG', 'name'=>'Serbia'),
		array('iso'=>'SYC', 'name'=>'Seychelles'),
		array('iso'=>'SLE', 'name'=>'Sierra Leone'),
		array('iso'=>'SGP', 'name'=>'Singapore'),
		array('iso'=>'SVK', 'name'=>'Slovakia'),
		array('iso'=>'SVN', 'name'=>'Slovenia'),
		array('iso'=>'SLB', 'name'=>'Solomon Islands'),
		array('iso'=>'SOM', 'name'=>'Somalia'),
		array('iso'=>'ZAF', 'name'=>'South Africa'),
		array('iso'=>'ESP', 'name'=>'Spain'),
		array('iso'=>'LKA', 'name'=>'Sri Lanka'),
		array('iso'=>'SDN', 'name'=>'Sudan'),
		array('iso'=>'SUR', 'name'=>'Suriname'),
		array('iso'=>'SJM', 'name'=>'Svalbard and Jan Mayen Islands'),
		array('iso'=>'SWZ', 'name'=>'Swaziland'),
		array('iso'=>'SWE', 'name'=>'Sweden'),
		array('iso'=>'CHE', 'name'=>'Switzerland'),
		array('iso'=>'SYR', 'name'=>'Syria'),
		array('iso'=>'TWN', 'name'=>'Taiwan'),
		array('iso'=>'TJK', 'name'=>'Tajikistan'),
		array('iso'=>'TZA', 'name'=>'Tanzania'),
		array('iso'=>'THA', 'name'=>'Thailand'),
		array('iso'=>'TLS', 'name'=>'Timor-Leste'),
		array('iso'=>'TGO', 'name'=>'Togo'),
		array('iso'=>'TON', 'name'=>'Tonga'),
		array('iso'=>'TTO', 'name'=>'Trinidad and Tobago'),
		array('iso'=>'TUN', 'name'=>'Tunisia'),
		array('iso'=>'TUR', 'name'=>'Turkey'),
		array('iso'=>'TKM', 'name'=>'Turkmenistan'),
		array('iso'=>'TCA', 'name'=>'Turks and Caicos Islands'),
		array('iso'=>'TUV', 'name'=>'Tuvalu'),
		array('iso'=>'UGA', 'name'=>'Uganda'),
		array('iso'=>'UKR', 'name'=>'Ukraine'),
		array('iso'=>'ARE', 'name'=>'United Arab Emirates'),
		array('iso'=>'GBR', 'name'=>'United Kingdom'),
		array('iso'=>'USA', 'name'=>'United States of America'),
		array('iso'=>'VIR', 'name'=>'United States Virgin Islands'),
		array('iso'=>'URY', 'name'=>'Uruguay'),
		array('iso'=>'SUN', 'name'=>'USSR'),
		array('iso'=>'UZB', 'name'=>'Uzbekistan'),
		array('iso'=>'VUT', 'name'=>'Vanuatu'),
		array('iso'=>'VAT', 'name'=>'Vatican City State (Holy See)'),
		array('iso'=>'VEN', 'name'=>'Venezuela'),
		array('iso'=>'VNM', 'name'=>'Vietnam'),
		array('iso'=>'WLF', 'name'=>'Wallis and Futuna Islands'),
		array('iso'=>'ESH', 'name'=>'Western Sahara'),
		array('iso'=>'YEM', 'name'=>'Yemen'),
		array('iso'=>'YUG', 'name'=>'Yugoslavia'),
		array('iso'=>'ZMB', 'name'=>'Zambia'),
		array('iso'=>'ZWE', 'name'=>'Zimbabwe')
	);
	// }
	function __construct($countries=array()) {
		$this->disabled=array();
		$this->selected=array();
		if (!count($countries)) {
			$this->_loadDefaultCountries();
		}
		else {
			$this->_buildIsoNameHash();
			$arr=array();
			foreach ($countries as $country) {
				if (isset($this->isoNameHash[$country])) {
					$arr[$country]=$this->isoNameHash[$country];
				}
			}
			asort($arr);
			$this->countries=array(array('iso'=>'', 'name'=>'--Please Choose--'));
			foreach ($arr as $key=>$val) {
				$this->countries[]=array('iso'=>$key, 'name'=>$val);
			}
		}
	}
	private function _buildIsoNameHash() {	// utility function
		if (isset($this->isoNameHash)) {
			return;
		}
		$this->isoNameHash=array();
		$list=$this->countriesList;
		foreach ($list as $country) {
			$this->isoNameHash[$country['iso']]=$country['name'];
		}
	}
	private function _loadDefaultCountries() {
		$this->countries=$this->countriesList;
	}
	static function iso3_to_iso2($iso) {
		// { translation array
		$arr=array(
			'AFG'=>'AF', 'ALA'=>'AX', 'ALB'=>'AL', 'DZA'=>'DZ', 'ASM'=>'AS',
			'AND'=>'AD', 'AGO'=>'AO', 'AIA'=>'AI', 'ATG'=>'AG', 'ARG'=>'AR',
			'ARM'=>'AM', 'ABW'=>'AW', 'AUS'=>'AU', 'AUT'=>'AT', 'AZE'=>'AZ',
			'BHS'=>'BS', 'BHR'=>'BH', 'BGD'=>'BD', 'BRB'=>'BB', 'BLR'=>'BY',
			'BEL'=>'BE', 'BLZ'=>'BZ', 'BEN'=>'BJ', 'BMU'=>'BM', 'BTN'=>'BT',
			'BOL'=>'BO', 'BIH'=>'BA', 'BWA'=>'BW', 'BRA'=>'BR', 'VGB'=>'IO',
			'BRN'=>'BN', 'BGR'=>'BG', 'BFA'=>'BF', 'BDI'=>'BI', 'KHM'=>'KH',
			'CMR'=>'CM', 'CAN'=>'CA', 'CPV'=>'CV', 'CYM'=>'KY', 'CAF'=>'CF',
			'TCD'=>'TD', 'CHL'=>'CL', 'CHN'=>'CN', 'COL'=>'CO', 'COM'=>'KM',
			'COG'=>'CG', 'COD'=>'CD', 'COK'=>'CK', 'CRI'=>'CR', 'CIV'=>'CI',
			'HRV'=>'HR', 'CUB'=>'CU', 'CYP'=>'CY', 'CZE'=>'CZ', 'DNK'=>'DK',
			'DJI'=>'DJ', 'DMA'=>'DM', 'DOM'=>'DO', 'ECU'=>'EC', 'EGY'=>'EG',
			'SLV'=>'SV', 'GNQ'=>'GQ', 'ERI'=>'ER', 'EST'=>'EE', 'ETH'=>'ET',
			'FLK'=>'FK', 'FRO'=>'FO', 'FJI'=>'FJ', 'FIN'=>'FI', 'FRA'=>'FR',
			'GUF'=>'GF', 'PYF'=>'PF', 'GAB'=>'GA', 'GMB'=>'GM', 'GEO'=>'GE',
			'DEU'=>'DE', 'GHA'=>'GH', 'GIB'=>'GI', 'GRC'=>'GR', 'GRL'=>'GL',
			'GRD'=>'GD', 'GLP'=>'GP', 'GUM'=>'GU', 'GTM'=>'GT', 'GGY'=>'GG',
			'GIN'=>'GN', 'GNB'=>'GW', 'GUY'=>'GY', 'HTI'=>'HT', 'HND'=>'HN',
			'HKG'=>'HK', 'HUN'=>'HU', 'ISL'=>'IS', 'IND'=>'IN', 'IDN'=>'ID',
			'IRN'=>'IR', 'IRQ'=>'IQ', 'IRL'=>'IE', 'IMN'=>'IM', 'ISR'=>'IL',
			'ITA'=>'IT', 'JAM'=>'JM', 'JPN'=>'JP', 'JEY'=>'JE', 'JOR'=>'JO',
			'KAZ'=>'KZ', 'KEN'=>'KE', 'KIR'=>'KI', 'PRK'=>'KP', 'KOR'=>'KR',
			'KWT'=>'KW', 'KGZ'=>'KG', 'LAO'=>'LA', 'LVA'=>'LV', 'LBN'=>'LB',
			'LSO'=>'LS', 'LBR'=>'LR', 'LBY'=>'LY', 'LIE'=>'LI', 'LTU'=>'LT',
			'LUX'=>'LU', 'MAC'=>'MO', 'MKD'=>'MK', 'MDG'=>'MG', 'MWI'=>'MW',
			'MYS'=>'MY', 'MDV'=>'MV', 'MLI'=>'ML', 'MLT'=>'MT', 'MHL'=>'MH',
			'MTQ'=>'MQ', 'MRT'=>'MR', 'MUS'=>'MU', 'MYT'=>'YT', 'MEX'=>'MX',
			'FSM'=>'FM', 'MDA'=>'MD', 'MCO'=>'MC', 'MNG'=>'MN', '1;1'=>';;',
			'MSR'=>'MS', 'MAR'=>'MA', 'MOZ'=>'MZ', 'MMR'=>'MM', 'NAM'=>'NA',
			'NRU'=>'NR', 'NPL'=>'NP', 'NLD'=>'NL', 'ANT'=>'AN', 'NCL'=>'NC',
			'NZL'=>'NZ', 'NIC'=>'NI', 'NIU'=>'NU', 'NER'=>'NE', 'NGA'=>'NG',
			'NFK'=>'NF', 'MNP'=>'MP', 'NOR'=>'NO', 'OMN'=>'OM', 'PAK'=>'PK',
			'PLW'=>'PW', 'PSE'=>'PS', 'PAN'=>'PA', 'PNG'=>'PG', 'PRY'=>'PY',
			'PER'=>'PE', 'PHL'=>'PH', 'PCN'=>'PN', 'POL'=>'PL', 'PRT'=>'PT',
			'PRI'=>'PR', 'QAT'=>'QA', 'REU'=>'RE', 'ROU'=>'RO', 'RUS'=>'RU',
			'RWA'=>'RW', 'BLM'=>'BL', 'SHN'=>'SH', 'KNA'=>'KN', 'LCA'=>'LC',
			'MAF'=>'MF', 'SPM'=>'PM', 'VCT'=>'VC', 'WSM'=>'WS', 'SMR'=>'SM',
			'STP'=>'ST', 'SAU'=>'SA', 'SEN'=>'SN', 'SCG'=>'CS', 'SYC'=>'SC',
			'SLE'=>'SL', 'SGP'=>'SG', 'SVK'=>'SK', 'SVN'=>'SI', 'SLB'=>'SB',
			'SOM'=>'SO', 'ZAF'=>'ZA', 'ESP'=>'ES', 'LKA'=>'LK', 'SDN'=>'SD',
			'SUR'=>'SR', 'SJM'=>'SJ', 'SWZ'=>'SZ', 'SWE'=>'SE', 'CHE'=>'CH',
			'SYR'=>'SY', 'TWN'=>'TW', 'TJK'=>'TJ', 'TZA'=>'TZ', 'THA'=>'TH',
			'TLS'=>'TL', 'TGO'=>'TG', 'TON'=>'TO', 'TTO'=>'TT', 'TUN'=>'TN',
			'TUR'=>'TR', 'TKM'=>'TM', 'TCA'=>'TC', 'TUV'=>'TV', 'UGA'=>'UG',
			'UKR'=>'UA', 'ARE'=>'AE', 'GBR'=>'GB', 'USA'=>'US', 'VIR'=>'VI',
			'URY'=>'UY', 'SUN'=>'SU', 'UZB'=>'UZ', 'VUT'=>'VU', 'VAT'=>'VA',
			'VEN'=>'VE', 'VNM'=>'VN', 'WLF'=>'WF', 'ESH'=>'EH', 'YEM'=>'YE',
			'YUG'=>'YU', 'ZMB'=>'ZM', 'ZWE'=>'ZW'
		);
		// }
		return $arr[$iso];
	}
	function getCountryName($iso) {
		if (!isset($this->isoNameHash)) {
			$this->_buildIsoNameHash();
		}
		return $this->isoNameHash[$iso];
	}
	function getPhoneExtensionsSelectbox($name, $max_name_length=20, $multiple=0) {
		// { list of extensions
		$extensions=array(
			'ABW'=>'297', 'AFG'=>'93', 'AGO'=>'244', 'AIA'=>'264', 'ALB'=>'355',
			'AND'=>'376', 'ANT'=>'599', 'ARE'=>'971', 'ARG'=>'54', 'ARM'=>'374',
			'ASM'=>'684', 'ATG'=>'268', 'AUS'=>'61', 'AUT'=>'43', 'AZE'=>'994',
			'BDI'=>'257', 'BEL'=>'32', 'BEN'=>'229', 'BFA'=>'226', 'BGD'=>'880',
			'BGR'=>'359', 'BHR'=>'973', 'BHS'=>'242', 'BIH'=>'387', 'BLR'=>'375',
			'BLZ'=>'501', 'BMU'=>'441', 'BOL'=>'591', 'BRA'=>'55', 'BRB'=>'246',
			'BRN'=>'673', 'BTN'=>'975', 'BWA'=>'267', 'CAF'=>'236', 'CAN'=>'1',
			'CHE'=>'41', 'CHL'=>'56', 'CHN'=>'86', 'CIV'=>'225', 'CMR'=>'237',
			'COD'=>'243', 'COG'=>'242', 'COK'=>'682', 'COL'=>'57', 'COM'=>'269',
			'CPV'=>'238', 'CRI'=>'506', 'CUB'=>'53', 'CYM'=>'345', 'CYP'=>'357',
			'CZE'=>'420', 'DEU'=>'49', 'DJI'=>'253', 'DMA'=>'767', 'DNK'=>'45',
			'DOM'=>'1809', 'DZA'=>'213', 'ECU'=>'593', 'EGY'=>'20', 'ERI'=>'291',
			'ESP'=>'34', 'EST'=>'372', 'ETH'=>'251', 'FIN'=>'358', 'FJI'=>'679',
			'FLK'=>'500', 'FRA'=>'33', 'FRO'=>'298', 'FSM'=>'691', 'GAB'=>'241',
			'GBR'=>'44', 'GEO'=>'995', 'GGY'=>'502', 'GHA'=>'233', 'GIB'=>'350',
			'GIN'=>'224', 'GLP'=>'590', 'GMB'=>'220', 'GNB'=>'245', 'GNQ'=>'240',
			'GRC'=>'30', 'GRD'=>'473', 'GRL'=>'299', 'GTM'=>'5399', 'GUF'=>'594',
			'GUM'=>'671', 'GUY'=>'592', 'HKG'=>'852', 'HND'=>'504', 'HRV'=>'385',
			'HTI'=>'509', 'HUN'=>'36', 'IDN'=>'62', 'IND'=>'91', 'IRL'=>'353',
			'IRN'=>'98', 'IRQ'=>'964', 'ISL'=>'354', 'ISR'=>'972', 'ITA'=>'39',
			'JAM'=>'876', 'JOR'=>'962', 'JPN'=>'81', 'KAZ'=>'7', 'KEN'=>'254',
			'KGZ'=>'996', 'KHM'=>'855', 'KIR'=>'686', 'KNA'=>'869', 'KOR'=>'82',
			'KWT'=>'965', 'LAO'=>'856', 'LBN'=>'961', 'LBR'=>'231', 'LBY'=>'218',
			'LCA'=>'758', 'LIE'=>'423', 'LKA'=>'94', 'LSO'=>'266', 'LTU'=>'370',
			'LUX'=>'352', 'LVA'=>'371', 'MAC'=>'853', 'MAR'=>'212', 'MCO'=>'377',
			'MDA'=>'373', 'MDG'=>'261', 'MDV'=>'960', 'MEX'=>'52', 'MHL'=>'692',
			'MKD'=>'389', 'MLI'=>'223', 'MLT'=>'356', 'MMR'=>'95', 'MNG'=>'976',
			'MNP'=>'670', 'MOZ'=>'258', 'MRT'=>'222', 'MSR'=>'664', 'MTQ'=>'596',
			'MUS'=>'230', 'MWI'=>'265', 'MYS'=>'60', 'MYT'=>'269', 'NAM'=>'264',
			'NCL'=>'687', 'NER'=>'227', 'NFK'=>'672', 'NGA'=>'234', 'NIC'=>'505',
			'NIU'=>'683', 'NLD'=>'31', 'NOR'=>'47', 'NPL'=>'977', 'NRU'=>'674',
			'NZL'=>'64', 'OMN'=>'968', 'PAK'=>'92', 'PAN'=>'507', 'PER'=>'51',
			'PHL'=>'63', 'PLW'=>'680', 'PNG'=>'675', 'POL'=>'48', 'PRI'=>'1787',
			'PRK'=>'850', 'PRT'=>'351', 'PRY'=>'595', 'PSE'=>'970', 'PYF'=>'689',
			'QAT'=>'974', 'REU'=>'262', 'ROU'=>'40', 'RUS'=>'7', 'RWA'=>'250',
			'SAU'=>'966', 'SCG'=>'381', 'SDN'=>'249', 'SEN'=>'221', 'SGP'=>'65',
			'SHN'=>'290', 'SLB'=>'677', 'SLE'=>'232', 'SLV'=>'503', 'SMR'=>'378',
			'SOM'=>'252', 'SPM'=>'508', 'STP'=>'239', 'SUR'=>'597', 'SVK'=>'421',
			'SVN'=>'386', 'SWE'=>'46', 'SWZ'=>'268', 'SYC'=>'248', 'SYR'=>'963',
			'TCA'=>'649', 'TCD'=>'235', 'TGO'=>'228', 'THA'=>'66', 'TJK'=>'992',
			'TKM'=>'993', 'TLS'=>'670', 'TON'=>'676', 'TTO'=>'868', 'TUN'=>'216',
			'TUR'=>'90', 'TUV'=>'688', 'TWN'=>'886', 'TZA'=>'255', 'UGA'=>'256',
			'UKR'=>'380', 'URY'=>'598', 'USA'=>'1', 'UZB'=>'998', 'VAT'=>'379',
			'VCT'=>'784', 'VEN'=>'58', 'VGB'=>'284', 'VIR'=>'340', 'VNM'=>'84',
			'VUT'=>'678', 'WLF'=>'681', 'WSM'=>'685', 'YEM'=>'967', 'ZAF'=>'27',
			'ZMB'=>'260', 'ZWE'=>'263'
		);
		// }
		$c='<select name="'.htmlspecialchars($name)
			.'" class="countryExtensions_list"';
		if ($this->onChange) {
			$c.=' onchange="'.htmlspecialchars($this->onChange).'"';
		}
		if ($multiple) {
			$c.=' multiple="multiple" size="'.$multiple.'"';
		}
		$c.='>';
		foreach ($this->countries as $country) {
			$iso=$country['iso'];
			$extension=(int)$extensions[$iso];
			if (!$extension) {
				continue;
			}
			$c.='<option value="'.$extension.'"';
			if (in_array($iso, $this->selected)) {
				$c.=' selected="selected"';
			}
			if (in_array($iso, $this->disabled)) {
				$c.=' disabled="disabled"';
			}
			$c.='>'.htmlspecialchars($country['iso']).' ('.$extension.')</option>';
		}
		$c.='</select>';
		return $c;
	}
	function getSelectbox($name, $max_name_length=20, $multiple=0) {
		$c='<select name="'.htmlspecialchars($name).'" class="countries_list"';
		if ($this->onChange) {
			$c.=' onchange="'.htmlspecialchars($this->onChange).'"';
		}
		if ($multiple) {
			$c.=' multiple="multiple" size="'.$multiple.'"';
		}
		$c.='>';
		foreach ($this->countries as $country) {
			$c.='<option value="'.$country['iso'].'"';
			if (in_array($country['iso'], $this->selected)) {
				$c.=' selected="selected"';
			}
			if (in_array($country['iso'], $this->disabled)) {
				$c.=' disabled="disabled"';
			}
			$n=$country['name'];
			if (strlen($n)>$max_name_length) {
				$c.=' title="'.htmlspecialchars($n).'"';
				$n=substr($n, 0, $max_name_length-3).'...';
			}
			$c.='>'.htmlspecialchars($n).'</option>';
		}
		$c.='</select>';
		return $c;
	}
	function setDisabled($arr) {
		$this->disabled=array_merge($this->disabled, $arr);
	}
	function setOnChange($val) {
		$this->onChange=$val;
	}
	function setSelected($arr) {
		if (!is_array($arr)) {
			return $this->setSelected(array($arr));
		}
		$this->selected=array_merge($this->selected, $arr);
	}
};
