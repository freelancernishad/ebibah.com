<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Visitor;

class VisitorController extends Controller
{
    public function index()
    {
        $reports = $this->generateReports();
        return view('visitors.index', compact('reports'));
    }

    private function generateReports()
    {
        $reports = [
            'all' => Visitor::all(),
            'page' => Visitor::groupBy('page')->get(),
            'user' => Visitor::groupBy('user_id')->get(),
            'device' => Visitor::groupBy('device')->get(),
            'ip' => Visitor::groupBy('ip_address')->get(),
            'date' => Visitor::groupBy('visit_date')->get(),
        ];

        return $reports;
    }
}