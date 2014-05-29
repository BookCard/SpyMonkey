<?php
namespace SpyMonkey;

class SpyMonkey{

	/**
	 * @var \PDO
	 **/
	private $pdo 		= null;
	/**
	 * @var string
	 **/
	private $resource = null;
	/**
	 * @var string
	 **/
	private $field 	= null;
	/**
	 * @var string
	 **/
	private $value 	= null;
	/**
	 * @var int
	 **/
	private $offset 	= null;
	/**
	 * @var int
	 **/
	private $limit 	= null;
	/**
	 * @var string
	 **/
	private $compare  = null;

	public function __construct(\PDO $pdo){
		$this->pdo = $pdo;
		$this->compare = "=";
	}
    /**
     * Gets the value of pdo.
     *
     * @return \PDO
     */
    private function getPDO()
    {
        return $this->pdo;
    }
    /**
     * Get Resource
     * @return string
     */
    public function getResource(){
		return $this->resource;
	}
    /**
     * set Resource
     * @param string
     */
	public function setResource($resource){
		$this->resource = $this->filter($resource);
		return $this;
	}
	/**
	 * Checa existencia do resource
	 * @return bool
	 */
	public function hasResource(){
		return (is_null($this->getResource()))? false:true;
	}
    /**
     * set Field
     * @param string
     */
	public function setField($field){
		$this->field = $this->filter($field);
		return $this;
	}
    /**
     * Get Field
     * @return string
     */
	public function getField(){
		return $this->field;
	}
    /**
     * set Value
     * @return string
     */
	public function setValue($value){
		$this->value = $this->filter($value);
		return $this;
	}
    /**
     * Get Field
     * @return string
     */
	public function getValue(){
		return $this->value;
	}
    /**
     * Gets the value of offset.
     *
     * @return mixed
     */
    public function getOffSet(){
        return $this->offset;
    }
    /**
     * Sets the value of offset.
     *
     * @param mixed $offset the offset
     *
     * @return self
     */
    public function setOffSet($offset){
    	if(!is_numeric($offset)){
    		throw new \InvalidArgumentException("offset must be an integer!");
    	}
        $this->offset = (int) abs($offset);
		return $this;
    }
    /**
     * Gets the value of limit.
     *
     * @return mixed
     */
    public function getLimit(){
        return $this->limit;
    }
    /**
     * Sets the value of limit.
     *
     * @param mixed $limit the limit
     *
     * @return self
     */
    public function setLimit($limit){
    	if(!is_numeric($limit)){
    		throw new \InvalidArgumentException("limit must be an integer!");
    	}
        $this->limit = (int) abs($limit);
        return $this;
    }
    /**
     * Gets the value of compare.
     *
     * @return mixed
     */
    public function getCompare(){
        return $this->compare;
    }
    /**
     * Sets the value of compare.
     *
     * @param mixed $compare the compare
     *
     * @return self
     */
    private function setCompare($compare){
    	if(!in_array($compare,array(">","<","=","!=","<=",">=","!="))){
    		throw new \InvalidArgumentException;
    	}
        $this->compare = $compare;
        return $this;
    }
	/**
	 * Higienizador
	 * @return int|string
	 **/
	private function filter($value){
		$pattern = "/[^[:alnum:]]/";
		#$pattern = "/[^A-Za-z0-9]/";
		return preg_replace($pattern,"",$value);
	}
	/**
	 * Get type
	 **/
	private function getType($value){
		if(is_numeric($value)){
			return \PDO::PARAM_INT;
		}
		return \PDO::PARAM_STR;
	}
	/**
	 * Constroi o sql
	 * @return string
	 * @throws \BadMethodCallException se não for definido antes da chamada o resource
	 */
	public function build(){
		if(!$this->hasResource()){
			throw new \BadMethodCallException("Error missing resource!");
		}
		return $this->select().$this->where().$this->condition().$this->limit().$this->offset();
	}
	/**
	 * @return string
	 */
	private function select(){
		return " SELECT * FROM `".$this->getResource()."` r ";
	}
	/**
	 * @return string
	 */
	private function where(){
		return " WHERE `r`.`".$this->getField()."` ";
	}
	/**
	 * @return string
	 */
	private function condition(){
		return " ".$this->getCompare()." :value ";
	}
	/**
	 * @return string
	 */
	private function hasLimit(){
		return (is_null($this->getLimit()))? false : true;
	}
	/**
	 * @return string
	 */
	private function limit(){
		if($this->hasLimit()){
			return " LIMIT :limit ";
		}else{
			return "";
		}
	}
	/**
	 * @return string
	 */
	private function hasOffSet(){
		return (is_null($this->getOffSet()))? false : true;
	}
	/**
	 * @return string
	 */
	private function offset(){
		if($this->hasOffSet()){
			return " OFFSET :offset";
		}else{
			return "";
		}
	}
	/**
	 * Executa o comando no bd
	 * @param string $sql
	 * @return array
	 * @throws \PDOException
	 */
	private function execute($sql){
		try{
			$stmt = $this->getPDO()->prepare($sql);
			$stmt->bindParam(":value", $value,$this->getType($value));
			if($this->hasLimit()){
				$stmt->bindParam(":limit", $this->getLimit(),\PDO::PARAM_INT);
			}
			if($this->hasOffSet()){
				$stmt->bindParam(":offset", $this->getOffSet(),\PDO::PARAM_INT);
			}
			if($stmt->execute()){
				throw new \PDOException("Error in query!");
			}
			return $stmt->fetchAll(\PDO::FETCH_OBJ);
		}catch(\PDOException $e){
			throw new \BadMethodCallException($e->getMessage());
		}
	}
	/**
	 * Faz a consulta
	 * @return array
	 * @throws \Exception erro na consulta
	 */
	public function consult(){
		try{
			$built = $this->build();
			return $this->execute($built);
		}catch(\PDOException $e){
			throw new \Exception("Error while executing query! :".$e->getMessage());
		}
	}

	/**
	 * Alias para Set resource
	 * @param string $resource
	 */
	public function about($resource){
		$this->setResource($resource);
		return $this;
	}

	/**
	 * Alias duplo para setOffset e setLimit
	 * @param int $index
	 * @param int $limit
 	 */
	public function between($index,$limit){
		$this->setOffSet($index);
		$this->setLimit($limit);
		return $this;
	}

	/**
	 * Alias duplo para setValue e setCompare
	 * @param string $value
	 */
	public function equals($value){
		$this->setCompare("=");
		$this->setValue($value);
		return $this;
	}

	/**
	 * Alias duplo para setValue e setCompare
	 * @param string $value
	 */
	public function greaterThan($value){
		$this->setCompare(">");
		$this->setValue($value);
		return $this;
	}

	/**
	 * Alias duplo para setValue e setCompare
	 * @param string $value
	 */
	public function lessThan($value){
		$this->setCompare("<");
		$this->setValue($value);
		return $this;
	}

	/**
	 * Alias duplo para setValue e setCompare
	 * @param string $value
	 */
	public function different($value){
		$this->setCompare("!=");
		$this->setValue($value);
		return $this;
	}

	/**
	 * Alias para setField
	 * @param string $value
	 */
	public function with($field){
		$this->setField($field);
		return $this;
	}

	/**
	 * Alias para consult
	 * @return array
	 * @throws \Exception
	 */
	public function whatYouSee(){
		return $this->consult();
	}

}