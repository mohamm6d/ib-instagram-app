<?php

//Start sessions and ob_start functions
session_start();
ob_start();

include_once(dirname(__FILE__).'/vendor/autoload.php'); 
include_once(dirname(__FILE__).'/inc/Instagram.class.php');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

