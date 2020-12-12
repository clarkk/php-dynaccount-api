<?php

spl_autoload_register(function($class_name){
	$namespaces = explode('\\', $class_name);
	
	if($namespaces[0] == 'Dynaccount_API'){
		require_once __DIR__.'/'.$namespaces[1].'.php';
	}
});