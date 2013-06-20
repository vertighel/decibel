<?php
session_start(); // Senza questo non salva le sessioni
// http://www.sciax2.it/forum/php-mysql/login-sessioni-ajax-jquery-194098.html

include_once("./decibel.v1.php");


function login($g,$t){
  /* $u='asdasd'; */
  /* $p=md5(trim('asdasd')); */
  $u=$_POST['user'];
  $p=md5($_POST['pass']);
  $a=new stdClass();
  $a->answer=null; // Inizializzazione oggetto di risposta
  $ifuser=$g->select(COUNTALL,$t)->where('username="'.$u.'"')
    ->query()->values()->last[0][0];
  
  if($ifuser){ // L'utente esiste (diverso da zero)?

    $ifpass=$g->select('password',$t)->where('username="'.$u.'"')
      ->query()->values()->last[0][0];
    
    if($ifpass==$p){ // La password corrisponde a quella dell'utente
      $_SESSION['user'] = $u; // Salvo la sessione dell'username
      $_SESSION['pass'] = $p; // Salvo la sessione della password
      $a->answer='info';
    }else{
      $a->answer='warn';
    }
  }else{
    $a->answer='err';
  }
  echo json_encode($a); //->answer;
  return;
}


function private_area(){
  if (empty($_SESSION['user'])){ // verifico che esista la sessione di autenticazione
    header("Location: ./cine.html");
    //  echo json_encode(["user"=>"not identified"]);
    exit;
  }

  // echo json_encode(["user"=>"ciccio"]);
  echo json_encode($_SESSION);
}


function logout(){
  session_destroy();
  //  header("Location: ./cine.html");
  exit;
}


function table($g,$t){
  $g->select(ALL,$t)->order('id')->query()->populate()->show();
}


function insert($g,$t){  /// Magari sta cosa della trasposizione passarla nei metodi!!!!
  $p=$_POST;
  
  $keys=array_keys($p);
  $vals=array_values($p); // in the wrong "direction"

  /// "transpose" the array of array values 
  array_unshift($vals, null);
  $valarray=call_user_func_array('array_map', $vals);

  $g->insert($t,$keys,$valarray)->query();

  //  echo json_encode(["$g->query"]); // watch query
  echo json_encode($_POST);
}


function replace($g,$t){
  $p=$_POST;
  
  $keys=array_keys($p);
  $vals=array_values($p); 

  $g->replace($t,$keys,$vals)->query();

  //  echo json_encode(["$g->query"]);  // watch query
  echo json_encode($_POST);
}


function delete($g,$t){
  $p=$_POST;
  
  $keys=array_keys($p);
  $vals=array_values($p); 

  $g->delete($t)->where($keys[0]."=".$vals[0])->query();

  //    echo json_encode(["$g->query"]);  // watch query
  echo json_encode($_POST);
}

////////// MAIN //////////
$g=new decibel('127.0.0.1','root','password','test'); // DB connection
$t='movies'; $f='form'; // Table

if(!isset($what))
  $what=$_GET['what'];

if($what=='login')
  login($g,$f);

if($what=='private_area')
  private_area();

if($what=='logout')
  logout();

if($what=='table')
  table($g,$t);

if($what=='insert')
  insert($g,$t);

if($what=='replace')
  replace($g,$t);

if($what=='delete')
  delete($g,$t);


?>
