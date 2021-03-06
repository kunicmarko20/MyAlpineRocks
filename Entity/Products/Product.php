<?php
namespace Myalpinerocks;

use \ArrayObject;
use \JsonSerializable;

class Product implements JsonSerializable
{
	
	private $repository = "";
	private $id, $name, $description, $price, $valuta, $status, $categories, $ID_admin, $err = ""; 
	private $photos;
	
	function __construct()
	{
		$this->repository = new ProductsRepository();
		$this->photos = new ArrayObject();
	}
	
	public function getProducts()
	{
		return $this->repository->getProducts();
	}
	
	public function getProduct(array $paramValue)
	{
		$this->repository->openDataBaseConnection();
		$sgn = $this->repository->getProduct($paramValue, $this);
		$this->repository->closeDataBaseConnection();	
		return $sgn;
	}
	
	public function insertProduct()
	{
		return $this->repository->insertProduct($this);
	}
	
	//checks if categories of two products are the same
	public function areCategoriesEqual(Product $proizvod)
	{
		$sgnKat = FALSE;
		$k1 = $this->getCategories(); $k2 = $proizvod->getCategories();
		if (count($k1) == count($k2)) {
			$br = 0;
			for($i = 0; $i<count($k1); $i++) {				
				foreach($k2 as $no => $cat) {					
					if ($k1[$i]->getID() === $cat->getID()) {
						unset($k2[$no]);						
						$br++;						
					}					
				}
				
			}
			if ($br == count($k1)) $sgnKat = TRUE; //categries are the same. Arrays have the same no of elements and everu k1 is in k2)
		}
		return $sgnKat;
	}
	
	//checks if product data are the same (doesn't check if categories of product are same')
	public function areDataEqual(Product $proizvod)
	{
		$sgnP = FALSE;
		if (strtolower($this->getName()) == strtolower($proizvod->getName()) && 
			strtolower($this->getDescription()) == strtolower($proizvod->getDescription()) && 
			$this->getPrice() == $proizvod->getPrice()) {
				$sgnP = TRUE;
		}
		return $sgnP;
	}
	
	//checks if the product has same data and same categories as product that is passed to method as argument
	public function isEqual(Product $proizvod)
	{
		$sgnKat = $this->areCategoriesEqual($proizvod);
		$sgnP = $this->areDataEqual($proizvod);
		return($sgnKat&&$sgnP);	
	}
		
	public function editProduct()
	{
		$oldProduct = new Product();
		if ($oldProduct->getProduct(array("ID" => $this->getID()), $oldProduct)) {			
			$queryArray = new ArrayObject();
			$quest = TRUE;
			if (!$this->areDataEqual($oldProduct)) {
				$this->repository->prepareStatement_editProduct($this, $oldProduct, $queryArray);
				$quest = FALSE;
			}			
			if (!$this->areCategoriesEqual($oldProduct)) {
				$this->repository->prepareStatement_editCategoriesOfProduct($this, $oldProduct, $queryArray);
				$quest = FALSE;
			}			
			if ($quest) {
				$this->setErr("You didn't change any data.");
				return FALSE;
			}							
			$this->repository->openDataBaseConnection();	
								
			$sgn = $this->repository->executeTransaction($queryArray);	
				
			$this->repository->closeDataBaseConnection();
			return $sgn ?  TRUE :  FALSE;
		} else {
			$this->setErr("Product with given ID doesn't exist in database.");
			return FALSE;
		}
	}
	
	public function getPhotosOfProduct()
	{
		$this->setPhotos($this->repository->getPicturesOfProduct($this->getID()));
		return TRUE;
	}
	
	public function deleteProduct()
	{
		$queryArray = new ArrayObject();
		$this->repository->prepareStatement_deleteProduct($this, $queryArray);		
		$this->repository->openDataBaseConnection();
		
		$sgn = $this->repository->executeTransaction(new ArrayObject($queryArray));	
		$this->repository->closeDataBaseConnection();
		return $sgn ?  TRUE :  FALSE;
	}
	
	
	public function jsonSerialize()
   {
      return get_object_vars($this);
   }
	//    getters
	public function getID() {
		return $this->id;
	}
	public function getName() {
		return $this->name;
	}
	public function getDescription() {
		return $this->description;
	}
	public function getPrice() {
		return $this->price;
	}
	public function getValuta() {
		return $this->valuta;
	}
	public function getStatus() {
		return $this->status;
	}
	public function getCategories() {
		return $this->categories;
	}
	public function getID_admin() {
		return $this->ID_admin;
	}
	function getErr() {
		return $this->err;
	}
	function getPhotos() {
		return $this->photos;
	}
	//    setters
	public function setID(int $i) {
		$this->id = $i;
	}
	public function setName(string $i) {
		$this->name = $i;
	}
	public function setDescription(string $i) {
		$this->description = $i;
	}
	public function setPrice(float $i) {
		$this->price = $i;
	}
	public function setValuta(string $i) {
		$this->valuta = new Valuta($i);
	}
	public function setCategoryToNull() {
		$this->categories = NULL;
	}
	public function addCategory(Category $i) {	
		if (is_a($i, "\Myalpinerocks\Category")) {
			$this->categories[] = $i;
			return TRUE;
		} else {
			return FALSE;
		}
	}
	public function setStatus(int $i) {
		$this->status = $i;
	}
	public function setID_admin(int $i) {
		$this->ID_admin = $i;
	}
	function setErr(string $i) {
		$this->err = $this->err."\n".$i;
	}
	function setPhotos(array $i = NULL) {
		
		    $this->photos = $i;
			
	}
	function addPhoto(Photo $i)
	{
		$this->photos[] = $i;
	}
}
?>
