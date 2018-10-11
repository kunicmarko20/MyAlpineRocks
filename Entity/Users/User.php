<?php
namespace Myalpinerocks;

use \JsonSerializable;
use \ArrayObject;

class User{
	
	public $ID, $name, $lastName, $username, $email, $password, $locked, $accessRights, $status, $APIKey = "";
	private $uRepository;
	public $err = "";
	
	//NE POSTOJI KORISNIK BEZ EMAILA!!! kao ali ajde...
	
	function __construct(string $e)
	{
		$this->uRepository = new UserRepository();
		$this->email = $e;
		$this->err = new UserERR("","");
		
	}
	
	public function getUser(User $user, array $columnValuePairs)
	{
		$this->uRepository->openDataBaseConnection();
		if($this->uRepository->getUser($user,$columnValuePairs)){
			$this->uRepository->closeDataBaseConnection();
			return TRUE;
		}else{
			$this->uRepository->closeDataBaseConnection();
			return FALSE;
		}
			
	}
	
	public function logIn()
	{
		$testUser = new User($this->email);
		
		$this->uRepository->openDataBaseConnection();
		
		if($this->uRepository->getUser($testUser, array("Email" => $testUser->getEmail()))) {
			$sgn = false;
			if($testUser->getLocked() != 0){
				if($testUser->getPassword() === $this->password){
					$this->uRepository->unlockUser($this);
					$this->ID = $testUser->getID();
					$this->name = $testUser->getName();
					$this->lastName = $testUser->getLastName();
					$this->username = $testUser->getUsername();
					$this->locked = $testUser->getLocked();
					$this->accessRights = $testUser->getAccessRights();
					$this->err = $testUser->getERRStatus();
					$this->uRepository->logLogin($this->getID());
					$sgn = true;
				}else{					
					$this->setERRStatus("pass", "Wrong password presented");
					$this->setLocked($testUser->getLocked());
					$this->lockUser();
					$sgn = false;
				}
			}else{
				$this->setERRStatus("zak", "ERR:User account is locked");
				$sgn = false;
			}			
		}else{
			$this->err = $testUser->getERRStatus();
			$sgn = false;
		}
		$this->uRepository->closeDataBaseConnection();
		return $sgn;		
	}
	 
	public function lockUser()
	{
		$this->uRepository->lockUser($this);
	}
	
	public function unlockUser()
	{
		$this->uRepository->unlockUser($this);
	}
	
	public function newUser()
	{
		//Fja vraca TRUE ako u bazi nema aktivnog korisnika sa datim usernameom i emailom i ako upise korisnika u bazu
		//proveri da li ima u bazi po mailu i po usernameu
		$testEmail = $testUsername = FALSE;
		
		$testUser = new User($this->email);
		
		$this->uRepository->openDataBaseConnection();
		
		$this->uRepository->getUser($testUser, array("Email" => $testUser->getEmail()));
			if($testUser->getErrKod()=="n"){
				$testEmail = TRUE;
			}elseif($testUser->getErrKod()== "ok"){
				$this->setERRStatus("errMail", "User email is already registered.");
				$testEmail = FALSE;
				//return false;
			}else{
				$this->setERRStatus("err???", "Problem u proveri maila");
				$testEmail = FALSE;
				//return FALSE;
			}
		
		$this->uRepository->getUser($testUser, array("Username" => $this->getUsername()));
		
		if($testUser->getErrKod()=="n"){
				$testUsername = TRUE;
			}elseif($testUser->getErrKod()== "ok"){
				$this->setERRStatus("errMail", "Username already registered.");
				$testUsername = FALSE;
				//return FALSE;
			}else{
				$testUsername = FALSE;
				$this->setERRStatus("nije ok", "Nesto ne valja 1.");
				//return FALSE;
			}
		//ako u bazi nema AKTIVNOG naloga sa istim mailom ili usernameom upisi u bazu; 
		//u suprotnom samo vrati false, a greska je vec upisana u korisnika
		
		
		if($testEmail == TRUE && $testUsername == TRUE){
			if($this->uRepository->insertUser($this)){
				$this->setERRStatus("ok", "New user saved.");
				return TRUE;
			}
		}else{
			$this->setERRStatus("not ok", "Something is wrong 2.");
			return FALSE;
		}
		$this->uRepository->closeDataBaseConnection();	
		
	}
	
	public function editUser()
	{
		//proveri da li postoji u bazi
		//ako da, promeni podatke o korisniku
		$this->uRepository->openDataBaseConnection();
		$testUser = new User($this->getEmail()); 				//postojeci korisnik u bazi
		if($this->uRepository->getUser($testUser, array("Email" => $testUser->getEmail()))){
			if($this->getLocked() == 3 && $testUser->getLocked() != 0){		//ovo je provera za slucaj kada korisnik kog menjam ima locked=1 ili 2, pa da to ostane nepromenjeno u bazi ako vec nisam odlucila da ga zakljucam namerno
				$this->setLocked($testUser->getLocked());
			}
			if($this->getPassword() == "no change"){
				$this->setPassword($testUser->getPassword());
			}
			if($this->uRepository->editUser($this, $testUser)){
				$this->uRepository->closeDataBaseConnection();	
				return TRUE;
			}else{
				return FALSE;
			}
			
			
		}else{
			$this->setERRStatus("baza","User doesn't exist in database");
			return FALSE;
		}	
	}
	
  	public function getUsers(ArrayObject $userArray)
  	{       
       $this->uRepository->openDataBaseConnection();
       $sgn = $this->uRepository->getUsers($userArray);
       $this->uRepository->closeDataBaseConnection();
       return $sgn;
   }
    
 	public function deleteUser(User $user){
 		$this->uRepository->openDataBaseConnection();
 		if($this->uRepository->deleteUser($user)){
			$this->uRepository->closeDataBaseConnection();
			return TRUE;
		}else{
			$this->uRepository->closeDataBaseConnection();
			return FALSE;
		}
		
	}
	
	public function validatePassword(string $password){
		$this->getUser($this, array("Email" => $this->getEmail()));
		
		if($this->getPassword() === hash("sha256", $password, $raw_output = false)) {
			return true;		
		}else {
			return false;		
		}		
	}
	
	public function generateAPIKey(){
		if($this->getUser($this, array("Email" => $this->getEmail()))){
			$this->uRepository->openDataBaseConnection();
			$sgn = $this->uRepository->generateAPIKey($this);
			$this->uRepository->closeDataBaseConnection();
			return $sgn;
		}else{
			return FALSE;
		}
	}
	
	
	//    setters
   public function setID(int $i){
		$this->ID = $i;
	}
	public function setName(string $i){
		$this->name = $i;
	}	
	public function setLastName(string $i){
		$this->lastName = $i;
	}
	
	public function setUsername(string $i){
		$this->username = $i;
	}
		
	public function  setPassword(string $i){
		$this->password = $i;
	}
	public function setLocked(int $i){
		$this->locked = $i;
	}
	public function setEmail(string $i){
		$this->email = $i;
	}
	public function setAccessRights(string $i){
		$this->accessRights = $i;
	}
	public function setAPIKey(string $ak = NULL){
		$this->APIKey = $ak;
	}
	public function setERRStatus(string $i, string $p){
		$this->err->kod = $i;
		$this->err->msg = $this->err->msg." ".$p;
	}
	public function setStatus(int $i){
		$this->status = $i;
	}
	
	//    getters
   public function getID(){
		return $this->ID;
	}
	public function getName(){
		return $this->name;
	}	
	public function getLastName(){
		return $this->lastName;
	}	
	public function getUsername(){
		return $this->username;
	}		
	public function getPassword(){
		return $this->password;
	}
	public function getLocked(){
		return $this->locked;
	}
	public function getEmail(){
		return $this->email;
	}
	public function getAccessRights(){
		return $this->accessRights;
	}
	public function getAPIKey(){
		return $this->APIKey;
	}
	public function getERRStatus(){
		return $this->err;
	}
	public function getErrKod(){
		return $this->err->kod;
	}
	public function getErrMsg(){
		return $this->err->msg;
	}
	public function getStatus(){
		return $this->status;
	}
		
}

class UserERR{
	public $kod, $msg = "";
	
	function __construct($k, $p){
		$this->kod = $k;
		$this->msg = $p;
	}
	
	
	/*KODOVI:
	n - ne postoji u bazi
	baza - neki problem sa bazom
	rezSet - u bazi postoji vise od 1 korisnika sa istim mailom. U poruci je broj u result setu
	*/
	
	
}

?>
