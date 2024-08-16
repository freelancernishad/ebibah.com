<?php

namespace App\Http\Controllers\Ekpay;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EkpayPaymentController extends Controller
{

    public function ipn(Request $request)
    {
        $data = $request->all();
        Log::info(json_encode($data));
        $sonod = Sonod::find($data['cust_info']['cust_id']);
        $trnx_id = $data['trnx_info']['mer_trnx_id'];
        $payment = payment::where('trxid', $trnx_id)->first();

        $Insertdata = [];
        if ($data['msg_code'] == '1020') {
            $Insertdata = [
                'status' => 'Paid',
                'method' => $data['pi_det_info']['pi_name'],
            ];



            if($payment->sonod_type=='holdingtax'){
                $hosdingBokeya = HoldingBokeya::find($payment->sonodId);
                // $hosdingtax= Holdingtax::find($hosdingBokeya->holdingTax_id);
                $hosdingBokeya->update(['status'=>'Paid','payYear'=>date('Y'),'payOB'=>COB(1)]);
            }elseif($payment->sonod_type=='Tenders_form'){



                $TenderFormBuy = Tender::find($payment->sonodId);
                $TenderFormBuy->update(['payment_status'=>'Paid']);


                $tenderList = TenderList::find($TenderFormBuy->tender_id);
                $unioun_name = $tenderList->union_name;
                $deccription = "Your Tender Successfuly submited";
                SmsNocSmsSend($deccription, $TenderFormBuy->mobile,$unioun_name);



            }else{
                $sonod->update(['khat' => 'সনদ ফি','stutus' => 'Pending', 'payment_status' => 'Paid']);
            }



        } else {
            $sonod->update(['khat' => 'সনদ ফি','stutus' => 'failed', 'payment_status' => 'Failed']);
            $Insertdata = ['status' => 'Failed',];
        }
        $Insertdata['ipnResponse'] = json_encode($data);
        // return $Insertdata;
        return $payment->update($Insertdata);
    }


    public function ReCallIpn(Request $request)
    {

        $trnx_id = $request->trnx_id;
        $trans_date = date("Y-m-d", strtotime($request->trans_date));

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://pg.ekpay.gov.bd/ekpaypg/v1/get-status',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{

         "trnx_id":"'.$trnx_id.'",
         "trans_date":"'.$trans_date.'"

        }',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));

        $response1 = curl_exec($curl);

        curl_close($curl);
         $data =  json_decode($response1);





        // $data = $request->all();



        Log::info(json_encode($data));
        $sonod = Sonod::find($data->cust_info->cust_id);
        $trnx_id = $data->trnx_info->mer_trnx_id;
        $payment = payment::where('trxid', $trnx_id)->first();

        $Insertdata = [];
        if ($data->msg_code == '1020') {
            $Insertdata = [
                'status' => 'Paid',
                'method' => $data->pi_det_info->pi_name,
            ];
            if($payment->sonod_type=='holdingtax'){
                $hosdingBokeya = HoldingBokeya::find($payment->sonodId);
                // $hosdingtax= Holdingtax::find($hosdingBokeya->holdingTax_id);
                $hosdingBokeya->update(['status'=>'Paid','payYear'=>date('Y')]);
            }else{
                // return  $sonod;
                $sonod->update(['khat' => 'সনদ ফি','stutus' => 'Pending', 'payment_status' => 'Paid']);
            }
        } else {
            $sonod->update(['khat' => 'সনদ ফি','stutus' => 'failed', 'payment_status' => 'Failed']);
            $Insertdata = ['status' => 'Failed',];
        }
        $Insertdata['ipnResponse'] = json_encode($data);
        // return $Insertdata;
        return $payment->update($Insertdata);
    }





    public function AkpayPaymentCheck(Request $request)
    {

        $trnx_id = $request->trnx_id;
        $trans_date = date("Y-m-d", strtotime($request->trans_date));

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://pg.ekpay.gov.bd/ekpaypg/v1/get-status',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{

         "trnx_id":"'.$trnx_id.'",
         "trans_date":"'.$trans_date.'"

        }',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));

        $response1 = curl_exec($curl);

        curl_close($curl);


        $myserver = Payment::where(['trxId'=>$trnx_id])->first();


      return   $data =  [
        'myserver'=>$myserver,
        'akpay'=> json_decode($response1),
      ];


    }


    public function handlePaymentSuccess(Request $request)
    {
        $transId = $request->transId;
        $payment = Payment::where('trxId', $transId)->first();


            $sonod = Sonod::find($payment->sonodId);

            $redirect = "/payment/success/confirm?transId=$transId";



        return response("
            <h3 style='text-align:center'>Please wait 10 seconds. This page will auto redirect you</h3>
            <script>
                setTimeout(() => {
                    window.location.href='$redirect';
                }, 10000);
            </script>
        ");
    }



    public function sonodpaymentSuccess(Request $request)
    {
        $transId =  $request->transId;
         $payment = Payment::where(['trxId' => $transId])->first();
        $id = $payment->sonodId;

        $sonod = Sonod::find($id);







        if($payment->status=='Paid'){
                    $InvoiceUrl =  url("/invoice/c/$id");
                    // $deccription = "অভিনন্দন! আপনার আবেদনটি সফলভাবে পরিশোধিত হয়েছে। অনুমোদনের জন্য অপেক্ষা করুন।";
                    $deccription = "Congratulation! Your application $sonod->sonod_Id has been Paid.Wait for Approval.";
                    // smsSend($deccription, $sonod->applicant_mobile);
                    return view('applicationSuccess', compact('payment', 'sonod'));
        }else{
            echo "
            <div style='text-align:center'>
            <h1 style='text-align:center'>Payment Failed</h1>
            <a href='/' style='border:1px solid black;padding:10px 12px; background:red;color:white'>Back To Home</a>
            <a href='/sonod/payment/$sonod->id' style='border:1px solid black;padding:10px 12px; background:green;color:white'>Pay Again</a>
            </div>
            ";
        }




    }

}
