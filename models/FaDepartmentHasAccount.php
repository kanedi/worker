<?php




class FaDepartmentHasAccount extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $ms_department_id;
     
    /**
     *
     * @var integer
     */
    public $fa_account_id;
	
	public $dataAcc = array();
	
	public function getAccountDepartment($connectionPDO) {			
		$request = new Phalcon\Http\Request();		
		$rows = array();
		if($request->get('idAccountType'))  {
			$idAccountType = $request->get('idAccountType');
			$where = " WHERE 0 = 0";
			$departmentId = $request->get('departmentId');
			$dept = MsDepartment::findFirst(array(
					'id = :departmentId:',
					'bind' => array(
						'departmentId' => $departmentId,
					)
			));
				
			$branchId = $dept->MsDirectorate->ms_branch_id;
			$branch_type = $dept->MsDirectorate->MsBranch->type;
			
			if($branch_type != "HEAD OFFICE") {
					$where = " LEFT JOIN fa_branch_has_account AS brc ON acc.code=brc.fa_account_code ".$where;			       
					$where .= " AND brc.ms_branch_id='".$branchId."'  ";
					
			}
				
			if($idAccountType == "sm") {								
				
				$sql = "SELECT *,(SELECT COUNT(*) FROM fa_department_has_account dha WHERE acc.code=dha.fa_account_code AND dha.ms_department_id='".$departmentId."') AS c 
				FROM fa_account AS acc ".$where;			       
				
						
				$account = $connectionPDO->fetchAll($sql);		
				
				foreach ($account as $accounts) {
					  $c=0;
					  if($accounts['c'] > 0) $c = 1;		
					  $data = array(
						 'id_account' => $accounts['code'],
						 'account' => $accounts['code'],
						 'account_name' => $accounts['code']." - ".$accounts['name'],
						 'active' => $c,
					  );              
					  array_push($rows,$data);
				}	
			}
			else			
				$rows = $this->get_data_account($connectionPDO,$idAccountType,$branchId,$departmentId,$where);		
		}
		$json=array(
			   'data' => $rows,
		);
		
		return $json;		
	}
	
	function get_data_account($connectionPDO,$idAccountType,$branchId,$departmentId,$where) {			
		$sql = "SELECT *,(SELECT COUNT(*) FROM fa_department_has_account dha WHERE acc.code=dha.fa_account_code AND dha.ms_department_id='".$departmentId."') AS c 
				 FROM fa_account AS acc ".$where." AND acc.fa_account_type_code='".$idAccountType."'	";			       
		$account = $connectionPDO->fetchAll($sql);		
		
		foreach ($account as $accounts) {
				  $c=0;
				  if($accounts['c'] > 0) $c = 1;		
				  $data = array(
					 'id_account' => $accounts['code'],
					 'account' => $accounts['code'],
					 'account_name' => $accounts['code']." - ".$accounts['name'],
					 'active' => $c,
				  );              
				  array_push($this->dataAcc,$data);
			}					
		
		$sql = "SELECT * FROM fa_account_type
			    WHERE parent_id='".$idAccountType."'";
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		while ($r = $result->fetch()) {			
			$idAccountType = $r['code']; 			
			$this->get_data_account($connectionPDO,$idAccountType,$branchId,$departmentId,$where);				
		}
		
		return $this->dataAcc;
	}	
	
	
	public function createAccountDepartment($connectionPDO) {			
		$decoded = json_decode($_REQUEST['data'],true);
		$departmentId = $_REQUEST['departmentId'];
		foreach ($decoded as $value) {
			$fcode = $value['id'];
			if($value['active'] == 0 OR $value['active'] == false)
				$sql = "DELETE FROM fa_department_has_account WHERE ms_department_id=".$departmentId." AND fa_account_code='".$fcode."'";      
			else		
				$sql = "REPLACE INTO fa_department_has_account (ms_department_id,fa_account_code) VALUES('$departmentId','$fcode')";               
			$connectionPDO->execute($sql);	
		};		
		
		return array();		
	}
}
