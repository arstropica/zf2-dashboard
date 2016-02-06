<?php
return array (
		'elastica' => array (
				'clients' => array (
						'default' => array (
								'host' => '127.0.0.1',
								'port' => 9200 
						) 
				),
				'entities' => array (
						'paths' => array (
								'Lead\Entity\LeadAttribute' => __DIR__ . '/../../module/Lead/src/Lead/Entity/',
								'Lead\Entity\LeadAttributeValue' => __DIR__ . '/../../module/Lead/src/Lead/Entity/',
								'Lead\Entity\Lead' => __DIR__ . '/../../module/Lead/src/Lead/Entity/',
								'Account\Entity\Account' => __DIR__ . '/../../module/Account/src/Account/Entity/',
								'Agent\Entity\Geo\Locality' => __DIR__ . '/../../module/Agent/src/Agent/Entity/Geo/' 
						) 
				),
				'indices' => array (
						'reports' => array (
								'client' => 'default',
								'settings' => array (
										'index.cache.query.enable' => true,
										"index.search.slowlog.threshold.query.warn" => "10s",
										"index.search.slowlog.threshold.fetch.debug" => "500ms",
										"index.indexing.slowlog.threshold.index.info" => "5s",
										"number_of_shards" => 10,
										"number_of_replicas" => 0 
								),
								'types' => array (
										'attribute' => array (
												'ns' => 'Lead\Entity\LeadAttribute' 
										),
										'value' => array (
												'ns' => 'Lead\Entity\LeadAttributeValue' 
										),
										'lead' => array (
												'ns' => 'Lead\Entity\Lead' 
										),
										'account' => array (
												'ns' => 'Account\Entity\Account' 
										) 
								) 
						) 
				),
				'serialization' => array (
						'groups' => array (
								'attributes',
								'list',
								'details',
								'geo' 
						) 
				) 
		) 
);