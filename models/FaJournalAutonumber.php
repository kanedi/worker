<?php




class FaJournalAutonumber extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var string
     */
    public $code_trx;
     
    /**
     *
     * @var integer
     */
    public $ms_branch_id;
     
    /**
     *
     * @var string
     */
    public $description;
     
    /**
     *
     * @var string
     */
    public $format_code;
     
    /**
     *
     * @var integer
     */
    public $seq;
     
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
    
	public function getAutoNumberCode($connectionPDO,$trxCode,$branchId) {			
		$year = date("Y");
		$month = date("n");
		
		$sqlj = "SELECT * FROM fa_journal_autonumber WHERE code_trx='".$trxCode."' AND ms_branch_id=".$branchId;				
		$jan = $connectionPDO->fetchOne($sqlj);		
		if($jan['restart_type'] == "month") {
			if($month != $jan['month']) {
				if($month != 1 AND $year == $jan['year'])
					$year = $jan['year'];
				$autoNumberCode = addslashes("".$trxCode."".$branchId."/".$month."/".$year."/00001");
				$sql = "UPDATE fa_journal_autonumber SET seq='1', year='$year', month='$month' WHERE code_trx='".$trxCode."' AND ms_branch_id=".$branchId;				
			}
			else if($month == $jan['month'] AND $year != $jan['year']) {
				$autoNumberCode = addslashes("".$trxCode."".$branchId."/".$month."/".$year."/00001");
				$sql = "UPDATE fa_journal_autonumber SET seq='1', year='$year', month='$month' WHERE code_trx='".$trxCode."' AND ms_branch_id=".$branchId;				
			}
			else {				
				$seq = sprintf("%05d", $jan['seq']);
				$autoNumberCode = addslashes("".$trxCode."".$branchId."/".$month."/".$year."/".$seq."");				
				$seq = $jan['seq']+1;
				$sql = "UPDATE fa_journal_autonumber SET seq=(seq+1) WHERE code_trx='".$trxCode."' AND ms_branch_id=".$branchId;				
			}
		}
		else if($jan['restart_type'] == "year") {
			if($year != $jan['year']) {
				$autoNumberCode = addslashes("".$trxCode."".$branchId."/".$month."/".$year."/00001");
				$sql = "UPDATE fa_journal_autonumber SET seq='1', year='$year', month='$month' WHERE code_trx='".$trxCode."' AND ms_branch_id=".$branchId;				
			}
			else if($year == $jan['year']) {
				$seq = sprintf("%05d", $jan['seq']);
				$autoNumberCode = addslashes("".$trxCode."".$branchId."/".$month."/".$year."/".$seq."");				
				$seq = $jan['seq']+1;
				$sql = "UPDATE fa_journal_autonumber SET seq=(seq+1) WHERE code_trx='".$trxCode."' AND ms_branch_id=".$branchId;				
			}
		}
		
		$connectionPDO->execute($sql);
		return $autoNumberCode;
	}	
	
	public function getAutoNumberCodeSeq($connectionPDO) {			
		$year = date("Y");
		$month = date("m");
		
		$sql = "SELECT YEAR(trx_date) AS thn FROM fa_journal_header WHERE id = (SELECT MAX(id) FROM fa_journal_header)";				
		
		$jan = $connectionPDO->fetchOne($sql);		
		if($year != $jan['thn']) {
			$seq = 1;
			$sql = "UPDATE ms_seq SET seq='1' WHERE id=1";		
			$connectionPDO->execute($sql);			
		}
		else {
			$sql = "SELECT seq FROM ms_seq WHERE id=1";				
			$jan = $connectionPDO->fetchOne($sql);		
			$seq = $jan['seq'] + 1;
			$sql = "UPDATE ms_seq SET seq=(seq+1) WHERE id=1";				
			$connectionPDO->execute($sql);	
		}
		
		return $month."/".$year."/".$seq;
	}	
}
