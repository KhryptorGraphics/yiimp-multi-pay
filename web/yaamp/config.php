<?php

return array(
	'name'=>YAAMP_SITE_URL,

	'defaultController'=>'site',
	'layout'=>'main',

	'basePath'=>YAAMP_HTDOCS."/yaamp",
	'extensionPath'=>YAAMP_HTDOCS.'/extensions',
	'controllerPath'=>'modules',
	'viewPath'=>'modules',
	'layoutPath'=>'ui',

	'preload'=>array('log'),
	'import'=>array(
		'application.components.*',
		'application.models.*',
	),

	'aliases'=>array(
		'modules'=>YAAMP_HTDOCS.'/yaamp/modules',
	),

	'controllerMap'=>array(
		'site'=>array(
			'class'=>'modules.site.SiteController',
		),
		'api'=>array(
			'class'=>'modules.api.ApiController',
		),
		'stats'=>array(
			'class'=>'modules.stats.StatsController',
		),
		'trading'=>array(
			'class'=>'modules.trading.TradingController',
		),
		'bench'=>array(
			'class'=>'modules.bench.BenchController',
		),
		'coin'=>array(
			'class'=>'modules.coin.CoinController',
		),
		'nicehash'=>array(
			'class'=>'modules.nicehash.NicehashController',
		),
		'market'=>array(
			'class'=>'modules.market.MarketController',
		),
		'admin'=>array(
			'class'=>'modules.admin.AdminController',
		),
		'explorer'=>array(
			'class'=>'modules.explorer.ExplorerController',
		),
	),

	'components'=>array(

		'urlManager'=>array(
			'urlFormat'=>'path',
			'showScriptName'=>false,
			'appendParams'=>true,
			'caseSensitive'=>true,
			'rules'=>array(
				'' => 'site/index',
				'site/<action:\w+>' => 'site/<action>',
				'<controller:\w+>/<action:\w+>' => '<controller>/<action>',
				'<controller:\w+>' => '<controller>',
			),
		),

		'assetManager'=>array(
			'basePath'=>YAAMP_HTDOCS."/assets",
			'baseUrl'=>'/assets',
		),

		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
			),
		),

		'cache'=>array(
			'class'=>'CMemCache',
			'servers'=>array(
				array(
					'host'=>YIIMP_MEMCACHE_HOST,
					'port'=>YIIMP_MEMCACHE_PORT,
					'weight'=>100,
				),
			),
		),

		'db'=>array(
			'connectionString'=>'mysql:host='.YIIMP_DBHOST.';dbname='.YIIMP_DBNAME,
			'emulatePrepare'=>true,
			'autoConnect'=>false,
			'username'=>YIIMP_DBUSER,
			'password'=>YIIMP_DBPASSWORD,
			'charset'=>'utf8',
			'tablePrefix'=>'yaamp_',
			'enableParamLogging'=>true,
		),
	),

	'params'=>array(),
);
