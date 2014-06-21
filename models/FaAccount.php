<?php




class FaAccount extends \Phalcon\Mvc\Model
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
    public $fa_account_type_id;
     
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
	
	public function getAccount($connectionPDO) {			
		if(isset($_REQUEST['idAccountClass']) AND isset($_REQUEST['idAccountType'])) {	
			$json = $this->getAccountJoinType($connectionPDO);				
		}	
		else if(isset($_REQUEST['idAccountType']))	{
			$json = $this->getAccountJoinType($connectionPDO);
				
		}	
		else if(isset($_REQUEST['idAccountClass']))	{
			$json = $this->getAccountJoinClass($connectionPDO);
				
		}	
		else if(isset($_REQUEST['departmentId'])) {	
			//$json = $this->getAccountToDepartment($connectionPDO);
			$json=$this->getAccountOne($connectionPDO);						
		}	
		else if(isset($_REQUEST['branchId'])) {	
			$json = $this->getAccountToBranch($connectionPDO);
				
		}	
		else {
				$json=$this->getAccountOne($connectionPDO);					
		}	
	
		return $json;	  
	}
	public function getAccountOne($connectionPDO) {				
		$where = "";
		$limit = 10;
		$start = 0;
		$request = new Phalcon\Http\Request();		
		if($request->get('limit')) 
			$limit = $request->get('limit');
		if($request->get('start')) 
			$start = $request->get('start');			
		if(isset($_REQUEST['query'])) {
			$where = "WHERE fa_account.code LIKE '".$_REQUEST['query']."%' OR fa_account.name LIKE '%".$_REQUEST['query']."%'";
		}				
				
		$sql = "SELECT SQL_CALC_FOUND_ROWS fa_account.code AS code,fa_account.name AS name FROM fa_account ".$where." LIMIT ".$start.",".$limit;				
		$account = $connectionPDO->fetchAll($sql);		
		$rows = array();
		foreach ($account as $accounts) {
			$data = array(
				'id' => $accounts['code'],
				'code' => $accounts['code'],
				'name' => $accounts['name'],
				'text' => $accounts['code']." - ".$accounts['name'],
			);              
			array_push($rows,$data);
		}
				
		$sql = "SELECT FOUND_ROWS() as total";
		$total = $connectionPDO->query($sql)->fetch();
				
		$json=array(
		   'total' => $total['total'],
		   'account' => $rows
		);
		return $json;			  		
	}
	public function getAccountJoinType($connectionPDO) {			
		$where = "";
		$limit = 10;
		$start = 0;
		
		$request = new Phalcon\Http\Request();		
		if($request->get('limit')) 
			$limit = $request->get('limit');
		if($request->get('start')) 
			$start = $request->get('start');	
				
		$idAct = $request->get('idAccountType');		
		
		if(isset($_REQUEST['query'])) {
			$where = "WHERE acc.code LIKE '".$_REQUEST['query']."%' OR acc.name LIKE '%".$_REQUEST['query']."%'";
		}				
				
		$sql = "SELECT SQL_CALC_FOUND_ROWS acc.code AS code,acc.name AS name FROM fa_account_class acl 
				INNER JOIN fa_account_type act ON acl.code=act.fa_account_class_code AND act.code='$idAct'
				INNER JOIN fa_account acc ON act.code=acc.fa_account_type_code
				".$where." LIMIT ".$start.",".$limit;				

		$account = $connectionPDO->fetchAll($sql);		
		$rows = array();
		foreach ($account as $accounts) {
			$data = array(
				'id' => $accounts['code'],
				'code' => $accounts['code'],
				'name' => $accounts['name'],
				'text' => $accounts['code']." - ".$accounts['name'],
			);              
			array_push($rows,$data);
		}
				
		$sql = "SELECT FOUND_ROWS() as total";
		$total = $connectionPDO->query($sql)->fetch();
				
		$json=array(
			   'total' => $total['total'],
			   'account' => $rows
		);					
		return $json;
	}
	public function getAccountJoinClass($connectionPDO) {			
		$where = "";
		$limit = 10;
		$start = 0;
		
		$request = new Phalcon\Http\Request();		
		if($request->get('limit')) 
			$limit = $request->get('limit');
		if($request->get('start')) 
			$start = $request->get('start');		
		
		$idAcl = $request->get('idAccountClass');		
		
		if(isset($_REQUEST['query'])) {
			$where = "WHERE acc.code LIKE '".$_REQUEST['query']."%' OR acc.name LIKE '%".$_REQUEST['query']."%'";
		}				
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS acc.code AS code,acc.name AS name FROM fa_account_class acl 
				INNER JOIN fa_account_type act ON acl.code=act.fa_account_class_code AND acl.code='$idAcl'
				INNER JOIN fa_account acc ON act.code=acc.fa_account_type_code
				".$where." LIMIT ".$start.",".$limit;				
		
		$account = $connectionPDO->fetchAll($sql);		
		$rows = array();
		foreach ($account as $accounts) {
			$data = array(
			'id' => $accounts['code'],
			'code' => $accounts['code'],
			'name' => $accounts['name'],
			'text' => $accounts['code']." - ".$accounts['name'],
			);              
			 array_push($rows,$data);
		}
				
		$sql = "SELECT FOUND_ROWS() as total";
		$total = $connectionPDO->query($sql)->fetch();
				
		$json=array(
				   'total' => $total['total'],
				   'account' => $rows
		);		
		return $json;
	}
	public function getAccountToDepartment($connectionPDO) {
		
		$where = "";
		$limit = 10;
		$start = 0;
		
		$request = new Phalcon\Http\Request();		
		if($request->get('limit')) 
			$limit = $request->get('limit');
		if($request->get('start')) 
			$start = $request->get('start');		
		$departmentId = $request->get('departmentId');
				
		$sql = "SELECT SQL_CALC_FOUND_ROWS acc.code AS code,acc.name AS name FROM fa_account acc  
				INNER JOIN fa_department_has_account dha ON dha.fa_account_code=acc.code WHERE dha.ms_department_id=".$departmentId;
								
		
		if(isset($_REQUEST['query'])) {
			$sql .= " AND (acc.code LIKE '".$_REQUEST['query']."%' OR acc.name LIKE '%".$_REQUEST['query']."%') ";
		}	
		
		$sql .= " LIMIT ".$start.",".$limit; 
		
		$account = $connectionPDO->fetchAll($sql);		
		$rows = array();
		foreach ($account as $accounts) {
			$data = array(
				'id' => $accounts['code'],
				'code' => $accounts['code'],
				'name' => $accounts['name'],
				'text' => $accounts['code']." - ".$accounts['name'],
			);              
			array_push($rows,$data);
		}
				
		$sql = "SELECT FOUND_ROWS() as total";
		$total = $connectionPDO->query($sql)->fetch();
				
		$json=array(					   
					   'total' => $total['total'],
					   'account' => $rows
		);				
		return $json;
	}
	public function getAccountToBranch($connectionPDO) {
		
		$where = "";
		$limit = 10;
		$start = 0;
		
		$request = new Phalcon\Http\Request();		
		if($request->get('limit')) 
			$limit = $request->get('limit');
		if($request->get('start')) 
			$start = $request->get('start');		
		$branchId = $request->get('branchId');
				
		$sql = "SELECT SQL_CALC_FOUND_ROWS acc.code AS code,acc.name AS name FROM fa_account acc  ";
		
		$branchType = MsBranch::findFirst(array(
            'id = :branchId:',
            'bind' => array(
                'branchId' => $branchId,
            )
        ));
		
		if($branchType->type != "HEAD OFFICE") {
			$sql .= " LEFT JOIN fa_branch_has_account bha ON bha.fa_account_code=acc.code  ";
		}
		
		$sql .= " WHERE 1 = 1";
		if(isset($_REQUEST['query'])) {
			$sql .= " AND (acc.code LIKE '".$_REQUEST['query']."%' OR acc.name LIKE '%".$_REQUEST['query']."%') ";
		}
		
		if(isset($_REQUEST['branchId']) AND $_REQUEST['branchId'] !=null AND $branchType->type != "HEAD OFFICE") {
			$sql .= " AND bha.ms_branch_id= '".$branchId."' ";
		}	
		
		$sql .= " LIMIT ".$start.",".$limit; 
		
		$account = $connectionPDO->fetchAll($sql);		
		$rows = array();
		foreach ($account as $accounts) {
			$data = array(
				'id' => $accounts['code'],
				'code' => $accounts['code'],
				'name' => $accounts['name'],
				'text' => $accounts['code']." - ".$accounts['name'],
			);              
			array_push($rows,$data);
		}
				
		$sql = "SELECT FOUND_ROWS() as total";
		$total = $connectionPDO->query($sql)->fetch();
				
		$json=array(					   
					   'total' => $total['total'],
					   'account' => $rows
		);				
		return $json;
	}
	
	public function getAccountToBranchBukuBesar($connectionPDO,$action) {
		
		$where = "";
		$limit = 10;
		$start = 0;
		
		$request = new Phalcon\Http\Request();		
		if($request->get('limit')) 
			$limit = $request->get('limit');
		if($request->get('start')) 
			$start = $request->get('start');		
		$branchId = $request->get('branchId');
				
		$sql = "SELECT SQL_CALC_FOUND_ROWS acc.code AS code,acc.name AS name FROM fa_account acc  ";
		if($branchId != "sm") {
			$branchType = MsBranch::findFirst(array(
				'id = :branchId:',
				'bind' => array(
					'branchId' => $branchId,
				)
			));
			
			if($branchType->type != "HEAD OFFICE") {
				$sql .= " LEFT JOIN fa_branch_has_account bha ON bha.fa_account_code=acc.code  ";
			}
		}
		else {		
			if(isset($_REQUEST['branchType'])) {
				if($_REQUEST['branchType'] != "HEAD OFFICE")
					$sql .= " LEFT JOIN fa_branch_has_account bha ON bha.fa_account_code=acc.code  ";
			}
		}
		$sql .= " WHERE 1 = 1 ";
		if(isset($_REQUEST['query'])) {
			$sql .= " AND (acc.code LIKE '".$_REQUEST['query']."%' OR acc.name LIKE '%".$_REQUEST['query']."%') ";
		}
		
		if(isset($_REQUEST['branchId']) AND $_REQUEST['branchId'] !=null AND $_REQUEST['branchId'] !="sm" AND $branchType->type != "HEAD OFFICE") {
			$sql .= " AND bha.ms_branch_id= '".$branchId."' ";
		}	
		
		if($action != "coa1")
			$sql .= " LIMIT ".$start.",".$limit; 
		
		$account = $connectionPDO->fetchAll($sql);		
		$rows = array();
		foreach ($account as $accounts) {
			$data = array(
				'id' => $accounts['code'],
				'code' => $accounts['code'],
				'name' => $accounts['name'],
				'text' => $accounts['code']." - ".$accounts['name'],
			);              
			array_push($rows,$data);
		}
				
		$sql = "SELECT FOUND_ROWS() as total";
		$total = $connectionPDO->query($sql)->fetch();
				
		$json=array(					   
					   'total' => $total['total'],
					   'account' => $rows
		);				
		return $json;
	}
	
}
