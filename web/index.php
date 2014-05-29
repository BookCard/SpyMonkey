<?php
/**
 * Autoload Composer
 **/
require dirname(__DIR__).'/vendor/autoload.php';

/**
 * App Slim
 **/
$app = new \Slim\Slim();

/**
 * Routers
 **/
$app->get("/", function () use ($app){
	$app->response->headers->set('Content-Type', 'application/json');
	$app->response->setStatus(200);
	try{

		$Monkey = new \SpyMonkey\SpyMonkey(new \PDO('mysql:host=localhost;dbname=spo', 'root', 123456789));


		#is bad
		#echo $Monkey->setResource("oferta")->setField("id")->setValue(1)->build();

		# is very cool
		#$cards = $Monkey->about("oferta")->with("id")->equals(1)->whatYouSee();

		# is very inteligent
		echo $Monkey->about("oferta")->between(0,10)->with("imagem")->different(NULL)->build();


		$app->response->write("");
	}catch(\LogicException $e){
		$app->response->write($e);
		$app->response->setStatus(400);
	}catch(\Exception $e){
		$app->response->write($e);
		$app->response->setStatus(500);
	}
});

/**
 * Bootstrap Application and Run
 **/
$app->run();