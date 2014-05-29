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
	$app->response->headers->setStatus(200);
	try{

		$Monkey = new \SpyMonkey\SpyMonkey();

		#is bad
		$cards = $Monkey->setResource("oferta")->setField("id")->setValue(1)->consult();

		# is very cool
		$cards = $Monkey->about("oferta")->with("id")->equals(1)->whatYouSee();

		# is very inteligent
		$cards = $Monkey->about("oferta")->between(0,10)->with("imagem")->different(NULL)->whatYouSee();


		$app->response->write($return);
	}catch(\LogicException $e){
		$app->response->setStatus(400);
	}catch(\Exception $e){
		$app->response->setStatus(500);
	}
});

/**
 * Bootstrap Application and Run
 **/
$app->run();