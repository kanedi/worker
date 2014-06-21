<?php




class FaBudget extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $year;
     
    /**
     *
     * @var integer
     */
    public $month;
     
    /**
     *
     * @var integer
     */
    public $fa_account_id;
     
    /**
     *
     * @var integer
     */
    public $ms_department_id;
     
    /**
     *
     * @var double
     */
    public $amount;
    
	public function getBudget($connectionPDO) {			
		$request = new Phalcon\Http\Request();		
		$rows = array();
		if($request->get('idAccountType'))  {
			$idAccountType = $request->get('idAccountType');
			$where = "";
			if($idAccountType != "sm") {
				$where =" AND acc.fa_account_type_code ='".$idAccountType."'";
			}
			$departmentId = $request->get('departmentId');
			$periodYear = $request->get('periodYear');
			//$idBranch = $request->get('idBranch');
			$sql = "SELECT *,(SELECT amount FROM fa_budget bgt WHERE acc.code=bgt.fa_account_code AND ms_department_id=$departmentId AND year=$periodYear AND month=1) AS jan, (SELECT amount FROM fa_budget bgt WHERE acc.code=bgt.fa_account_code AND ms_department_id=$departmentId AND year=$periodYear AND month=2) AS feb, (SELECT amount FROM fa_budget bgt WHERE acc.code=bgt.fa_account_code AND ms_department_id=$departmentId AND year=$periodYear AND month=3) AS mar, (SELECT amount FROM fa_budget bgt WHERE acc.code=bgt.fa_account_code AND ms_department_id=$departmentId AND year=$periodYear AND month=4) AS apr, (SELECT amount FROM fa_budget bgt WHERE acc.code=bgt.fa_account_code AND ms_department_id=$departmentId AND year=$periodYear AND month=5) AS mei, (SELECT amount FROM fa_budget bgt WHERE acc.code=bgt.fa_account_code AND ms_department_id=$departmentId AND year=$periodYear AND month=6) AS jun, (SELECT amount FROM fa_budget bgt WHERE acc.code=bgt.fa_account_code AND ms_department_id=$departmentId AND year=$periodYear AND month=7) AS jul, (SELECT amount FROM fa_budget bgt WHERE acc.code=bgt.fa_account_code AND ms_department_id=$departmentId AND year=$periodYear AND month=8) AS ags, (SELECT amount FROM fa_budget bgt WHERE acc.code=bgt.fa_account_code AND ms_department_id=$departmentId AND year=$periodYear AND month=9) AS sep, (SELECT amount FROM fa_budget bgt WHERE acc.code=bgt.fa_account_code AND ms_department_id=$departmentId AND year=$periodYear AND month=10) AS okt, (SELECT amount FROM fa_budget bgt WHERE acc.code=bgt.fa_account_code AND ms_department_id=$departmentId AND year=$periodYear AND month=11) AS nov, (SELECT amount FROM fa_budget bgt WHERE acc.code=bgt.fa_account_code AND ms_department_id=$departmentId AND year=$periodYear AND month=12) AS des FROM fa_account AS acc INNER JOIN fa_department_has_account dha ON acc.code=dha.fa_account_code WHERE dha.ms_department_id='".$departmentId."'".$where;			       
			$account = $connectionPDO->fetchAll($sql);		
			
			foreach ($account as $accounts) {
				  $data = array(
					 'id_account' => $accounts['code'],
					 'account' => $accounts['code'],
					 'account_name' => $accounts['code']." - ".$accounts['name'],
					 'januari' => $accounts['jan'],
					 'februari' => $accounts['feb'],
					 'maret' => $accounts['mar'],
					 'april' => $accounts['apr'],
					 'mei' => $accounts['mei'],
					 'juni' => $accounts['jun'],
					 'juli' => $accounts['jul'],
					 'agustus' => $accounts['ags'],
					 'september' => $accounts['sep'],
					 'oktober' => $accounts['okt'],
					 'november' => $accounts['nov'],
					 'desember' => $accounts['des'],
				  );              
				  array_push($rows,$data);
			}					
		}
		$json=array(
			   'data' => $rows
		);
		
		return $json;		
	}
	
	public function createBudget($connectionPDO) {			
		$decoded = json_decode($_REQUEST['data'],true);
		$departmentId = $_REQUEST['departmentId'];
		$periodYear = $_REQUEST['periodYear'];
		foreach ($decoded as $value) {			
			for($i=0; $i<12; $i++) {				
				$month = $i+1;
				$account = $value['account'];
				$amount = $value['budget'][$i];
				if(empty($amount) OR $amount == null)
					$amount = 0;
				$sql = "REPLACE INTO fa_budget (year,month,ms_department_id,fa_account_code,amount) 
						VALUES('$periodYear','$month','$departmentId','$account','$amount')";               
				$connectionPDO->execute($sql);	
			}	
		};			
		
		return array();		
	}	
}
