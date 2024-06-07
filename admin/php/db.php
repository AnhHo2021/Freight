<?php

class DB {
	//function DB($user,$pass) {
		public function __construct($user,$pass){
            //$this->db = "norwayfreight";
		//$this->host = "localhost";

        $this->host = "35.238.134.226";
		$this->db = "norwayfreight";
		$this->user = '1wire_phone';
		$this->pass = 'Trouble54321!';
//		$this->link = mysql_connect($this->host, $this->user, $this->pass);
		$this->conn = mysqli_connect($this->host, $this->user, $this->pass, $this->db);
//		mysql_select_db($this->db,$this->link);

	}

	function close() {
//		mysql_close($this->link);
		mysqli_close($this->conn);

	}

	function query($sql){
		global $QUERY_STRING;

	    //	$return = "";
//		$result = mysql_query($sql,$this->link);
		$result = mysqli_query($this->conn, $sql);
		

		
		if($result && substr($sql,0,6) == "SELECT"){
		    $return = array();
		    
//			while($row = mysql_fetch_assoc($result)){

		    	while($row = mysqli_fetch_assoc($result)){
				$return[]=$row;
			}
			
			return $return;
			
        			
		}else{
			if(1){//substr($sql,0,6) == "SELECT"){
//				if(mysql_error()){
				if(mysqli_error($this->conn)){
					$ERROR = $QUERY_STRING."\n\n".mysqli_error($this->conn)."\n\n".$sql;
					//echo "DB ERROR: ".$ERROR;
					//die;
					mail("kevin@narvaezfamily.com","norwayfreight db error",$ERROR);
				}
			}
		
		
		    return	$return = false;
		}
	//	return $return;
	}

	function wasupdated(){
//		$return = (mysql_affected_rows($this->link) > 0)?true:false;
		$return = (mysqli_affected_rows($this->conn) > 0)?true:false;
		return $return;
	}

	function tblColumns($tbl,$skipFields){
		$return=array();

//		$result = mysql_query("SHOW COLUMNS FROM $tbl");
		$result = mysqli_query($this->conn, "SHOW COLUMNS FROM $tbl");
		if($result){
//			if(mysql_num_rows($result) > 0){
			if(mysqli_num_rows($result) > 0){
//			   while($row = mysql_fetch_assoc($result)){
			   while($row = mysqli_fetch_assoc($result)){
				   if(!in_array($row["Field"],$skipFields)){
					$return[]=$row["Field"];
				   }
			   }
			}
		}

		return $return;
	}

	function lastid(){
//		return mysql_insert_id();
		return mysqli_insert_id($this->conn);
	}
}

?>