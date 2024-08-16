<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>



</head>

<body style="font-family: 'bangla', sans-serif;">

@php


    $html = "
            <style type='text/css'>

        td{
            text-align:center
        }
        </style>
        <div class='pdfhead' style='text-align:center'>

        <h5 style='margin:0;width:100%;font-size:16px'>গণপ্রজাতন্ত্রী বাংলাদেশ</h6>
        <h4 style='margin:0;width:100%;font-size:20px' > $uniouninfo->full_name </h4>
        <h6 style='margin:0;width:100%;font-size:16px' >উপজেলা:   $uniouninfo->thana , জেলা:   $uniouninfo->district  ।</h6>
        <h2 style='margin:0 auto;width:100%;background:black;color:white;width:300px;' >ট্যাক্স, রেট ও বিবিধ প্রাপ্তি রশিদ</h2>


        </div>

<p style='text-align:right'>রশিদ নং : $TaxInvoice->trxId</p>
        <p>প্রদানকারীর নাম:  $row->applicant_name  , পিতা/স্বামীর নাম:   $row->applicant_father_name  ,  ওয়ার্ড নং-  $row->applicant_present_word_number ,  গ্রাম:   $row->applicant_present_village ,  উপজেলা:  $row->applicant_present_Upazila  , জেলা:  $row->applicant_present_district </p>";





        $html .="".invoiceView($row->id)."";



        $html .="</div>
        <p style='text-align:left'>কথায় :  $row->the_amount_of_money_in_words ।</p>


        <p style='text-align:right'>আদায়কারীর স্বাক্ষর</p>
        ";
        echo $html;




       if($row->sonod_name=='ট্রেড লাইসেন্স'){
        echo "<h5>..................................................................................................................................................................................................</h5>";
    echo $html;
       }


@endphp

</body>

</html>
