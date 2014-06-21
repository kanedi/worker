<?php




class FaAccountClass extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id;
     
    /**
     *
     * @var string
     */
    public $code;
     
    /**
     *
     * @var string
     */
    public $name;
     
    /**
     *
     * @var string
     */
    public $is_active;
	
	public function geAccountClass($connectionPDO) {					
		$where = "";
		$limit = 10;
		$start = 0;
		
		$request = new Phalcon\Http\Request();		
		if($request->get('limit')) 
			$limit = $request->get('limit');
		if($request->get('start')) 
			$start = $request->get('start');		
			
		if(isset($_REQUEST['query'])) {
			$where = "WHERE fa_account_class.code LIKE '%".$_REQUEST['query']."%' OR fa_account_class.name LIKE '%".$_REQUEST['query']."%' OR fa_account_class.type LIKE '%".$_REQUEST['query']."%'";
		}				
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM fa_account_class ".$where." LIMIT ".$start.",".$limit;				
		$acc = $connectionPDO->fetchAll($sql);		
        $rows = array();
        foreach ($acc as $accs) {
              $data = array(
                 'id' => $accs['id'],
				 'code' => $accs['code'],
				 'name' => $accs['name'],
				 'text' => $accs['code']." - ".$accs['name'],
				 'is_active' => $accs['is_active'],
				 'type' => $accs['type'],
              );              
	          array_push($rows,$data);
        }
		
		$sql = "SELECT FOUND_ROWS() as total";
		$total = $connectionPDO->query($sql)->fetch();
		
		$json=array(
		               'total' => $total['total'],
					   'accountClass' => $rows
			  );
			  
		return $json;	  		
	}
     
}
