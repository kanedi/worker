<?php




class FaAccountType extends \Phalcon\Mvc\Model
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
    public $fa_account_type_class_id;
     
    /**
     *
     * @var integer
     */
    public $parent_id;
     
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
    
	public function getAccountType($connectionPDO) {	
		$request = new Phalcon\Http\Request();		
		$where = "";
		$limit = 10;
		$start = 0;
			
		$request = new Phalcon\Http\Request();		
		if($request->get('limit')) 
			$limit = $request->get('limit');
		if($request->get('start')) 
			$start = $request->get('start');		
				
		if(isset($_REQUEST['idAccountClass']))	{			
			$idAcl=$request->get('idAccountClass');
			$where ="WHERE 0 = 0";
			if(!empty($_REQUEST['idAccountClass'])) {	
				$where.=" AND acl.code='$idAcl'";
			}		
			
			if(isset($_REQUEST['query'])) {
				$where = " act.code LIKE '".$_REQUEST['query']."%' OR act.name LIKE '%".$_REQUEST['query']."%'";
			}				
			
			$sql = "SELECT SQL_CALC_FOUND_ROWS act.* FROM fa_account_type act INNER JOIN fa_account_class acl ON act.fa_account_class_code=acl.code".$where." LIMIT ".$start.",".$limit;				
			$account = $con->fetchAll($sql);		
			$rows = array();
			foreach ($account as $accounts) {
				  $data = array(
					 'id' => $accounts['code'],
					 'code' => $accounts['code'],
					 'name' => $accounts['name'],
					 'text' => $accounts['code']." - ".$accounts['name'],
					 'is_active' => $accounts['is_active'],
					 'parent_id' => $accounts['parent_id'],
				  );              
				  array_push($rows,$data);
			}
			
			$sql = "SELECT FOUND_ROWS() as total";
			$total = $con->query($sql)->fetch();
			
			$json=array(
						   'total' => $total['total'],
						   'accounttype' => $rows
				  );
				  
			return $json;	  
		}	
		else {
			//$this->getAccountTypeOne($connectionPDO);		 
			
			if(isset($_REQUEST['query'])) {
				$where = "WHERE fa_account_type.code LIKE '".$_REQUEST['query']."%' OR fa_account_type.name LIKE '%".$_REQUEST['query']."%'";
			}				
			
			$sql = "SELECT SQL_CALC_FOUND_ROWS fa_account_type.* FROM fa_account_type ".$where." LIMIT ".$start.",".$limit;				
			$account = $connectionPDO->fetchAll($sql);		
			$rows = array();
			$c = 0;
			if(isset($_REQUEST['all'])) {
			$data = array(
					 'id' => "sm",
					 'code' => "",
					 'name' => "All Account",
					 'text' => "All Account",
					 'is_active' => '0',
					 'parent_id' => '',
				  );              
				  array_push($rows,$data);
				$c = 1;  
			}	  
			foreach ($account as $accounts) {
				  $data = array(
					 'id' => $accounts['code'],
					 'code' => $accounts['code'],
					 'name' => $accounts['name'],
					 'text' => $accounts['code']." - ".$accounts['name'],
					 'is_active' => $accounts['is_active'],
					 'parent_id' => $accounts['parent_id'],
				  );              
				  array_push($rows,$data);
			}
			
			$sql = "SELECT FOUND_ROWS() as total";
			$total = $connectionPDO->query($sql)->fetch();
			
			$json=array(
						   'total' => $total['total']+$c,
						   'accounttype' => $rows
				  );
				  
			return $json;	  
		}	
	}	
	public function getAccountTypeOne($connectionPDO) {
			
		$where = "";
		$limit = 10;
		$start = 0;
		
		$request = new Phalcon\Http\Request();		
		if($request->get('limit')) 
			$limit = $request->get('limit');
		if($request->get('start')) 
			$start = $request->get('start');		
		
		if(isset($_REQUEST['query'])) {
			$where = "WHERE fa_account_type.code LIKE '".$_REQUEST['query']."%' OR fa_account_type.name LIKE '%".$_REQUEST['query']."%'";
		}				
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS fa_account_type.* FROM fa_account_type ".$where." LIMIT ".$start.",".$limit;				
		$account = $connectionPDO->fetchAll($sql);		
        $rows = array();
        foreach ($account as $accounts) {
              $data = array(
                 'id' => $accounts['code'],
				 'code' => $accounts['code'],
				 'name' => $accounts['name'],
				 'text' => $accounts['code']." - ".$accounts['name'],
				 'is_active' => $accounts['is_active'],
				 'parent_id' => $accounts['parent_id'],
              );              
	          array_push($rows,$data);
        }
		
		$sql = "SELECT FOUND_ROWS() as total";
		$total = $connectionPDO->query($sql)->fetch();
		
		$json=array(
		               'total' => $total['total'],
					   'accounttype' => $rows
			  );
			  
		return $json;	  
	}
	
	public function getAccountTypeJoinClass($con) {
			
		$where = "";
		$limit = 10;
		$start = 0;
		
		$request = new Phalcon\Http\Request();		
		if($request->get('limit')) 
			$limit = $request->get('limit');
		if($request->get('start')) 
			$start = $request->get('start');		
		
		$idAcl=$request->get('idAccountClass');
		$where ="WHERE 0 = 0";
		if(!empty($_REQUEST['idAccountClass']))	{
			$where.=" AND acl.code='$idAcl'";
		}		
		
		if(isset($_REQUEST['query'])) {
			$where = " act.code LIKE '".$_REQUEST['query']."%' OR act.name LIKE '%".$_REQUEST['query']."%'";
		}				
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS act.* FROM fa_account_type act INNER JOIN fa_account_class acl ON act.fa_account_class_code=acl.code".$where." LIMIT ".$start.",".$limit;				
		$account = $con->fetchAll($sql);		
        $rows = array();
        foreach ($account as $accounts) {
              $data = array(
				 'id' => $accounts['code'],
				 'code' => $accounts['code'],
				 'name' => $accounts['name'],
				 'text' => $accounts['code']." - ".$accounts['name'],
				 'is_active' => $accounts['is_active'],
				 'parent_id' => $accounts['parent_id'],
              );              
	          array_push($rows,$data);
        }
		
		$sql = "SELECT FOUND_ROWS() as total";
		$total = $con->query($sql)->fetch();
		
		$json=array(
		               'total' => $total['total'],
					   'accounttype' => $rows
			  );
			  
		return $json;	  
	}
}
