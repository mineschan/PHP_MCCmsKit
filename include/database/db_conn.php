<?
  function conn(){
		$conn = mysql_connect(DB_HOST,DB_USER,DB_PWD);
				
		$select_db = mysql_select_db(DB_NAME);
		mysql_query("SET NAMES 'utf8'"); 
        mysql_query("SET CHARACTER_SET_CLIENT=utf8"); 
        mysql_query("SET CHARACTER_SET_RESULTS=utf8");
         			
		if(!$conn || !$select_db)
			echo "<script>alert('Connect to Database error')</script>";	
  }
