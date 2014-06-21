<?php




class FaBranchHasAccount extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $ms_branch_id;
     
    /**
     *
     * @var integer
     */
    public $fa_account_id;
	
	public $dataAcc = array();
	public function getAccountBranch($connectionPDO) {			
		$request = new Phalcon\Http\Request();		
		$rows = array();
		if($request->get('idAccountType'))  {
			$idAccountType = $request->get('idAccountType');
			$branchId = $request->get('branchId');
			if($idAccountType == "sm") {
				$sql = "SELECT *,(SELECT COUNT(*) FROM fa_branch_has_account bha WHERE acc.code=bha.fa_account_code AND bha.ms_branch_id='".$branchId."') AS c FROM fa_account AS acc ";			       
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
				$rows = $this->get_data_account($connectionPDO,$idAccountType,$branchId);		
			
		}
		$json=array(
			   'data' => $rows
		);
		
		return $json;		
	}
	
	function get_data_account($connectionPDO,$idAccountType,$branchId) {				
		$sql = "SELECT *,(SELECT COUNT(*) FROM fa_branch_has_account bha WHERE acc.code=bha.fa_account_code AND bha.ms_branch_id='".$branchId."') AS c 
				FROM fa_account AS acc WHERE acc.fa_account_type_code='".$idAccountType."'	";			       
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
			$this->get_data_account($connectionPDO,$idAccountType,$branchId);				
		}
		
		return $this->dataAcc;
	}	
	
	public function createAccountBranch($connectionPDO) {			
		$decoded = json_decode($_REQUEST['data'],true);
		$branchId = $_REQUEST['branchId'];
		foreach ($decoded as $value) {
			$fcode = $value['id'];
			if($value['active'] == 0 OR $value['active'] == false)
				$sql = "DELETE FROM fa_branch_has_account WHERE ms_branch_id=".$branchId." AND fa_account_code='".$fcode."'";      
			else		
				$sql = "REPLACE INTO fa_branch_has_account (ms_branch_id,fa_account_code) VALUES('$branchId','$fcode')";               
			$connectionPDO->execute($sql);	
		};		
		
		return array();		
	}
}
