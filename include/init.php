<?

$loader = ModelLoader::LoadDir(MODEL_DIR,NULL,"Model_",1);

//method 1

foreach($loader as $file){

  $fp = fopen(MODEL_DIR.$file, 'r');
	$class = $buffer = '';
	$i = 0;
	while (!$class) {
	    if (feof($fp)) break;
	
	    $buffer .= fread($fp, 512);
	    if (preg_match('/class\s+(\w+)(.*)?\{/', $buffer, $matches)) {
	        $class = $matches[1];
	        break;
	    }
	}
	
	$$class = new $class();
	define($class,$$class);
	
	if($class == get_class($$class)){
		$created[] = $class;
	}
}

//method 2

$classesDeclared = get_declared_classes();
for($i = sizeof($classesDeclared) - 1; $i >= (sizeof($classesDeclared) - sizeof($loader)); $i--){
	$class = $classesDeclared[$i];
	$$class = new $class();
	define($class,$$class);
}



?>
