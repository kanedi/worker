<?php


use Phalcon\Mvc\Model\Behavior\SoftDelete;

class CrDonationHeader extends \Phalcon\Mvc\Model
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
    public $cr_donor_id;
    public $cr_counter_id;
    public $cr_donation_bank_detail_id;
    /**
     *
     * @var integer
     */
    public $user_id;
    public $trx_date;
    public $via;
    public $created;
    public $updated;
    public function initialize()
    {
        $this->belongsTo('cr_donor_id', 'CrDonor', 'id');
        $this->belongsTo('cr_counter_id', 'CrCounter', 'id');
        $this->belongsTo('user_id', 'User', 'id');
        $this->belongsTo('cr_donation_bank_detail_id', 'CrDonationBankDetail', 'id');

        if (!defined('HARD_DELETE')) {
            define('HARD_DELETE', false);
        }

        if(!HARD_DELETE){
            $this->addBehavior(new SoftDelete(
                array(
                    'field' => 'is_deleted',
                    'value' => 1
                )
            ));
        }
    }
    /**
     *
     *
     */
    public function getStatus($header_id){
        $status = '';
        $fund = CrDonationFundTracking::findFirst('cr_donation_header_id='.$header_id);
        if($fund){
           $status = $fund->status;
        }
        return $status;
    }
    
    /**
     * 
     * @param string $token
     * @param string $con
     * @param array $param
     * @return Ambigous <multitype:boolean string , multitype:boolean number multitype:multitype:NULL multitype:NULL    >
     */
    public function getData($token,$con,$param=''){
        $date = date('Y-m-d', time());
        $t = $this->extrackToken($token);
        $total = 0;        
        $kond = '';
        $cond = 'cdh.is_deleted = 0 ';
        if(is_array($param)){
            if(isset($param['limit']) && isset($param['start'])){
                $limit = $param['limit'];
                $start = $param['start'];
            }
            if(isset($param['query']) && $param['query']!=''){
                $query = $param['query'];
                $cond .= 'AND (cdh.id LIKE "%' . $query . '%" OR cdon.name LIKE "%' . $query . '%")';
            }
        }
        if($con=='myinput'){
            $cond .= ' AND cdh.user_id = '.$t['user_id'];
        }else if($con=='donmanage'){
            $kond = 'JOIN CrCounter ctr ON cdh.cr_counter_id = ctr.id ';
            $cond .= ' AND ctr.ms_branch_id ='.$t['branch_id'];
        }elseif($con=='donmanageall'){
            $kond = 'JOIN CrCounter ctr ON cdh.cr_counter_id = ctr.id ';
        }else if($con=='donduplicate'){
            if($param['cr_donor_id']!=''){
                $cond .= ' AND cdh.cr_donor_id ='.$param['cr_donor_id'];
            }else{
                $cond .= ' AND cdh.cr_donor_id =0';
            }
        }else if($con == 'settle'){
            $cond .= ' AND cdh.settle_status IS NULL AND cdh.user_id = '.$t['user_id'];
        }else if($con == 'void'){
            $cond .= ' AND cdh.settle_status = "void"';
        }else if($con == 'pending_void'){
            $cond .= ' AND cdh.settle_status = "pending_void"';
        }
        if ($this->request->get('query')) {
            $query = $this->request->get('query');
            
        }
        $kue = 'SELECT cdh.cr_counter_id, cdh.user_id,cdh.payment_type, cdh.id, cdh.trx_date, cdon.name, SUM( cdd.amount ) AS total
            FROM CrDonationHeader cdh
            JOIN  CrDonationDetail cdd ON cdh.id = cdd.cr_donation_header_id
            JOIN CrDonor cdon ON cdh.cr_donor_id = cdon.id
            '.$kond.'
            WHERE ' . $cond . '
            GROUP BY cdh.user_id,cdh.id, cdh.trx_date , cdh.payment_type, cdon.name ORDER BY cdh.id DESC';
        $qr = $this->modelsManager->createQuery($kue);
        $data = $qr->execute();
        $total = count($data);
        if(isset($limit) && isset($start)){
            $qr = $this->modelsManager->createQuery($kue.' LIMIT ' . $start . ',' . $limit);
            $data = $qr->execute();
        }
        $gt = 0;
        $datas = array();
        if ($data) {
            foreach ($data as $dd) {
                $v = User::getDetail($dd->user_id);
                $datas[] = array(
                    'id' => $dd->id,
                    'trx_date' => $dd->trx_date,
                    'cr_counter_id' => $dd->cr_counter_id,
                    'cr_counter_name' => CrCounter::getName($dd->cr_counter_id),
                    'total' => $dd->total,
                    'cr_donor_name' => $dd->name,
                    'user_name' => $v['name'],
                    'payment_type' => $dd->payment_type,
                    'status' => CrDonationHeader::getStatus($dd->id)
                );
                $gt = $gt + $dd->total;
            }
        }
        if ($data) {
            $response = array('success' => true, 'data' => $datas, 'total' => $total, 'gt' => $gt);
        } else {
            $response = array('success' => false, 'msg' => 'data not found');
        }
        return $response;
    }
    
    public function getDetail($header_id)
    {
        $header = CrDonationHeader::findFirst($header_id);
        $detail = CrDonationDetail::find('cr_donation_header_id = ' . $header_id);
        $gt = 0;
        $head = array();
        $donor = array();
        $datadetail = array();

        $dateHelper = new Library\Ozip\DateHelper();

        if($this->request->getPost('token')){
            $token_data = $this->extrackToken($this->request->getPost('token'));
        }elseif($this->request->get('token')){
            $token_data = $this->extrackToken($this->request->get('token'));
        }

        $local_timezone = $token_data['local_timezone'];

        $head = array(
            'id' => $header->id,
            'cr_donor_id' => $header->cr_donor_id,
            'user_id' => $header->user_id,
            'user_name' => $header->User->HcEmployee->name,
            'cr_counter_id' => $header->cr_counter_id,
            'cr_counter_name' => $header->CrCounter->name,
            'trx_date' => $header->trx_date,
            'via' => $header->via,
            'created' => $dateHelper->tzConvert($header->created,$local_timezone),
            'batch_user' => $header->batch_user,
            'batch_counter' => $header->batch_counter,
            'payment_type' => $header->payment_type,
            'ms_branch_name' => $header->CrCounter->MsBranch->name
        );

        $donor = array(
            'id' => $header->CrDonor->id,
            'name' => $header->CrDonor->name,
            'public_id' => $header->CrDonor->public_id,
            'email' => $header->CrDonor->email,
            'hp' => $header->CrDonor->hp,
            'npwp' => $header->CrDonor->npwp,
            'address' => $header->CrDonor->address,
            'city' => $header->CrDonor->city,
            'province' => $header->CrDonor->province,
            'country' => $header->CrDonor->country,
            'branch_origin' => $header->CrDonor->branch_origin,
            'branch_current' => $header->CrDonor->branch_current,
            'kd_cc' => $header->CrDonor->kd_cc,
            'public_id' => $header->CrDonor->public_id,
            'created' => $header->CrDonor->created
        );
        foreach ($detail as $dd) {
            $gt = $gt + $dd->amount;
            $datadetail[] = array(
                'id' => $dd->id,
                'fa_fund_type_id' => $dd->fa_fund_type_id,
                'fa_fund_type_name' => $dd->FaFundType->name,
                'fa_fund_category_sub_id' => $dd->FaFundType->fa_fund_category_sub_id,
                'fa_fund_category_sub_name' => $dd->FaFundType->FaFundCategorySub->name,
                'fa_fund_category_id' => $dd->FaFundType->FaFundCategorySub->FaFundCategory->id,
                'fa_fund_category_name' => $dd->FaFundType->FaFundCategorySub->FaFundCategory->name,
                'ms_currency_code' => $dd->ms_currency_code,
                'currency_rate' => $dd->currency_rate,
                'currency_amount' => $dd->currency_amount,
                'amount' => $dd->amount,
            );
        }
        $data = array('donor' => $donor, 'donationheader' => $head, 'donationdetail' => $datadetail,'total'=>$gt);
        return $data;
    }
    
    public function getDataSettle(){
        
    }
    
    /**
     *@param string $token token from ui
     *@param string $act pending_void,void,rejected_void
     *@param array $headarr header_id
     *@param string $msgs message reason void
     *@return array 
     */
    public function void($token,$act,$headarr,$msgs=''){
        $status = '';
        $status = $act;
        $success = false;
        $msg = 'request parameter wrong';
        $dh = new Library\Ozip\DateHelper();
        $now = $dh->currentTimestam();
        $tok = $this->extrackToken($token);
        if($status == 'pending_void' || $status == 'void' || $status == 'rejected_void'){
            try{
                $transactionManager = new Phalcon\Mvc\Model\Transaction\Manager();
                $transaction = $transactionManager->get();
                foreach($headarr as $head_id){
                    $head = CrDonationHeader::findFirst($head_id);
                    $head->setTransaction($transaction);
                    if($head->settle_status == 'void' || $head->settle_status == 'rejected_void'){
                        $transaction->rollback('Status Already '.$head->settle_status);
                        break;
                    }
                    if($status == 'void'){
                        if($head->settle_status != 'pending_void'){
                            $transaction->rollback('Status must bisa Pending Void to Approve Void');
                            break;
                        }
                        $reverse = self::autojournalbalik($tok,$head_id);
                        if(is_string($reverse) || $reverse == false){
                            $transaction->rollback($reverse);
                            break;
                        }
                        self::copyDonation($head_id);
                    }
                    $head->settle_status = $status;
                    $head->updated = $now;
                    if($head->save() == false){
                        foreach ($head->getMessages() as $message) {
                            $transaction->rollback($message->getMessage());
                            break;
                        }
                    }
                    //log void
                    $logvoid = new CrDonationVoidLog();
                    $logvoid->setTransaction($transaction);
                    $logvoid->cr_donation_header_id = $head_id;
                    $logvoid->message = $msgs;
                    $logvoid->status = $status;
                    $logvoid->created = $now;
                    if($logvoid->save() == false){
                        foreach ($logvoid->getMessages() as $message) {
                            $transaction->rollback($message->getMessage());
                            break;
                        }
                    }
                }
                $transaction->commit();
                $success = true;
                $msg = $status.' Was Updated';
            }catch (Phalcon\Mvc\Model\Transaction\Failed $e) {
                $success = false;
                $msg = $e->getMessage();
            }
        }
        $response = array('success'=>$success , 'msg'=>$msg);
        return $response;
    }
    
    public function copyDonation($header_id){
        try{
            $transactionManager = new Phalcon\Mvc\Model\Transaction\Manager();
            $transaction = $transactionManager->get();
            $oldhead = CrDonationHeader::findFirst($header_id);
            if(!$oldhead){
                $transaction->rollback($message->getMessage());
            }
            $header = new CrDonationHeader();
            $header->setTransaction($transaction);
            $header->is_deleted = $oldhead->is_deleted;
            $header->batch_user = $oldhead->batch_user;
            $header->batch_counter = $oldhead->batch_counter;
            $header->cr_donor_id = $oldhead->cr_donor_id;
            $header->user_id = $oldhead->user_id;
            $header->cr_counter_id = $oldhead->cr_counter_id;
            $header->payment_type = $oldhead->payment_type;
            $header->trx_date = $oldhead->trx_date;
            $header->via = $oldhead->via;
            $header->created = $oldhead->created;
            $header->settle_status = $oldhead->settle_status;
            if($header->save() == false){
                foreach ($header->getMessages() as $message) {
                    $transaction->rollback($message->getMessage());
                    break;
                }
            }
            $olddetail = CrDonationDetail::find('cr_donation_header_id ='.$header_id);
            if(!$olddetail){
                $transaction->rollback($message->getMessage());
            }
            foreach($olddetail as $dets){
                $detail = new CrDonationDetail();
                $detail->setTransaction($transaction);
                $detail->is_deleted = $dets->is_deleted;
                $detail->cr_donation_header_id = $header->id;
                $detail->fa_fund_type_id = $dets->fa_fund_type_id;
                $detail->ms_currency_code = $dets->ms_currency_code;
                $detail->currency_rate = $dets->currency_rate;
                $detail->currency_amount = $dets->currency_amount * -1;
                $detail->amount = $dets->amount * -1;
                $detail->created = $dets->created;
                if($detail->save() == false){
                    foreach ($detail->getMessages() as $message) {
                        $transaction->rollback($message->getMessage());
                        break;
                    }
                }
            }
            $transaction->commit();
            $res = true;
        }catch(Phalcon\Mvc\Model\Transaction\Failed $e){
            $res = false;
        }
        return $res;
    }
    
    /**
     *@param string $token token from ui
     *@param string $act do
     *@param array $headarr header_id
     *@return array 
     */
    public function settle($token,$act,$headarr){
        $success = false;
        $status = '';
        $msg = 'request parameter wrong';
        $dh = new Library\Ozip\DateHelper();
        $now = $dh->currentTimestam();
        $tok = $this->extrackToken($token);
        if($act == 'do'){
            $status = 'settle';
            try{
                $transactionManager = new Phalcon\Mvc\Model\Transaction\Manager();
                $transaction = $transactionManager->get();
                foreach($headarr as $head_id){
                    $head = CrDonationHeader::findFirst($head_id);
                    $head->setTransaction($transaction);
                    if($head->settle_status == 'pending_void'){
                        $transaction->rollback('Found '.$head->settle_status.' Status');
                        break;
                    }
                    if($head->settle_status != 'void'){
                        $head->settle_status = $status;
                        $head->updated = $now;
                        if($head->save() == false){
                            foreach ($head->getMessages() as $message) {
                                $transaction->rollback($message->getMessage());
                                break;
                            }
                        }
                    }
                }
                $transaction->commit();
                $success = true;
                $msg = $status.' Was Updated';
            }catch (Phalcon\Mvc\Model\Transaction\Failed $e) {
                $success = false;
                $msg = $e->getMessage();
            }
        }
        $response = array('success'=>$success , 'msg'=>$msg);
        return $response;
    }
    
    public function autojournalbalik($tok,$header_id){
        $response = false;
        $msg = '';
        $dh = new Library\Ozip\DateHelper();
        $now = $dh->currentTimestam();
        $paramHead = array(
            'code' => 'CRM',
            'trx_date' => $now,
            'memo' => 'CRM Reverse Journal on donation_id '.$header_id
        );
        $det = CrDonationDetail::find('cr_donation_header_id='.$header_id);
        $isidet = array();
        $isidets = array();
        $isidets2 = array();
        if(count($det) > 0){
            foreach($det as $dets){
                $fc = $dets->FaFundType->FaFundCategorySub->fa_fund_category_id;
                $config = FaJournalConfig::findFirst('fa_fund_category_id = ' . $fc);
                if(!$config){
                    $msg = 'Missing Configuration Journal on '.$dets->FaFundType->FaFundCategorySub->FaFundCategory->name;
                    return $msg;    
                }
                $isidets = array(
                    'ms_currency_code' => $dets->ms_currency_code,
                    'fa_account_code' => $config->debit,
                    'fa_fund_type_id' => $dets->fa_fund_type_id,
                    'currency_rate' => $dets->currency_rate,
                    'credit' => $dets->amount
                );
                $isidets2 = array(
                    'ms_currency_code' => $dets->ms_currency_code,
                    'fa_account_code' => $config->credit,
                    'fa_fund_type_id' => $dets->fa_fund_type_id,
                    'currency_rate' => $dets->currency_rate,
                    'debit' => $dets->amount
                );
                if(is_array($isidets)){
                    array_push($isidet,$isidets);
                }
                if(is_array($isidets2)){
                    array_push($isidet,$isidets2);
                }
            }
        }else{
            return false;
        }
        $paramDetail = $isidet;
        //print_r($paramDetail);            
        $headjur = json_decode(FaJournalHeader::setJournalHeader($paramHead));
        $detailjur = json_decode(FaJournalHeader::setJournalDetail($paramDetail));
        if($headjur->success && $detailjur->success){
            $post = FaJournalHeader::postingJournal($tok, $headjur->msg, $detailjur->msg);
            $response = true;
        }
        return $response;
    }
    
    public function autojournal2($tok,$header_id,$det){
        $response = false;
        $dh = new Library\Ozip\DateHelper();
        $now = $dh->currentTimestam();
        $paramHead = array(
            'code' => 'CRM',
            'trx_date' => $now,
            'memo' => 'CRM Journal on donation_id '.$header_id
        );
        //$det = CrDonationDetail::find('cr_donation_header_id='.$header_id);
        $isidet = array();
        $isidets = array();
        $isidets2 = array();
        if(count($det) > 0){
            foreach($det as $dets){
                $fc = FaFundType::getDetail($dets['fa_fund_type_id']);
                $config = FaJournalConfig::findFirst('fa_fund_category_id = ' . $fc['fa_fund_category_id']);
                if(!$config){
                    $msg = 'Missing Configuration Journal on '.$fc['fa_fund_category_name'];
                    return $msg;
                    break;
                }
                $isidets = array(
                    'ms_currency_code' => $dets['ms_currency_code'],
                    'fa_account_code' => $config->debit,
                    'fa_fund_type_id' => $dets['fa_fund_type_id'],
                    'currency_rate' => $dets['currency_rate'],
                    'debit' => $dets['amount']
                );
                $isidets2 = array(
                    'ms_currency_code' => $dets['ms_currency_code'],
                    'fa_account_code' => $config->credit,
                    'fa_fund_type_id' => $dets['fa_fund_type_id'],
                    'currency_rate' => $dets['currency_rate'],
                    'credit' => $dets['amount']
                );
                if(is_array($isidets)){
                    array_push($isidet,$isidets);
                }
                if(is_array($isidets2)){
                    array_push($isidet,$isidets2);
                }
            }
        }else{
            return false;
        }
        $paramDetail = $isidet;
        //print_r($paramDetail);            
        $headjur = json_decode(FaJournalHeader::setJournalHeader($paramHead));
        $detailjur = json_decode(FaJournalHeader::setJournalDetail($paramDetail));
        if($headjur->success && $detailjur->success){
            $post = FaJournalHeader::postingJournal($tok, $headjur->msg, $detailjur->msg);
            $response = true;
        }
        return $response;
    }
    
    public function autojournal($header_id)
    {
        $hasil = false;
        $donhead = CrDonationHeader::findFirst($header_id);
        $branch = $donhead->User->HcEmployee->MsDepartment->MsDirectorate->ms_branch_id;
        $today = date("Y-m-d H:i:s");
        $year = date("Y");
        $month = date("m");
        $genid = new Library\Ozip\Id();
        $seq = $genid->getSeq('jurnal' . $year);
        $autoNumberCode = "CRM/" . $branch . "/" . $month . "/" . $year . "/" . $seq;
        $department = MsDepartment::findFirst('name = "CRM"');
        $dcrm = $department->id;

        try {
            $transactionManager = new Phalcon\Mvc\Model\Transaction\Manager();
            $transaction = $transactionManager->get();
            $jurnalhead = new FaJournalHeader;
            $jurnalhead->setTransaction($transaction);
            $jurnalhead->cr_donation_header_id = $header_id;
            $jurnalhead->ms_branch_id = $branch;
            $jurnalhead->code = $autoNumberCode;
            $jurnalhead->trx_date = $donhead->trx_date;
            $jurnalhead->created = $today;
            $jurnalhead->created_user_id = $donhead->user_id;
            $jurnalhead->is_deleted = 0;
            $jurnalhead->status = 1;
            if ($jurnalhead->save() == false) {
                foreach ($jurnalhead->getMessages() as $message) {
                    $hasil = false;
                    $transaction->rollback("Can't save Header, error = " . $message->getMessage());
                    break;
                }
            }

            $dondetail = CrDonationDetail::find('cr_donation_header_id =' . $header_id);
            foreach ($dondetail as $dd) {
                $fc = $dd->FaFundType->FaFundCategorySub->fa_fund_category_id;
                $config = FaJournalConfig::findFirst('fa_fund_category_id = ' . $fc);
                //$fabank = FaBank::findFirst('fa_account_code = "'.$config->debit.'"');
                //$fabank2 = FaBank::findFirst('fa_account_code = "'.$config->credit.'"');

                $jurnaldetail = new FaJournalDetail;
                $jurnaldetail->setTransaction($transaction);
                $jurnaldetail->fa_journal_header_id = $jurnalhead->id;
                $jurnaldetail->ms_currency_code = $dd->ms_currency_code;
                $jurnaldetail->fa_account_code = $config->debit;
                $jurnaldetail->fa_fund_type_id = $dd->fa_fund_type_id;
                $jurnaldetail->ms_department_id = $dcrm;
                //if($fabank){
                //    $jurnaldetail->fa_bank_id = $fabank->id;
                //}
                $jurnaldetail->currency_rate = $dd->currency_rate;
                $jurnaldetail->debit = $dd->amount;
                $jurnaldetail->credit = 0;
                $jurnaldetail->created = $today;
                $jurnaldetail->created_user_id = $donhead->user_id;
                $jurnaldetail->is_deleted = 0;
                if ($jurnaldetail->save() == false) {
                    foreach ($jurnaldetail->getMessages() as $message) {
                        $hasil = false;
                        $transaction->rollback("Can't save detail, error = " . $message->getMessage());
                        break;
                    }
                }

                $jn2 = new FaJournalDetail;
                $jn2->setTransaction($transaction);
                $jn2->fa_journal_header_id = $jurnalhead->id;
                $jn2->ms_currency_code = $dd->ms_currency_code;
                $jn2->fa_account_code = $config->credit;
                $jn2->fa_fund_type_id = $dd->fa_fund_type_id;
                $jn2->ms_department_id = $dcrm;
                //if($fabank2){
                //    $jn2->fa_bank_id = $fabank2->id;
                //}
                $jn2->currency_rate = $dd->currency_rate;
                $jn2->credit = $dd->amount;
                $jn2->debit = 0;
                $jn2->created = $today;
                $jn2->created_user_id = $donhead->user_id;
                $jn2->is_deleted = 0;
                if ($jn2->save() == false) {
                    foreach ($jn2->getMessages() as $message) {
                        $hasil = false;
                        $transaction->rollback("Can't save detail, error = " . $message->getMessage());
                        break;
                    }
                }
            }
            $transaction->commit();
            $hasil = true;
            //$response = array('success' => true, 'msg'=>'Data saved Successfull!');
        } catch (Phalcon\Mvc\Model\Transaction\Failed $e) {
            $hasil = $e->getMessage();
            //echo $e->getMessage();
            //$response = array('success' => false, 'msg' => $e->getMessage());
        }

        return $hasil;
    }
    
    public function getReportByCategory($token,$con,$param=''){
        $now = date('Y-m-d', time());
        $hasil ='';
        if(is_array($param)){
            $first = $param['first'];
            $second = $param['second'];
            $fund = $param['fund'];
        }
        $sel = 'fa.id, fa.name';
        $groupsel = 'fa.id';
        $opt = 'AND facs.id = '.$fund;
        $name = 'fund_type';
        if($con == 'fundcategory'){
            $sel = 'fac.id, fac.name';
            $groupsel = 'fac.id';
            $opt = '';
            $name = 'fund_category';
        }
        if($con == 'fundcategorysub'){
            $sel = 'facs.id, facs.name';
            $groupsel = 'facs.id';
            $opt = 'AND fac.id = '.$fund;
            $name = 'fund_category_sub';
        }
        $tok = $this->extrackToken($token);
        $cond = 'cdh.is_deleted = 0 AND fac.crm_display = 1 '.$opt.' AND ctr.ms_branch_id = '.$tok['branch_id'].' AND cdh.trx_date BETWEEN "'.$first.'" AND "'.$second.'"';
        $kue = 'SELECT '.$sel.', SUM(cdd.amount) AS total
            FROM CrDonationHeader cdh
            JOIN CrDonor dnr ON cdh.cr_donor_id = dnr.id
            JOIN CrCounter ctr ON cdh.cr_counter_id = ctr.id
            JOIN CrDonationDetail cdd ON cdh.id = cdd.cr_donation_header_id
            JOIN FaFundType fa ON cdd.fa_fund_type_id = fa.id
            JOIN FaFundCategorySub facs ON fa.fa_fund_category_sub_id = facs.id
            JOIN FaFundCategory fac ON facs.fa_fund_category_id = fac.id';
        $qr = $this->modelsManager->createQuery($kue.'            
            WHERE '.$cond.'
            GROUP BY '.$groupsel);
        $data = $qr->execute();
        $gt= 0;
        if(count($data)>0){
            foreach($data as $dd){                    
                $hasil[] = array(
                    'id' => $dd->id,
                    $name => $dd->name,
                    'sub_total'=> $dd->total,
                );
                $gt = $gt+$dd->total;
            }            
        }else{
            $hasil[] = array(
                    'id' => '',
                    $name => '',
                    'sub_total'=> 0,
                );
            $return = array('hasil'=>$hasil,'gt'=>0);
        }
        $return = array('hasil'=>$hasil,'gt'=>$gt);
        return $return;
    }
    
    public function getReportByCounter($token,$cond,$param=''){
        $now = date('Y-m-d', time());
        $hasil ='';
        $limit = '';
        if(is_array($param)){
            $first = $param['first'];
            $second = $param['second'];
            $counter = $param['counter'];
            if($param['start'] != '' && $param['limit'] != ''){
                $limit = ' LIMIT '.$param['start'].','.$param['limit'];
            }
        }
        
        $tok = $this->extrackToken($token);
        $cond = 'cdh.is_deleted=0 AND fac.crm_display = 1 AND ctr.ms_branch_id = '.$tok['branch_id'].' AND cdh.trx_date BETWEEN "'.$first.'" AND "'.$second.'" AND cdh.cr_counter_id ='.$counter;
        $kue = 'SELECT ctr.name AS ctr, fac.name, SUM( cdd.amount ) AS total
                FROM CrDonationHeader cdh
                JOIN  CrDonationDetail cdd ON cdh.id = cdd.cr_donation_header_id
                JOIN FaFundType fa ON cdd.fa_fund_type_id = fa.id
                JOIN FaFundCategorySub facs ON fa.fa_fund_category_sub_id = facs.id
                JOIN FaFundCategory fac ON facs.fa_fund_category_id = fac.id
                JOIN CrCounter ctr ON cdh.cr_counter_id = ctr.id';
        $qr = $this->modelsManager->createQuery($kue.'            
                WHERE '.$cond.'
                GROUP BY ctr.name, fac.name');
        $data = $qr->execute();
        $gt= 0;
        $totalrow = count($data);
        if(count($data)>0){
            $qr = $this->modelsManager->createQuery($kue.'            
                WHERE '.$cond.'
                GROUP BY ctr.name, fac.name'.$limit);
            $data = $qr->execute();
            foreach($data as $dd){
                $hasil[] = array(
                    'ctr'=> $dd->ctr,
                    'name' => $dd->name,
                    'total'=> $dd->total,                    
                );
                $gt = $gt+$dd->total;
            }
        }else{
            $hasil[] = array(
                    'name' => '',
                    'total'=> number_format(0,0,',','.'),         
                );
        }
        $return = array('hasil'=>$hasil,'gt'=>$gt,'total'=>$totalrow);
        return $return;
    }
    
    public function getReportByCounterAll($token,$param=''){
        $hasil =array();
        $jumlah='';
        $fac=array();
        $limit = '';
        if(is_array($param)){
            $first = $param['first'];
            $second = $param['second'];
            if($param['start'] != '' && $param['limit'] != ''){
                $limit = ' LIMIT '.$param['start'].','.$param['limit'];
            }
        }
        $tok = $this->extrackToken($token);
        $ctr = CrCounter::find('is_deleted = 0 AND ms_branch_id = '.$tok['branch_id']);
        foreach($ctr as $cc){               
            $facs = FaFundCategory::find('crm_display = 1');
            $i = 0;
            $gt= 0;
            $kue = 'FROM CrDonationHeader cdh
                    JOIN  CrDonationDetail cdd ON cdh.id = cdd.cr_donation_header_id
                    JOIN FaFundType fa ON cdd.fa_fund_type_id = fa.id
                    JOIN FaFundCategorySub facs ON fa.fa_fund_category_sub_id = facs.id
                    JOIN FaFundCategory fac ON facs.fa_fund_category_id = fac.id
                    JOIN CrCounter ctr ON cdh.cr_counter_id = ctr.id';
            foreach($facs as $f){
                $cond = 'cdh.is_deleted=0 AND fac.crm_display = 1 AND ctr.id = '.$cc->id.' AND cdh.trx_date BETWEEN "'.$first.'" AND "'.$second.'" AND fac.id ='.$f->id;
                $qr = $this->modelsManager->createQuery('
                    SELECT ctr.name AS ctr, SUM( cdd.amount ) AS total
                    '.$kue.'
                    WHERE '.$cond.'
                    GROUP BY ctr.id');
                $data = $qr->execute();
                if(count($data)>0){
                    foreach($data as $dd){
                        $gt = $gt + $dd->total;                            
                        $hasil[$cc->name][$i] = array(                               
                            'fac_name'=> $f->name,                                
                            'jumlah' => number_format($dd->total,0,',','.'),
                        );
                    }                    
                }else{
                    $hasil[$cc->name][$i] = array(                               
                            'fac_name'=> $f->name,                                
                            'jumlah' => number_format(0,0,',','.'),
                        );
                }
                $fac[$f->id] = array(
                        'id' => $f->id,
                        'name' => $f->name    
                    );
                $i++;                    
            }            
            $hasil[$cc->name][$i+1] = array(                                       
                'jumlah' => number_format($gt,0,',','.'),
            );
        }
        $gtt=array();
        $gtotal = 0;
        foreach($fac as $f){
            $cond = 'cdh.is_deleted=0 AND fac.crm_display = 1 AND ctr.is_deleted=0 AND ctr.ms_branch_id= '.$tok['branch_id'].' AND cdh.trx_date BETWEEN "'.$first.'" AND "'.$second.'" AND fac.id ='.$f['id'];
            $qr = $this->modelsManager->createQuery('
                SELECT fac.name, SUM( cdd.amount ) AS total
                '.$kue.'
                WHERE '.$cond.
                ' GROUP BY fac.id');
            $data = $qr->execute();
            $gt= 0;
            if(count($data)>0){
                foreach($data as $dd){
                    $gtt[] = array(
                        'gt' => number_format($gt+$dd->total,0,',','.')
                    );                            
                }                
                $gtotal = $gtotal + $dd->total;
            }else{
                $gtt[] = array(
                        'gt' => 0
                ); 
            }
        }
        
        $cond = 'ctr.ms_branch_id = '.$tok['branch_id'].' AND cdh.is_deleted=0 AND fac.crm_display = 1 AND cdh.trx_date BETWEEN "'.$first.'" AND "'.$second.'"';
        $qr = $this->modelsManager->createQuery('
                    SELECT ctr.id,ctr.name, SUM( cdd.amount ) AS total
                    '.$kue.'
                    WHERE '.$cond.'
                    GROUP BY ctr.id');
        $data = $qr->execute();
        $display =array();
        if($data){
            $totalrow = (count($data));
            $qr = $this->modelsManager->createQuery('
                    SELECT ctr.id,ctr.name, SUM( cdd.amount ) AS total
                    '.$kue.'
                    WHERE '.$cond.'
                    GROUP BY ctr.id'.$limit);
            $data = $qr->execute();
            foreach($data as $d)
            $display[]= array(
                'counter_id' => $d->id,
                'counter_name' => $d->name,
                'total'=>$d->total
            );    
        }
        $return = array(
            'hasil' => $hasil,
            'fac' => $fac,
            'gtotal' => $gtotal,
            'gtt'=>$gtt,
            'display' => $display,
            'total' => $totalrow
        );
        return $return;
    }
    
    public function getReportByCounterDetail($token,$param=''){
        
        $limit = '';
        if(is_array($param)){
            $first = $param['first'];
            $second = $param['second'];
            $counter = $param['counter'];
            if($param['start'] != '' && $param['limit'] != ''){
                $limit = ' LIMIT '.$param['start'].','.$param['limit'];
            }
        }
        $tok = $this->extrackToken($token);
        $hasil ='';
        $cond = 'cdh.is_deleted=0 AND fac.crm_display = 1 AND counter.ms_branch_id = '.$tok['branch_id'].' AND cdh.trx_date BETWEEN "'.$first.'" AND "'.$second.'" AND cdh.cr_counter_id ='.$counter;
        $kue = 'FROM CrDonationHeader cdh
            JOIN CrDonationDetail cdd ON cdh.id = cdd.cr_donation_header_id
            JOIN FaFundType fa ON cdd.fa_fund_type_id = fa.id
            JOIN FaFundCategorySub fas ON fa.fa_fund_category_sub_id = fas.id
            JOIN FaFundCategory fac ON fas.fa_fund_category_id = fac.id
            JOIN CrCounter counter ON cdh.cr_counter_id = counter.id';
        $qr = $this->modelsManager->createQuery('
            SELECT cdh.trx_date, don.name as donor_name,cdh.id as trans_id, counter.name AS counter, fa.name, SUM( cdd.amount ) AS total
            '.$kue.'
            JOIN CrDonor don ON cdh.cr_donor_id = don.id
            WHERE '.$cond.'
            GROUP BY counter.id, fa.id,cdh.id,don.id');
        $data = $qr->execute();
        $gt= 0;
        $qrs = $this->modelsManager->createQuery('
            SELECT fas.name as cname,fa.name, SUM( cdd.amount ) AS total
            '.$kue.'
            WHERE '.$cond.'
            GROUP BY fa.id');
        $datafund = $qrs->execute();
        $totalrow = count($data);
        if($totalrow > 0){
            $i=1;
            $qr = $this->modelsManager->createQuery('
                SELECT cdh.trx_date, don.name as donor_name,cdh.id as trans_id, counter.name AS counter, fa.name, SUM( cdd.amount ) AS total
                '.$kue.'
                JOIN CrDonor don ON cdh.cr_donor_id = don.id
                WHERE '.$cond.'
                GROUP BY counter.id, fa.id,cdh.id,don.id'.$limit);
            $data = $qr->execute();
            foreach($data as $dd){                    
                $hasil[] = array(
                    'no'=>$i,
                    'counter'=> $dd->counter,
                    'name' => $dd->name,
                    'trx_date' => date('d/m/Y', strtotime($dd->trx_date)),
                    'total'=> $dd->total,
                    'trans_id' => number_format($dd->trans_id,0,',','.'),
                    'donor_name' => $dd->donor_name
                );
                $gt = $gt+$dd->total;
                $i++;
            }
            foreach($datafund as $dd){                    
                $fund[] = array(
                    'name'=>$dd->cname.' - '.$dd->name,
                    'total'=> number_format($dd->total,0,',','.'),
                );
            }
        }else{
            $hasil[] = array(
                    'no'=>'',
                    'counter'=> '',
                    'name' => '',
                    'total'=> number_format(0,0,',','.'),
                    'trans_id' => '',
                    'donor_name' => ''   
                );
            $fund[] = array(
                    'name'=>'',
                    'total'=> number_format(0,0,',','.'),
                );
        }
        $return = array(
            'hasil' => $hasil,
            'fund' => $fund,
            'gt' => $gt,
            'total' => $totalrow
        );
        return $return;
    }
    
    public function getReportByFundraiser($token,$param=''){
        if(is_array($param)){
            $first = $param['first'];
            $second = $param['second'];
            $fr = $param['fr'];
        }
        $hasil ='';
        $tok = $this->extrackToken($token);
        $cond = 'cdh.is_deleted=0 AND counter.ms_branch_id = '.$tok['branch_id'].' AND cdh.trx_date BETWEEN "'.$first.'" AND "'.$second.'" AND cdh.user_id ='.$fr;
        $qr = $this->modelsManager->createQuery('
            SELECT counter.name AS counter, fac.name, SUM( cdd.amount ) AS total
            FROM CrDonationHeader cdh
            JOIN  CrDonationDetail cdd ON cdh.id = cdd.cr_donation_header_id
            JOIN FaFundType fa ON cdd.fa_fund_type_id = fa.id
            JOIN FaFundCategorySub facs ON fa.fa_fund_category_sub_id = facs.id
            JOIN FaFundCategory fac ON facs.fa_fund_category_id = fac.id
            JOIN CrCounter counter ON cdh.cr_counter_id = counter.id
            WHERE '.$cond.'
            GROUP BY counter.id, fac.id');
        $data = $qr->execute();
        $gt= 0;
        if(count($data)>0){
            foreach($data as $dd){
                $hasil[] = array(
                    'counter'=> $dd->counter,
                    'name' => $dd->name,
                    'total'=> $dd->total,                    
                );
                $gt = $gt+$dd->total;
            }
        }else{
            $hasil[] = array(
                    'counter' => '',
                    'name' => '',
                    'total'=> number_format(0,0,',','.'),         
                );
        }
        $return = array(
            'hasil' => $hasil,
            'gt' => $gt
        );
        return $return;
    }
    
    public function getReportByBank($token,$param=''){
        if(is_array($param)){
            $first = $param['first'];
            $second = $param['second'];
            $bank = $param['bank'];
        }
        $bb = FaBank::findFirst($bank);
        $bankname = $bb->bank_name.' - '.$bb->bank_acc_no;
        $hasil ='';
        $cond = 'cdh.is_deleted=0 AND cdbh.fa_bank_id = '.$bank.' AND cdh.trx_date BETWEEN "'.$first.'" AND "'.$second.'"';
        $qr = $this->modelsManager->createQuery('
            SELECT fac.name, SUM( cdd.amount ) AS total               
            FROM CrDonationHeader cdh
            JOIN CrDonationDetail cdd ON cdh.id = cdd.cr_donation_header_id
            JOIN CrDonationBankDetail cdbd ON cdh.cr_donation_bank_detail_id = cdbd.id
            JOIN CrDonationBankHeader cdbh ON cdbd.cr_donation_bank_header_id = cdbh.id
            JOIN FaFundType fa ON cdd.fa_fund_type_id = fa.id
            JOIN FaFundCategorySub facs ON fa.fa_fund_category_sub_id = facs.id
            JOIN FaFundCategory fac ON facs.fa_fund_category_id = fac.id
            WHERE '.$cond.'
            GROUP BY fac.id');
        $data = $qr->execute();
        $gt= 0;
        if(count($data)>0){
            foreach($data as $dd){
                $hasil[] = array(
                    'name' => $dd->name,
                    'total'=> $dd->total,                    
                );
                $gt = $gt+$dd->total;
            }
        }else{
            $hasil[] = array(
                    'name' => '',
                    'total'=> number_format(0,0,',','.'),         
                );
        }
        $return = array(
            'hasil' => $hasil,
            'gt' => $gt,
            'bankname' => $bankname
        );
        return $return;
    }
    
    public function getReportByDonorDetail($token,$param=''){
        $now = date('Y-m-d', time());
        $hasil ='';
        if(is_array($param)){
            $first = $param['first'];
            $second = $param['second'];
        }
        $tok = $this->extrackToken($token);
        $cond = 'cdh.is_deleted = 0 AND ctr.ms_branch_id = '.$tok['branch_id'].' AND cdh.trx_date BETWEEN "'.$first.'" AND "'.$second.'"';
        $kue = 'SELECT dnr.public_id, dnr.hp, dnr.email, dnr.address, dnr.city, dnr.name, cdh.trx_date, SUM(cdd.amount) AS total
            FROM CrDonationHeader cdh
            JOIN CrDonor dnr ON cdh.cr_donor_id = dnr.id
            JOIN CrCounter ctr ON cdh.cr_counter_id = ctr.id
            JOIN CrDonationDetail cdd ON cdh.id = cdd.cr_donation_header_id
            JOIN FaFundType fa ON cdd.fa_fund_type_id = fa.id
            JOIN FaFundCategorySub facs ON fa.fa_fund_category_sub_id = facs.id
            JOIN FaFundCategory fac ON facs.fa_fund_category_id = fac.id';
        $qr = $this->modelsManager->createQuery($kue.'            
            WHERE '.$cond.'
            GROUP BY dnr.public_id, dnr.hp, dnr.email, dnr.address, dnr.city, dnr.name,cdh.trx_date');
        $data = $qr->execute();
        $gt= 0;
        if(count($data)>0){
            foreach($data as $dd){                    
                $hasil[] = array(
                    'public_id' => $dd->public_id,
                    'name' => $dd->name,
                    'hp' => $dd->hp,
                    'email' => $dd->email,
                    'address' => $dd->address,
                    'city' => $dd->city,
                    'trx_date' => date('d/m/Y', strtotime($dd->trx_date)),
                    'sub_total'=> $dd->total,
                );
                $gt = $gt+$dd->total;
            }            
        }else{
            $hasil[] = array(
                    'public_id' => '',
                    'name' => '',
                    'hp' => '',
                    'email' => '',
                    'address' => '',
                    'city' => '',
                    'trx_date' => '',
                    'sub_total'=> 0,
                );
            $return = array('hasil'=>$hasil,'gt'=>0);
        }
        $return = array('hasil'=>$hasil,'gt'=>$gt);
        return $return;
    }
    
    public function moveToDonor($from,$to){
        $response = array('success' => false, 'msg' => 'notfound');
        $data = CrDonationHeader::find('cr_donor_id = '.$from);
        if(count($data)>0){
            $response = array('success' => false, 'msg' => 'donor destination must be different');
            if($from != $to){
                foreach($data as $dd){
                    $dd->cr_donor_id = $to;
                    $dd->save();
                }
                $donor = CrDonor::findFirst($from);
                $donor->delete();
                $response = array('success' => true, 'msg' => 'data updated succesfull');  
            }
        }
		else
		{
			$donor = CrDonor::findFirst($from);
			$donor->delete();
			$response = array('success' => true, 'msg' => 'data updated succesfull');
		}
        return $response;
    }
    
    public function getReportByDonor($token,$param=''){
        $kata ='';
        $hasil ='';
        if(is_array($param)){
            $year = $param['year'];
            $cr_donor_id = $param['cr_donor_id'];
        }
        $first = $year.'-01-01';
        $second = $year.'-12-31';
        $tok = $this->extrackToken($token);
        $cond = 'cdh.is_deleted = 0 AND cdh.trx_date BETWEEN "'.$first.'" AND "'.$second.'" AND cdh.cr_donor_id = '.$cr_donor_id;
        $cond .= ' AND (fac.id = 7 OR fac.id = 1 OR fac.id = 8 OR fac.id = 9)';
        $cond .= ' AND (cdh.settle_status IS NULL OR cdh.settle_status != "void")';
        $select = 'SELECT fac.name as cat, SUM( cdd.amount ) AS total';
        $formkue = '
            FROM CrDonationHeader cdh
            JOIN CrDonor dnr ON cdh.cr_donor_id = dnr.id
            JOIN CrDonationDetail cdd ON cdh.id = cdd.cr_donation_header_id
            JOIN FaFundType fa ON cdd.fa_fund_type_id = fa.id
            JOIN FaFundCategorySub facs ON fa.fa_fund_category_sub_id = facs.id
            JOIN FaFundCategory fac ON facs.fa_fund_category_id = fac.id';
        $kue = $select.$formkue;
        $qr = $this->modelsManager->createQuery($kue.'            
            WHERE '.$cond.'
            GROUP BY fac.id');
        $select = 'SELECT cdh.id,cdh.trx_date, fac.name as cat, SUM( cdd.amount ) AS total';
        $kue2 = $select.$formkue;
        $det = $this->modelsManager->createQuery($kue2.' WHERE '.$cond.'
            GROUP BY cdh.id,cdh.trx_date,fac.id ORDER BY trx_date desc LIMIT 0 , 12');
         $det2 = $this->modelsManager->createQuery($kue2.' WHERE '.$cond.'
            GROUP BY cdh.id,cdh.trx_date,fac.id ORDER BY trx_date desc LIMIT 12 , 12');
        $datadet = $det->execute();
        $datadet2 = $det2->execute();
        $data = $qr->execute();
        $gt= 0;
        $infaq = 0;
        $zakat = 0;
        $wakaf = 0;
        $kurban = 0;
        $sel = 'SELECT SUM( cdd.amount ) AS total';
        $infaqk = $this->modelsManager->createQuery($sel.$formkue.'            
            WHERE '.$cond.' AND fac.id = 1')->execute();
        $zakatk = $this->modelsManager->createQuery($sel.$formkue.'            
            WHERE '.$cond.' AND fac.id = 7')->execute();
        $wakafk = $this->modelsManager->createQuery($sel.$formkue.'            
            WHERE '.$cond.' AND fac.id = 8')->execute();
        $kurbank = $this->modelsManager->createQuery($sel.$formkue.'            
            WHERE '.$cond.' AND fac.id = 9')->execute();
        if(count($infaqk) > 0){
            foreach($infaqk as $dj){
                if($dj->total != null){
                    $infaq = $dj->total; 
                }    
            }
        }
        if(count($zakatk) > 0){
            foreach($zakatk as $dj){
               if($dj->total != null){
                    $zakat = $dj->total; 
                }    
            }
        }
        if(count($wakafk) > 0){
            foreach($wakafk as $dj){
                if($dj->total != null){
                    $wakaf = $dj->total; 
                }
            }
        }
        if(count($kurbank) > 0){
            foreach($kurbank as $dj){
                if($dj->total != null){
                    $kurban = $dj->total; 
                }
            }
        }
        $gtotal = array(
            'infak' => number_format($infaq,0,',','.'),
            'zakat' => number_format($zakat,0,',','.'),
            'wakaf' => number_format($wakaf,0,',','.'),
            'kurban'=> number_format($kurban,0,',','.')
        );
        if(count($data)>0){
            foreach($data as $dd){          
                $hasil[] = array(
                    'cat' => $dd->cat,
                    'total' => $dd->total,
                );
                $gt = $gt+$dd->total;          
            }
            foreach($datadet as $dt){
                $detail[] = array(
                    'id' => $dt->id,
                    'trx_date' => date_format(date_create($dt->trx_date), 'd M Y'),
                    'cat' => $dt->cat,
                    'total' => $dt->total
                );
            }
            if(count($datadet2) > 0){
                foreach($datadet2 as $dt2){
                    $detail2[] = array(
                        'id' => $dt2->id,
                        'trx_date' => date_format(date_create($dt2->trx_date), 'd M Y'),
                        'cat' => $dt2->cat,
                        'total' => $dt2->total
                    );
                }
            }else{
                $detail2[] = array(
                    'id' => '',
                    'trx_date' => '',
                    'cat' => '',
                    'total' => ''
                );
            }
        }else{
            $kata='
            Kami Mengucapkan terima kasih atas kepercayaan Bapak/ Ibu kepada <br>
            Dompet Dhuafa. <br>
            Untuk periode diatas, kami belum menerima konfirmasi atas donasi Zakat, <br>
            Infaq / Shadaqoh atau wakaf anda <br>
            Untuk Konfirmasi Donasi, Bapak / Ibu dapat menghubungi :  <br>
            Layan Donatur, Telp. 021-7416050 (Hunting), Fax. 021-7416070, <br>
            Email : layandonatur@dompetdhuafa.org
            ';
            $hasil[] = array(
                    'cat' => '',
                    'total' => '',
                );
            $detail[] = array(
                'id' => '',
                'trx_date' => '',
                'cat' => '',
                'total' => '',
            );
            $detail2[] = array(
                'id' => '',
                'trx_date' => '',
                'cat' => '',
                'total' => '',
            );
        }
        $return = array('kata' => $kata , 'hasil'=>$hasil,'detail' => $detail, 'detail2' => $detail2 ,'gt'=>$gt,'gtotal'=>$gtotal);
        return $return;
    }
    
    public function exportallfields($token,$param){
        $response = array('success' => false, 'msg' => 'Invalid Request');
        $cond = 'where head.is_deleted = 0';
        $limit = '';
        if($param['first'] != '' && $param['second'] != ''){
            $first = $param['first'];
            $second = $param['second'];
            $cond .= ' and head.created BETWEEN "'.$first.'" and "'.$second.'"';
        }
        if($param['start'] != '' && $param['limit'] != ''){
            $limit = ' LIMIT '.$param['start'].','.$param['limit'];
        }
        $kue = 'select 
        head.id as donation_id, head.created, head.trx_date,
        dnr.public_id, dnr.name, dnr.email, dnr.hp, dnr.npwp, dnr.address, dnr.city, dnr.province, dnr.country ,detail.amount, dnr.kd_cc,
        fundt.name as fund_type, fundcs.name as fund_category_sub, fac.name as fund_category, 
        branch.name as branch_name, branch.city as branch_city, branch.state as branch_state, branch.country as branch_country,
        ctr.name as counter_name, ctr.address as counter_address, ctr.city as counter_city, ctr.province as counter_province
        from CrDonationDetail detail 
        join CrDonationHeader head on detail.cr_donation_header_id = head.id
        join CrCounter ctr on head.cr_counter_id = ctr.id
        join CrDonor dnr on head.cr_donor_id = dnr.id 
        join FaFundType fundt on detail.fa_fund_type_id = fundt.id
        join FaFundCategorySub fundcs on fundt.fa_fund_category_sub_id = fundcs.id
        join FaFundCategory fac on fundcs.fa_fund_category_id = fac.id
        join MsBranch branch on ctr.ms_branch_id = branch.id
        '.$cond;
        $qr = $this->modelsManager->createQuery($kue);
        $data = $qr->execute();
        $total = count($data);
        $gt = 0;
        if($total > 0){
            $qr = $this->modelsManager->createQuery($kue.$limit);
            $data = $qr->execute();
            foreach($data as $datas){
                $rs[] = array(
                    'donation_id' => $datas->donation_id,
                    'created' => $datas->created,
                    'trx_date' => $datas->trx_date,
                    'donor_public_id' => $datas->public_id,
                    'donor_name' => $datas->name,
                    'donor_email' => $datas->email,
                    'donor_hp' => $datas->hp,
                    'donor_npwp' => $datas->npwp,
                    'donor_address'=> $datas->address,
                    'donor_city' => $datas->city,
                    'donor_province' => $datas->province,
                    'donor_country' => $datas->country,
                    'kd_cc' => $datas->kd_cc,
                    'amount'=> $datas->amount,
                    'fund_type' => $datas->fund_type,
                    'fund_category_sub' => $datas->fund_category_sub,
                    'fund_category' => $datas->fund_category,
                    'branch_name' => $datas->branch_name,
                    'branch_city' => $datas->branch_city,
                    'branch_state' => $datas->branch_state,
                    'branch_country' => $datas->branch_country,
                    'counter_name' => $datas->counter_name,
                    'counter_address' => $datas->counter_address,
                    'counter_city' => $datas->counter_city,
                    'counter_province' => $datas->counter_province
                );
                $gt = $gt + $datas->amount;
            }
            $response = array('success'=>true, 'data'=>$rs, 'total'=>$total , 'gt'=>$gt , 'param' => $param);
        }else{
            $response = array('success'=>false, 'msg' => 'Empty Data');    
        }
        return $response;
    }
}
