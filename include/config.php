<?

session_start();

//connect
include_once("database/db.conn.php");
include_once("database/db.class.php");
include_once("database/db.ModelLoader.php");

//function class
include_once("function/AwesomeImageUpload.php")

/*****************
**MYSQL Settings**
*****************/
define('DB_HOST', "$your_db_host");
define('DB_USER',"$your_db_user");
define('DB_PWD',"$your_db_pwd");
define('DB_NAME',"$your_db_name");
