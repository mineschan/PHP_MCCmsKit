<?

session_start();

//connect
include_once("MCCmsKit/database/db.conn.php");
include_once("MCCmsKit/database/db.class.php");

//function class
include_once("MCCmsKit/function/fc.common.php");
include_once("class/AwesomeImageUpload.php")

/*****************
**MYSQL Settings**
*****************/
define('DB_HOST', "$your_db_host");
define('DB_USER',"$your_db_user");
define('DB_PWD',"$your_db_pwd");
define('DB_NAME',"$your_db_name");
