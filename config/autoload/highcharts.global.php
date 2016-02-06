<?php
return array (
		'highcharts' => array (
				'options' => array (
						'global' => array (
								'credits' => [ 
										'enabled' => false 
								] 
						),
						'types' => array (
								'solidgauge' => array (
										'chart' => array (
												'type' => 'solidgauge' 
										),
										'title' => NULL,
										'pane' => array (
												'center' => array (
														0 => '50%',
														1 => '85%' 
												),
												'size' => '140%',
												'startAngle' => -90,
												'endAngle' => 90,
												'background' => array (
														'backgroundColor' => '#EEE',
														'innerRadius' => '60%',
														'outerRadius' => '100%',
														'shape' => 'arc' 
												) 
										),
										'tooltip' => array (
												'enabled' => false 
										),
										'yAxis' => array (
												'stops' => array (
														0 => array (
																0 => 0.1,
																1 => '#DF5353' 
														),
														1 => array (
																0 => 0.5,
																1 => '#DDDF0D' 
														),
														2 => array (
																0 => 0.9,
																1 => '#55BF3B' 
														) 
												),
												'lineWidth' => 0,
												'minorTickInterval' => null,
												'tickPixelInterval' => 400,
												'tickWidth' => 0,
												'title' => array (
														'y' => -140,
														'style' => array (
																'font-size' => '14px' 
														) 
												),
												'labels' => array (
														'y' => 16 
												),
												'min' => 0,
												'max' => 100,
												'title' => array (
														'text' => "" 
												) 
										),
										'plotOptions' => array (
												'solidgauge' => array (
														'dataLabels' => array (
																'y' => 5,
																'borderWidth' => 0,
																'useHTML' => true 
														) 
												) 
										),
										'series' => array (
												array (
														'name' => null,
														'data' => array (),
														'dataLabels' => array (
																'format' => '<div style="text-align:center"><span style="font-size:25px;color:black">{y}</span><br/><span style="font-size:12px;color:silver">units</span></div>' 
														),
														'tooltip' => array (
																'valueSuffix' => '' 
														) 
												) 
										) 
								) 
						) 
				) 
		) 
);