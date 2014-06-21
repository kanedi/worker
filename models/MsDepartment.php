<?php




class MsDepartment extends \Phalcon\Mvc\Model
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
    public $ms_directorate_id;
     
    /**
     *
     * @var string
     */
    public $name;

    public function initialize()
    {
        $this->belongsTo('ms_directorate_id', 'MsDirectorate', 'id');
    }
	public function getDepartment($connectionPDO) {					
		$where = "";
		$limit = 10;
		$start = 0;
		
		$request = new Phalcon\Http\Request();		
		if($request->get('limit')) 
			$limit = $request->get('limit');
		if($request->get('start')) 
			$start = $request->get('start');		
		
		$where ="WHERE 0 = 0";
		if(($request->get('branchId') AND !empty($_REQUEST['branchId'])) AND $request->get('branchType') != "HEAD OFFICE")	{
			$where.=" AND brc.id=".$request->get('branchId');
		}
		
		if(isset($_REQUEST['query'])) {
			$where.=" AND dept.name LIKE '%".$_REQUEST['query']."%'";
		}				
		
		/*
		$sql = "SELECT SQL_CALC_FOUND_ROWS dept.id AS dept_id, dept.name AS dept_name, dir.name AS dir_name, brc.name AS brc_name FROM ms_department AS dept 	INNER JOIN ms_directorate As dir ON dept.ms_directorate_id = dir.id
		INNER JOIN ms_branch AS brc ON dir.ms_branch_id = brc.id ".$where." LIMIT ".$start.",".$limit;		
		*/
		$sql = "SELECT SQL_CALC_FOUND_ROWS dept.id AS dept_id, dept.name AS dept_name, dir.name AS dir_name, brc.name AS brc_name FROM
		ms_branch AS brc INNER JOIN ms_directorate As dir ON brc.id = dir.ms_branch_id
		INNER JOIN ms_department AS dept ON dir.id = dept.ms_directorate_id	".$where." LIMIT ".$start.",".$limit;		
        
		$rows = array();
		$department = $connectionPDO->fetchAll($sql);		
		foreach ($department as $departments) {
				  $data = array(
					 'id' => $departments['dept_id'],
					 'name' => $departments['dept_name'],
					 'text' => $departments['brc_name']." - ".$departments['dir_name']." - ".$departments['dept_name'],
				  );              
				  array_push($rows,$data);
		}					
		
		$sql = "SELECT FOUND_ROWS() as total";
		$total = $connectionPDO->query($sql)->fetch();
		
		$json=array(
		               'total' => $total['total'],
					   'department' => $rows
			  );
			  
		return $json;	  
		
	}
}
