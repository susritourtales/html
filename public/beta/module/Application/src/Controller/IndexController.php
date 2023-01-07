<?php


namespace Application\Controller;

use Application\Handler\CronJob;
use Application\Handler\Razorpay;
use Application\Handler\SendFcmNotification;
use \Mpdf\Mpdf;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Razorpay\Api\Api;
class IndexController extends BaseController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    public function paymentResponseAction()
    {
        $details=$_REQUEST;
        $fp = fopen(getcwd() . "/public/data/payment.txt" , 'w');
        fwrite($fp,json_encode($details));
        fclose($fp);

          if(count($details))
          {
                if(isset($details['payment_request_id']))
                {

                    $checkPayment = $this->paymentTable()->getPayment(array('payment_request_id' => $details['payment_request_id']));

                     if(count($checkPayment))
                     {
                         if ($details['status'] == 'Credit' && $details['payment_id'])
                         {
                             $status = \Admin\Model\Payments::payment_success;
                             $updateData = array('status' => $status, 'payment_response_id' => $details['payment_id']);
                             $updateBookingData = array('payment_status' => $status);
                         }else
                         {
                             $status = \Admin\Model\Payments::payment_failed;
                             $updateData = array('status' => $status, 'payment_response_id' => $details['payment_id']);
                             $updateBookingData = array('payment_status' => $status);
                         }

                         $paymentRequest = $this->paymentTable()->updatePayment($updateData, array('payment_request_id' => $details['payment_request_id']));


                            if($status==\Admin\Model\Payments::payment_success)
                            {
                                $bookingId=$checkPayment[0]['booking_id'];
                                $paymentRequest = $this->bookingsTable()->updateBooking($updateBookingData, array('booking_id' => $bookingId));

                                $bookingList=$this->bookingsTable()->bookingsDetailsEmail($bookingId);

                                $userDetails=$this->userTable()->getFields(array('user_id'=>$bookingList['user_id']),array('email','mobile','mobile_country_code','role'));
                                $mobile=$userDetails['mobile'];
                                $mobileCountry=$userDetails['mobile_country_code'];
                                $userType = $userDetails['role'];
                                if($userType==\Admin\Model\User::Tour_Operator_role)
                                {
                                    $tourOperatorDetails=$this->userTable()->getUserDetails($bookingList['user_id']);
                                    $email = $tourOperatorDetails['email_id'];


                                }else if($userType==\Admin\Model\User::Tour_coordinator_role)
                                {
                                    $tourCoordinatorDetails=$this->userTable()->getUserDetails($bookingList['user_id']);
                                    $email=$tourCoordinatorDetails['email'];
                                }else
                                {
                                    $email=$userDetails['email'];
                                }
                                if($email)
                                {
                                    $html = file_get_contents($this->getBaseUrl() . '/application/booking-pdf?bid=' . $bookingId, true);
                                    $mpdf = new mPDF(['tempDir' => getcwd()."/public/data/temp"]);
                                    $mpdf->SetDisplayMode("fullpage");
                                    /*$stylesheet = file_get_contents(getcwd() . "/public/css/bootstrap.css");*/
                                    /*$mpdf->WriteHtml($stylesheet, 1);*/
                                    $mpdf->WriteHTML($html);
                                    $mpdf->Output(getcwd()."/public/data/susri_booking_".$bookingId.".pdf", "F");
                                    $subject =\Admin\Model\PlacePrices::tour_type[$bookingList['tour_type']] ." Booking Details ";
                                    $bookingList['user_type']=$userType;
                                    $this->sendbookingDetails($email, $subject, 'mail-booking-details', $bookingList,getcwd()."/public/data/susri_booking_".$bookingId.".pdf");
                                    $userId=$bookingList['user_id'];
                                    $message=sprintf('Congratulations, You booked %s  on date %s and Passwords have been sent to email %s.', \Admin\Model\PlacePrices::tour_type[$bookingList['tour_type']],date("d-m-Y",strtotime($bookingList['created_at'])),$email);
                                    $tourType=$bookingList['tour_type'];
                                    $notificationDetails = array('notification_data_id'=>$bookingId ,'status' => \Admin\Model\Notification::STATUS_UNREAD, 'notification_recevier_id' => $userId, 'notification_type' => \Admin\Model\Notification::NOTIFICATION_TYPE_BOOKING_NOTIFICATION, 'notification_text' => $message,'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s"));
                                    $registrationIds = $this->fcmTable()->getDeviceIds($userId);
                                    $notification=   $this->notificationTable()->saveData($notificationDetails);

                                    if($registrationIds)
                                    {
                                        $notification = new SendFcmNotification();
                                        $notification->sendPushNotificationToFCMSever($registrationIds, array('message' => $notificationDetails['notification_text'], 'title' =>   \Admin\Model\PlacePrices::tour_type[$tourType]." Booked successfully", 'id' => $notificationDetails['notification_data_id'], 'type' => $notificationDetails['notification_type']));
                                    }
                                    $smsMessage="Congratulations! You have booked  ".\Admin\Model\PlacePrices::tour_type[$bookingList['tour_type']] ." tales on ".date("d-m-Y",strtotime($bookingList['created_at']))." and Password has been sent to your ".$email;
                                    $counter=1;
                                     /* foreach ($bookingList['passwords'] as $password)
                                      {
                                          $smsMessage .=' User '.$counter .': '.$password.",";
                                          $counter++;
                                      }*/

                                    $this->sendPasswordSms($mobileCountry.$mobile,array('text'=>$smsMessage));
                                }
                            }

                         echo 'success';
                         exit;
                     }
                }

          }
        echo 'fail';
        exit;
    }
    public function bookingPdfAction_old()
    {
           $bookingId="";
           $suerid="";
           if(!is_null($_GET['bid'])){
            $bookingId=$_GET['bid'];
           }
           if(!is_null($_GET['suid'])){
            $suerid=$_GET['suid'];
           }
           if($bookingId != "0") {
               $bookingDetails = $this->bookingsTable()->bookingsDetailsEmail($bookingId);
                if(count($bookingDetails)>0)
                {
                    $userDetails = $this->userTable()->getFields(array('user_id' => $bookingDetails['user_id']), array('email', 'mobile', 'mobile_country_code','role','user_name'));

                    $mobile = $userDetails['mobile'];
                    $mobileCountry = $userDetails['mobile_country_code'];
                    $userType = $userDetails['role'];
                /*  if($userType==\Admin\Model\User::Tour_Operator_role)
                    {
                        $tourOperatorDetails=$this->userTable()->getUserDetails($bookingDetails['user_id']);
                        $userName = $tourOperatorDetails['contact_person'].' ('.$tourOperatorDetails['company_name'].')';
                        $email = $tourOperatorDetails['email_id'];
                    }else if($userType==\Admin\Model\User::Tour_coordinator_role)
                    {
                        $tourCoordinatorDetails=$this->userTable()->getUserDetails($bookingDetails['user_id']);

                        $userName=$tourCoordinatorDetails['user_name'].' ('.$tourCoordinatorDetails['company_name'].')';
                        $email=$tourCoordinatorDetails['email'];
                    }else
                    { */
                        $userName=$userDetails['user_name'];
                        $email=$userDetails['email'];
                    //    }
                    $view = new ViewModel(array('baseUrl' => $this->getBaseUrl(), 'data' => $bookingDetails,'user_name'=>$userName));
                }
                else
                {
                    $userId=$this->bookingsTable()->getField(array('booking_id'=>$bookingId), 'user_id');
                    $userDetails=$this->userTable()->getFields(array('user_id'=>$userId),array('user_name','email','mobile','mobile_country_code','role','subscription_start_date','subscription_end_date'));
                    $bookingTourDetails=$this->bookingtourdetailsTable()->getFields(array('booking_id'=>$bookingId),  array('discount_percentage','tour_date','no_of_days','no_of_users','expiry_date','sponsered_users','price','booking_tour_id', 'created_at'));
                    
                    $bookingList = $bookingTourDetails;
                    //$bookingList['email']=$userDetails['email'];
                    //$bookingList['role'] = $userDetails['role'];
                    //$bookingList['tax'] = ($bookingTourDetails['price']?($bookingTourDetails['price']-($bookingTourDetails['price']/1.18)):0);
                    //$bookingList['discount_price'] = ($bookingTourDetails['discount_percentage']==""?0:$bookingTourDetails['discount_percentage']);
                    $bookingList['booking_id'] = $bookingId;
                    //$bookingList['subs_start_date'] = $userDetails['subscription_start_date'];
                    $bookingList['subs_end_date'] = $userDetails['subscription_start_date'];
                    $bookingList['booking_type'] = $this->bookingsTable()->getField(array('booking_id'=>$bookingId), 'booking_type');
                    //$bookingList['user_name'] = $userDetails['user_name'];
                    //$bookingList['user_id'] = $userId;
                    if($bookingList['booking_type']==\Admin\Model\Bookings::booking_Buy_Passwords)
                    {
                        $bookingList['passwords']=$this->bookingsTable()->bookingPasswords($bookingId);
                    }

                    $view = new ViewModel(array('baseUrl' => $this->getBaseUrl(), 'data' => $bookingList,'user_name'=>$userDetails['user_name']));
                } 
                $view->setTerminal(true);
                return $view; 
           }else{
                if($suerid){
                    $userDetails=$this->userTable()->getFields(array('user_id'=>$suerid),array('user_name','email','mobile','mobile_country_code','role','subscription_end_date'));                    
                    $view = new ViewModel(array('baseUrl' => $this->getBaseUrl(), 'data' => $userDetails,'user_name'=>$userDetails['user_name']));
                    $view->setTerminal(true);
                    return $view;
                }
           }
           
    }

    public function bookingPdfAction()
    {
        $bookingId="";
        if(!is_null($_GET['bid'])){
            $bookingId=$_GET['bid'];
        }
        
        if($bookingId != "0") {
            $bookingDetails = $this->bookingsTable()->bookingsDetailsEmail($bookingId);
            if(count($bookingDetails)>0)
            {
                $userName = $this->userTable()->getField(array('user_id' => $bookingDetails['user_id']), 'user_name');
                $view = new ViewModel(array('baseUrl' => $this->getBaseUrl(), 'data' => $bookingDetails,'user_name'=>$userName));
            }
            else
            {
                $userId=$this->bookingsTable()->getField(array('booking_id'=>$bookingId), 'user_id');
                $userDetails=$this->userTable()->getFields(array('user_id'=>$userId),array('user_name','subscription_end_date','bonus_flag','passwords_count'));
                $bookingTourDetails=$this->bookingtourdetailsTable()->getFields(array('booking_id'=>$bookingId),  array('discount_percentage','tour_date','no_of_days','no_of_users','expiry_date','sponsered_users','price','booking_tour_id', 'created_at'));
                /* $pdata['bonus_flag'] = false;
                if($userDetails['passwords_count']<10 && $userDetails['passwords_count'] + $bookingTourDetails['no_of_users'] >= 10){
                    $pdata['bonus_flag'] = true;
                } */
                $bookingList = $bookingTourDetails;
                $bookingList['booking_id'] = $bookingId;
                $bookingList['subs_end_date'] = $userDetails['subscription_end_date'];
                $bookingList['bonus_flag'] = $userDetails['bonus_flag']; //$pdata['bonus_flag'];
                $bookingList['booking_type'] = $this->bookingsTable()->getField(array('booking_id'=>$bookingId), 'booking_type');
                if($bookingList['booking_type']==\Admin\Model\Bookings::booking_Buy_Passwords)
                {
                    $bookingList['passwords']=$this->bookingsTable()->bookingPasswords($bookingId);
                }
                $view = new ViewModel(array('baseUrl' => $this->getBaseUrl(), 'data' => $bookingList,'user_name'=>$userDetails['user_name']));
            } 
            $view->setTerminal(true);
            return $view; 
        }
    }

    public function paymentGateWayResponseAction()
    {
        try{
            $details=$this->getRequest()->getPost();
            // print_r($details); exit;
            if(!isset($details['error'])) 
            {
                $attributes = array("razorpay_signature" => $_REQUEST['razorpay_signature'], "razorpay_payment_id" => $_REQUEST['razorpay_payment_id'], "razorpay_order_id" => $_REQUEST['razorpay_order_id']);
                
                if(count($details) && count($attributes))
                {
                    if(isset($details['razorpay_order_id']) && $details['razorpay_payment_id'])
                    {
                        $checkPayment = $this->paymentTable()->getPayment(array('payment_request_id' => $details['razorpay_order_id']));
                        if(count($checkPayment))
                        {
                            if($checkPayment[0]['status']==\Admin\Model\Payments::payment_success)
                            {
                                return new JsonModel(array('success'=>true,'message'=>'Payment Done'));
                            }   
                            $status = 0; //\Admin\Model\Payments::payment_success;
                            $updateData = array('status' => $status, 'payment_response_id' => $details['razorpay_payment_id']);
                            $updateBookingData = array('payment_status' => $status);
                            $paymentRequest = $this->paymentTable()->updatePayment($updateData, array('payment_request_id' => $details['razorpay_order_id']));
                            return new JsonModel(array('success'=>false,'message'=>'2'));
                            if($paymentRequest) //if($status==\Admin\Model\Payments::payment_success)
                            {
                                $bookingId=$checkPayment[0]['booking_id'];
                                $paymentRequest = $this->bookingsTable()->updateBooking($updateBookingData, array('booking_id' => $bookingId));
                                //$getUserDetails = array();
                                $bookingList=$this->bookingsTable()->bookingsDetailsEmail($bookingId);
                                
                                $userId=$this->bookingsTable()->getField(array('booking_id'=>$bookingId), 'user_id');
                                $udata=$this->userTable()->getFields(array('user_id'=>$userId), array('role','subscription_count', 'subscription_start_date', 'subscription_end_date'));
                                $pdata['bonus_flag'] = false;
                                $userType=$udata['role'];
                                $scount=$udata['subscription_count'];                                    
                                $bookingDetails=$this->bookingsTable()->bookingDetails(array('user_id'=>$userId, 'booking_id'=>$bookingId));
                                $bookingType = $bookingDetails['booking_type'];
                                if($bookingType == \Admin\Model\Bookings::booking_Subscription)
                                {
                                    $pricingDetails = $this->getEffectivePricingDetails($userId);
                                    $validityPeriod = $pricingDetails['subscription_validity'];
                                }                                        
                                elseif($bookingType == \Admin\Model\Bookings::booking_Buy_Passwords)
                                {
                                    $pricingDetails = $this->getPasswordPricingDetails($userId);
                                    $validityPeriod = $pricingDetails["sponsor_pwd_validity"];
                                }
                                //if($userType == \Admin\Model\User::Individual_role){
                                if($bookingType == \Admin\Model\Bookings::booking_Subscription){
                                    $where=array("user_id"=>$userId);
                                    $subsdt = date("Y-m-d");
                                    $subedt = date('Y-m-d', strtotime(date("y-m-d") . " +$validityPeriod days"));
                                    $renewed_on = date("Y-m-d");
                                    if($scount > 0) // if renewal
                                    {
                                        if(date('Y-m-d', strtotime($udata['subscription_end_date'])) > date("Y-m-d")){
                                            $subsdt = date('Y-m-d', strtotime($udata['subscription_end_date'] . " +1 days"));
                                            $subedt = date('Y-m-d', strtotime($subsdt . " +$validityPeriod days"));
                                        }
                                    } 
                                    $update=array("subscription_count"=>$scount+1, "subscription_type"=>$bookingType,"subscription_start_date"=>$subsdt, "subscription_end_date"=>$subedt, "renewed_on" => $renewed_on);
                                    
                                    //$update=array("subscription_count"=>$scount+1, "subscription_type"=>$bookingType,"subscription_start_date"=>date("Y-m-d"), "subscription_end_date"=>date('Y-m-d', strtotime(date("y-m-d") . " +  $validityPeriod days")));
                                    if($userType == \Admin\Model\User::Individual_role)
                                        $update["role"] = \Admin\Model\User::Subscriber_role;
                                    
                                    $this->userTable()->updateUser($update,$where);
                                }elseif($bookingType == \Admin\Model\Bookings::booking_Buy_Passwords){
                                    $subedt = $udata['subscription_end_date'];
                                }
                                // if subscriber/sponsor - start
                                $userDetails=$this->userTable()->getFields(array('user_id'=>$userId),array('user_id','user_name','email','mobile','mobile_country_code','role','subscription_count','subscription_start_date','subscription_end_date','res_state','address','status','company_role','bonus_flag', 'discount_percentage','passwords_count', 'sponsor_type', 'subscription_type'));

                                $bookingTourDetails=$this->bookingtourdetailsTable()->getFields(array('booking_id'=>$bookingId),  array('discount_percentage','tour_date','no_of_days','no_of_users','expiry_date','sponsered_users','price','booking_tour_id', 'created_at'));
                                $pdata['bonus_flag'] = 0; //false;
                                if($bookingType == \Admin\Model\Bookings::booking_Buy_Passwords){
                                    /* if($userDetails['passwords_count'] < $pricingDetails["sponsor_bonus_min"] && $userDetails['passwords_count'] + $bookingTourDetails['no_of_users'] >= $pricingDetails["sponsor_bonus_min"]) */
                                    if($userDetails['passwords_count']<10 && $userDetails['passwords_count'] + $bookingTourDetails['no_of_users'] >= 10)
                                    {
                                        $pdata['bonus_flag'] = true;
                                        $data['passwords_count'] = $userDetails['passwords_count'] + $bookingTourDetails['no_of_users'];
                                        $data['bonus_flag'] = 1;
                                        $pcUpdate=$this->userTable()->updateUser($data,array('user_id'=>$userId));
                                    }else{
                                        $data['passwords_count'] = $userDetails['passwords_count'] + $bookingTourDetails['no_of_users'];
                                        $data['bonus_flag'] = 0;
                                        $pcUpdate=$this->userTable()->updateUser($data,array('user_id'=>$userId));
                                    }
                                    // check if the sponsor has promoter
                                    $promoterId = $this->referTable()->getField(array('user_id'=> $userId), 'ref_id');
                                    $spoPwdOldCount = $this->referTable()->getField(array('user_id'=> $userId), 'pwds_purchased');
                                    $amtPerPwd = $this->promoterParametersTable()->getField(array('id'=>'1'), 'amt_per_pwd');
                                    //echo "$userId, $promoterId , $amtPerPwd"; exit;
                                    $spoPwdCount = $bookingTourDetails['no_of_users'];
                                    if($pdata['bonus_flag'])
                                        $spoPwdCount = $bookingTourDetails['no_of_users']-$pricingDetails["sponsor_comp_pwds"];
                                    $prmTransAmt = round(($amtPerPwd * $spoPwdCount),2);
                                    
                                    // if sponsor has a promoter, get promoter details
                                    if($promoterId){ 
                                        //get and check Promoter status and subscription validity (=> he ll be sponsor until sed)
                                        $promoterRes = $this->userTable()->getFields(array('user_id'=> $promoterId), array('is_promoter', 'role', 'subscription_end_date'));
                                        $today = date("Y-m-d");
                                        if($promoterRes['is_promoter'] == \Admin\Model\User::Is_Promoter && $promoterRes['role'] == \Admin\Model\User::Sponsor_role && $promoterRes['subscription_end_date'] >= $today){ 
                                            // get and check sponsor status
                                            $sponsorStatus = $this->referTable()->getField(array('user_id'=> $userId), 'sponsor_status');
                                            if($sponsorStatus == \Admin\Model\Refer::sponsor_active){
                                                // update no of passwords bought by sponsor under promoter in refer table
                                                $updatePwdCount = $this->referTable()->updateRefer(array('pwds_purchased'=>$spoPwdOldCount + $spoPwdCount), array('user_id'=>$userId));
                                                $pwdCeiling = $this->promoterParametersTable()->getField(array('id'=>1), 'pwd_ceiling');
                                                $redeemCeiling = $this->promoterParametersTable()->getField(array('id'=>1), 'redeem_ceiling');
                                                if($spoPwdOldCount + $spoPwdCount >= $pwdCeiling){ 
                                                    // update sponsor status to successful
                                                    $updateSS = $this->referTable()->updateRefer(array('sponsor_status'=>\Admin\Model\Refer::sponsor_successful), array('user_id'=>$userId));
                                                }
                                                if($spoPwdOldCount < $pwdCeiling)
                                                { 
                                                    /* $promBank = $this->promoterDetailsTable()->getFields(array('user_id'=>$promoterId), array('ifsc_code', 'bank_ac_no', 'due_amount', 'trigger_payment', 'latest_paid_date')); */
                                                    $promBank = $this->promoterDetailsTable()->getFields(array('user_id'=>$promoterId), array('ifsc_code', 'bank_ac_no', 'redeemed_at', 'redeem'));
                                                   
                                                    $ttlPromPwds = $this->referTable()->getTotalPromoterPasswords($promoterId);
                                                    
                                                    if($promBank['redeem'] == '1'){ 
                                                        if($ttlPromPwds + $spoPwdCount - $promBank['redeemed_at'] >= $redeemCeiling){
                                                            // update redeem value
                                                            $updateRV = $this->promoterDetailsTable()->updatePromoterDetails(array('redeem'=>'0'), array('user_id'=>$promoterId));
                                                        }
                                                    }
                                                    $spoPay = $this->referTable()->getFields(array('user_id'=>$userId), array('due_amount', 'trigger_payment', 'latest_paid_date'));
                                                    $currentDT = date("Y-m-d H:i:s");
                                                    // create promoter transactions array
                                                    $transArr = array('promoter_id'=>$promoterId, 
                                                    'transaction_type'=>\Admin\Model\PromoterTransactions::transaction_due, 'sponsor_id'=>$userId, 'account_no'=>$promBank['bank_ac_no'], 'ifsc_code'=>$promBank['ifsc_code'], 'transaction_date'=>$currentDT,'no_of_pwds'=>$spoPwdCount, 'amount'=>$prmTransAmt);
                                                    // update promoter transaction
                                                    $addPrmTrans = $this->promoterTransactionsTable()->addPromoterTransaction($transArr);
                                                    //print_r($addPrmTrans);
                                                    //update promoter payments table
                                                    $ppres = $this->promoterPaymentsTable()->setSponsorPaymentDetails(array("due_date"=>$currentDT, "due_amount"=>$prmTransAmt, "promoter_id"=>$promoterId, "sponsor_id"=>$userId, 'transaction_type'=>\Admin\Model\PromoterTransactions::transaction_due));

                                                    //update promoter details table
                                                    /* $upPrmDetails = $this->promoterDetailsTable()->updatePromoterDetails(array('due_amount'=> $promBank['due_amount'] + $prmTransAmt, 'latest_due_date'=>$currentDT, 'trigger_payment'=>$promBank['trigger_payment']+$spoPwdCount), array('user_id'=>$promoterId)); */
                                                    $upRefDetails = $this->referTable()->updateRefer(array('due_amount'=> $spoPay['due_amount'] + $prmTransAmt, 'latest_due_date'=>$currentDT, 'trigger_payment'=>$spoPay['trigger_payment']+$spoPwdCount), array('user_id'=>$userId));
                                                }
                                            }
                                            /* else{
                                                return new JsonModel(array('success'=>false,'message'=>"sponsor status = " . $sponsorStatus));
                                            } */
                                        }
                                        /* else {
                                            return new JsonModel(array('success'=>false,'message'=>"promoter status = " . $promoterRes['is_promoter']. ", role = ". $promoterRes['role']));
                                        } */
                                    }
                                }
                                
                                if($pdata['bonus_flag'])
                                    $totalOriPrice = round(($pricingDetails['oriprice'] * ($bookingTourDetails['no_of_users']-$pricingDetails["sponsor_comp_pwds"])),2);
                                else                                    
                                    $totalOriPrice = round(($pricingDetails['oriprice'] * $bookingTourDetails['no_of_users']),2);

                                $GSTD = 1 + ($pricingDetails['GST']/100);
                                if(count($bookingList)==0){
                                    $bookingList = $bookingTourDetails;
                                    $bookingList['user_type'] = $userType;
                                    $bookingList['tax'] = ($bookingTourDetails['price']?($bookingTourDetails['price']-($bookingTourDetails['price']/$GSTD)):0);
                                    $bookingList['discount_percentage'] = ($bookingTourDetails['discount_percentage']==""?0:$bookingTourDetails['discount_percentage']);
                                    $bookingList['discount_price'] = $bookingTourDetails['price'];
                                }
                                                                
                                $bookingList['booking_id'] = $bookingId;
                                $bookingList['subs_start_date'] = $subsdt; //$userDetails['subscription_start_date'];
                                $bookingList['subs_end_date'] = $subedt; //$userDetails['subscription_end_date'];
                                $bookingList['renewed_on'] = $renewed_on;
                                //$bookingList['tour_type'] = \Admin\Model\PlacePrices::tour_type_All_tour;
                                $bookingList['booking_type'] = $bookingType;
                                $bookingList['user_name'] = $userDetails['user_name'];
                                $bookingList['mobile'] = $userDetails['mobile'];
                                $bookingList['mobile_country_code'] = $userDetails['mobile_country_code'];
                                $bookingList['email'] = $userDetails['email'];
                                $bookingList['res_state'] = $userDetails['res_state'];
                                $bookingList['address'] = $userDetails['address'];
                                $bookingList['status'] = $userDetails['status'];
                                $bookingList['company_role'] = $userDetails['company_role'];
                                $bookingList['role'] = $userDetails['role'];
                                $bookingList['user_type']=$userDetails['role'];
                                $bookingList['passwords_count'] = $userDetails['passwords_count'];
                                $bookingList['user_id'] = $userId;
                                $bookingList['annual_price'] = $pricingDetails['annual_price'];
                                $bookingList['plantype'] = $pricingDetails['plantype'];
                                $bookingList['start_date'] = $pricingDetails['start_date'];
                                $bookingList['end_date'] = $pricingDetails['end_date'];
                                $bookingList['sponsor_bonus_min'] = $pricingDetails['sponsor_bonus_min'];
                                //$bookingList['price'] = $pricingDetails['price'];
                                $bookingList['offerPrice'] = $pricingDetails['oriprice'];
                                $bookingList['oriprice'] = $totalOriPrice; //  * $GSTD;
                                $bookingList['bonus_flag'] = $pdata['bonus_flag']; //$userDetails['bonus_flag'];
                                $bookingList['subscription_count'] = $userDetails['subscription_count'];
                                $bookingList['sponsor_type'] = $userDetails['sponsor_type'];
                                $bookingList['subscription_type'] = $userDetails['subscription_type'];
                                if($userDetails['email'])
                                {
                                    // added by Manjary - start - remove on live - start
                                    /* $stream_opts = [
                                        "ssl" => [
                                            "verify_peer"=>false,
                                            "verify_peer_name"=>false,
                                        ]
                                    ];  */
                                    // added by Manjary - end -- remove on live
                                    if($bookingList['booking_type']==\Admin\Model\Bookings::booking_Subscription){
                                        $bookingList['heading']  ="Welcome as STT Subscriber";
                                        $this->mailSTTUserNoAttachment($userDetails['email'], $bookingList['heading'] , 'mail-stt-user', $bookingList);
                                    }
                                    elseif($bookingList['booking_type']==\Admin\Model\Bookings::booking_Buy_Passwords)
                                    {
                                        $bookingList['passwords']=$this->bookingsTable()->bookingPasswords($bookingId);
                                        //$html = file_get_contents($this->getBaseUrl() . '/application/booking-pdf?suid=0&bid=' . $bookingId, false, stream_context_create($stream_opts));// added by Manjary - end -- remove on live
                                        $html = file_get_contents($this->getBaseUrl() . '/application/booking-pdf?suid=0&bid=' . $bookingId, true);// - removed by Manjary to make local work - use on live
                                        $mpdf = new mPDF(['tempDir' => getcwd()."/public/data/temp"]);
                                        $mpdf->SetDisplayMode("fullpage");
                                        $mpdf->WriteHTML($html);
                                        $mpdf->Output(getcwd()."/public/data/susri_booking_".$bookingId.".pdf", "F"); 
                                        $bookingList['heading'] = "STT Passwords Purchase Details";
                                        $this->emailSTTUserWithAttachment($userDetails['email'], $bookingList['heading'], 'mail-stt-user', $bookingList,getcwd()."/public/data/susri_booking_".$bookingId.".pdf"); 
                                    }
                                    //$this->sendbookingDetails($email, $subject, 'mail-booking-details', $bookingList,getcwd()."/public/data/susri_booking_".$bookingId.".pdf");
                                }
                                // if subscriber/sponsor -  end


                                $message = "";
                                $notificationTitle = "";
                                $smsMessage = "";
                                if($bookingType == \Admin\Model\Bookings::booking_Subscription){
                                    if($bookingList['subscription_count'] == "1"){
                                        if($bookingList['mobile_country_code'] == "91"){
                                            $message="Congratulations on your choice of becoming “QUESTT” Subscriber of STT.\nWelcome to Susri Tour Tales.\nSelect, Download and Listen to the Tales about the Tourist places in the language you prefer.\nEnjoy your time with STT.\n\n***\n\nYou can also help others to become “TWISTT” Subscribers- for Short Duration of 15 days. For details, see the message  sent to your registered mail Id or the Web Site.";
                                        } else {
                                            $message="Congratulations on your choice of becoming “QUESTT” Subscriber of STT.\nWelcome to Susri Tour Tales.\nSelect, Download and Listen to the Tales about the Tourist places in the language you prefer.\nEnjoy your time with STT.";
                                        }
                                        $notificationTitle = "Welcome as Subscriber";
                                        $smsMessage="Welcome%20to%20Susri%20Tour%20Tales.%0ADownload%20and%20Listen%20to%20the%20tales%20of%20your%20choice.%0A%0ABecome%20a%20sponsor%20and%20earn%20through%20App.%0ASee%20e-mail%20message%20for%20details.";
                                    }else{
                                        /* $message="Thank you for your association with STT.\nWish you a happy time with STT."; */
                                        $message = "Thank you for your timely renewal of STT. We wish you happy tours with STT.";
                                        $notificationTitle = "Renewal sucsessful";
                                        $smsMessage="Thank%20you%20for%20your%20continued%20association%20with%20STT.%20%0AWish%20you%20one%20more%20year%20of%20awareness%20and%20enjoyment.";
                                    }
                                }
                                elseif($bookingType == \Admin\Model\Bookings::booking_Buy_Passwords){
                                    if($bookingList['bonus_flag']){
                                        $message ="Congratulations.\nAs you have completed the purchase of FIRST 10 TWISTT passwords, you are eligible to receive 5 bonus passwords. They are sent to your registered e-mail and to your Password History Table.";
                                        /* $message = "Congratulations.\nAs you have completed the purchase of first 10 passwords, you are eligible to receive 5 bonus passwords. They are sent to your registered e-mail."; */
                                        $smsMessage = "Congratulations.%0ABy%20purchasing%20ten%20passwords%2C%20you%20have%20become%20eligible%20to%20receive%205%20bonus%20passwords.%20%0A%0AThey%20are%20sent%20to%20your%20registered%20e-mail.";
                                    }else{
                                        $message="Your " . $bookingList['no_of_users'] . " TWISTT passwords purchased for Short Duration Subscription of 15 days- have been mailed to your registered mail Id. The passwords are also shown in your Password History Table. Passwords are to be redeemed before one year. Password used once cannot be reused again."; 
                                        /* $message="Your " . $bookingList['no_of_users'] . " passwords purchased have been mailed to your registered mail Id.\nThe passwords are required to be redeemed before one year.\nPassword  used on one device cannot be reused on other."; */
                                        $smsMessage="Your%20" . $bookingList['no_of_users'] . "%20passwords%20have%20been%20mailed%20to%20your%20registered%20mail%20Id.%0APasswords%20are%20required%20to%20be%20redeemed%20before%20one%20year.";
                                    }
                                    $notificationTitle = "Passwords purchased successfully";

                                    // promoter portal code - check if the sponsor is under a promoter -> if yes update promoter_transactions table
                                    $exists = $this->referTable()->getRefers(array("user_id"=>$bookingList['user_id']));
                                    if($exists)
                                        $promoter_id = $this->referTable()->getField(array('user_id'=>$bookingList['user_id']), array('ref_id'));
                                    if($promoter_id){
                                        $app = $this->promoterParametersTable()->getField(array('id'=>1), array('amt_per_pwd'));
                                        if($app){
                                            if($bookingList['bonus_flag'])
                                                $amt = round(($app * ($bookingList['no_of_users']-$pricingDetails["sponsor_comp_pwds"])),2);
                                            else                                    
                                                $amt = round(($app * $bookingList['no_of_users']),2);
                                            
                                            $ptData = array('promoter_id'=>$promoter_id, 'sponsor_id'=>$bookingList['user_id'], 'transaction_type'=>\Admin\Model\PromoterTransactions::transaction_due, 'transaction_date'=>date("Y-m-d"), 'amount'=>$amt, 'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s"));
                                            $ptres = $this->promoterTransactionsTable()->addPromoterTransaction($ptData);
                                        }
                                    }
                                }

                                $notificationDetails = array('notification_data_id'=>$bookingId ,'status' => \Admin\Model\Notification::STATUS_UNREAD, 'notification_recevier_id' => $userId, 'notification_type' => \Admin\Model\Notification::NOTIFICATION_TYPE_BOOKING_NOTIFICATION, 'notification_text' => $message,'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s"));
                                $registrationIds = $this->fcmTable()->getDeviceIds($userId);
                                $notification=   $this->notificationTable()->saveData($notificationDetails);
                                
                                if ($registrationIds)
                                {
                                    $notification = new SendFcmNotification();
                                    $notification->sendPushNotificationToFCMSever($registrationIds, array('message' => $notificationDetails['notification_text'], 'title' => $notificationTitle, 'id' => $notificationDetails['notification_data_id'], 'type' => $notificationDetails['notification_type']));
                                }
                                //$counter=1;
                                $this->sendPasswordSms($bookingList['mobile_country_code'].$bookingList['mobile'],$smsMessage);//array('text'=>$smsMessage));
                                if($bookingList['bonus_flag'])
                                    $this->userTable()->updateUser(array('bonus_flag'=>false),array('user_id'=>$userId));
                            }
                            $refDetails = $this->referTable()->getRefers(array('user_id'=>$userId));
                            return new JsonModel(array('success'=>true,'message'=>'success','user'=> array(
                                'id'=>$bookingList['user_id'],  // 'id'=>$bookingList['user_id'],
                                'name'=>$bookingList['user_name'],
                                'email'=>$bookingList['email'],
                                'resState'=>$bookingList['res_state'],
                                'address'=>$bookingList['address'],
                                'type'=>$bookingList['status'],
                                'mobile_country_code'=>$bookingList['mobile_country_code'],
                                'mobile'=>$bookingList['mobile'],
                                'ref_name'=>$refDetails[0]['ref_by'],
                                'ref_mobile'=>$refDetails[0]['ref_mobile'],
                                'company_name'=>$bookingList['company_role'],
                                'role'=>$bookingList['role'],
                                'subscription_end_date'=>$bookingList['subs_end_date'],
                                'sponsor_type'=>$bookingList['sponsor_type'],
                                'subscription_type'=>$bookingList['subscription_type']
                                )
                            ));
                        }
                    }
                }
            }
            return new JsonModel(array('success'=>false,'message'=>'false'));
        }
        catch (\Exception $e)
        {
            return new JsonModel(array('success'=>false,'message'=>$e->getMessage()));
        }
    }

    public function paymentGateWayResponseAction_13092021()
    {
        try{
            $details=$this->getRequest()->getPost();
            // print_r($details);
            if(!isset($details['error'])) 
            {
                $attributes = array("razorpay_signature" => $_REQUEST['razorpay_signature'], "razorpay_payment_id" => $_REQUEST['razorpay_payment_id'], "razorpay_order_id" => $_REQUEST['razorpay_order_id']);
                if(count($details) && count($attributes))
                {
                    if(isset($details['razorpay_order_id']) && $details['razorpay_payment_id'])
                    {
                        $checkPayment = $this->paymentTable()->getPayment(array('payment_request_id' => $details['razorpay_order_id']));

                        if(count($checkPayment))
                        {
                            if($checkPayment[0]['status']==\Admin\Model\Payments::payment_success)
                            {
                                return new JsonModel(array('success'=>true,'message'=>'Payment Done'));
                            }   
                            $status = \Admin\Model\Payments::payment_success;
                            $updateData = array('status' => $status, 'payment_response_id' => $details['razorpay_payment_id']);
                            $updateBookingData = array('payment_status' => $status);
                            $paymentRequest = $this->paymentTable()->updatePayment($updateData, array('payment_request_id' => $details['razorpay_order_id']));
                            if($paymentRequest) //if($status==\Admin\Model\Payments::payment_success)
                            {
                                $bookingId=$checkPayment[0]['booking_id'];
                                $paymentRequest = $this->bookingsTable()->updateBooking($updateBookingData, array('booking_id' => $bookingId));
                                //$getUserDetails = array();
                                $bookingList=$this->bookingsTable()->bookingsDetailsEmail($bookingId);
                                if(count($bookingList)==0){
                                    $userId=$this->bookingsTable()->getField(array('booking_id'=>$bookingId), 'user_id');
                                    $udata=$this->userTable()->getFields(array('user_id'=>$userId), array('role','subscription_count'));
                                    $pdata['bonus_flag'] = false;
                                    $userType=$udata['role'];
                                    $scount=$udata['subscription_count'];                                    
                                    $bookingDetails=$this->bookingsTable()->bookingDetails(array('user_id'=>$userId, 'booking_id'=>$bookingId));
                                    $bookingType = $bookingDetails['booking_type'];
                                    if($bookingType == \Admin\Model\Bookings::booking_Subscription)
                                    {
                                        $pricingDetails = $this->getEffectivePricingDetails($userId);
                                        $validityPeriod = $pricingDetails['subscription_validity'];
                                    }                                        
                                    elseif($bookingType == \Admin\Model\Bookings::booking_Buy_Passwords)
                                    {
                                        $pricingDetails = $this->getPasswordPricingDetails($userId);
                                        $validityPeriod = $pricingDetails["sponsor_pwd_validity"];
                                    }
                                    //if($userType == \Admin\Model\User::Individual_role){
                                    if($bookingType == \Admin\Model\Bookings::booking_Subscription){
                                        $where=array("user_id"=>$userId);
                                        $update=array("subscription_count"=>$scount+1,
                                        "subscription_type"=>$bookingType,"subscription_start_date"=>date("Y-m-d"), "subscription_end_date"=>date('Y-m-d', strtotime(date("y-m-d") . " +  $validityPeriod days")));
                                        
                                        if($userType == \Admin\Model\User::Individual_role)
                                            $update["role"] = \Admin\Model\User::Subscriber_role;
                                        $this->userTable()->updateUser($update,$where);
                                    }
                                    // if subscriber/sponsor - start
                                    $userDetails=$this->userTable()->getFields(array('user_id'=>$userId),array('user_id','user_name','email','mobile','mobile_country_code','role','subscription_count','subscription_start_date','subscription_end_date','res_state','address','status','company_role','bonus_flag', 'discount_percentage','passwords_count'. 'sponsor_type'));                                    
                                    $bookingTourDetails=$this->bookingtourdetailsTable()->getFields(array('booking_id'=>$bookingId),  array('discount_percentage','tour_date','no_of_days','no_of_users','expiry_date','sponsered_users','price','booking_tour_id', 'created_at'));
                                    $pdata['bonus_flag'] = false;
                                    if($bookingType == \Admin\Model\Bookings::booking_Buy_Passwords){
                                        /* if($userDetails['passwords_count'] < $pricingDetails["sponsor_bonus_min"] && $userDetails['passwords_count'] + $bookingTourDetails['no_of_users'] >= $pricingDetails["sponsor_bonus_min"]) */
                                        if($userDetails['passwords_count']<10 && $userDetails['passwords_count'] + $bookingTourDetails['no_of_users'] >= 10)
                                        {
                                            $pdata['bonus_flag'] = true;
                                            $data['passwords_count'] = $userDetails['passwords_count'] + $bookingTourDetails['no_of_users'];
                                            $data['bonus_flag'] = 1;
                                            $pcUpdate=$this->userTable()->updateUser($data,array('user_id'=>$userId));
                                        }else{
                                            $data['passwords_count'] = $userDetails['passwords_count'] + $bookingTourDetails['no_of_users'];
                                            $data['bonus_flag'] = 0;
                                            $pcUpdate=$this->userTable()->updateUser($data,array('user_id'=>$userId));
                                        }
                                    }
                                    
                                    if($pdata['bonus_flag'])
                                        $totalOriPrice = round(($pricingDetails['oriprice'] * ($bookingTourDetails['no_of_users']-$pricingDetails["sponsor_comp_pwds"])),2);
                                    else                                    
                                        $totalOriPrice = round(($pricingDetails['oriprice'] * $bookingTourDetails['no_of_users']),2);

                                    $bookingList = $bookingTourDetails;
                                    $bookingList['user_type'] = $userType;
                                    $GSTD = 1 + ($pricingDetails['GST']/100);
                                    $bookingList['tax'] = ($bookingTourDetails['price']?($bookingTourDetails['price']-($bookingTourDetails['price']/$GSTD)):0);
                                    $bookingList['discount_percentage'] = ($bookingTourDetails['discount_percentage']==""?0:$bookingTourDetails['discount_percentage']);
                                    $bookingList['discount_price'] = $bookingTourDetails['price'];
                                    $bookingList['booking_id'] = $bookingId;
                                    $bookingList['subs_start_date'] = $userDetails['subscription_start_date'];
                                    $bookingList['subs_end_date'] = $userDetails['subscription_end_date'];
                                    //$bookingList['tour_type'] = \Admin\Model\PlacePrices::tour_type_All_tour;
                                    $bookingList['booking_type'] = $bookingType;
                                    $bookingList['user_name'] = $userDetails['user_name'];
                                    $bookingList['mobile'] = $userDetails['mobile'];
                                    $bookingList['mobile_country_code'] = $userDetails['mobile_country_code'];
                                    $bookingList['email'] = $userDetails['email'];
                                    $bookingList['res_state'] = $userDetails['res_state'];
                                    $bookingList['address'] = $userDetails['address'];
                                    $bookingList['status'] = $userDetails['status'];
                                    $bookingList['company_role'] = $userDetails['company_role'];
                                    $bookingList['role'] = $userDetails['role'];
                                    $bookingList['user_type']=$userDetails['role'];
                                    $bookingList['passwords_count'] = $userDetails['passwords_count'];
                                    $bookingList['user_id'] = $userId;
                                    $bookingList['annual_price'] = $pricingDetails['annual_price'];
                                    $bookingList['plantype'] = $pricingDetails['plantype'];
                                    $bookingList['start_date'] = $pricingDetails['start_date'];
                                    $bookingList['end_date'] = $pricingDetails['end_date'];
                                    $bookingList['sponsor_bonus_min'] = $pricingDetails['sponsor_bonus_min'];
                                    //$bookingList['price'] = $pricingDetails['price'];
                                    $bookingList['offerPrice'] = $pricingDetails['oriprice'];
                                    $bookingList['oriprice'] = $totalOriPrice; //  * $GSTD;
                                    $bookingList['bonus_flag'] = $pdata['bonus_flag']; //$userDetails['bonus_flag'];
                                    $bookingList['subscription_count'] = $userDetails['subscription_count'];
                                    if($userDetails['email'])
                                    {
                                        $stream_opts = [
                                            "ssl" => [
                                                "verify_peer"=>false,
                                                "verify_peer_name"=>false,
                                            ]
                                        ]; 
                                        
                                        if($bookingList['booking_type']==\Admin\Model\Bookings::booking_Subscription){
                                            $bookingList['heading']  ="Welcome as STT Subscriber";
                                            $this->mailSTTUserNoAttachment($userDetails['email'], $bookingList['heading'] , 'mail-stt-user', $bookingList);
                                        }
                                        elseif($bookingList['booking_type']==\Admin\Model\Bookings::booking_Buy_Passwords)
                                        {
                                            $bookingList['passwords']=$this->bookingsTable()->bookingPasswords($bookingId);
                                            //$html = file_get_contents($this->getBaseUrl() . '/application/booking-pdf?suid=0&bid=' . $bookingId, false, stream_context_create($stream_opts));// added by Manjary - end -- remove on live
                                            $html = file_get_contents($this->getBaseUrl() . '/application/booking-pdf?suid=0&bid=' . $bookingId, true);// - removed by Manjary to make local work - use on live
                                            $mpdf = new mPDF(['tempDir' => getcwd()."/public/data/temp"]);
                                            $mpdf->SetDisplayMode("fullpage");
                                            $mpdf->WriteHTML($html);
                                            $mpdf->Output(getcwd()."/public/data/susri_booking_".$bookingId.".pdf", "F"); 
                                            $bookingList['heading'] = "STT Passwords Purchase Details";
                                            $this->emailSTTUserWithAttachment($userDetails['email'], $bookingList['heading'], 'mail-stt-user', $bookingList,getcwd()."/public/data/susri_booking_".$bookingId.".pdf"); 
                                        }
                                        //$this->sendbookingDetails($email, $subject, 'mail-booking-details', $bookingList,getcwd()."/public/data/susri_booking_".$bookingId.".pdf");
                                    }
                                    // if subscriber/sponsor -  end
                                }
                                else
                                {
                                    $userId=$this->bookingsTable()->getField(array('booking_id'=>$bookingId), 'user_id');
                                    $udata=$this->userTable()->getFields(array('user_id'=>$userId), array('role','subscription_count'));
                                    $userType=$udata['role'];
                                    $scount=$udata['subscription_count'];                                    
                                    $bookingDetails=$this->bookingsTable()->bookingDetails(array('user_id'=>$userId, 'booking_id'=>$bookingId));
                                    $bookingType = $bookingDetails['booking_type'];

                                    if($bookingType == \Admin\Model\Bookings::booking_Subscription)
                                    {
                                        $pricingDetails = $this->getEffectivePricingDetails($userId);
                                        $validityPeriod = $pricingDetails['subscription_validity'];
                                    }                                        
                                    elseif($bookingType == \Admin\Model\Bookings::booking_Buy_Passwords)
                                    {
                                        $pricingDetails = $this->getPasswordPricingDetails($userId);
                                        $validityPeriod = $pricingDetails["sponsor_pwd_validity"];
                                    }
                                    //if($userType == \Admin\Model\User::Individual_role){
                                    if($bookingType == \Admin\Model\Bookings::booking_Subscription){
                                        $where=array("user_id"=>$userId);
                                        $update=array("subscription_count"=>$scount+1,"subscription_type"=>$bookingType,"subscription_start_date"=>date("Y-m-d"), "subscription_end_date"=>date('Y-m-d', strtotime(date("y-m-d") . " +  $validityPeriod days")));
                                        if($userType == \Admin\Model\User::Individual_role)
                                            $update["role"] = \Admin\Model\User::Subscriber_role;
                                        $this->userTable()->updateUser($update,$where);
                                    }
                                    $userDetails=$this->userTable()->getFields(array('user_id'=>$bookingList['user_id']),array('user_id','user_name','email','mobile','mobile_country_code','role', 'subscription_count', 'subscription_start_date','subscription_end_date','res_state','address','status','company_role','bonus_flag', 'discount_percentage','passwords_count')); // -- modified by Manjary
                                    $bookingTourDetails=$this->bookingtourdetailsTable()->getFields(array('booking_id'=>$bookingId),  array('discount_percentage','tour_date','no_of_days','no_of_users','expiry_date','sponsered_users','price','booking_tour_id', 'created_at'));
                                    $pdata['bonus_flag'] = false;
                                    if($bookingType == \Admin\Model\Bookings::booking_Buy_Passwords){
                                        /* if($userDetails['passwords_count'] < $pricingDetails["sponsor_bonus_min"] && $userDetails['passwords_count'] + $bookingTourDetails['no_of_users'] >= $pricingDetails["sponsor_bonus_min"]) */
                                        if($userDetails['passwords_count']<10 && $userDetails['passwords_count'] + $bookingTourDetails['no_of_users'] >= 10)
                                        {
                                            $pdata['bonus_flag'] = true;
                                            $data['passwords_count'] = $userDetails['passwords_count'] + $bookingTourDetails['no_of_users'];
                                            $pcUpdate=$this->userTable()->updateUser($data,array('user_id'=>$userId));
                                        }
                                        else{
                                            $data['passwords_count'] = $userDetails['passwords_count'] + $bookingTourDetails['no_of_users'];
                                            $pcUpdate=$this->userTable()->updateUser($data,array('user_id'=>$userId));
                                        }
                                    }
                                    //$bookingDetails=$this->bookingsTable()->bookingDetails(array('user_id'=>$userId, 'booking_id'=>$bookingId));
                                    //$bookingType = $bookingDetails['booking_type'];
                                    $bookingList['booking_type'] = $bookingType;
                                    //$pricingDetails = $this->getEffectivePricingDetails($userId);
                                    if($pdata['bonus_flag'])
                                        $totalOriPrice = round(($pricingDetails['oriprice'] * ($bookingTourDetails['no_of_users']-$pricingDetails["sponsor_comp_pwds"])),2);
                                    else                                    
                                        $totalOriPrice = round(($pricingDetails['oriprice'] * $bookingTourDetails['no_of_users']),2);

                                    $GSTD = 1 + ($pricingDetails['GST']/100);
                                    $bookingList['tax'] = ($bookingTourDetails['price']?($bookingTourDetails['price']-($bookingTourDetails['price']/$GSTD)):0);
                                    $bookingList['booking_id'] = $bookingId;
                                    $bookingList['subs_start_date'] = $userDetails['subscription_start_date'];
                                    $bookingList['subs_end_date'] = $userDetails['subscription_end_date'];
                                    $bookingList['user_name'] = $userDetails['user_name'];
                                    $bookingList['mobile'] = $userDetails['mobile'];
                                    $bookingList['mobile_country_code'] = $userDetails['mobile_country_code'];
                                    $bookingList['email'] = $userDetails['email'];
                                    $bookingList['res_state'] = $userDetails['res_state'];
                                    $bookingList['address'] = $userDetails['address'];
                                    $bookingList['status'] = $userDetails['status'];
                                    $bookingList['company_role'] = $userDetails['company_role'];
                                    $bookingList['role'] = $userDetails['role'];
                                    $bookingList['user_type']=$userDetails['role'];
                                    $bookingList['passwords_count'] = $userDetails['passwords_count'];
                                    $bookingList['user_id'] = $userId;
                                    $bookingList['annual_price'] = $pricingDetails['annual_price'];
                                    $bookingList['plantype'] = $pricingDetails['plantype'];
                                    $bookingList['start_date'] = $pricingDetails['start_date'];
                                    $bookingList['end_date'] = $pricingDetails['end_date'];
                                    $bookingList['sponsor_bonus_min'] = $pricingDetails['sponsor_bonus_min'];
                                    //$bookingList['price'] = $pricingDetails['price'];
                                    $bookingList['offerPrice'] = $pricingDetails['oriprice'];
                                    $bookingList['oriprice'] = $totalOriPrice; // * $GSTD;
                                    $bookingList['bonus_flag'] = $pdata['bonus_flag']; //$userDetails['bonus_flag'];
                                    $bookingList['subscription_count'] = $userDetails['subscription_count'];
                                    $bookingList['sponsor_type'] = $userDetails['sponsor_type'];
                                    if($userDetails['email'])
                                    {
                                        // added by Manjary - start - remove on live - start
                                        /* $stream_opts = [
                                            "ssl" => [
                                                "verify_peer"=>false,
                                                "verify_peer_name"=>false,
                                            ]
                                        ];  */
                                        // added by Manjary - end -- remove on live
                                        if($bookingList['booking_type']==\Admin\Model\Bookings::booking_Subscription){
                                            $bookingList['heading'] = "Welcome as STT Subscriber";
                                            $this->mailSTTUserNoAttachment($userDetails['email'], $bookingList['heading'], 'mail-stt-user', $bookingList);
                                        }
                                        elseif($bookingList['booking_type']==\Admin\Model\Bookings::booking_Buy_Passwords)
                                        {
                                            $bookingList['passwords']=$this->bookingsTable()->bookingPasswords($bookingId);
                                            //$html = file_get_contents($this->getBaseUrl() . '/application/booking-pdf?bid=' . $bookingId, false, stream_context_create($stream_opts)); 
                                            
                                            $html = file_get_contents($this->getBaseUrl() . '/application/booking-pdf?suid=0&bid=' . $bookingId, true);// - removed by Manjary to make local work - use on live
                                            $mpdf = new mPDF(['tempDir' => getcwd()."/public/data/temp"]);
                                            $mpdf->SetDisplayMode("fullpage");
                                            $mpdf->WriteHTML($html);
                                            $mpdf->Output(getcwd()."/public/data/susri_booking_".$bookingId.".pdf", "F"); // removed by Manjary
                                            $bookingList['heading'] =\Admin\Model\PlacePrices::tour_type[$bookingList['tour_type']] ." Booking Details ";
                                            $userId=$bookingList['user_id'];
                                            //$this->sendbookingDetails($email, $subject, 'mail-booking-details', $bookingList,getcwd()."/public/data/susri_booking_".$bookingId.".pdf");
                                            $this->emailSTTUserWithAttachment($userDetails['email'], $bookingList['heading'], 'mail-stt-user', $bookingList,getcwd()."/public/data/susri_booking_".$bookingId.".pdf"); 
                                        }
                                    }
                                }
                                $message = "";
                                $notificationTitle = "";
                                $smsMessage = "";
                                if($bookingType == \Admin\Model\Bookings::booking_Subscription){
                                    if($bookingList['subscription_count'] == "1"){
                                        if($bookingList['mobile_country_code'] == "91"){
                                            $message="Congratulations on your choice of becoming “QUESTT” Subscriber of STT.\nWelcome to Susri Tour Tales.\nSelect, Download and Listen to the Tales about the Tourist places in the language you prefer.\nEnjoy your time with STT.\n\n***\n\nYou can also help others to become “TWISTT” Subscribers- for Short Duration of 15 days. For details, see the message  sent to your registered mail Id or the Web Site.";
                                        } else {
                                            $message="Congratulations on your choice of becoming “QUESTT” Subscriber of STT.\nWelcome to Susri Tour Tales.\nSelect, Download and Listen to the Tales about the Tourist places in the language you prefer.\nEnjoy your time with STT.";
                                        }
                                        $notificationTitle = "Welcome as Subscriber";
                                        $smsMessage="Welcome%20to%20Susri%20Tour%20Tales.%0ADownload%20and%20Listen%20to%20the%20tales%20of%20your%20choice.%0A%0ABecome%20a%20sponsor%20and%20earn%20through%20App.%0ASee%20e-mail%20message%20for%20details.";
                                    }else{
                                        /* $message="Thank you for your association with STT.\nWish you a happy time with STT."; */
                                        $message = "Thank you for your timely renewal of STT. We wish you happy tours with STT.";
                                        $notificationTitle = "Renewal sucsessful";
                                        $smsMessage="Thank%20you%20for%20your%20continued%20association%20with%20STT.%20%0AWish%20you%20one%20more%20year%20of%20awareness%20and%20enjoyment.";
                                    }
                                }
                                elseif($bookingType == \Admin\Model\Bookings::booking_Buy_Passwords){
                                    if($bookingList['bonus_flag']){
                                        $message ="Congratulations.\nAs you have completed the purchase of FIRST 10 TWISTT passwords, you are eligible to receive 5 bonus passwords. They are sent to your registered e-mail and to your Password History Table";
                                        /* $message = "Congratulations.\nAs you have completed the purchase of first 10 passwords, you are eligible to receive 5 bonus passwords. They are sent to your registered e-mail."; */
                                        $smsMessage = "Congratulations.%0ABy%20purchasing%20ten%20passwords%2C%20you%20have%20become%20eligible%20to%20receive%205%20bonus%20passwords.%20%0A%0AThey%20are%20sent%20to%20your%20registered%20e-mail.";
                                    }else{
                                        $message="Your " . $bookingList['no_of_users'] . " TWISTT passwords purchased for Short Duration Subscription of 15 days- have been mailed to your registered mail Id. The passwords are also shown in your Password History Table. Passwords are to be redeemed before one year. Password used once cannot be reused again."; 
                                        /* $message="Your " . $bookingList['no_of_users'] . " passwords purchased have been mailed to your registered mail Id.\nThe passwords are required to be redeemed before one year.\nPassword  used on one device cannot be reused on other."; */
                                        $smsMessage="Your%20" . $bookingList['no_of_users'] . "%20passwords%20have%20been%20mailed%20to%20your%20registered%20mail%20Id.%0APasswords%20are%20required%20to%20be%20redeemed%20before%20one%20year.";
                                    }
                                    $notificationTitle = "Passwords purchased successfully";
                                }

                                $notificationDetails = array('notification_data_id'=>$bookingId ,'status' => \Admin\Model\Notification::STATUS_UNREAD, 'notification_recevier_id' => $userId, 'notification_type' => \Admin\Model\Notification::NOTIFICATION_TYPE_BOOKING_NOTIFICATION, 'notification_text' => $message,'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s"));
                                $registrationIds = $this->fcmTable()->getDeviceIds($userId);
                                $notification=   $this->notificationTable()->saveData($notificationDetails);
                                
                                
                                /* if($bookingType == \Admin\Model\Bookings::booking_Subscription){
                                    if($bookingList['subscription_count'] == "1")
                                        $smsMessage="Welcome%20to%20Susri%20Tour%20Tales.%0ADownload%20and%20Listen%20to%20the%20tales%20of%20your%20choice.%0A%0ABecome%20a%20sponsor%20and%20earn%20through%20App.%0ASee%20e-mail%20message%20for%20details.";
                                    else
                                        $smsMessage="Thank%20you%20for%20your%20continued%20association%20with%20STT.%20%0AWish%20you%20one%20more%20year%20of%20awareness%20and%20enjoyment.";
                                }
                                elseif($bookingType == \Admin\Model\Bookings::booking_Buy_Passwords){
                                    if($bookingList['bonus_flag']){
                                        $smsMessage = "Congratulations.%0ABy%20purchasing%20ten%20passwords%2C%20you%20have%20become%20eligible%20to%20receive%205%20bonus%20passwords.%20%0A%0AThey%20are%20sent%20to%20your%20registered%20e-mail.";
                                    }else{
                                        $smsMessage="Your%20" . $bookingList['no_of_users'] . "%20passwords%20have%20been%20mailed%20to%20your%20registered%20mail%20Id.%0APasswords%20are%20required%20to%20be%20redeemed%20before%20one%20year.";
                                    }
                                } */
                                if ($registrationIds)
                                {
                                    $notification = new SendFcmNotification();
                                    $notification->sendPushNotificationToFCMSever($registrationIds, array('message' => $notificationDetails['notification_text'], 'title' => $notificationTitle, 'id' => $notificationDetails['notification_data_id'], 'type' => $notificationDetails['notification_type']));
                                }
                                //$counter=1;
                                $this->sendPasswordSms($bookingList['mobile_country_code'].$bookingList['mobile'],$smsMessage);//array('text'=>$smsMessage));
                                if($bookingList['bonus_flag'])
                                    $this->userTable()->updateUser(array('bonus_flag'=>false),array('user_id'=>$userId));
                            }
                            $refDetails = $this->referTable()->getRefers(array('user_id'=>$userId));
                            return new JsonModel(array('success'=>true,'message'=>'success','user'=> array(
                                'id'=>$bookingList['user_id'],  // 'id'=>$bookingList['user_id'],
                                'name'=>$bookingList['user_name'],
                                'email'=>$bookingList['email'],
                                'resState'=>$bookingList['res_state'],
                                'address'=>$bookingList['address'],
                                'type'=>$bookingList['status'],
                                'mobile_country_code'=>$bookingList['mobile_country_code'],
                                'mobile'=>$bookingList['mobile'],
                                'ref_name'=>$refDetails[0]['ref_by'],
                                'ref_mobile'=>$refDetails[0]['ref_mobile'],
                                'company_name'=>$bookingList['company_role'],
                                'role'=>$bookingList['role'],
                                'subscription_end_date'=>$bookingList['subs_end_date'],
                                'sponsor_type'=>$bookingList['sponsor_type']
                                )
                            ));
                        }
                    }
                }
            }
            return new JsonModel(array('success'=>false,'message'=>'false'));
        }
        catch (\Exception $e)
        {
            return new JsonModel(array('success'=>false,'message'=>$e->getMessage()));
        }
    }

    public function paymentProcessAction()
    {
        $paramId = $this->params()->fromRoute('id', '');
              if($paramId) {
                  $paymentRequestId = $paramId;
                  $checkPayment = $this->paymentTable()->getPayment(array('payment_request_id' => $paymentRequestId));
                  $bookingId = $checkPayment[0]['booking_id'];

                  $bookingList = $this->bookingsTable()->bookingsDetailsEmail($bookingId);

                  $userDetails = $this->userTable()->getFields(array('user_id' => $bookingList['user_id']), array('email', 'mobile', 'mobile_country_code','role','user_name'));
                  $mobile = $userDetails['mobile'];
                  $mobileCountry = $userDetails['mobile_country_code'];
                  $userType = $userDetails['role'];
                  if($userType==\Admin\Model\User::Tour_Operator_role)
                  {
                      $tourOperatorDetails=$this->userTable()->getUserDetails($bookingList['user_id']);
                      $userName = $tourOperatorDetails['contact_person'];
                      $email = $tourOperatorDetails['email_id'];
                  }else if($userType==\Admin\Model\User::Tour_coordinator_role)
                  {
                      $tourCoordinatorDetails=$this->userTable()->getUserDetails($bookingList['user_id']);

                      $userName=$tourCoordinatorDetails['user_name'];
                      $email=$tourCoordinatorDetails['email'];
                  }else
                  {
                      $userName=$userDetails['user_name'];
                      $email=$userDetails['email'];
                  }
                  $view= new ViewModel(array('razorpayOrderId' => $paymentRequestId, 'userDetails' => array('email' => $email, 'mobile' => $mobile), 'url' => $this->getBaseUrl()));
                  $view->setTerminal(true);
                  return $view;
              }
    }
    public function paymentProcessDetailsAction()
    {
        $paramId = $this->params()->fromRoute('id', '');
        if($paramId) {
            $paymentRequestId = $paramId;
            $checkPayment = $this->paymentTable()->getPayment(array('payment_request_id' => $paymentRequestId));
            $bookingId = $checkPayment[0]['booking_id'];

            $bookingList = $this->bookingsTable()->bookingsDetailsEmail($bookingId);

            $userDetails = $this->userTable()->getFields(array('user_id' => $bookingList['user_id']), array('email', 'mobile', 'mobile_country_code','role','user_name'));
            $mobile = $userDetails['mobile'];
            $mobileCountry = $userDetails['mobile_country_code'];
            $userType = $userDetails['role'];
            if($userType==\Admin\Model\User::Tour_Operator_role)
            {
                $tourOperatorDetails=$this->userTable()->getUserDetails($bookingList['user_id']);
                $userName = $tourOperatorDetails['contact_person'];
                $email = $tourOperatorDetails['email_id'];
            }else if($userType==\Admin\Model\User::Tour_coordinator_role)
            {
                $tourCoordinatorDetails=$this->userTable()->getUserDetails($bookingList['user_id']);
                $userName=$tourCoordinatorDetails['user_name'];
                $email=$tourCoordinatorDetails['email'];
            }else
            {
                $userName = $userDetails['user_name'];
                $email = $userDetails['email'];
            }

            $details=array('keyId'=>\Application\Handler\Razorpay::keyId,'razorpayOrderId' => $paymentRequestId, 'userDetails' => array('email' => $email, 'mobile' => $mobile), 'url' => $this->getBaseUrl());
            $view= new JsonModel($details);
            return $view;
        }
    }
    public function bookingStatusAction()
    {
        $details=$_REQUEST;
       // $fp = fopen(getcwd() . "/public/data/payment.txt" , 'w');
       // fwrite($fp,json_encode($details));
       // fclose($fp);

        if(count($details))
        {
            if(isset($details['payment_request_id']))
            {

                $checkPayment = $this->paymentTable()->getPayment(array('payment_request_id' => $details['payment_request_id']));
                $status=0;
                $paymentId='';
                if(count($checkPayment))
                {
                    if ($details['payment_status'] == 'Credit' && $details['payment_id']) {
                        $status=1;
                        $paymentId=$details['payment_id'];
                   }
                }
                    $view=new ViewModel(array('status'=>$status,'payment_id'=>$paymentId));
                    $view->setTerminal(true);
                       return $view;
            }
        }
    }

    public function andriodRedirectAction()
    {
        $details=$_REQUEST;
        if (isset($details['payment_status']) && $details['payment_status'] == 'Credit' && $details['payment_id'])
        {
            echo 'success';
            exit;
        }else
        {
            echo 'failure';
            exit;
        }

    }
    function isValidJSON($str) {
        json_decode($str);
        return json_last_error() == JSON_ERROR_NONE;
    }


     public function paymentCallbackAction()
     {
          try{

              $json_params = file_get_contents("php://input");
              if (strlen($json_params) > 0 && $this->isValidJSON($json_params)){
                  $details = json_decode($json_params);
              }else{
                  echo 'fail';
                  exit;
              }
            //  $fp = fopen(getcwd() . "/public/data/payment.txt" , "a+");
             // fwrite($fp,'request 1'.$json_params);
             // fwrite($fp,date("Y-m-d H:i"));
             // fclose($fp);
              if(!array_key_exists('error',$details))
              {
                  $headers = $this->getRequest()->getHeaders();
                  $logResult = $this->logRequest($this->getRequest()->toString());
                  if(!$headers->get('X-Razorpay-Signature'))
                  {
                      return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
                  }

                  $api=new Razorpay();
                  $webhookSignature= $headers->get('X-Razorpay-Signature')->getFieldValue();

                  if(!$api->checkPaymentCaptureSecret($details,$webhookSignature))
                  {
                      return new JsonModel(array('success'=>false,'message'=>'Invalid Access'));
                  }
                  if(isset($details['payload'])) {

                      if(!isset($details['payload']['payment']))
                      {
                          echo 'fail';
                      }
                      $details=$details['payload']['payment'];
                      $razorpayPaymentId = $details['id'];
                      $razorpayOrderId = $details['order_id'];
                  $attributes = array(
                      "razorpay_payment_id" => $razorpayPaymentId,
                      "razorpay_order_id" => $razorpayOrderId);
                  if(count($details) && count($attributes))
                  {
                      if(isset($details['order_id']) && $details['payment_id'])
                      {
                          $checkPayment = $this->paymentTable()->getPayment(array('payment_request_id' => $details['order_id']));
                          if(count($checkPayment)) {
                              if ($checkPayment[0]['status'] == \Admin\Model\Payments::payment_success) {
                                  return new JsonModel(array('success' => true, 'message' => 'Payment Done'));
                              }
                              $status = \Admin\Model\Payments::payment_success;
                              $updateData = array('status' => $status, 'payment_response_id' => $details['id']);
                              $updateBookingData = array('payment_status' => $status);
                              $paymentRequest = $this->paymentTable()->updatePayment($updateData, array('payment_request_id' => $details['order_id']));
                              if ($status == \Admin\Model\Payments::payment_success) {
                                  $bookingId = $checkPayment[0]['booking_id'];
                                  $paymentRequest = $this->bookingsTable()->updateBooking($updateBookingData, array('booking_id' => $bookingId));

                                  $bookingList = $this->bookingsTable()->bookingsDetailsEmail($bookingId);

                                  $userDetails = $this->userTable()->getFields(array('user_id' => $bookingList['user_id']), array('email', 'mobile', 'mobile_country_code', 'role','user_name'));
                                  $mobile = $userDetails['mobile'];
                                  $mobileCountry = $userDetails['mobile_country_code'];
                                  $userType = $userDetails['role'];
                                  if ($userType == \Admin\Model\User::Tour_Operator_role) {
                                      $tourOperatorDetails = $this->userTable()->getUserDetails($bookingList['user_id']);
                                      $email = $tourOperatorDetails['email_id'];


                                  } else if ($userType == \Admin\Model\User::Tour_coordinator_role) {
                                      $tourCoordinatorDetails = $this->userTable()->getUserDetails($bookingList['user_id']);
                                      $email = $tourCoordinatorDetails['email'];
                                  } else {
                                      $email = $userDetails['email'];
                                  }
                                  if ($email) {

                                      $html = file_get_contents($this->getBaseUrl() . '/application/booking-pdf?bid=' . $bookingId, true);
                                      $mpdf = new mPDF(['tempDir' => getcwd() . "/public/data/temp"]);
                                      $mpdf->SetDisplayMode("fullpage");
                                      /*$stylesheet = file_get_contents(getcwd() . "/public/css/bootstrap.css");*/
                                      /*$mpdf->WriteHtml($stylesheet, 1);*/
                                      $mpdf->WriteHTML($html);
                                      $mpdf->Output(getcwd() . "/public/data/susri_booking_" . $bookingId . ".pdf", "F");
                                      $subject = \Admin\Model\PlacePrices::tour_type[$bookingList['tour_type']] . " Booking Details ";
                                      $bookingList['user_type']=$userType;
                                      $this->sendbookingDetails($email, $subject, 'mail-booking-details', $bookingList, getcwd() . "/public/data/susri_booking_" . $bookingId . ".pdf");
                                      $userId = $bookingList['user_id'];
                                      $message = sprintf('Congratulations, You booked %s  on date %s and Passwords have been sent to email %s.', \Admin\Model\PlacePrices::tour_type[$bookingList['tour_type']], date("d-m-Y", strtotime($bookingList['created_at'])), $email);
                                      $tourType = $bookingList['tour_type'];
                                      $notificationDetails = array('notification_data_id' => $bookingId, 'status' => \Admin\Model\Notification::STATUS_UNREAD, 'notification_recevier_id' => $userId, 'notification_type' => \Admin\Model\Notification::NOTIFICATION_TYPE_BOOKING_NOTIFICATION, 'notification_text' => $message, 'created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s"));
                                      $registrationIds = $this->fcmTable()->getDeviceIds($userId);
                                      $notification = $this->notificationTable()->saveData($notificationDetails);
                                      if ($registrationIds) {
                                          $notification = new SendFcmNotification();
                                          $notification->sendPushNotificationToFCMSever($registrationIds, array('message' => $notificationDetails['notification_text'], 'title' => \Admin\Model\PlacePrices::tour_type[$tourType] . " Booked successfully", 'id' => $notificationDetails['notification_data_id'], 'type' => $notificationDetails['notification_type']));
                                      }

                                      $smsMessage = "Congratulations! You have booked  " . \Admin\Model\PlacePrices::tour_type[$bookingList['tour_type']] . " tales on " . date("d-m-Y", strtotime($bookingList['created_at'])) . " and Password has been sent to your " . $email;
                                      $counter = 1;
                                      /* foreach ($bookingList['passwords'] as $password)
                                       {
                                           $smsMessage .=' User '.$counter .': '.$password.",";
                                           $counter++;
                                       }*/

                                      $this->sendPasswordSms($mobileCountry . $mobile, array('text' => $smsMessage));
                                  }
                               //   header('Location:' . $this->getBaseUrl() . '/application/andriod-redirect?payment_status=Credit&payment_id=1');
                                  echo 'success';
                                  exit;
                              }

                          }
                          }


                      }
                  }
              }
              //header('Location:'.$this->getBaseUrl().'/application/andriod-redirect');

              echo 'failure';
              exit;
          }catch (\Exception $e)
          {
              //header('Location:'.$this->getBaseUrl().'/application/andriod-redirect');
              echo 'failure';
              exit;
          }
     }
    
    /* public function paymentGateWayResponseAction_old()
    {
        try{
            $details=$this->getRequest()->getPost();

            if(!isset($details['error'])) //if(!array_key_exists('error',$details)) - modified by Manjary to remove deprecated function
            {
                $attributes = array("razorpay_signature" => $_REQUEST['razorpay_signature'], "razorpay_payment_id" => $_REQUEST['razorpay_payment_id'], "razorpay_order_id" => $_REQUEST['razorpay_order_id']);
                if(count($details) && count($attributes))
                {
                    if(isset($details['razorpay_order_id']) && $details['razorpay_payment_id'])
                    {
                        $checkPayment = $this->paymentTable()->getPayment(array('payment_request_id' => $details['razorpay_order_id']));

                        if(count($checkPayment))
                        {
                            //  if($checkPayment[0]['status']==\Admin\Model\Payments::payment_success)
                            // {
                            //     return new JsonModel(array('success'=>true,'message'=>'Payment Done'));
                            // }    -- uncomment after testing  
                            $status = \Admin\Model\Payments::payment_success;
                            $updateData = array('status' => $status, 'payment_response_id' => $details['razorpay_payment_id']);
                            $updateBookingData = array('payment_status' => $status);
                            $paymentRequest = $this->paymentTable()->updatePayment($updateData, array('payment_request_id' => $details['razorpay_order_id']));
                            if($status==\Admin\Model\Payments::payment_success)
                            {
                                $bookingId=$checkPayment[0]['booking_id'];
                                $paymentRequest = $this->bookingsTable()->updateBooking($updateBookingData, array('booking_id' => $bookingId));
                                $getUserDetails = array();
                                $bookingList=$this->bookingsTable()->bookingsDetailsEmail($bookingId);
                                if(count($bookingList)==0){
                                    $userId=$this->bookingsTable()->getField(array('booking_id'=>$bookingId), 'user_id');
                                    $userType=$this->userTable()->getField(array('user_id'=>$userId), 'role');
                                    if($userType == \Admin\Model\User::Individual_role){
                                        $where=array("user_id"=>$userId);
                                        $update=array("role"=>\Admin\Model\User::Subscriber_role, "subscription_start_date"=>date("Y-m-d"), "subscription_end_date"=>date('Y-m-d', strtotime(date("y-m-d") . " +  1 year")));
                                        $this->userTable()->updateUser($update,$where);
                                    }
                                    // if subscriber/sponsor - start
                                    $userDetails=$this->userTable()->getFields(array('user_id'=>$userId),array('user_id','user_name','email','mobile','mobile_country_code','role','subscription_start_date','subscription_end_date','res_state','status','company_role'));
                                    $pricingDetails = $this->getEffectivePricingDetails($userId);
                                    $bookingDetails=$this->bookingsTable()->bookingDetails(array('user_id'=>$userId, 'booking_id'=>$bookingId));
                                    $bookingType = $bookingDetails['booking_type'];
                                    $bookingTourDetails=$this->bookingtourdetailsTable()->getFields(array('booking_id'=>$bookingId),  array('discount_percentage','tour_date','no_of_days','no_of_users','expiry_date','sponsered_users','price','booking_tour_id', 'created_at'));
                                    $userName=$userDetails['user_name'];
                                    $mobile=$userDetails['mobile'];
                                    $mobileCountry=$userDetails['mobile_country_code'];
                                    $userType = $userDetails['role'];
                                    $email=$userDetails['email'];
                                    $subStartDate = $userDetails['subscription_start_date'];
                                    $subEndDate = $userDetails['subscription_end_date'];
                                    $getUserDetails = $userDetails;
                                    $totalOriPrice = round(($pricingDetails['oriprice'] * $bookingTourDetails['no_of_users']),2);

                                    $bookingList = $bookingTourDetails;
                                    $bookingList['user_type'] = $userType;
                                    $bookingList['tax'] = ($bookingTourDetails['price']?($bookingTourDetails['price']-($bookingTourDetails['price']/1.18)):0);
                                    $bookingList['discount_price'] = ($bookingTourDetails['discount_percentage']==""?0:$bookingTourDetails['discount_percentage']);
                                    $bookingList['booking_id'] = $bookingId;
                                    $bookingList['subs_start_date'] = $subStartDate;
                                    $bookingList['subs_end_date'] = $subEndDate;
                                    $bookingList['tour_type'] = \Admin\Model\PlacePrices::tour_type_All_tour;
                                    $bookingList['booking_type'] = $bookingType;
                                    $bookingList['user_name'] = $userName;
                                    $bookingList['user_id'] = $userId;
                                    $bookingList['passwords']=$this->bookingsTable()->bookingPasswords($bookingId);
                                    $bookingList['oriprice'] = $totalOriPrice;
                                    $bookingList['annual_price'] = $pricingDetails['annual_price'];
                                    if($email)
                                    {
                                        $stream_opts = [
                                            "ssl" => [
                                                "verify_peer"=>false,
                                                "verify_peer_name"=>false,
                                            ]
                                        ]; 
                                        $html = file_get_contents($this->getBaseUrl() . '/application/booking-pdf?suid=0&bid=' . $bookingId, false, stream_context_create($stream_opts));// added by Manjary - end -- remove on live
                                        //$html = file_get_contents($this->getBaseUrl() . '/application/booking-pdf?bid=' . $bookingId, true);// - removed by Manjary to make local work - use on live
                                        $mpdf = new mPDF(['tempDir' => getcwd()."/public/data/temp"]);
                                        $mpdf->SetDisplayMode("fullpage");
                                        $mpdf->WriteHTML($html);
                                        $mpdf->Output(getcwd()."/public/data/susri_booking_".$bookingId.".pdf", "F"); // removed by Manjary
                                        $subject ="STT Booking Details";
                                        if($bookingList['booking_type']==\Admin\Model\Bookings::booking_Subscription){
                                            $this->mailSTTnoAttachments($email, $subject, 'mail-stt-user', $bookingList);
                                        }
                                        elseif($bookingList['booking_type']==\Admin\Model\Bookings::booking_Buy_Passwords)
                                        {
                                            $this->emailSTTUser($email, $subject, 'mail-stt-user', $bookingList,getcwd()."/public/data/susri_booking_".$bookingId.".pdf"); 
                                        }
                                        //$this->sendbookingDetails($email, $subject, 'mail-booking-details', $bookingList,getcwd()."/public/data/susri_booking_".$bookingId.".pdf");
                                    }
                                    // if subscriber/sponsor -  end
                                }
                                else
                                {
                                    $userId=$this->bookingsTable()->getField(array('booking_id'=>$bookingId), 'user_id');
                                    $userType=$this->userTable()->getField(array('user_id'=>$userId), 'role');
                                    if($userType == \Admin\Model\User::Individual_role){
                                        $where=array("user_id"=>$bookingList['user_id']);
                                        $update=array("role"=>\Admin\Model\User::Subscriber_role);
                                        $this->userTable()->updateUser($update,$where);
                                    }
                                    $userDetails=$this->userTable()->getFields(array('user_id'=>$bookingList['user_id']),array('user_id','user_name','email','mobile','mobile_country_code','role','subscription_start_date','subscription_end_date','res_state','status','company_role')); // -- modified by Manjary
                                    $bookingDetails=$this->bookingsTable()->bookingDetails(array('user_id'=>$userId, 'booking_id'=>$bookingId));
                                    $bookingType = $bookingDetails['booking_type'];
                                    $mobile=$userDetails['mobile'];
                                    $mobileCountry=$userDetails['mobile_country_code'];
                                    $userType = $userDetails['role'];
                                    $subStartDate = $userDetails['subscription_start_date'];
                                    $subEndDate = $userDetails['subscription_end_date'];
                                    $getUserDetails = $userDetails;  
                                    $bookingList['booking_type'] = $bookingType;
                                    $pricingDetails = $this->getEffectivePricingDetails($userId);
                                    $totalOriPrice = round(($pricingDetails['oriprice'] * $bookingList['no_of_users']),2);
                                    $bookingList['oriprice'] = $totalOriPrice;
                                    $bookingList['annual_price'] = $pricingDetails['annual_price'];
                                    $email=$userDetails['email'];
                                    if($email)
                                    {
                                        // added by Manjary - start - remove on live - start
                                        $stream_opts = [
                                            "ssl" => [
                                                "verify_peer"=>false,
                                                "verify_peer_name"=>false,
                                            ]
                                        ]; 
                                        $html = file_get_contents($this->getBaseUrl() . '/application/booking-pdf?bid=' . $bookingId, false, stream_context_create($stream_opts)); 
                                        // added by Manjary - end -- remove on live

                                        //$html = file_get_contents($this->getBaseUrl() . '/application/booking-pdf?bid=' . $bookingId, true);// - removed by Manjary to make local work - use on live
                                        $mpdf = new mPDF(['tempDir' => getcwd()."/public/data/temp"]);
                                        $mpdf->SetDisplayMode("fullpage");
                                        $mpdf->WriteHTML($html);
                                        $mpdf->Output(getcwd()."/public/data/susri_booking_".$bookingId.".pdf", "F"); // removed by Manjary
                                        $subject =\Admin\Model\PlacePrices::tour_type[$bookingList['tour_type']] ." Booking Details ";
                                        $bookingList['user_type']=$userType;
                                        $this->sendbookingDetails($email, $subject, 'mail-booking-details', $bookingList,getcwd()."/public/data/susri_booking_".$bookingId.".pdf");  
                                        $userId=$bookingList['user_id'];
                                    }
                                }
                                $message = "";
                                if($bookingType == \Admin\Model\Bookings::booking_Subscription){
                                    $message="Congratulations on your choice of subscribing to STT.\nWelcome to Susri Tour Tales.\nSelect, Download and Listen to the tales about the tourist places  of your choice.\nEnjoy your time with STT.\n\nYou can also become a sponsor. For details, see the message  sent to your registered mail Id.";
                                }
                                elseif($bookingType == \Admin\Model\Bookings::booking_Buy_Passwords){
                                    $message="Your " . $bookingList['no_of_users'] . " passwords purchased have been mailed to your registered mail Id.\nThe passwords are required to be redeemed before one year.\nPassword  used on one device cannot be reused on other.";
                                }

                                $notificationDetails = array('notification_data_id'=>$bookingId ,'status' => \Admin\Model\Notification::STATUS_UNREAD, 'notification_recevier_id' => $userId, 'notification_type' => \Admin\Model\Notification::NOTIFICATION_TYPE_BOOKING_NOTIFICATION, 'notification_text' => $message,'created_at'=>date("Y-m-d H:i:s"),'updated_at'=>date("Y-m-d H:i:s"));
                                $registrationIds = $this->fcmTable()->getDeviceIds($userId);
                                $notification=   $this->notificationTable()->saveData($notificationDetails);
                                $smsMessage = "";
                                $notificationTitle = "";
                                if($bookingType == \Admin\Model\Bookings::booking_Subscription){
                                    $smsMessage="Congratulations on your choice of subscribing to STT.\nWelcome to Susri Tour Tales.\nSelect, Download and Listen to the tales about the tourist places  of your choice.\nEnjoy your time with STT.\n\nYou can also become a sponsor. For details, see the message  sent to your registered mail Id.";
                                    $notificationTitle = "Welcome as Subscriber";
                                }
                                elseif($bookingType == \Admin\Model\Bookings::booking_Buy_Passwords){
                                    $smsMessage="Your " . $bookingList['no_of_users'] . " passwords purchased have been mailed to your registered mail Id.\nThe passwords are required to be redeemed before one year.\nPassword  used on one device cannot be reused on other.";
                                    $notificationTitle = "Passwords purchased successfully";
                                }
                                if ($registrationIds)
                                {
                                    $notification = new SendFcmNotification();
                                    $notification->sendPushNotificationToFCMSever($registrationIds, array('message' => $notificationDetails['notification_text'], 'title' => $notificationTitle, 'id' => $notificationDetails['notification_data_id'], 'type' => $notificationDetails['notification_type']));
                                }
                                $counter=1;
                                $this->sendPasswordSms($mobileCountry.$mobile,array('text'=>$smsMessage));
                            }
                            return new JsonModel(array('success'=>true,'message'=>'success','user'=> array(
                                'id'=>$getUserDetails['user_id'],  // 'id'=>$bookingList['user_id'],
                                'name'=>$getUserDetails['user_name'],
                                'email'=>$getUserDetails['email'],
                                'resState'=>$getUserDetails['res_state'],
                                'type'=>$getUserDetails['status'],
                                'mobile_country_code'=>$getUserDetails['mobile_country_code'],
                                'mobile'=>$getUserDetails['mobile'],
                                'company_name'=>$getUserDetails['company_role'],
                                'role'=>$getUserDetails['role']
                                )
                            ));
                        }
                    }
                }
            }
            return new JsonModel(array('success'=>false,'message'=>'false'));
        }
        catch (\Exception $e)
        {
            return new JsonModel(array('success'=>false,'message'=>'false'));
        }
    } */

    public function paymentCancelAction()
     {
         header('Location:'.$this->getBaseUrl().'/application/andriod-redirect');
         echo 'failure';
         exit;
     }
    public function testCronAction()
    {
          $cron=new CronJob();
          $cronList=$cron->getJobs();
           echo '<pre>';
          print_r($cronList);
          exit;
    }
    public function smsTestAction()
    {
        $request = $this->getRequest()->getPost();
        $this->sendPasswordSms($request['mobile'],array('text'=>"test message from stt api"));
        return new JsonModel(array('success'=>true, 'message'=>'message sent'));
    }
}