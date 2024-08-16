<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function invoice(Request $request, $name, $id)
    {
        $row = Sonod::find($id);



        $TaxInvoice = Payment::where('sonodId',$row->id)->latest()->first();



            $pdf = LaravelMpdf::loadView('invoice', compact('row', 'sonod', 'uniouninfo','TaxInvoice'));
            $pdf->stream("$row->sonod_Id.pdf");





    }

}
