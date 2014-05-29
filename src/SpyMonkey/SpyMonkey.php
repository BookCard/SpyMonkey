<?php
namespace SpyMonkey;

class SpyMonkey{

	/**
	 * @var \PDO
	 **/
	protected $pdo 		= null;
	/**
	 * @var string
	 **/
	protected $resource = null;
	/**
	 * @var string
	 **/
	protected $field 	= null;
	/**
	 * @var string
	 **/
	protected $value 	= null;
	/**
	 * @var int
	 **/
	protected $offset 	= null;
	/**
	 * @var int
	 **/
	protected $limit 	= null;
	/**
	 * @var string
	 **/
	protected $compare  = "=";

	public function SpyMonkey(\PDO $pdo){
		$this->pdo = $pdo;
		$this->compare = "=";
	}
    /**
     * Gets the value of pdo.
     *
     * @return \PDO
     */
    protected function getPdo()
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
		$resource = $this->filter($resource);
		if(empty($resource) || is_null($resource)){
			throw new \InvalidArgumentException;
		}
		$this->resource = $resource;
		return $this;
	}
    /**
     * set Field
     * @param string
     */
	public function setField($field){
		$field = $this->filter($field);
		if(empty($field) || is_null($field)){
			throw new \InvalidArgumentException;
		}
		$this->field = $field;
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
		$value = $this->filter($value);
		if(empty($value) || is_null($value)){
			throw new \InvalidArgumentException;
		}
		$this->value = $value;
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
    	if(!is_numeric($limit)){
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
    protected function setCompare($compare){
        $this->compare = $compare;

        return $this;
    }
	/**
	 * Higienizador
	 * @return int|string
	 **/
	protected function filter($value){
		#$pattern = "/^[[:alnum:]]/";
		$pattern = "/^[[A-Za-z0-9 ]]/";
		return preg_replace($pattern,"",$value);
	}
	/**
	 * Get type
	 **/
	protected function getType($value){
		if(is_numeric($value)){
			return \PDO::PARAM_INT;
		}
		return \PDO::PARAM_STRING;
	}
	/**
	 * Constroi o sql
	 * @return string
	 */
	public function build(){
		return $this->select().$this->where().$this->condition().$this->limit().$this->offset();
	}
	/**
	 * @return string
	 */
	protected function select(){
		return " SELECT * FROM `".$this->getResource()."` r ";
	}
	/**
	 * @return string
	 */
	protected function where(){
		return " WHERE `r`.`".$this->getField()."` ";
	}
	/**
	 * @return string
	 */
	protected function condition(){
		return " ".$this->getCompare()." :value ";
	}
	/**
	 * @return string
	 */
	protected function hasLimit(){
		return (is_null($this->getLimit()))? false : true;
	}
	/**
	 * @return string
	 */
	protected function limit(){
		if($this->hasLimit()){
			return " LIMIT :limit ";
		}else{
			return "";
		}
	}
	/**
	 * @return string
	 */
	protected function hasOffSet(){
		return (is_null($this->getOffSet()))? false : true;
	}
	/**
	 * @return string
	 */
	protected function offset(){
		if($this->hasOffSet()){
			return " offset :offset";
		}else{
			return "";
		}
	}
	/**
	 * @param string sql
	 * @return array
	 * @throws \PDOException
	 */
	protected function execute($sql){
		try{
			$stmt = $this->getPDO()->prepare($query);
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
			return $stmt->fetchAll(PDO::FETCH_OBJ);
		}catch(\PDOException $e){
			throw new \BadMethodCallException;
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
			throw new \Exception("Error while executing query!");
		}
	}


}