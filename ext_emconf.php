<?php
$EM_CONF[$_EXTKEY] = array(
	'title' => 'Image Cycle',
	'description' => 'Insert a slideshow into your page or template. Manage the images, captions and hrefs recursively in the pagetree and show it in a jQuery-Cycle, Coin-Slider, Nivo-Slider or Cross-Slider. Use t3jquery for better integration with other jQuery extensions.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '3.1.3',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => 'uploads/tx_imagecycle',
	'modify_tables' => '',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Juergen Furrer',
	'author_email' => 'juergen.furrer@gmail.com',
	'author_company' => '',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'php' => '5.5.0-0.0.0',
			'typo3' => '7.6.0-8.7.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'jftcaforms' => '0.2.1-0.0.0',
		),
	),
	'autoload' => array(
		'psr-4' => array(
			'TYPO3Extension\\Imagecycle\\' => 'Classes',
		),
	),
);
