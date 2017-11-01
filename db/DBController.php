<?php

abstract class DBController {
	protected $connection = NULL;

abstract protected function getTableName();

public final function openDataBaseConnection(){
	//var_dump($dbUser); var_dump($dbPass);
	try{
		$s = $GLOBALS['serverName'];
		$this->connection = new PDO("mysql:host = $s; dbname = onlineshop", $GLOBALS['dbUser'], $GLOBALS['dbPass']);
		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		//echo "Konektovali smo se na bazu $db_name";
	}catch(PDOException $e){
		echo "<br>Greska u konekciji sa bazom: " . $e->getMessage();
		}
}

public function closeDataBaseConnection(){
	try{
		$this->connection=NULL;
		//echo "<br>Zatvorili smo konekciju";
	}catch(PDOException $e){
		echo "<br>Greska u zatvaranju konekcije: " . $e->getMessage();
	}
}

public function vratiIDPoslednjegSloga($table){
	$query = "SELECT * FROM onlineshop.".$table." ORDER BY ID DESC LIMIT 1";
	$this->openDataBaseConnection();
	try{
		$stmt = $this->connection->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		$this->closeDataBaseConnection();
		
			if(count($result)>0){
				return $result[0]["ID"];
			}else{
				return 0;
			}
	}catch(PDOException $e){
		//bilo bi super da su mi sve klase nasledile neku klasu XX koja ima metodu za dodavanje gresaka
		echo $e->getMessage();
	}
}


public function executeQuery($query){
	
}

public function executeTransaction($queryArray){
	try{
		$this->connection->beginTransaction();
			for($i=0; $i<count($queryArray);$i++){
				$stmt = $this->connection->prepare($queryArray[$i]);
				$stmt ->execute();	
			}			
			$this->connection->commit();
			return TRUE;				
		}catch(PDOException $e){
			$this->connection->rollback();
			return FALSE;
		}	
}

	
}





?>