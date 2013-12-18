<?php
/**
 * Step 1: Require the Slim Framework
 *
 * If you are not using Composer, you need to require the
 * Slim Framework and register its PSR-0 autoloader.
 *
 * If you are using Composer, you can skip this step.
 */
require 'Slim/Slim.php';
require 'RedBean/rb.php';

//database connection
R::setup('mysql:host=localhost;dbname=loan','root','m3ongaja');
R::freeze(true);

\Slim\Slim::registerAutoloader();

class ResourceNotFoundException extends Exception {}

/**
 * Step 2: Instantiate a Slim application
 *
 * This example instantiates a Slim application using
 * its default settings. However, you will usually configure
 * your Slim application now by passing an associative array
 * of setting names and values into the application constructor.
 */
$app = new \Slim\Slim();

/**
 * Step 3: Define the Slim application routes
 *
 * Here we define several Slim application routes that respond
 * to appropriate HTTP request methods. In this example, the second
 * argument for `Slim::get`, `Slim::post`, `Slim::put`, `Slim::patch`, and `Slim::delete`
 * is an anonymous function.
 */

// GET route
$app->get('/', function () {
  
});

// POST route
$app->post('/api', function () use($app){
  // get and decode JSON request body
  $request = $app->request();
  // store article record
  R::setStrictTyping(false);
  $book = R::dispense('loan_book');
  $book->name = (string)$request->post('name');
  $book->price = (int)$request->post('price');
  $id = R::store($book);
  // return JSON-encoded response body
  $app->response()->header('Content-Type', 'application/json');
  echo json_encode(R::exportAll($book));
});

//update data
$app->post('/api/:id', function ($id) use($app) {
    $request = $app->request();

    $book = R::findOne('loan_book', 'id=?', array($id));
    if ($book) {      
      $book->name = (string)$request->put('name');
      $book->price = (int)$request->put('price');
      R::store($book);
      $app->response()->header('Content-Type', 'application/json');
      echo json_encode(R::exportAll($book));
    } else {
      $app->response()->header('Content-Type', 'application/json');
      echo json_encode(array('error' => 'not found'));
    }
});

//delete id
$app->delete('/api/:id', function ($id) use ($app) {
   $book = R::findOne('loan_book', 'id=?', array($id)); 
   if($book){
    R::trash($book);
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode(array('error' => 'deleted!!'));
   }else{
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode(array('error' => 'cannot delete'));
   }
});

//get all data
$app->get('/api', function () use ($app) {
  $book = R::find('loan_book');
  $app->response()->header('Content-Type', 'application/json');
  echo json_encode(R::exportAll($book));
});

//get data with id
$app->get('/api/:id', function ($id) use ($app) {
  $book = R::findOne('loan_book', 'id=?', array($id));
  if($book){
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode(R::exportAll($book));
  }else{
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode(array('error' => 'not found'));
  }
});

$app->run();
