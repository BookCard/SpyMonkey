<?php
namespace SpyMonkey;

class SpyMonkeyTest extends \PHPUnit_Framework_TestCase{

	protected $object;

	protected function setUp(){
		$this->object = new SpyMonkey(new \PDO('sqlite::memory:'));

	}

	protected function tearDown(){
		$this->object = null;
	}

    /**
     * getPrivateMethod
     *
     * @author    Joe Sexton <joe@webtipblog.com>
     * @param     string $className
     * @param     string $methodName
     * @return    ReflectionMethod
     */
    public function getPrivateMethod( $className, $methodName ) {
        $reflector = new \ReflectionClass( $className );
        $method = $reflector->getMethod( $methodName );
        $method->setAccessible( true );
		return $method;
    }

	public function dataProviderTestSetResource(){
		return array(
			array(
				"!","'","@","#","$",'"',"%","¨","&","*","(",")",
				"_","-","+","§","^","~","º"," ","   ","?","ç","à",
				"è","ó","^u","!!!","^}}{{_+'","--'","0x001"),
		);
	}

	/**
	 * @dataProvider dataProviderTestSetResource
	 */
	public function testFilter($value){
		$method = $this->getPrivateMethod("SpyMonkey\\SpyMonkey","filter");
		$result = $method->invokeArgs($this->object,array($value));
		$this->assertEquals("",$result);
	}

	/**
	 * @depends testFilter
	 */
	public function testSetAndGetResource(){
		$this->object->setResource("fooBar");
		$this->assertEquals("fooBar",$this->object->getResource());

		$this->object->setResource("àìóú!@#$%*()ção");
		$this->assertEquals("o",$this->object->getResource());
	}

	/**
	 * @depends testFilter
	 */
	public function testSetAndGetField(){
		$this->object->setField("fooBar");
		$this->assertEquals("fooBar",$this->object->getField());

		$this->object->setField("àìóú!@#$%*()ção");
		$this->assertEquals("o",$this->object->getField());
	}

	/**
	 * @depends testFilter
	 */
	public function testSetAndGetValue(){
		$this->object->setValue("fooBar");
		$this->assertEquals("fooBar",$this->object->getValue());

		$this->object->setValue("àìóú!@#$%*()ção");
		$this->assertEquals("o",$this->object->getValue());
	}

	public function dataProviderTestSetCompare(){
		return array(
			array("!","@","#","$","'%*%'","-","+","abc","123456789",">!","<@",">#","<¨","&>")
		);
	}
	/**
	 * @expectedException \InvalidArgumentException
	 * @dataProvider dataProviderTestSetCompare
	 */
	public function testSetCompare($value){
		$method = $this->getPrivateMethod("SpyMonkey\\SpyMonkey","setCompare");
		$result = $method->invokeArgs($this->object,array($value));
	}

	public function dataProviderTestGetType(){
		return array(
			array(000001,\PDO::PARAM_INT),
			array('000001',\PDO::PARAM_INT),
			array("123A",\PDO::PARAM_STR),
			array("asddfdgfgfgh",\PDO::PARAM_STR),
			array("123456789",\PDO::PARAM_INT),
			array("(qwert)",\PDO::PARAM_STR),
			array("0x01A",\PDO::PARAM_INT),
			array("====§§",\PDO::PARAM_STR),
			array("!!!!A",\PDO::PARAM_STR),
		);
	}

	/**
	 * @dataProvider dataProviderTestGetType
	 */
	public function testGetType($value,$response){
		$method = $this->getPrivateMethod("SpyMonkey\\SpyMonkey","getType");
		$result = $method->invokeArgs($this->object,array($value));
		$this->assertEquals($response,$result);
	}

	public function dataProviderTestSelectAndWhere(){
		return array(
			array("foob'ar","foobar"),
			array("foob\ar","foobar"),
			array("foob/ar","foobar"),
			array("foobçar","foobar"),
			array("foob?ar","foobar"),
			array("foob''ar","foobar"),
			array("foob*ar","foobar"),
			array("foobaár","foobar"),
			array("foobºar","foobar")
		);
	}
	/**
	 * @depends testSetAndGetResource
	 * @dataProvider dataProviderTestSelectAndWhere
	 */
	public function testSelect($value,$expected){
		$expected = " SELECT * FROM `$expected` r ";
		$this->object->setResource($value);
		$method = $this->getPrivateMethod("SpyMonkey\\SpyMonkey","select");
		$result = $method->invokeArgs($this->object,array());
		return $this->assertEquals($expected,$result);
	}
	/**
	 * @depends testSetAndGetField
	 * @dataProvider dataProviderTestSelectAndWhere
	 */
	public function testWhere($value, $expected){
		$expected = " WHERE `r`.`$expected` ";
		$this->object->setField($value);
		$method = $this->getPrivateMethod("SpyMonkey\\SpyMonkey","where");
		$result = $method->invokeArgs($this->object,array());
		return $this->assertEquals($expected,$result);
	}

	public function dataProviderTestCondition(){
		return array(
			array(" = :value ","="),
			array(" > :value ",">"),
			array(" < :value ","<"),
			array(" >= :value ",">="),
			array(" <= :value ","<="),
			array(" != :value ","!="),
		);
	}

	/**
	 * @depends testSetCompare
	 * @dataProvider dataProviderTestCondition
	 */
	public function testCondition($expected,$value){
		$method = $this->getPrivateMethod("SpyMonkey\\SpyMonkey","setCompare");
		$method->invokeArgs($this->object,array($value));
		$method = $this->getPrivateMethod("SpyMonkey\\SpyMonkey","condition");
		$result = $method->invokeArgs($this->object,array());
		return $this->assertEquals($expected,$result);
	}

}