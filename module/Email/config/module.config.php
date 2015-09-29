<?php
return array(
		'controllers' => array(
				'invokables' => array(
						'Email\Controller\Email' => 'Email\Controller\EmailController'
				)
		),
		'service_manager' => array(
				'factories' => array(
						'Email\Service\SendMail' => 'Email\Service\Factory\SendMailServiceFactory'
				)
		)
);
