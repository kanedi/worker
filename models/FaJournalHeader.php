<?php




class FaJournalHeader extends \Phalcon\Mvc\Model
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
    public $ms_branch_id;
     
    /**
     *
     * @var string
     */
    public $fa_code_trx;
     
    /**
     *
     * @var integer
     */
    public $ms_department_id;
     
    /**
     *
     * @var integer
     */
    public $fa_fund_category_sub_id;
     
    /**
     *
     * @var integer
     */
    public $cr_donation_detail_id;
     
    /**
     *
     * @var string
     */
    public $code;
     
    /**
     *
     * @var string
     */
    public $trx_date;
     
    /**
     *
     * @var string
     */
    public $journal_headercol;
     
    /**
     *
     * @var string
     */
    public $status;
     
    /**
     *
     * @var string
     */
    public $memo;
     
    /**
     *
     * @var string
     */
    public $is_deleted;
     
    /**
     *
     * @var string
     */
    public $created;
     
    /**
     *
     * @var string
     */
    public $updated;
     
    /**
     *
     * @var integer
     */
    public $created_user_id;
     
    /**
     *
     * @var integer
     */
    public $updated_user_id;
	
	public $data = array();
	
	public $idx_neraca = 0;
	
	public $pd = 25;
	
	public $totNN = 0;
	
	public $totNN_1 = 0;
	
	public $totNN_3 = 0;
	
	public function read($connectionPDO) {			
		$request = new Phalcon\Http\Request();		
		
		$this->where="";		
		$limit = 20;
		$start = 0;
		if($request->get('limit')) 
			$limit = $request->get('limit');
		if($request->get('start')) 
			$start = $request->get('start');		
		
		$this->prep = new ControllerBase();
		$this->prep->defaultSortProperty='fa_journal_header.id';		
		$this->prep->prep();		
		$this->prep->sortDirection = "DESC";
		$srt = " AND status='1' ORDER BY ".$this->prep->sortProperty." ".$this->prep->sortDirection;				
		$this->where="WHERE ".$this->prep->where;	
		$branchId = $_REQUEST['branchType'];
		if($_REQUEST['branchType'] != "HEAD OFFICE") {
			$this->where .= " AND ms_branch.id = ".$branchId;
		}			
		
		if(isset($_REQUEST['query'])) {
			$this->where .= " AND (ms_branch.name LIKE '%".$_REQUEST['query']."%' OR fa_journal_header.code LIKE '%".$_REQUEST['query']."%')";
		}
		
		$this->where=" ".$this->where.$srt;
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS fa_journal_header.id AS fa_journal_header_id, fa_journal_header.code AS fa_journal_header_code, DATE_FORMAT(fa_journal_header.trx_date,'%d-%m-%Y') AS fa_journal_header_trx_date,  fa_journal_header.status AS fa_journal_header_status, fa_journal_header.memo AS fa_journal_header_memo, fa_journal_header.cr_donation_detail_id AS cr_donation_detail_id, ms_branch.id AS ms_branch_id, ms_branch.name AS ms_branch_name
		FROM fa_journal_header
		INNER JOIN ms_branch ON fa_journal_header.ms_branch_id=ms_branch.id
		".$this->where." LIMIT ".$start.",".$limit;		
		
		$result = $connectionPDO->query($sql);	
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC); // Menghasilkan Index array string as fields
		$rows = $result->fetchAll();	
		
		$sql = "SELECT FOUND_ROWS() as total";
		$result = $connectionPDO->query($sql);	
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC); // Menghasilkan Index array string as fields
		$total = $result->fetch();		
		$json=array(
		               'total' => $total['total'],
					   'data' => $rows
					 );
					 
		return $json;
	}
	function create_trx($connectionPDO) {
		$header = json_decode($_REQUEST['header'],true);
		$data = json_decode($_REQUEST['data'],true);
		$today = date("Y-m-d H:i:s");		
		//$mdl = new FaJournalAutonumber();
		$year = date("Y");
		$month = date("m");		
		
		foreach ($header as $value) {			
			
			//$branchId = $value['branchId'];			
		    $trxCode = $value['trxCode'];
		    /*
			$idDepartment = $value['idDepartment'];
			$dept = MsDepartment::findFirst(array(
				'id = :idDepartment:',
				'bind' => array(
					'idDepartment' => $idDepartment,
				)
			));
			*/
			$genid = new Library\Ozip\Id();
                $seq = $genid->getSeq('jurnal'.$year);			
			$branchId = $value['branchId'];
			$autoNumberCode = $value['trxCode']."/".$branchId."/".$month."/".$year."/".$seq;	
			
			//$idFundCatSub = $value['idFundCatSub'];
			//$idProgram = $value['idProgram'];
			if($value['idDonationDetail']!="")
				$idDonationDetail = "'$value[idDonationDetail]'";
			else	
				$idDonationDetail ="NULL";
				
			//$autoNumberCode = $autoNumberCode;
			$trxDate = $value['trxDate'];
			/*
			if($value['headerColJournal']!="")
				$headerColJournal = "'$value[headerColJournal]'";
			else	
				$headerColJournal = "NULL";
			*/	
			$status = "1";					   
			$memo = $value['memo'];
			$created = $today;
			$userId = $_REQUEST['userId'];
			
			$sql = "INSERT INTO fa_journal_header (ms_branch_id, cr_donation_detail_id, code, trx_date, memo, created, created_user_id)            
			VALUES ('$branchId',".$idDonationDetail.",'$autoNumberCode','$trxDate','$memo','$created','$userId')";			
		}
		$connectionPDO->query($sql);	
		
		$idHeader = FaJournalHeader::maximum(array("column" => "id"));
		//$idHeader = mysql_insert_id();
		foreach ($data as $value) {
					$debit = "'$value[debit]'";
					$credit = "'$value[credit]'";
					$bank = "'$value[bank]'";
					$program = "'$value[program]'";
					/*
					if($value['debit']==0)
					    $debit = "NULL";
					if($value['credit']==0)
					    $credit = "NULL";	
					*/	
					if($value['bank']=="")
					    $bank = "NULL";						
					if($value['program']=="")
					    $program = "NULL";								
					$sql = "INSERT INTO fa_journal_detail (fa_journal_header_id, ms_department_id, fa_account_code, fa_bank_id, fa_fund_type_id, fa_program_id, ms_currency_code, currency_rate, memo, debit, credit, is_deleted, created, created_user_id)					
					VALUES ('$idHeader', '$value[department]', '$value[account]', ".$bank.", '$value[type]', ".$program.", '$value[currency]', '$value[rate]', '$value[memo]', ".$debit.", ".$credit.", '0', '$today', '$_REQUEST[userId]')";				
					
					$connectionPDO->query($sql);	
		}		
		$json=array(
		                'id' => $idHeader,
						'code_trx' => $autoNumberCode
					 );
					 
		return $json;
	}
	function update_trx($connectionPDO) {		
		$header = json_decode($_REQUEST['header'],true);
		$data = json_decode($_REQUEST['data'],true);
		$today = date("Y-m-d H:i:s");		
		$id_header = 0;
		foreach ($header as $value) {			
			$id_header = $value['idHeader'];
			$branchId = $value['branchId'];
		    //$trxCode = $value['trxCode'];
		    //$idDepartment = $value['idDepartment'];
			/*
			$dept = MsDepartment::findFirst(array(
				'id = :idDepartment:',
				'bind' => array(
					'idDepartment' => $idDepartment,
				)
			));
			$branchId = $dept->MsDirectorate->ms_branch_id;
			$idFundCatSub = $value['idFundCatSub'];
			$idProgram = $value['idProgram'];
			*/
			if($value['idDonationDetail']!="")
				$idDonationDetail = "'$value[idDonationDetail]'";
			else	
				$idDonationDetail ="NULL";				
			
			$trxDate = $value['trxDate'];
			/*
			if($value['headerColJournal']!="")
				$headerColJournal = "'$value[headerColJournal]'";
			else	
				$headerColJournal = "NULL";
			*/	
			$status = "1";					   
			$memo = $value['memo'];
			$updated = $today;
			$userId = $_REQUEST['userId'];
			
			$sql = "UPDATE fa_journal_header SET ms_branch_id = '$branchId', cr_donation_detail_id = ".$idDonationDetail.",  trx_date = '$trxDate', memo = '$memo', updated = '$updated', updated_user_id = $userId WHERE id = '".$value['idHeader']."'";			
				
		}
		$connectionPDO->query($sql);
		
		$sql = "DELETE FROM fa_journal_detail WHERE fa_journal_header_id = '".$id_header."'";                         
		$connectionPDO->query($sql);
		
		foreach ($data as $value) {
					$debit = $value['debit'];
					$credit = $value['credit'];
					$debit = "'$value[debit]'";
					$credit = "'$value[credit]'";
					$bank = "'$value[bank]'";
					$program = "'$value[program]'";
					/*
					if($value['debit']==0)
					    $debit = "NULL";
					if($value['credit']==0)
					    $credit = "NULL";	
					*/
					if($value['bank']=="")
					    $bank = "NULL";						
					if($value['program']=="")
					    $program = "NULL";							
						
					$sql = "INSERT INTO fa_journal_detail (fa_journal_header_id, ms_department_id, fa_account_code, fa_bank_id, fa_fund_type_id, fa_program_id, ms_currency_code, currency_rate, memo, debit, credit, is_deleted, updated, updated_user_id)					
					VALUES ('$id_header', '$value[department]', '$value[account]', ".$bank.", '$value[type]', ".$program.", '$value[currency]', '$value[rate]', '$value[memo]', ".$debit.", ".$credit.", '0', '$today', '$_REQUEST[userId]')";				
					
					$connectionPDO->query($sql);	
		}		
			
		$json=array(
		                'id' => $id_header
					 );
					 
		return $json;
	}
	
	function get_data_rjurnal($connectionPDO, $start_date, $end_date, $branchId, $no_trx, $code_category="") {	
		$q_add = ($branchId) ? " AND a.ms_branch_id = '".$branchId."' " : "";
		$q_add .= ($no_trx) ? " AND a.code = '".$no_trx."' " : "";
		
		$sql = "SELECT a.id, DATE_FORMAT(a.trx_date,'%d-%m-%Y') AS trx_date, a.memo, a.code,
				b.memo, b.debit, b.credit , b.currency_rate, b.ms_currency_code, c.name as account_name 
				FROM fa_journal_header a 
				INNER JOIN fa_journal_detail b on a.id = b.fa_journal_header_id 
				INNER JOIN fa_account c on c.code = b.fa_account_code 
				WHERE b.is_deleted = '0' and (date(a.trx_date) >= '$start_date' and  date(a.trx_date) <= '$end_date') ".$q_add;
		$sql .= " ORDER BY a.id DESC ";  	
		
		$result = $connectionPDO->query($sql);			
		//$result->setFetchMode(Phalcon\Db::FETCH_NUM); >> Menghasilkan Index array number
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC); // Menghasilkan Index array string as fields
		$data = $result->fetchAll();			
	
		if ($result->numRows() > 0)
			return $data;
		return array();
	}
	
    function get_data_jurnal_entry($connectionPDO, $idJH) {	
		
		$sql = "SELECT a.id, a.trx_date, a.memo, a.code, g.name AS nm_create, i.name AS nm_update ,
				b.name AS branchNm  FROM fa_journal_header a 
			    LEFT JOIN ms_branch b on b.id = a.ms_branch_id 				
				LEFT JOIN user f on f.id = a.created_user_id								
				LEFT JOIN hc_employee g on g.id = f.employee_id	
				LEFT JOIN user h on f.id = a.updated_user_id								
				LEFT JOIN hc_employee i on i.id = h.employee_id	
			    WHERE a.id=".$idJH;
		$result = $connectionPDO->query($sql);			
		//$result->setFetchMode(Phalcon\Db::FETCH_NUM); >> Menghasilkan Index array number
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC); // Menghasilkan Index array string as fields
		$data = $result->fetch();			

		if ($result->numRows() > 0)
			return $data;
		return array();
	} 
	
	function get_data_jurnal_entry_detail($connectionPDO, $idJH) {	
		
		$sql = "SELECT a.memo, b.debit, b.credit, b.currency_rate, c.code AS bcode ,
				c.name ,d.code AS ccode,e.bank_name AS bnm,e.bank_acc_no AS bac,e.bank_acc_name AS ban,f.name AS fnm,g.name AS gnm,i.name AS inm   
				FROM fa_journal_header a
				INNER JOIN fa_journal_detail b ON a.id=b.fa_journal_header_id	
			    INNER JOIN fa_account c on c.code = b.fa_account_code				
				INNER JOIN ms_currency d on d.code = b.ms_currency_code				
				LEFT JOIN fa_bank e on e.id = b.fa_bank_id				
				INNER JOIN ms_department f on f.id = b.ms_department_id
				INNER JOIN fa_fund_type g on g.id = b.fa_fund_type_id
				INNER JOIN fa_fund_category_sub h on h.id = g.fa_fund_category_sub_id
				INNER JOIN fa_fund_category i on i.id = h.fa_fund_category_id
				
			    WHERE a.id=".$idJH." ORDER BY b.id  DESC";
		$result = $connectionPDO->query($sql);			
		//$result->setFetchMode(Phalcon\Db::FETCH_NUM); >> Menghasilkan Index array number
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC); // Menghasilkan Index array string as fields
		$data = $result->fetchAll();			

		if ($result->numRows() > 0)
			return $data;
		return array();
	} 	
	
	/*
	function get_data_neraca($connectionPDO, $account_type, $date_start, $end_start, $id_branch = false, $akt, $lvl, $is_closed = 1,  $pd=0, $v=1) {	
		$sql = "SELECT * FROM fa_account_type WHERE parent_id='".$account_type."' ORDER BY code";
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);				
		while ($r = $result->fetch()) {			
			$in = $pd + 30;
			$account_type = $r['code']; 
			
			if($v < $lvl)	{	
				$row = array(
								"accNm" => "<b>".$r['name']."</b>",
								"accVl" => "",
								"in" => $in,
								"sts" => "0",
								"lgt" => $v,
							);
				array_push($this->data,$row);		
			}
			else if($v == $lvl) {				
				$totNN = $this->get_data_aktivitas_value_limit($connectionPDO, $account_type, $date_start, $end_start, $id_branch, $akt, $is_closed = 1, $limit=0);			
				$row = array(
							"accNm" => $r['name'],
							"accVl" => $totNN,
							"in" => $in,
							"sts" => "1",
							"lgt" => $v,
						);
				array_push($this->data,$row);		
			}		
			
			if($v < $lvl)	{
				$NA = $this->get_data_aktivitas_acc($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $akt, $is_closed = 1);			
				foreach($NA as $vl) {
					$in = $pd + 45;
					$accVl = $vl['value'];
					if($vl['value'] == "")
						$accVl = 0;
					$row = array(
								"accNm" => $vl['name'],
								"accVl" => (int)$accVl,
								"in" => $in,
								"sts" => "1",
								"lgt" => $v+1,
						);
					array_push($this->data,$row);
				}			
				$this->get_data_aktivitas($connectionPDO, $account_type, $date_start, $end_start, $id_branch, $akt, $lvl, $is_closed = 1, $pd+15, $v+1);					
				
				$in = $pd + 30;
				$row = array(
								"accNm" => "<b>Total ".$r['name']."</b>",
								"accVl" => 0,
								"in" => $in,
								"sts" => "2",
								"lgt" => $v,
						);
				array_push($this->data,$row);
			}
		}		
		return $this->data;
	}	
	*/
	function get_data_aktivitas($connectionPDO, $account_type, $date_start, $end_start, $id_branch = false, $akt, $lvl, $is_closed = 1,  $pd=0, $v=1) {	
		$sql = "SELECT * FROM fa_account_type WHERE parent_id='".$account_type."' ORDER BY code";
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);				
		while ($r = $result->fetch()) {			
			$in = $pd + 30;
			$account_type = $r['code']; 
			
			if($v < $lvl)	{	
				$row = array(
								"accNm" => "<b>".$r['name']."</b>",
								"accVl" => "",
								"in" => $in,
								"sts" => "0",
								"lgt" => $v,
							);
				array_push($this->data,$row);		
				
				$NA = $this->get_data_aktivitas_acc($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $akt, $is_closed = 1);			
				foreach($NA as $vl) {
					$in = $pd + 45;
					$accVl = $vl['value'];
					if($vl['value'] == "")
						$accVl = 0;
					$row = array(
								"accNm" => $vl['name'],
								"accVl" => $accVl,
								"in" => $in,
								"sts" => "1",
								"lgt" => $v+1,
						);
					array_push($this->data,$row);
				}			
				$this->get_data_aktivitas($connectionPDO, $account_type, $date_start, $end_start, $id_branch, $akt, $lvl, $is_closed = 1, $pd+15, $v+1);					
				
				$in = $pd + 30;
				$row = array(
								"accNm" => "<b>Total ".$r['name']."</b>",
								"accVl" => 0,
								"in" => $in,
								"sts" => "2",
								"lgt" => $v,
						);
				array_push($this->data,$row);
			}
			else if($v == $lvl) {				
				$totNN = $this->get_data_aktivitas_value_limit($connectionPDO, $account_type, $date_start, $end_start, $id_branch, $akt, $is_closed = 1, $limit=0);			
				$row = array(
							"accNm" => $r['name'],
							"accVl" => $totNN,
							"in" => $in,
							"sts" => "1",
							"lgt" => $v,
						);
				array_push($this->data,$row);		
			}		
		}		
		return $this->data;
	}	
	
	function get_data_neraca_vs_budget($connectionPDO, $account_type, $date_start, $end_start, $id_branch = false, $akt, $lvl, $is_closed = 1,  $pd=0, $v=1) {	
		$sql = "SELECT * FROM fa_account_type WHERE parent_id='".$account_type."' ORDER BY code";
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);				
		while ($r = $result->fetch()) {			
			$in = $pd + 30;
			$account_type = $r['code']; 
			
			if($v < $lvl)	{	
				$row = array(
								"accNm" => "<b>".$r['name']."</b>",
								"accVl" => "",
								"bgtVl" => "",
								"in" => $in,
								"sts" => "0",
								"lgt" => $v,
							);
				array_push($this->data,$row);		
				
				$NA = $this->get_data_aktivitas_acc($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $akt, $is_closed = 1);			
				foreach($NA as $vl) {
					$in = $pd + 45;
					$accVl = $vl['value'];
					if($vl['value'] == "")
						$accVl = 0;
					
					$jBGT = $this->get_data_budget_acc($connectionPDO, $vl['code'], $date_start, $end_start, $id_branch , $akt, $is_closed = 1);								
					
					$row = array(
								"accNm" => $vl['name'],
								"accVl" => $accVl,
								"bgtVl" => $jBGT,								
								"in" => $in,
								"sts" => "1",
								"lgt" => $v+1,
						);
					array_push($this->data,$row);
				}			
				$this->get_data_neraca_vs_budget($connectionPDO, $account_type, $date_start, $end_start, $id_branch, $akt, $lvl, $is_closed = 1, $pd+15, $v+1);					
				
				$in = $pd + 30;
				$row = array(
								"accNm" => "<b>Total ".$r['name']."</b>",
								"accVl" => 0,
								"bgtVl" => 0,
								"in" => $in,
								"sts" => "2",
								"lgt" => $v,
						);
				array_push($this->data,$row);
			}
			else if($v == $lvl) {				
				$totNN = $this->get_data_aktivitas_value_limit($connectionPDO, $account_type, $date_start, $end_start, $id_branch, $akt, $is_closed = 1, $limit=0);			
				
				$totBGT = $this->get_data_neraca_vs_budget_value_limit($connectionPDO, $account_type, $date_start, $end_start, $id_branch, $akt, $is_closed = 1, $limit=0);			
				$row = array(
							"accNm" => $r['name'],
							"accVl" => $totNN,
							"bgtVl" => $totBGT,
							"in" => $in,
							"sts" => "1",
							"lgt" => $v,
						);
				array_push($this->data,$row);		
			}		
		}		
		return $this->data;
	}	
	
	function get_data_budget_acc($connectionPDO, $account, $date_start, $end_start, $id_branch , $akt, $is_closed = 1) {		
		$sqlBrc = "";	
		$year = date("Y",strtotime($date_start));
		$month = date("m",strtotime($date_start));		
		$this->totNN = 0;
		if ($id_branch) {
			$sqlBrc = "a.id = ".$id_branch." AND";
		}	
		
		if(!$end_start)
			$where = " d.year='".$year."' AND d.month='".(int)$month."'";
		else
			$where = " (date(a.trx_date) >= '".$date_start."' AND date(a.trx_date) <= '".$end_start."')";	
		$sql = "SELECT SUM(d.amount) AS value 
				FROM ms_branch a 
				INNER JOIN ms_directorate b ON a.id = b.ms_branch_id 
				INNER JOIN ms_department c ON b.id = c.ms_directorate_id 
				INNER JOIN fa_budget d ON d.ms_department_id = c.id 
				WHERE ".$sqlBrc.$where."
				AND d.fa_account_code='".$account."'	";			     
			
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		$r_1 = $result->fetch();
		$this->totNN = $r_1['value'];				
		return $this->totNN;
	}	
	
	function get_data_neraca_vs_budget_value_limit($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $akt, $is_closed = 1, $limit) {		
		$year = date("Y",strtotime($date_start));
		$month = date("m",strtotime($date_start));
		$sqlBrc = "";
		if($limit == 0) 
			$this->totNN = 0;
		if ($id_branch) {
			$sqlBrc = "a.id = ".$id_branch." AND";
		}	
		
		if(!$end_start)
			$where = " d.year='".$year."' AND d.month='".(int)$month."'";
		else
			$where = " AND (date(a.trx_date) >= '".$date_start."' AND date(a.trx_date) <= '".$end_start."')";	
		
		$sql = "SELECT SUM(d.amount) AS value 
				FROM ms_branch a 
				INNER JOIN ms_directorate b ON a.id = b.ms_branch_id 
				INNER JOIN ms_department c ON b.id = c.ms_directorate_id 
				INNER JOIN fa_budget d ON d.ms_department_id = c.id 
				INNER JOIN fa_account e ON d.fa_account_code=e.code								 				 
				WHERE ".$sqlBrc.$where."
				AND e.fa_account_type_code='".$account_type."'	";				     
			
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		$r_1 = $result->fetch();
		$this->totNN += $r_1['value'];		
		
		$sql = "SELECT * FROM fa_account_type
			    WHERE parent_id='".$account_type."'";
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		while ($r = $result->fetch()) {			
			$account_type = $r['code']; 			
			$this->get_data_neraca_vs_budget_value_limit($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $akt, $is_closed = 1, $limit+1);	
			
		}
		
		return $this->totNN;
	}	
	
	function get_data_aktivitas_value_limit($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $akt, $is_closed = 1, $limit) {		
		$sqlBrc = "";
		if($limit == 0) 
			$this->totNN = 0;
		if ($id_branch) {
			$sqlBrc = "a.ms_branch_id = ".$id_branch." AND";
		}	
		
		if(!$end_start)
			$where = " AND date(a.trx_date) <= '".$date_start."'";
		else
			$where = " AND (date(a.trx_date) >= '".$date_start."' AND date(a.trx_date) <= '".$end_start."')";	
		$sql = "SELECT (".$akt.") AS value FROM fa_journal_header a 
					 INNER JOIN fa_journal_detail b ON a.id = b.fa_journal_header_id 
					 INNER JOIN fa_account c ON b.fa_account_code=c.code
					INNER JOIN fa_fund_type d ON b.fa_fund_type_id = d.id
					INNER JOIN fa_fund_category_sub e ON d.fa_fund_category_sub_id = e.id
					INNER JOIN fa_fund_category f ON e.fa_fund_category_id = f.id					 						 
					 WHERE ".$sqlBrc." a.status IN (1,0) ".$where."
					 AND c.fa_account_type_code='".$account_type."'	";			     
			
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		$r_1 = $result->fetch();
		$this->totNN += $r_1['value'];		
		
		$sql = "SELECT * FROM fa_account_type
			    WHERE parent_id='".$account_type."'";
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		while ($r = $result->fetch()) {			
			$account_type = $r['code']; 			
			$this->get_data_aktivitas_value_limit($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $akt, $is_closed = 1, $limit+1);	
			
		}
		
		return $this->totNN;
	}	
	
	function get_data_aktivitas_acc($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $akt, $is_closed = 1) {			
		$sqlBrc = "";
		if ($id_branch) {
			$sqlBrc = "a.ms_branch_id = ".$id_branch." AND";
		}		
		
		if(!$end_start)
			$where = " AND date(a.trx_date) <= '".$date_start."'";
		else
			$where = " AND (date(a.trx_date) >= '".$date_start."' AND date(a.trx_date) <= '".$end_start."')";	
		
		$sql = "SELECT (".$akt.") AS value, c.name , c.code
				FROM fa_account c 
				LEFT JOIN 
				(SELECT b.debit, b.credit, b.currency_rate, b.fa_account_code 
				FROM fa_journal_header a 
			    INNER JOIN fa_journal_detail b ON a.id = b.fa_journal_header_id 
				INNER JOIN fa_fund_type d ON b.fa_fund_type_id = d.id
				INNER JOIN fa_fund_category_sub e ON d.fa_fund_category_sub_id = e.id
				INNER JOIN fa_fund_category f ON e.fa_fund_category_id = f.id					 	
				WHERE ".$sqlBrc." a.status IN (1,0) ".$where.") AS j
				ON j.fa_account_code=c.code								 
			    WHERE c.fa_account_type_code='".$account_type."' GROUP BY c.code";	
		
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		return $result->fetchAll();						
	}
	
	function get_saldo_awal_from_type($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $akt, $is_closed = 1, $limit) {		
		$sqlBrc = "";
		if($limit == 0) 
			$this->totNN = 0;
		if ($id_branch) {
			$sqlBrc = "a.ms_branch_id = ".$id_branch." AND";
		}	
		
		if(!$end_start)
			$where = " AND date(a.trx_date) < '".$date_start."'";
		else
			$where = " AND (date(a.trx_date) >= '".$date_start."' AND date(a.trx_date) <= '".$end_start."')";	
		
		$sql = "SELECT (".$akt.") AS value FROM fa_journal_header a 
					 INNER JOIN fa_journal_detail b ON a.id = b.fa_journal_header_id 
					 INNER JOIN fa_account c ON b.fa_account_code=c.code	
					INNER JOIN fa_fund_type d ON b.fa_fund_type_id = d.id
					INNER JOIN fa_fund_category_sub e ON d.fa_fund_category_sub_id = e.id
					INNER JOIN fa_fund_category f ON e.fa_fund_category_id = f.id					 						 
					 WHERE ".$sqlBrc." a.status IN (1,0) ".$where."
					 AND c.fa_account_type_code='".$account_type."'	";			     
			
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		$r_1 = $result->fetch();
		$this->totNN += $r_1['value'];		
		
		$sql = "SELECT * FROM fa_account_type
			    WHERE parent_id='".$account_type."'";
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		while ($r = $result->fetch()) {			
			$account_type = $r['code']; 			
			$this->get_saldo_awal_from_type($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $akt, $is_closed = 1, $limit+1);	
			
		}
		
		return $this->totNN;
	}	
	
	function getFormulaAC($connectionPDO, $account_type) {
		$sql_1 = "SELECT e.code as grp
				FROM fa_account_type d
				INNER JOIN fa_account_class e on e.code = d.fa_account_class_code
				WHERE d.code='".$account_type."'";	
		
		$result_1 = $connectionPDO->query($sql_1);					
		$result_1->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		$r = $result_1->fetch();
		switch ($r['grp']) {
			case  "1" :
				$SA = "SUM(debit * currency_rate) - SUM(credit * currency_rate)";
			break;
			case  "2" :
				$SA = "SUM(credit * currency_rate) - SUM(debit * currency_rate)";
			break;
			case  "3" :
				$SA = "SUM(credit * currency_rate) - SUM(debit * currency_rate)";
			break;
			case  "4" :
				$SA = "SUM(credit * currency_rate) - SUM(debit * currency_rate)";
			break;
			case  "5" :
				$SA = "SUM(debit * currency_rate) - SUM(credit * currency_rate)";
			break;
			case  "6" :
				$SA = "SUM(debit * currency_rate) - SUM(credit * currency_rate)";
			break;
		}
		return $SA;
	}
	
	function saldoAwalAccount($connectionPDO, $account, $date_pereriode, $id_branch = false ,$is_closed = 1) {
		$sql_1 = "SELECT e.code as kelompok,  SUM(b.debit * b.currency_rate) AS debit, SUM(b.credit * b.currency_rate) AS credit, c.name 
				FROM fa_journal_header a 
				inner join fa_journal_detail b ON a.id = b.fa_journal_header_id	
				inner join fa_account c on c.code =  b.fa_account_code 
				inner join fa_account_type  d on d.code = c.fa_account_type_code
				inner join fa_account_class e on e.code = d.fa_account_class_code
				INNER JOIN fa_fund_type g ON b.fa_fund_type_id = g.id
				INNER JOIN fa_fund_category_sub h ON g.fa_fund_category_sub_id = h.id
				INNER JOIN fa_fund_category i ON h.fa_fund_category_id = i.id					 	
				WHERE a.status IN (1,0) AND b.fa_account_code='".$account."' AND date(a.trx_date) < '".$date_pereriode."'";	
		
	    if ($id_branch) {
			$sql_1 .= " AND a.ms_branch_id = '".$id_branch."'";
		}
		$result_1 = $connectionPDO->query($sql_1);					
		$result_1->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		$r = $result_1->fetch();
		$SA = 0;
		$typeClass = $r['kelompok'];
		switch ($r['kelompok']) {
			case  "1" :
				$SA = $r['debit'] - $r['credit'];
			break;
			case  "2" :
				$SA = $r['credit'] - $r['debit'];
			break;
			case  "3" :
				$SA = $r['credit'] - $r['debit'];
			break;
			case  "4" :
				$SA = $r['credit'] - $r['debit'];
			break;
			case  "5" :
				$SA = $r['debit'] - $r['credit'];
			break;
			case  "6" :
				$SA = $r['debit'] - $r['credit'];
			break;
		}
		return array("typeClass"=>$typeClass,"amountBalance"=>$SA,"accNm"=>$r['name']);
	}
	
	function get_detail_trx_journal_by_periode($connectionPDO, $id_branch, $code_account, $date_start, $end_start, $is_closed=1) {
		$sql = "SELECT a.code,a.trx_date,b.memo,b.debit, b.credit, b.currency_rate FROM fa_journal_header a INNER JOIN fa_journal_detail b ON 	a.id=b.fa_journal_header_id
	   WHERE a.status IN (1,0) AND b.fa_account_code='".$code_account."' AND (date(a.trx_date) >= '".$date_start."' and  date(a.trx_date) <= '".$end_start."') ";

		if ($id_branch) {
			$sql .= " AND a.ms_branch_id = '".$id_branch."'";
		}			

		$sql .= " ORDER BY a.trx_date";		
		$result = $connectionPDO->query($sql);
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		$data = $result->fetchAll();
		return $data;
	}		
	
	function get_inquire_buku_besar($connectionPDO,$is_closed=1) {
		if(!isset($_REQUEST['idAccInquery'])) {
		    $json=array(
		                 'total' => 0,
					     'data' => ""
					   );		   
		}			   
		else {
			$where = "";
			$limit = 20;
			$start = 0;
			
			$request = new Phalcon\Http\Request();		
			if($request->get('limit')) 
				$limit = $request->get('limit');
			if($request->get('start')) 
				$start = $request->get('start');		
			
			$idAccInquery = $_REQUEST['idAccInquery'];			
			$start_date = $_REQUEST['start_date'];			
			$end_date = $_REQUEST['end_date'];			
			
			$sql = "SELECT SQL_CALC_FOUND_ROWS a.id AS a_id, a.code AS a_code, DATE_FORMAT(a.trx_date,'%d-%m-%Y') AS a_trx_date, b.memo AS b_memo, CASE WHEN b.debit IS NULL THEN 0 ELSE b.debit END AS b_debit, CASE WHEN b.credit IS NULL THEN 0 ELSE b.credit END AS b_credit, b.currency_rate AS currency_rate, c.code AS  c_code, c.name AS c_name, d.name AS d_name, e.code AS e_code  
					FROM fa_journal_header a 
					inner join fa_journal_detail b ON a.id = b.fa_journal_header_id	
					inner join fa_account c on c.code =  b.fa_account_code 
					inner join ms_branch d on d.id = a.ms_branch_id
					inner join ms_currency e on e.code = b.ms_currency_code
					WHERE a.status IN (1,0) AND b.fa_account_code='".$idAccInquery."' AND (date(a.trx_date) >= '".$start_date."' and  date(a.trx_date) <= '".$end_date."') ";		
			
			if($_REQUEST['branchId'] != "sm") {
				$id_branch = $_REQUEST['branchId'];
				$sql .= " AND a.ms_branch_id = '".$id_branch."'";
			}	
			else
				$id_branch = false;
			
			$sql .= " ORDER BY a.trx_date";			
			
			$result = $connectionPDO->query($sql);	
			$result->setFetchMode(Phalcon\Db::FETCH_ASSOC); // Menghasilkan Index array string as fields
			$rows = $result->fetchAll();	
			
			$sql = "SELECT FOUND_ROWS() as total";
			$result = $connectionPDO->query($sql);	
			$result->setFetchMode(Phalcon\Db::FETCH_ASSOC); // Menghasilkan Index array string as fields
			$total = $result->fetch();	

			$SA = $this->saldoAwalAccount($connectionPDO, $idAccInquery, $start_date, $id_branch = false ,$is_closed = 1);
			$trxMount = $SA['amountBalance'];
			$tot_d = 0;
			$tot_c = 0;
			$tot_prd = 0;
			foreach($rows as $v) {	
				$tot_d += $v['b_debit'] * $v['currency_rate'];
				$tot_c += $v['b_credit'] * $v['currency_rate'];
				switch ($SA['typeClass']) {
					case  "1" :
						$tot_prd += ($v['b_debit'] * $v['currency_rate']) - ($v['b_credit'] * $v['currency_rate']);					
						$trxMount = $trxMount + ($v['b_debit'] * $v['currency_rate']) - ($v['b_credit'] * $v['currency_rate']);
					break;
					case  "2" :
						$tot_prd += ($v['b_credit'] * $v['currency_rate']) - ($v['b_debit'] * $v['currency_rate']);					
						$trxMount = $trxMount - ($v['b_debit'] * $v['currency_rate']) + ($v['b_credit'] * $v['currency_rate']);
					break;
					case  "3" :
						$tot_prd += ($v['b_credit'] * $v['currency_rate']) - ($v['b_debit'] * $v['currency_rate']);					
						$trxMount = $trxMount - ($v['b_debit'] * $v['currency_rate']) + ($v['b_credit'] * $v['currency_rate']);
					break;
					case  "4" :
						$tot_prd += ($v['b_credit'] * $v['currency_rate']) - ($v['b_debit'] * $v['currency_rate']);					
						$trxMount = $trxMount - ($v['b_debit'] * $v['currency_rate']) + ($v['b_credit'] * $v['currency_rate']);
					break;
					case  "5" :
						$tot_prd += ($v['b_debit'] * $v['currency_rate']) - ($v['b_credit'] * $v['currency_rate']);					
						$trxMount = $trxMount + ($v['b_debit'] * $v['currency_rate']) - ($v['b_credit'] * $v['currency_rate']);
					break;
					case  "6" :
						$tot_prd += ($v['b_debit'] * $v['currency_rate']) - ($v['b_credit'] * $v['currency_rate']);					
						$trxMount = $trxMount + ($v['b_debit'] * $v['currency_rate']) - ($v['b_credit'] * $v['currency_rate']);
					break;
				}	
			}
			
			$json=array(
						   'total' => $total['total'],
						   'data' => $rows,
						   'sa' => $SA['amountBalance'],
						   'sak' => $trxMount,
						   'd' => $tot_d,
						   'prd' => $tot_prd,
						   'c' => $tot_c,						   
						 );
		}			 
		return $json;
	}
	
	function get_fund_category($connectionPDO) {
		$sql = "SELECT * FROM fa_fund_category ORDER BY id ";
		$result = $connectionPDO->query($sql);
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		$data = $result->fetchAll();
		return $data;
	}	
	
	function get_fund_category_saldo_dana_all($connectionPDO) {
		$sql = "SELECT fa_fund_category_id AS idFndCtg, fa_account_code AS accCode
				FROM fa_fund_category_saldo_dana";	
		$result = $connectionPDO->query($sql);
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		$data = $result->fetchAll();
		return $data;
	}	
	
	function get_fund_category_saldo_dana($connectionPDO,$acc) {
		$sql = "SELECT fa_fund_category_id AS idFndCtg 
				FROM fa_fund_category_saldo_dana
				WHERE fa_account_code='".$acc."'";	
		$result = $connectionPDO->query($sql);
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		$data = $result->fetch();
		return $data;
	}	
	
	function get_data_aktivitas_detail($connectionPDO, $account_type, $date_start, $end_start, $id_branch = false, $fct, $akt, $lvl, $is_closed = 1,  $pd=0, $v=1) {	
		$sql = "SELECT * FROM fa_account_type WHERE parent_id='".$account_type."' ORDER BY code";
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);				
		while ($r = $result->fetch()) {			
			$in = $pd + 30;
			$account_type = $r['code']; 
			
			if($v < $lvl)	{	
				$row = array(
								"accNm" => "<b>".$r['name']."</b>",
								"accVl" => "",
								"in" => $in,
								"sts" => "0",
								"lgt" => $v,
							);
				array_push($this->data,$row);		
				
				$NA = $this->get_data_aktivitas_acc_detail($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $fct, $akt, $is_closed = 1);			
				foreach($NA as $vl) {
					$in = $pd + 45;					
					$row = array(
								"accNm" => $vl['name'],
								"accVl" => $vl['value'],
								"in" => $in,
								"sts" => "1",
								"lgt" => $v+1,
						);
					array_push($this->data,$row);
				}			
				$this->get_data_aktivitas_detail($connectionPDO, $account_type, $date_start, $end_start, $id_branch, $fct, $akt, $lvl, $is_closed = 1, $pd+15, $v+1);					
				
				$in = $pd + 30;
				$row = array(
								"accNm" => "<b>Total ".$r['name']."</b>",
								"accVl" => 0,
								"in" => $in,
								"sts" => "2",
								"lgt" => $v,
						);
				array_push($this->data,$row);
			}
			else if($v == $lvl) {
				$data = array();
				foreach($fct as $vl) {
				    $totNN = $this->get_data_aktivitas_value_limit_detail($connectionPDO, $account_type, $date_start, $end_start, $id_branch, $vl['id'], $akt, $is_closed = 1, $limit=0);			
					
					if($totNN == 0)
						$totNN = "";
					array_push($data,$totNN);		
				}
				
				$row = array(
							"accNm" => $r['name'],
							"accVl" => $data,
							"in" => $in,
							"sts" => "1",
							"lgt" => $v,
						);
				array_push($this->data,$row);		
			}		
		}		
		return $this->data;
	}		
	
	function get_data_aktivitas_acc_detail($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $fct, $akt, $is_closed = 1) {			
		$sqlBrc = "";
		if ($id_branch) {
			$sqlBrc = "a.ms_branch_id = ".$id_branch." AND";
		}		
		
		if(!$end_start)
			$where = " AND date(a.trx_date) <= '".$date_start."'";
		else
			$where = " AND (date(a.trx_date) >= '".$date_start."' AND date(a.trx_date) <= '".$end_start."')";	
		
		$sql = "SELECT c.name , c.code
				FROM fa_account c 
			    WHERE c.fa_account_type_code='".$account_type."' GROUP BY c.code";	
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);	
		$rows =	array(); 
		while ($r = $result->fetch()) {									
			$row = array();
			foreach($fct as $v) {
				$sql1 = "SELECT (".$akt.") AS value
				FROM fa_journal_header a 
			    INNER JOIN fa_journal_detail b ON a.id = b.fa_journal_header_id
				INNER JOIN fa_fund_type c ON b.fa_fund_type_id = c.id
				INNER JOIN fa_fund_category_sub d ON c.fa_fund_category_sub_id = d.id
				INNER JOIN fa_fund_category e ON d.fa_fund_category_id = e.id
				WHERE b.fa_account_code = '".$r['code']."' AND e.id = ".$v['id']." AND ".$sqlBrc." a.status IN (1,0) ".$where;
				
				$result1 = $connectionPDO->query($sql1);
				$result1->setFetchMode(Phalcon\Db::FETCH_ASSOC);
				$data = $result1->fetch();	
				
				$accVl = $data['value'];
				if($data['value'] == NULL)
					$accVl = "";
					
				array_push($row,$accVl);		
			}				
			$data = array(
					'code' => $r['code'],
					'name' => $r['name'],
					'value' => $row,
			);
			array_push($rows,$data);		
		}
		return $rows;	
	}
	
	function get_data_aktivitas_value_limit_detail($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $fct, $akt, $is_closed = 1, $limit) {		
		$sqlBrc = "";
		if($limit == 0) 
			$this->totNN = 0;
		if ($id_branch) {
			$sqlBrc = "a.ms_branch_id = ".$id_branch." AND";
		}	
		
		if(!$end_start)
			$where = " AND date(a.trx_date) <= '".$date_start."'";
		else
			$where = " AND (date(a.trx_date) >= '".$date_start."' AND date(a.trx_date) <= '".$end_start."')";	
		$sql = "SELECT (".$akt.") AS value FROM fa_journal_header a 
					 INNER JOIN fa_journal_detail b ON a.id = b.fa_journal_header_id 
					 INNER JOIN fa_account c ON b.fa_account_code=c.code
					INNER JOIN fa_fund_type d ON b.fa_fund_type_id = d.id
					INNER JOIN fa_fund_category_sub e ON d.fa_fund_category_sub_id = e.id
					INNER JOIN fa_fund_category f ON e.fa_fund_category_id = f.id					 
					 WHERE ".$sqlBrc." a.status IN (1,0) ".$where."
					 AND c.fa_account_type_code='".$account_type."'	AND f.id =".$fct." ";			     
			
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		$r_1 = $result->fetch();
		if($r_1['value'] != NULL)
			$this->totNN += $r_1['value'];		
		
		$sql = "SELECT * FROM fa_account_type
			    WHERE parent_id='".$account_type."'";
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		while ($r = $result->fetch()) {			
			$account_type = $r['code']; 			
			$this->get_data_aktivitas_value_limit_detail($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $fct, $akt, $is_closed = 1, $limit+1);	
			
		}
		return $this->totNN;
	}	
	
	function get_saldo_awal_detail_from_type($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $fct, $akt, $is_closed = 1, $limit) {		
		$sqlBrc = "";
		if($limit == 0) 
			$this->totNN = 0;
		if ($id_branch) {
			$sqlBrc = "a.ms_branch_id = ".$id_branch." AND";
		}			
		
		if(!$end_start)
			$where = " AND date(a.trx_date) < '".$date_start."'";
		else
			$where = " AND (date(a.trx_date) >= '".$date_start."' AND date(a.trx_date) <= '".$end_start."')";	
		
		$sql = "SELECT (".$akt.") AS value FROM fa_journal_header a 
					 INNER JOIN fa_journal_detail b ON a.id = b.fa_journal_header_id 
					 INNER JOIN fa_account c ON b.fa_account_code=c.code	
					INNER JOIN fa_fund_type d ON b.fa_fund_type_id = d.id
					INNER JOIN fa_fund_category_sub e ON d.fa_fund_category_sub_id = e.id
					INNER JOIN fa_fund_category f ON e.fa_fund_category_id = f.id					 	
					 WHERE ".$sqlBrc." a.status IN (1,0) ".$where."
					 AND c.fa_account_type_code='".$account_type."'	AND f.id =".$fct." ";			     
			
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		$r_1 = $result->fetch();
		if($r_1['value'] != NULL)
			$this->totNN += $r_1['value'];		
		
		$sql = "SELECT * FROM fa_account_type
			    WHERE parent_id='".$account_type."'";
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		while ($r = $result->fetch()) {			
			$account_type = $r['code']; 			
			$this->get_saldo_awal_detail_from_type($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $fct, $akt, $is_closed = 1, $limit+1);	
			
		}
		
		return $this->totNN;
	}	
	
	//ARUS KAS
	function get_data_aktivitas_two_year($connectionPDO, $account_type, $date_start, $end_start, $date_starts, $end_starts, $id_branch = false, $akt, $lvl, $is_closed = 1,  $pd=0, $v=1) {	
		$sql = "SELECT * FROM fa_account_type WHERE parent_id='".$account_type."' ORDER BY code";
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);				
		while ($r = $result->fetch()) {			
			$in = $pd + 30;
			$account_type = $r['code']; 
			
			if($v < $lvl)	{	
				$row = array(
								"accNm" => "<b>".$r['name']."</b>",
								"accVl" => array("",""),
								"in" => $in,
								"sts" => "0",
								"lgt" => $v,
							);
				array_push($this->data,$row);		
				
				$NA = $this->get_data_aktivitas_two_year_acc($connectionPDO, $account_type, $date_start, $end_start, $date_starts, $end_starts, $id_branch , $akt, $is_closed = 1);			
				foreach($NA as $vl) {
					$in = $pd + 45;
					$accVl = $vl['value'];
					if($vl['value'] == "")
						$accVl = 0;
					$accVl_1 = $vl['value_1'];
					if($vl['value_1'] == "")
						$accVl_1 = 0;	
					$row = array(
								"accNm" => $vl['name'],
								"accVl" => array($accVl,$accVl_1),
								"in" => $in,
								"sts" => "1",
								"lgt" => $v+1,
						);
					array_push($this->data,$row);
				}			
				$this->get_data_aktivitas_two_year($connectionPDO, $account_type, $date_start, $end_start, $date_starts, $end_starts, $id_branch, $akt, $lvl, $is_closed = 1, $pd+15, $v+1);					
				
				$in = $pd + 30;
				$row = array(
								"accNm" => "<b>Total ".$r['name']."</b>",
								"accVl" => array(0,0),
								"in" => $in,
								"sts" => "2",
								"lgt" => $v,
						);
				array_push($this->data,$row);
			}
			else if($v == $lvl) {				
				$totNN = $this->get_data_aktivitas_value_limit_two_year($connectionPDO, $account_type, $date_start, $end_start, $date_starts, $end_starts, $id_branch, $akt, $is_closed = 1, $limit=0);			
				$row = array(
							"accNm" => $r['name'],
							"accVl" => $totNN,
							"in" => $in,
							"sts" => "1",
							"lgt" => $v,
						);
				array_push($this->data,$row);		
			}		
		}		
		return $this->data;
	}	
	
	function get_data_aktivitas_two_year_acc($connectionPDO, $account_type, $date_start, $end_start, $date_starts, $end_starts, $id_branch , $akt, $is_closed = 1) {			
		$sqlBrc = "";
		if ($id_branch) {
			$sqlBrc = "a.ms_branch_id = ".$id_branch." AND";
		}		
		
		if(!$end_start) {
			$where = " AND date(a.trx_date) <= '".$date_start."'";
			$where_1 = " AND date(a.trx_date) <= '".$date_starts."'";
		}	
		else {
			$where = " AND (date(a.trx_date) >= '".$date_start."' AND date(a.trx_date) <= '".$end_start."')";	
			$where_1 = " AND (date(a.trx_date) >= '".$date_starts."' AND date(a.trx_date) <= '".$end_starts."')";	
		}
		$sql = "SELECT (SELECT (".$akt.") FROM fa_journal_header a 
						INNER JOIN fa_journal_detail b ON a.id = b.fa_journal_header_id 
						INNER JOIN fa_fund_type d ON b.fa_fund_type_id = d.id
						INNER JOIN fa_fund_category_sub e ON d.fa_fund_category_sub_id = e.id
						INNER JOIN fa_fund_category f ON e.fa_fund_category_id = f.id					 	
						WHERE b.fa_account_code=c.code AND ".$sqlBrc." a.status IN (1,0) ".$where.") AS value, 
						(SELECT (".$akt.") FROM fa_journal_header a 
						INNER JOIN fa_journal_detail b ON a.id = b.fa_journal_header_id 
						INNER JOIN fa_fund_type d ON b.fa_fund_type_id = d.id
						INNER JOIN fa_fund_category_sub e ON d.fa_fund_category_sub_id = e.id
						INNER JOIN fa_fund_category f ON e.fa_fund_category_id = f.id					 	
						WHERE b.fa_account_code=c.code AND ".$sqlBrc." a.status IN (1,0) ".$where_1.") AS value_1, 				
						c.name , c.code
				FROM fa_account c 
			    WHERE c.fa_account_type_code='".$account_type."' ORDER BY c.code";	
		
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		return $result->fetchAll();						
	}
	
	function get_data_aktivitas_value_limit_two_year($connectionPDO, $account_type, $date_start, $end_start, $date_starts, $end_starts, $id_branch , $akt, $is_closed = 1, $limit) {		
		$sqlBrc = "";
		if($limit == 0) {
			$this->totNN = 0;
			$this->totNN_1 = 0;
		}
		if ($id_branch) {
			$sqlBrc = "a.ms_branch_id = ".$id_branch." AND";
		}	
		
		if(!$end_start) {
			$where = " AND date(a.trx_date) <= '".$date_start."'";
			$where_1 = " AND date(a.trx_date) <= '".$date_starts."'";
		}	
		else {
			$where = " AND (date(a.trx_date) >= '".$date_start."' AND date(a.trx_date) <= '".$end_start."') ";	
			$where_1 = " AND (date(a.trx_date) >= '".$date_starts."' AND date(a.trx_date) <= '".$end_starts."') ";	
		}
		$sql = "SELECT (SELECT (".$akt.")
						FROM fa_journal_header a 
						INNER JOIN fa_journal_detail b ON a.id = b.fa_journal_header_id 
						INNER JOIN fa_account c ON b.fa_account_code=c.code	
						INNER JOIN fa_fund_type d ON b.fa_fund_type_id = d.id
						INNER JOIN fa_fund_category_sub e ON d.fa_fund_category_sub_id = e.id
						INNER JOIN fa_fund_category f ON e.fa_fund_category_id = f.id					 						 
						WHERE ".$sqlBrc." a.status IN (1,0) ".$where."	
						AND c.fa_account_type_code='".$account_type."' 	
						) AS value,
						(SELECT (".$akt.")
						FROM fa_journal_header a 
						INNER JOIN fa_journal_detail b ON a.id = b.fa_journal_header_id 
						INNER JOIN fa_account c ON b.fa_account_code=c.code	
						INNER JOIN fa_fund_type d ON b.fa_fund_type_id = d.id
						INNER JOIN fa_fund_category_sub e ON d.fa_fund_category_sub_id = e.id
						INNER JOIN fa_fund_category f ON e.fa_fund_category_id = f.id					 						 
						WHERE ".$sqlBrc." a.status IN (1,0) ".$where_1."	
						AND c.fa_account_type_code='".$account_type."' 	
						) AS value_1";
			
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		$r_1 = $result->fetch();
		if($r_1['value'] != NULL)
			$this->totNN += $r_1['value'];		
		if($r_1['value_1'] != NULL)	
			$this->totNN_1 += $r_1['value_1'];		
		
		$sql = "SELECT * FROM fa_account_type
			    WHERE parent_id='".$account_type."'";
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		while ($r = $result->fetch()) {			
			$account_type = $r['code']; 			
			$this->get_data_aktivitas_value_limit_two_year($connectionPDO, $account_type, $date_start, $end_start, $date_starts, $end_starts, $id_branch , $akt, $is_closed = 1, $limit+1);	
			
		}
		
		return array($this->totNN,$this->totNN_1);
	}	
	
	function get_data_aktivitas_saldo_dana($connectionPDO, $account_type, $date_start, $end_start, $id_branch = false, $akt, $lvl, $dt1, $is_closed = 1,  $pd=0, $v=1) {	
		$sql = "SELECT * FROM fa_account_type WHERE parent_id='".$account_type."' ORDER BY code";
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);				
		while ($r = $result->fetch()) {			
			$in = $pd + 30;
			$account_type = $r['code']; 
			
			if($v < $lvl)	{	
				$row = array(
								"accNm" => "<b>".$r['name']."</b>",
								"accVl" => "",
								"in" => $in,
								"sts" => "0",
								"lgt" => $v,
							);
				array_push($this->data,$row);		
				
				$NA = $this->get_data_aktivitas_acc($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $akt, $is_closed = 1);			
				foreach($NA as $vl) {
					$in = $pd + 45;
					
					$accVl = $vl['value'];
					if($vl['value'] == "")
						$accVl = 0;
					$rF = $this->get_fund_category_saldo_dana($connectionPDO,$vl['code']);	
					if(!$end_start) {
						$ST1 = $this->laba_rugi($connectionPDO, $dt1, $end_start, $id_branch , $rF['idFndCtg'],$vl['code']);
						
						$ST = $ST1 + $accVl;						
					}
					else {
						$ST = $this->laba_rugi($connectionPDO, $date_start, $end_start, $id_branch , $rF['idFndCtg'],$vl['code']);
					}
					
					
					$row = array(
								"accNm" => $vl['name'],
								"accVl" => $ST,
								"in" => $in,
								"sts" => "1",
								"lgt" => $v+1,
						);
					array_push($this->data,$row);
				}			
				$this->get_data_aktivitas_saldo_dana($connectionPDO, $account_type, $date_start, $end_start, $id_branch, $akt, $lvl, $dt1, $is_closed = 1, $pd+15, $v+1);					
				
				$in = $pd + 30;
				$row = array(
								"accNm" => "<b>Total ".$r['name']."</b>",
								"accVl" => 0,
								"in" => $in,
								"sts" => "2",
								"lgt" => $v,
						);
				array_push($this->data,$row);
			}
			else if($v == $lvl) {				
				$totNN = $this->get_data_aktivitas_value_limit_saldo_dana($connectionPDO, $account_type, $date_start, $end_start, $id_branch, $akt, $dt1, $is_closed = 1, $limit=0);			
				$row = array(
							"accNm" => $r['name'],
							"accVl" => $totNN,
							"in" => $in,
							"sts" => "1",
							"lgt" => $v,
						);
				array_push($this->data,$row);		
			}		
		}		
		return $this->data;
	}		

	function get_data_aktivitas_value_limit_saldo_dana($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $akt, $dt1, $is_closed = 1, $limit) {		
		$sqlBrc = "";
		if($limit == 0) 
			$this->totNN_1 = 0;
		if ($id_branch) {
			$sqlBrc = "a.ms_branch_id = ".$id_branch." AND";
		}	
		
		if(!$end_start)
			$where = " AND date(a.trx_date) <= '".$date_start."'";
		else
			$where = " AND (date(a.trx_date) >= '".$date_start."' AND date(a.trx_date) <= '".$end_start."')";	
		
		$sqlcc = "SELECT c.name , c.code
				FROM fa_account c 
			    WHERE c.fa_account_type_code='".$account_type."'";	
		$resultacc = $connectionPDO->query($sqlcc);					
		$resultacc->setFetchMode(Phalcon\Db::FETCH_ASSOC);	
		$rows =	array(); 
		while ($rcc = $resultacc->fetch()) {		
					$rF = $this->get_fund_category_saldo_dana($connectionPDO,$rcc['code']);						
					if(!$end_start) {
						 $sql = "SELECT (".$akt.") AS value FROM fa_journal_header a 
								INNER JOIN fa_journal_detail b ON a.id = b.fa_journal_header_id 
								INNER JOIN fa_account c ON b.fa_account_code=c.code
								INNER JOIN fa_fund_type d ON b.fa_fund_type_id = d.id
								INNER JOIN fa_fund_category_sub e ON d.fa_fund_category_sub_id = e.id
								INNER JOIN fa_fund_category f ON e.fa_fund_category_id =f.id					 						 
								WHERE ".$sqlBrc." a.status IN (1,0) ".$where."
								AND c.code='".$rcc['code']."'";
							 
								$result = $connectionPDO->query($sql);					
								$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);	
								$r1 = $result->fetch();
								
								$accVl = $r1['value'];
								if($r1['value'] == NULL)
									$accVl = 0;						
						
						$ST1 = $this->laba_rugi($connectionPDO, $dt1, $end_start, $id_branch , $rF['idFndCtg'],$rcc['code']);
						
						$ST = $ST1 + $accVl;						
					}
					else {
						$ST = $this->laba_rugi($connectionPDO, $date_start, $end_start, $id_branch , $rF['idFndCtg'],$rcc['code']);
					}
			$this->totNN_1 += $ST;							 
		}
		
		$sql = "SELECT * FROM fa_account_type
			    WHERE parent_id='".$account_type."'";
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		while ($r = $result->fetch()) {			
			$account_type = $r['code']; 			
			$this->get_data_aktivitas_value_limit_saldo_dana($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $akt, $dt1, $is_closed = 1, $limit+1);	
			
		}
		
		return $this->totNN_1;
	}	
	
	function get_data_aktivitas_arus_kas_langsung($connectionPDO, $account_type, $date_start, $end_start, $id_branch = false, $akt, $lvl, $sld = false, $is_closed = 1,  $pd=0, $v=1) {	
		$sql = "SELECT * FROM fa_account_type WHERE parent_id='".$account_type."' AND code!='111000000000' ORDER BY code";
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);				
		while ($r = $result->fetch()) {			
			$in = $pd + 30;
			$account_type = $r['code']; 
			
			if($v < $lvl)	{	
				$row = array(
								"code" => $r['code'],
								"accNm" => "<b>".$r['name']."</b>",
								"accVl" => "",
								"in" => $in,
								"sts" => "0",
								"lgt" => $v,
							);
				array_push($this->data,$row);		
				
				$NA = $this->get_data_aktivitas_acc_arus_kas_langsung($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $akt, $is_closed = 1, $sld);			
				foreach($NA as $vl) {
					$in = $pd + 45;
					$accVl = $vl['value'];
					if($vl['value'] == "")
						$accVl = 0;
					$row = array(
								"code" => $vl['code'],
								"accNm" => $vl['name'],
								"accVl" => $accVl,
								"in" => $in,
								"sts" => "1",
								"lgt" => $v+1,
						);
					array_push($this->data,$row);
				}			
				$this->get_data_aktivitas_arus_kas_langsung($connectionPDO, $account_type, $date_start, $end_start, $id_branch, $akt, $lvl, $sld, $is_closed = 1, $pd+15, $v+1);					
				
				$in = $pd + 30;
				$row = array(
								"code" => $r['code'],
								"accNm" => "<b>Total ".$r['name']."</b>",
								"accVl" => 0,
								"in" => $in,
								"sts" => "2",
								"lgt" => $v,
						);
				array_push($this->data,$row);
			}
			else if($v == $lvl) {				
				$totNN = $this->get_data_aktivitas_value_limit_arus_kas_langsung($connectionPDO, $account_type, $date_start, $end_start, $id_branch, $akt, $is_closed = 1, $limit=0,$sld);			
				$row = array(
							"code" => $totNN[1],
							"accNm" => $r['name'],
							"accVl" => $totNN[0],
							"in" => $in,
							"sts" => "1",
							"lgt" => $v,
						);
				array_push($this->data,$row);		
			}		
		}		
		return $this->data;
	}	
	
	function get_data_aktivitas_acc_arus_kas_langsung($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $akt, $is_closed = 1, $sld) {			
		$sqlBrc = "";
		if ($id_branch) {
			$sqlBrc = "a.ms_branch_id = ".$id_branch." AND";
		}		
		
		if(!$end_start)
			$where = " AND date(a.trx_date) <= '".$date_start."'";
		else
			$where = " AND (date(a.trx_date) >= '".$date_start."' AND date(a.trx_date) <= '".$end_start."')";	
		if($sld)
			$where .= " AND (SELECT COUNT(*) FROM fa_journal_detail g WHERE g.fa_journal_header_id = a.id AND g.fa_account_code LIKE '111%') > 0";
		
		$sql = "SELECT (".$akt.") AS value, c.name , c.code
				FROM fa_account c 
				LEFT JOIN 
				(SELECT b.debit, b.credit, b.currency_rate, b.fa_account_code 
				FROM fa_journal_header a 
			    INNER JOIN fa_journal_detail b ON a.id = b.fa_journal_header_id 
				INNER JOIN fa_fund_type d ON b.fa_fund_type_id = d.id
				INNER JOIN fa_fund_category_sub e ON d.fa_fund_category_sub_id = e.id
				INNER JOIN fa_fund_category f ON e.fa_fund_category_id = f.id					 	
				WHERE ".$sqlBrc." a.status IN (1,0) ".$where.") AS j
				ON j.fa_account_code=c.code								 
			    WHERE c.fa_account_type_code='".$account_type."' GROUP BY c.code";	
		
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		return $result->fetchAll();						
	}
	
	function get_data_aktivitas_value_limit_arus_kas_langsung($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $akt, $is_closed = 1, $limit, $sld) {		
		$sqlBrc = "";
		if($limit == 0) {
			$this->totNN = 0;
			$account_type_ = $account_type;
		}	
		if ($id_branch) {
			$sqlBrc = "a.ms_branch_id = ".$id_branch." AND";
		}	
		
		if(!$end_start)
			$where = " AND date(a.trx_date) <= '".$date_start."'";
		else
			$where = " AND (date(a.trx_date) >= '".$date_start."' AND date(a.trx_date) <= '".$end_start."')";	
			
		if($sld)
			$where .= " AND (SELECT COUNT(*) FROM fa_journal_detail g WHERE g.fa_journal_header_id = a.id AND g.fa_account_code LIKE '111%') > 0";
		$sql = "SELECT (".$akt.") AS value FROM fa_journal_header a 
					 INNER JOIN fa_journal_detail b ON a.id = b.fa_journal_header_id 
					 INNER JOIN fa_account c ON b.fa_account_code=c.code
					INNER JOIN fa_fund_type d ON b.fa_fund_type_id = d.id
					INNER JOIN fa_fund_category_sub e ON d.fa_fund_category_sub_id = e.id
					INNER JOIN fa_fund_category f ON e.fa_fund_category_id = f.id					 						 
					 WHERE ".$sqlBrc." a.status IN (1,0) ".$where."					 
					 AND c.fa_account_type_code='".$account_type."'	";			     
			
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		$r_1 = $result->fetch();
		$this->totNN += $r_1['value'];		
		
		$sql = "SELECT * FROM fa_account_type
			    WHERE parent_id='".$account_type."'";
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		while ($r = $result->fetch()) {			
			$account_type = $r['code']; 			
			$this->get_data_aktivitas_value_limit($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $akt, $is_closed = 1, $limit+1, $sld);	
			
		}
		
		return array($this->totNN,$account_type_);
	}	
	
	function get_data_aktivitas_detail_saldo_dana($connectionPDO, $account_type, $date_start, $end_start, $id_branch = false, $fct, $akt, $arr_fct_sld, $lvl, $dt1, $sldChk, $is_closed = 1,  $pd=0, $v=1) {	
		
		$sql = "SELECT * FROM fa_account_type WHERE parent_id='".$account_type."' ORDER BY code";
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);		
		//$detail = true;	
		$jd = 0;
		while ($r = $result->fetch()) {			
			$in = $pd + 50;
			$account_type = $r['code']; 
			
			if($v < $lvl)	{	
				if($sldChk == 1) {
					$row = array(
									"code" => "<b>".$r['code']."</b>",
									"accNm" => "<b>".$r['name']."</b>",
									"accVl" => "",
									"in" => $in,
									"sts" => "0",
									"lgt" => $v,
								);
					array_push($this->data,$row);						
					
					$NA = $this->get_data_aktivitas_acc_saldo_dana_detail($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $fct, $akt, $arr_fct_sld, $is_closed = 1, $dt1);			
					foreach($NA as $vl) {
						$in = $pd + 65;					
						$row = array(
									"code" => $vl['code'],
									"accNm" => $vl['name'],
									"accVl" => $vl['value'],
									"in" => $in,
									"sts" => "1",
									"lgt" => $v+1,
							);
						array_push($this->data,$row);
					}			
				}
				else {
					$sql_c = "SELECT COUNT(*) AS jd FROM fa_account_type WHERE parent_id='".$account_type."'";
					$result_c = $connectionPDO->query($sql_c);					
					$result_c->setFetchMode(Phalcon\Db::FETCH_ASSOC);				
					$rc = $result_c->fetch();
					$jd = $rc['jd'];
					if($rc['jd'] > 0) {
						$row = array(
									"code" => "<b>".$r['code']."</b>",
									"accNm" => "<b>".$r['name']."</b>",
									"accVl" => "",
									"in" => $in,
									"sts" => "0",
									"lgt" => $v,
								);
					}
					else {
						$data = array();
						foreach($fct as $vl) {
							$totNN = $this->get_data_aktivitas_value_limit_detail_saldo_dana($connectionPDO, $account_type, $date_start, $end_start, $id_branch, $vl['id'], $akt,  $arr_fct_sld, $is_closed = 1, $limit=0, $dt1);			
							
							if($totNN == 0)
								$totNN = "";
							array_push($data,$totNN);		
						}
						
						$row = array(
									"code" => $r['code'],
									"accNm" => $r['name'],
									"accVl" => $data,
									"in" => $in,
									"sts" => "1",
									"lgt" => $v,
								);
						
					}
					array_push($this->data,$row);		
				}				
				$this->get_data_aktivitas_detail_saldo_dana($connectionPDO, $account_type, $date_start, $end_start, $id_branch, $fct, $akt, $arr_fct_sld, $lvl, $dt1, $sldChk, $is_closed = 1, $pd+15, $v+1);					
				if($jd > 0 OR $sldChk == 1) {
					$in = $pd + 50;
					$row = array(
									"code" => $r['code'],
									"accNm" => "<b>Total ".$r['name']."</b>",
									"accVl" => 0,
									"in" => $in,
									"sts" => "2",
									"lgt" => $v,
							);
					array_push($this->data,$row);
				}
			}
			else if($v == $lvl) {
				$data = array();
				foreach($fct as $vl) {
				    $totNN = $this->get_data_aktivitas_value_limit_detail_saldo_dana($connectionPDO, $account_type, $date_start, $end_start, $id_branch, $vl['id'], $akt,  $arr_fct_sld, $is_closed = 1, $limit=0, $dt1);			
					
					if($totNN == 0)
						$totNN = "";
					array_push($data,$totNN);		
				}
				
				$row = array(
							"accNm" => $r['name'],
							"code" => $r['code'],
							"accVl" => $data,
							"in" => $in,
							"sts" => "1",
							"lgt" => $v,
						);
				array_push($this->data,$row);		
			}		
		}		
		return $this->data;
	}	

	function get_data_aktivitas_acc_saldo_dana_detail($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $fct, $akt, $arr_fct_sld, $is_closed = 1, $dt1) {			
		$sqlBrc = "";
		if ($id_branch) {
			$sqlBrc = "a.ms_branch_id = ".$id_branch." AND";
		}		
		
		if(!$end_start)
			$where = " AND date(a.trx_date) <= '".$date_start."'";
		else
			$where = " AND (date(a.trx_date) >= '".$date_start."' AND date(a.trx_date) <= '".$end_start."')";	
		
		$sql = "SELECT c.name , c.code
				FROM fa_account c 
			    WHERE c.fa_account_type_code='".$account_type."' GROUP BY c.code";	
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);	
		$rows =	array(); 
		while ($r = $result->fetch()) {									
			$row = array();
			foreach($fct as $v) {
				if(isset($arr_fct_sld[$r['code']])) {				
					if($arr_fct_sld[$r['code']] == $v['id']) {		
						$sql1 = "SELECT (".$akt.") AS value
						FROM fa_journal_header a 
						INNER JOIN fa_journal_detail b ON a.id = b.fa_journal_header_id
						INNER JOIN fa_fund_type c ON b.fa_fund_type_id = c.id
						INNER JOIN fa_fund_category_sub d ON c.fa_fund_category_sub_id = d.id
						INNER JOIN fa_fund_category e ON d.fa_fund_category_id = e.id
						WHERE b.fa_account_code = '".$r['code']."' AND e.id = ".$v['id']." AND ".$sqlBrc." a.status IN (1,0) ".$where;
						
						$result1 = $connectionPDO->query($sql1);
						$result1->setFetchMode(Phalcon\Db::FETCH_ASSOC);
						$data = $result1->fetch();	
						
						$accVl = $data['value'];
						if($data['value'] == NULL)
							$accVl = 0;							
							if(!$end_start) {
								$ST = $this->laba_rugi($connectionPDO, $dt1, $end_start, $id_branch , $v['id'], $r['code']);																
									$accVl += $ST;						
							}
							else {
								$accVl = $this->laba_rugi($connectionPDO, $date_start, $end_start, $id_branch , $v['id'], $r['code']);
							}	
					}		
					else {
						$accVl = "";
					}				
				}
				else {
					$accVl = "";
				}				
				array_push($row,$accVl);		
			}				
			$data = array(
					'code' => $r['code'],
					'name' => $r['name'],
					'value' => $row,
			);
			array_push($rows,$data);		
		}
		return $rows;	
	}	
	
	
	function get_data_aktivitas_value_limit_detail_saldo_dana($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $fct, $akt, $arr_fct_sld, $is_closed = 1, $limit, $dt1) {		
		$sqlBrc = "";
		if($limit == 0) {
			$this->totNN = 0;
			$this->totNN_1 = 0;
		}	
		if ($id_branch) {
			$sqlBrc = "a.ms_branch_id = ".$id_branch." AND";
		}	
		
		if(!$end_start)
			$where = " AND date(a.trx_date) <= '".$date_start."'";
		else
			$where = " AND (date(a.trx_date) >= '".$date_start."' AND date(a.trx_date) <= '".$end_start."')";	
		
		$sqlcc = "SELECT c.name , c.code
				FROM fa_account c 
			    WHERE c.fa_account_type_code='".$account_type."'";	
		$resultacc = $connectionPDO->query($sqlcc);					
		$resultacc->setFetchMode(Phalcon\Db::FETCH_ASSOC);	
		$rows =	array(); 
		while ($rcc = $resultacc->fetch()) {		
				if(isset($arr_fct_sld[$rcc['code']])) {				
					if($arr_fct_sld[$rcc['code']] == $fct) {			
						//$rF = $this->get_fund_category_saldo_dana($connectionPDO,$rcc['code']);						
						if(!$end_start) {
								$sql_1 = "SELECT (".$akt.") AS value FROM fa_journal_header a 
										INNER JOIN fa_journal_detail b ON a.id = b.fa_journal_header_id
										INNER JOIN fa_fund_type c ON b.fa_fund_type_id = c.id
										INNER JOIN fa_fund_category_sub d ON c.fa_fund_category_sub_id = d.id
										INNER JOIN fa_fund_category e ON d.fa_fund_category_id = e.id
										WHERE b.fa_account_code = '".$rcc['code']."' AND e.id = ".$fct." AND ".$sqlBrc." a.status IN (1,0) ".$where;
							
								$result_1 = $connectionPDO->query($sql_1);					
								$result_1->setFetchMode(Phalcon\Db::FETCH_ASSOC);	
								$r1 = $result_1->fetch();
								$accVl = $r1['value']; 
								if($r1['value'] == NULL)
									$accVl = 0;						
							
								$ST1 = $this->laba_rugi($connectionPDO, $dt1, $end_start, $id_branch , $fct, $rcc['code']);
								$ST = $ST1 + $accVl;						
						}
						else {							
							$ST = $this->laba_rugi($connectionPDO, $date_start, $end_start, $id_branch , $fct, $rcc['code']);
						}
					}	
					else {
						$ST = 0;
					}				
				}
				else {
					$ST = 0;
				}		
			$this->totNN_1 += $ST;							 
		}
		
		$sql = "SELECT * FROM fa_account_type
			    WHERE parent_id='".$account_type."'";
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		while ($r = $result->fetch()) {			
			$account_type = $r['code']; 			
			$this->get_data_aktivitas_value_limit_detail_saldo_dana($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $fct, $akt, $arr_fct_sld, $is_closed = 1, $limit+1, $dt1);	
			
		}
		
		return $this->totNN_1;
	}	
	
	public function laba_rugi($connectionPDO, $date_start, $end_start, $id_branch , $fct, $acc) {
		$akt1 = $this->getFormulaAC($connectionPDO, '400000000000'); 
		$S4 = $this->get_saldo_awal_saldo_dana($connectionPDO, '400000000000', $date_start, $end_start, $id_branch , $akt1, $is_closed = 1, 0, $fct, $acc);	
		$akt1 = $this->getFormulaAC($connectionPDO, '500000000000', $acc); 
		$S5 = $this->get_saldo_awal_saldo_dana($connectionPDO, '500000000000', $date_start, $end_start, $id_branch , $akt1, $is_closed = 1, 0, $fct, $acc);	
		$akt1 = $this->getFormulaAC($connectionPDO, '600000000000'); 
		$S6 = $this->get_saldo_awal_saldo_dana($connectionPDO, '600000000000', $date_start, $end_start, $id_branch , $akt1, $is_closed = 1, 0, $fct, $acc);
		
		return $S4 - ($S5 + $S6);
	}
	
	function get_saldo_awal_saldo_dana($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $akt, $is_closed = 1, $limit, $idFndCtg, $acc) {				
		$sqlBrc = "";
		if($limit == 0) 
			$this->totNN = 0;
		if ($id_branch) {
			$sqlBrc = "a.ms_branch_id = ".$id_branch." AND";
		}			
		if(!$end_start)
			$where = " AND date(a.trx_date) < '".$date_start."'";
		else
			$where = " AND (date(a.trx_date) >= '".$date_start."' AND date(a.trx_date) <= '".$end_start."')";	
		
		$sql = "SELECT (".$akt.") AS value FROM fa_journal_header a 
					 INNER JOIN fa_journal_detail b ON a.id = b.fa_journal_header_id 
					 INNER JOIN fa_account c ON b.fa_account_code=c.code	
					 INNER JOIN fa_fund_type d ON b.fa_fund_type_id = d.id
					 INNER JOIN fa_fund_category_sub e ON d.fa_fund_category_sub_id = e.id
					 INNER JOIN fa_fund_category f ON e.fa_fund_category_id = f.id					 						 
					 WHERE ".$sqlBrc." a.status IN (1,0) ".$where."
					 AND f.id='".$idFndCtg."' AND c.fa_account_type_code='".$account_type."'";			     
		
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		$r_1 = $result->fetch();
		$this->totNN += $r_1['value'];		
		
		$sql = "SELECT * FROM fa_account_type
			    WHERE parent_id='".$account_type."'";
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		while ($r = $result->fetch()) {			
			$account_type = $r['code']; 			
			$this->get_saldo_awal_saldo_dana($connectionPDO, $account_type, $date_start, $end_start, $id_branch , $akt, $is_closed = 1, $limit+1, $idFndCtg, $acc);			
		}
		
		return $this->totNN;
	}	
	
	function get_data_aktivitas_two_year_saldo_dana($connectionPDO, $account_type, $date_start, $end_start, $date_starts, $end_starts, $id_branch = false, $akt, $lvl, $dt1, $dt2, $is_closed = 1,  $pd=0, $v=1) {	
		$sql = "SELECT * FROM fa_account_type WHERE parent_id='".$account_type."' ORDER BY code";
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);				
		while ($r = $result->fetch()) {			
			$in = $pd + 30;
			$account_type = $r['code']; 
			
			if($v < $lvl)	{	
				$row = array(
								"accNm" => "<b>".$r['name']."</b>",
								"accVl" => array("",""),
								"in" => $in,
								"sts" => "0",
								"lgt" => $v,
							);
				array_push($this->data,$row);		
				
				$NA = $this->get_data_aktivitas_two_year_acc($connectionPDO, $account_type, $date_start, $end_start, $date_starts, $end_starts, $id_branch , $akt, $is_closed = 1);			
				foreach($NA as $vl) {
					$in = $pd + 45;
					
					$accVl = $vl['value'];
					if($vl['value'] == "")
						$accVl = 0;
					$accVl_1 = $vl['value_1'];
					if($vl['value_1'] == "")
						$accVl_1 = 0;
						
					$rF = $this->get_fund_category_saldo_dana($connectionPDO,$vl['code']);	
					if(!$end_start) {
						$ST1 = $this->laba_rugi($connectionPDO, $dt1, $end_start, $id_branch , $rF['idFndCtg'],$vl['code']);
						
						$ST = $ST1 + $accVl;

						$ST1 = $this->laba_rugi($connectionPDO, $dt2, $end_start, $id_branch , $rF['idFndCtg'],$vl['code']);
						
						$ST_1 = $ST1 + $accVl_1;
					}
					else {
						$ST = $this->laba_rugi($connectionPDO, $date_start, $end_start, $id_branch , $rF['idFndCtg'],$vl['code']);
						
						$ST_1 = $this->laba_rugi($connectionPDO, $date_starts, $end_starts, $id_branch , $rF['idFndCtg'],$vl['code']);
					}
					
					
					$row = array(
								"accNm" => $vl['name'],
								"accVl" => array($ST,$ST_1),
								"in" => $in,
								"sts" => "1",
								"lgt" => $v+1,
						);
					array_push($this->data,$row);
				}			
				$this->get_data_aktivitas_two_year_saldo_dana($connectionPDO, $account_type, $date_start, $end_start, $date_starts, $end_starts, $id_branch, $akt, $lvl, $dt1, $dt2, $is_closed = 1, $pd+15, $v+1);					
				
				$in = $pd + 30;
				$row = array(
								"accNm" => "<b>Total ".$r['name']."</b>",
								"accVl" => array(0,0),
								"in" => $in,
								"sts" => "2",
								"lgt" => $v,
						);
				array_push($this->data,$row);
			}
			else if($v == $lvl) {				
				$totNN = $this->get_data_aktivitas_value_limit_two_year_saldo_dana($connectionPDO, $account_type, $date_start, $end_start, $date_starts, $end_starts, $id_branch, $akt, $dt1, $dt2, $is_closed = 1, $limit=0);			
				$row = array(
							"accNm" => $r['name'],
							"accVl" => $totNN,
							"in" => $in,
							"sts" => "1",
							"lgt" => $v,
						);
				array_push($this->data,$row);		
			}		
		}		
		return $this->data;
	}		

	function get_data_aktivitas_value_limit_two_year_saldo_dana($connectionPDO, $account_type, $date_start, $end_start, $date_starts, $end_starts, $id_branch , $akt, $dt1, $dt2, $is_closed = 1, $limit) {		
		$sqlBrc = "";
		if($limit == 0) { 
			$this->totNN = 0;
			$this->totNN_1 = 0;
		}	
		if ($id_branch) {
			$sqlBrc = "a.ms_branch_id = ".$id_branch." AND";
		}	
		
		if(!$end_start) {
			$where = " AND date(a.trx_date) <= '".$date_start."'";
			$where_1 = " AND date(a.trx_date) <= '".$date_starts."'";
		}	
		else {
			$where = " AND (date(a.trx_date) >= '".$date_start."' AND date(a.trx_date) <= '".$end_start."') ";	
			$where_1 = " AND (date(a.trx_date) >= '".$date_starts."' AND date(a.trx_date) <= '".$end_starts."') ";	
		}
		
		$sqlcc = "SELECT c.name , c.code
				FROM fa_account c 
			    WHERE c.fa_account_type_code='".$account_type."'";	
		$resultacc = $connectionPDO->query($sqlcc);					
		$resultacc->setFetchMode(Phalcon\Db::FETCH_ASSOC);	
		$rows =	array(); 
		while ($rcc = $resultacc->fetch()) {		
					$rF = $this->get_fund_category_saldo_dana($connectionPDO,$rcc['code']);						
					if(!$end_start) {						 
						 $sql = "SELECT (SELECT (".$akt.")
								FROM fa_journal_header a 
								INNER JOIN fa_journal_detail b ON a.id = b.fa_journal_header_id 
								INNER JOIN fa_account c ON b.fa_account_code=c.code	
								INNER JOIN fa_fund_type d ON b.fa_fund_type_id = d.id
								INNER JOIN fa_fund_category_sub e ON d.fa_fund_category_sub_id = e.id
								INNER JOIN fa_fund_category f ON e.fa_fund_category_id = f.id					 						 
								WHERE ".$sqlBrc." a.status IN (1,0) ".$where."	
								AND c.code='".$rcc['code']."' 	
								) AS value,
								(SELECT (".$akt.")
								FROM fa_journal_header a 
								INNER JOIN fa_journal_detail b ON a.id = b.fa_journal_header_id 
								INNER JOIN fa_account c ON b.fa_account_code=c.code	
								INNER JOIN fa_fund_type d ON b.fa_fund_type_id = d.id
								INNER JOIN fa_fund_category_sub e ON d.fa_fund_category_sub_id = e.id
								INNER JOIN fa_fund_category f ON e.fa_fund_category_id = f.id					 						 
								WHERE ".$sqlBrc." a.status IN (1,0) ".$where_1."	
								AND c.code='".$rcc['code']."' 	
								) AS value_1";
							 
								$result = $connectionPDO->query($sql);					
								$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
								$r_1 = $result->fetch();
								if($r_1['value'] != NULL)
									$accVl = $r_1['value'];		
								if($r_1['value_1'] != NULL)	
									$accVl_1 += $r_1['value_1'];		
						
						$ST1 = $this->laba_rugi($connectionPDO, $dt1, $end_start, $id_branch , $rF['idFndCtg'],$rcc['code']);						
						$ST = $ST1 + $accVl;						
						
						$ST1 = $this->laba_rugi($connectionPDO, $dt2, $end_start, $id_branch , $rF['idFndCtg'],$rcc['code']);
						
						$ST_1 = $ST1 + $accVl_1;						
					}
					else {
						$ST = $this->laba_rugi($connectionPDO, $date_start, $end_start, $id_branch , $rF['idFndCtg'],$rcc['code']);
						$ST_1 = $this->laba_rugi($connectionPDO, $date_starts, $end_starts, $id_branch , $rF['idFndCtg'],$rcc['code']);
					}
			$this->totNN += $ST;							 
			$this->totNN_1 += $ST_1;							 
		}
		
		$sql = "SELECT * FROM fa_account_type
			    WHERE parent_id='".$account_type."'";
		$result = $connectionPDO->query($sql);					
		$result->setFetchMode(Phalcon\Db::FETCH_ASSOC);
		while ($r = $result->fetch()) {			
			$account_type = $r['code']; 			
			$this->get_data_aktivitas_value_limit_two_year_saldo_dana($connectionPDO, $account_type, $date_start, $end_start,$date_starts, $end_starts, $id_branch , $akt, $dt1, $dt2, $is_closed = 1, $limit+1);	
			
		}
		
		return array($this->totNN,$this->totNN_1);
	}	
}
