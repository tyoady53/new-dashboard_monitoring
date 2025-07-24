<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerBranch;
use App\Models\DisplaySetup;
use App\Models\DisplaySetupDetail;
use App\Models\TableSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DisplaySetupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tables = TableSetting::where('display_type', '!=', 'StatBox')->get();
        $auth = User::where('id',auth()->user()->id)->first();

        if(!$auth->customer_id) {
            $selected_cust = null;
            $selected_branch = null;
        } else {
            if(!$auth->customer_branch) {
                $selected_cust = $auth->customer_id;
                $selected_branch = null;
            } else {
                $selected_cust = $auth->customer_id;
                $selected_branch = $auth->customer_branch;
            }
        }

        $master_customers = Customer::select("*")->where('is_show', 1)->orderBy("customer_name")->get();
        $master_customer_branches = CustomerBranch::select("*")->where('is_show', 1)->orderBy("branch_name")->get();

        return Inertia::render('Apps/Editor/Index', [
            'tables'                    => $tables,
            'selected_cust'             => $selected_cust,
            'selected_branch'           => $selected_branch,
            'master_customers'          => $master_customers,
            'master_customer_branches'  => $master_customer_branches,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = DisplaySetup::where('customer_id',$request->customer_id)->where('branch_id',$request->branch_id)->first();

        if($data) {
            $this->update_data($request,$data);
        }

        $insert_arr = [
            'customer_id'   => $request->customer_id,
            'branch_id'     => $request->branch_id,
            'column_count'  => $request->columns,
            'edited_by'     => auth()->user()->id,
        ];

        $insert = DisplaySetup::create($insert_arr);

        foreach($request->charts as $chart) {
            // dd($request->charts, $chart['sequence']);

            DisplaySetupDetail::create([
                'display_id'    => $insert->id,
                'sequence'      => $chart['sequence'],
                'chart_show'    => $chart['chartType'],
                'data_from'     => $chart['dataFrom']
            ]);
        }

        return redirect()->route('apps.index');
    }

    function update_data($request,$data) {
        $chart_arr = $request->charts;
        $data->update([
            'column_count'  => $request->columns,
            'edited_by'     => auth()->user()->id,
        ]);

        if($request->stat_box) {
                array_push($chart_arr, ['sequence'      => count($chart_arr) + 1,
                'chartType'    => 'StatBox',
                'dataFrom'     => 'dash_visitation']
            );
        }

        DisplaySetupDetail::where('display_id', $data->id)->delete();

        foreach($chart_arr as $chart) {
            // dd($chart);

            DisplaySetupDetail::create([
                'display_id'    => $data->id,
                'sequence'      => $chart['sequence'],
                'chart_show'    => $chart['chartType'],
                'data_from'     => $chart['dataFrom']
            ]);
        }

        return redirect()->route('apps.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(DisplaySetup $displaySetup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DisplaySetup $displaySetup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DisplaySetup $displaySetup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DisplaySetup $displaySetup)
    {
        //
    }
}
