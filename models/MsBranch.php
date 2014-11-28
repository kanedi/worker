<?php




class MsBranch extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id;
     
    /**
     *
     * @var integer
     */
    public $ms_company_id;
	
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
    public $country;
     
    /**
     *
     * @var string
     */
    public $state;
     
    /**
     *
     * @var string
     */
    public $city;
	
	
	public function getBranch($connectionPDO) {	
		$where = "";
		$limit = 10;
		$start = 0;
		
		$request = new Phalcon\Http\Request();		
		if($request->get('limit')) 
			$limit = $request->get('limit');
		if($request->get('start')) 
			$start = $request->get('start');		
			
		if(isset($_REQUEST['query'])) {
			$where = " AND ms_branch.name LIKE '%".$_REQUEST['query']."%'";
		}				
		
		if(($request->get('branchId') AND !empty($_REQUEST['branchId'])) AND $request->get('branchType') != "HEAD OFFICE")	{
			$where .= " AND ms_branch.id=".$request->get('branchId');
		}
		
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM  ms_branch WHERE 1 = 1  ".$where." LIMIT ".$start.",".$limit;
		$result = $connectionPDO->query($sql);	
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC); // Menghasilkan Index array string as fields
		$rows = $result->fetchAll();		
		
		if($request->get('rpt')) {
			$data = array('id' => "sm",
						   'code' => "",
						   'name' => "All Branch",
						   'country' => "",
						   'satate' => "",
						   'city' => "",
						 );	
			array_push($rows,$data);
			sort($rows);
		}
		
		$sql = "SELECT FOUND_ROWS() as total";
		$total = $connectionPDO->query($sql)->fetch();
		
		
		$json=array(
		               'total' => $total['total'],
					   'branch' => $rows
			  );
			  
		return $json;	  		
	}
	
	public function getDetail($token){
		$tok = $this->extrackToken($token);
		$return = MsBranch::detail($tok['branch_id']);
		return $return;
	}
	
	public function getDetailId($id){
		$return = MsBranch::detail($id);
		return $return;
	}
	
	private function detail($id){
		$branch = MsBranch::findFirst($id);
		$return = array(
			'id' => $branch->id,
			'code' => $branch->code,
			'name' => $branch->name,
			'state' => $branch->state,
			'type' => $branch->type,
			'local_timezone' => $branch->local_timezone
		);
		return $return;
	}
     
}
