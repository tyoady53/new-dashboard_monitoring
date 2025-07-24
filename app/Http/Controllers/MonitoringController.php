<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerBranch;
use App\Models\Dashboard;
use App\Models\DashMonitoringTat;
use App\Models\DashNilaiKritis;
use App\Models\DashTat;
use App\Models\DashTestGroup;
use App\Models\DashTotalKritis;
use App\Models\DashVisitation;
use App\Models\DashVisitClasification;
use App\Models\DashVisitHour;
use App\Models\DisplaySetup;
use App\Models\DisplaySetupDetail;
use App\Models\TableSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MonitoringController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permissions = $this->get_permissions();
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

        return Inertia::render('Apps/Dashboard/Monitoring', [
            'permissions'               => $permissions,
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
        $arr_type = ['cito', 'noncito'];
        for($i=0;$i<(int)request()['length']; $i++){
            $array[$i] = [
                "dttm"          => Carbon::now()->format("Y-m-d H:i:s"),
                "cust_name"     => request()['cust_name'],
                "cust_branch"   => request()['cust_branch'],
                "reg_no"        => Carbon::now()->format("ym").str_pad($i + 1,4,"0",STR_PAD_LEFT),
                "type"          => $arr_type[rand(0,1)],
                "sc" => rand(1,4),
                "ps" => rand(1,4),
                "rs" => rand(1,4),
                "vr" => rand(1,4),
                "au" => rand(1,4),
                "kr" => rand(0,1)
            ];
        };

        // dd(request(),$array,(int)request()['length']);
        return response()->json([
            'data' => $array
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // if(count($request->data) > 0) {
        //     $cust_name = '';
        //     $cust_branch = '';

        //     // Dashboard::truncate();
        //     for($i=0; $i < count($request->data); $i ++) {
        //         $cust_name_old = $cust_name; $cust_branch_old = $cust_branch;
        //         $cust_name = $request->data[0]['cust_name'];
        //         $cust_branch = $request->data[0]['cust_branch'];
        //         if($cust_name_old != $cust_name && $cust_branch_old != $cust_branch) {
        //             Dashboard::where('cust_name',$cust_name)->where('cust_branch',$cust_branch)->delete();
        //             // $last =
        //         }
        //         Dashboard::create([
        //             // "id"            => $i + 1,
        //             "dttm"          => $request->data[$i]['dttm'],
        //             "cust_name"     => $cust_name,
        //             "cust_branch"   => $cust_branch,
        //             "reg_no"         => $request->data[$i]['reg_no'],
        //             "type"          => $request->data[$i]['type'],
        //             "sc"            => $request->data[$i]['sc'],
        //             "ps"            => $request->data[$i]['ps'],
        //             "rs"            => $request->data[$i]['rs'],
        //             "vr"            => $request->data[$i]['vr'],
        //             "au"            => $request->data[$i]['au'],
        //             "kr"            => $request->data[$i]['kr']
        //         ]);
        //     }
        // }

        // return null;
        // dd(count($request->data));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function get_data($email)
    {
        $parameters = [1,2,3,5];
        $user = User::with('has_company','has_branch')->where('email',$email)->first();
        $array = Dashboard::where('cust_name',$user->has_company->customer_id)->where('cust_branch',$user->has_branch->outlet_id)->orderBy('reg_no')->get();
        $data = array();
        $idx_cito_sc = 0;
        $idx_non_sc = 0;
        $idx_cito_ps = 0;
        $idx_non_ps = 0;
        $idx_cito_rs = 0;
        $idx_non_rs = 0;
        $idx_cito_vr = 0;
        $idx_non_vr = 0;
        $idx_cito_au = 0;
        $idx_non_au = 0;
        $idx_kritis = 0;
        $arr_type = ['cito', 'noncito'];

        $data['cust_name'] = $user->has_company->customer_name;
        $data['cust_branch'] = $user->has_branch->branch_name;
        if(count($array)){
            $data['last_update'] = $array[0]->dttm;
        } else {
            $data['last_update'] = null;
        }

        foreach($array as $index=>$loop) {
            if($loop->cust_name) {
                // Speciment Collection
                $spe_col = null;
                if(in_array($loop['sc'], $parameters)){
                    $spe_col = ['reg_no' => str_pad($loop['reg_no'],4,"0",STR_PAD_LEFT), 'type' => $loop['sc']];
                    if(strtoupper($loop['type']) == 'CITO') {
                        if(count($data) > 0) {
                            $data['sc'][$idx_cito_sc]['cito'] = $spe_col;
                            if($idx_cito_sc >= $idx_non_sc) {
                                $data['sc'][$idx_cito_sc]['noncito'] = null;
                            }
                        } else {
                            $data['sc'][$idx_cito_sc]['cito'] = $spe_col;
                            $data['sc'][$idx_cito_sc]['noncito'] = null;
                        }
                        $idx_cito_sc += 1;
                    } else {
                        if(count($data) > 0) {
                            if($idx_cito_sc <= $idx_non_sc) {
                                $data['sc'][$idx_non_sc]['cito'] = null;
                            }
                            $data['sc'][$idx_non_sc]['noncito'] = $spe_col;
                        } else {
                            $data['sc'][$idx_non_sc]['cito'] = null;
                            $data['sc'][$idx_non_sc]['noncito'] = $spe_col;
                        }
                        $idx_non_sc += 1;
                    }
                }

                // Process Sample
                $pro_sam = null;
                if(in_array($loop['ps'], $parameters)){
                    $pro_sam = ['reg_no' => str_pad($loop['reg_no'],4,"0",STR_PAD_LEFT), 'type' => $loop['ps']];
                    if(strtoupper($loop['type']) == 'CITO') {
                        if(count($data) > 0) {
                            $data['ps'][$idx_cito_ps]['cito'] = $pro_sam;
                            if($idx_cito_ps >= $idx_non_ps) {
                                $data['ps'][$idx_cito_ps]['noncito'] = null;
                            }
                        } else {
                            $data['ps'][$idx_cito_ps]['cito'] = $pro_sam;
                            $data['ps'][$idx_cito_ps]['noncito'] = null;
                        }
                        $idx_cito_ps += 1;
                    } else {
                        if(count($data) > 0) {
                            if($idx_cito_ps <= $idx_non_ps) {
                                $data['ps'][$idx_non_ps]['cito'] = null;
                            }
                            $data['ps'][$idx_non_ps]['noncito'] = $pro_sam;
                        } else {
                            $data['ps'][$idx_non_ps]['cito'] = null;
                            $data['ps'][$idx_non_ps]['noncito'] = $pro_sam;
                        }
                        $idx_non_ps += 1;
                    }
                }

                // Result
                $result = null;
                if(in_array($loop['rs'], $parameters)){
                    $result = ['reg_no' => str_pad($loop['reg_no'],4,"0",STR_PAD_LEFT), 'type' => $loop['rs']];
                    if(strtoupper($loop['type']) == 'CITO') {
                        if(count($data) > 0) {
                            $data['rs'][$idx_cito_rs]['cito'] = $result;
                            if($idx_cito_rs >= $idx_non_rs) {
                                $data['rs'][$idx_cito_rs]['noncito'] = null;
                            }
                        } else {
                            $data['rs'][$idx_cito_rs]['cito'] = $result;
                            $data['rs'][$idx_cito_rs]['noncito'] = null;
                        }
                        $idx_cito_rs += 1;
                    } else {
                        if(count($data) > 0) {
                            if($idx_cito_rs <= $idx_non_rs) {
                                $data['rs'][$idx_non_rs]['cito'] = null;
                            }
                            $data['rs'][$idx_non_rs]['noncito'] = $result;
                        } else {
                            $data['rs'][$idx_non_rs]['cito'] = null;
                            $data['rs'][$idx_non_rs]['noncito'] = $result;
                        }
                        $idx_non_rs += 1;
                    }
                }

                // Verification
                $verif = null;
                if(in_array($loop['vr'], $parameters)){
                    $verif = ['reg_no' => str_pad($loop['reg_no'],4,"0",STR_PAD_LEFT), 'type' => $loop['vr']];
                    if(strtoupper($loop['type']) == 'CITO') {
                        if(count($data) > 0) {
                            $data['vr'][$idx_cito_vr]['cito'] = $verif;
                            if($idx_cito_vr >= $idx_non_vr) {
                                $data['vr'][$idx_cito_vr]['noncito'] = null;
                            }
                        } else {
                            $data['vr'][$idx_cito_vr]['cito'] = $verif;
                            $data['vr'][$idx_cito_vr]['noncito'] = null;
                        }
                        $idx_cito_vr += 1;
                    } else {
                        if(count($data) > 0) {
                            if($idx_cito_vr <= $idx_non_vr) {
                                $data['vr'][$idx_non_vr]['cito'] = null;
                            }
                            $data['vr'][$idx_non_vr]['noncito'] = $verif;
                        } else {
                            $data['vr'][$idx_non_vr]['cito'] = null;
                            $data['vr'][$idx_non_vr]['noncito'] = $verif;
                        }
                        $idx_non_vr += 1;
                    }
                }

                // Authorizazion
                $author = null;
                if(in_array($loop['au'], $parameters)){
                    $author = ['reg_no' => str_pad($loop['reg_no'],4,"0",STR_PAD_LEFT), 'type' => $loop['au']];
                    if(strtoupper($loop['type']) == 'CITO') {
                        if(count($data) > 0) {
                            $data['au'][$idx_cito_au]['cito'] = $author;
                            if($idx_cito_au >= $idx_non_au) {
                                $data['au'][$idx_cito_au]['noncito'] = null;
                            }
                        } else {
                            $data['au'][$idx_cito_au]['cito'] = $author;
                            $data['au'][$idx_cito_au]['noncito'] = null;
                        }
                        $idx_cito_au += 1;
                    } else {
                        if(count($data) > 0) {
                            if($idx_cito_au <= $idx_non_au) {
                                $data['au'][$idx_non_au]['cito'] = null;
                            }
                            $data['au'][$idx_non_au]['noncito'] = $author;
                        } else {
                            $data['au'][$idx_non_au]['cito'] = null;
                            $data['au'][$idx_non_au]['noncito'] = $author;
                        }
                        $idx_non_au += 1;
                    }
                }

                // Kritis
                $kritis = null;
                $kritis = ['reg_no' => str_pad($loop['reg_no'],4,"0",STR_PAD_LEFT), 'type' => $loop['kr']];
                if($loop['kr'] == '1') {
                    $data['kr'][$idx_kritis] = $kritis;
                    $idx_kritis += 1;
                }
            }
        }

        $data['tat'] = DashMonitoringTat::where('cust_name',$user->has_company->customer_id)->where('cust_branch',$user->has_branch->outlet_id)->get();

        response()->json([
            'status'    => true,
            'message'   => 'Monitoring Data',
            'data'      => $data
        ])->send();

        // Step 3: Close the connection
        // if (function_exists('fastcgi_finish_request')) {
        //     dd('fastcgi_finish_request exist');
        //     fastcgi_finish_request();
        // }
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();

        // Step 4: Perform any background tasks (optional)
        // Example: Log something, send an email, etc.
        // \Log::info("Connection closed. Background task is running.");

        return;
    }

    public function get_setup(Request $request) {
        $return_data = array();
        $statBox = 0;
        $data = DisplaySetup::where('customer_id', $request->cust_id)->where('branch_id', $request->cust_branch)->with(['details' => function ($query) {
            $query->where('chart_show', '!=', 'StatBox'); // exclude from results
        }])->first();

        $hasStatBox = DisplaySetupDetail::where('display_id', optional($data)->id)
        ->where('chart_show', 'StatBox')
        ->exists();

        if ($data) {
            $return_data['data'] = $data->makeHidden(['id','details']);

            $details = $data->details->map(function ($item) {
                return [
                    'sequence'   => $item->sequence,
                    'chartType'  => $item->chart_show,
                    'dataFrom'   => $item->data_from,
                ];
            });

            if($hasStatBox) {
                $statBox = 1;
            }
            $return_data['details'] = $details;

            $return_data['statbox'] = $hasStatBox;
        }

        return $return_data;
        dd($data);
    }

    public function get_last_update(Request $request) {
        $formatted = null;
        $tables = TableSetting::get();
        $cust = Customer::where('id', $request->cust_id)->first();
        $branch = CustomerBranch::where('id', $request->cust_branch)->first();
        foreach($tables as $table) {
            $dttm[] = DB::table($table->table_name)
                ->select('dttm')
                ->where('cust_name',$cust->customer_name)
                ->where('cust_branch',$branch->branch_name)
                ->orderBy('dttm', 'desc')
                ->first();
        }

        $latest = collect($dttm)->max('dttm');

        if($latest) {
            $formatted = Carbon::parse($latest)->format('d M Y/H:i');
        }

        return $formatted;
    }

    public function get_dashboard(Request $request) {
        $data = DisplaySetup::where('customer_id', $request->cust_id)->where('branch_id', $request->cust_branch)->with('details.setting')->first();

        return response()->json([
            'status'    => true,
            'message'   => 'Monitoring Data',
            'data'      => $data
        ]);
    }

    public function get_data_monitoring(Request $request) {
        $auth = auth()->user();
        $return_data = [];

        $cust = Customer::where('id', $request->cust_id)->first();
        $branch = CustomerBranch::where('id', $request->cust_branch)->first();

        switch ($request->link) {
            case 'dash_test_group':
                $return_data = $this->get_stat_test_group($cust,$branch);
                break;

            case 'dash_total_kritis':
                $return_data = $this->get_stat_nilai_kritis($cust,$branch);
                break;

            case 'dash_visit_clasification':
                $return_data = $this->get_stat_asal_pasien($cust,$branch);
                break;

            case 'dash_visit_hour':
                $return_data = $this->get_kunj_perjam($cust,$branch);
                break;

            case 'dash_nilai_kritis':
                $return_data = $this->get_nilai_ktitis($cust,$branch);
                break;

            case 'dash_tat':
                $return_data = $this->get_monitoring_tat($cust,$branch);
                break;

            default:
                $return_data = $this->get_statbox($cust,$branch);
                break;
        }

        return $return_data;
    }

    // New Dashboard
    function get_stat_test_group($cust,$branch) {
        // $cust = Customer::where('id', $request->cust_id)->first();
        // $branch = CustomerBranch::where('id', $request->cust_branch)->first();
        $labels = DashTestGroup::where('cust_name',$cust->customer_name)->where('cust_branch',$branch->branch_name)->distinct()->pluck('test_group')->toArray();
        $arr_selesai = DashTestGroup::where('cust_name',$cust->customer_name)->where('cust_branch',$branch->branch_name)->pluck('total')->toArray();
        $arr_belum_selesai = DashTestGroup::where('cust_name',$cust->customer_name)->where('cust_branch',$branch->branch_name)->pluck('total_not_finish')->toArray();
        $return_array = array();

        $return_data = [
            [
                'name' => 'BELUM SELESAI',
                'data' => $arr_belum_selesai
            ],
            [
                'name' => 'SELESAI',
                'data' => $arr_selesai
            ]
        ];

        $return_array['labels'] = $labels;
        $return_array['data'] = $return_data;
        $return_array['title'] = 'PASIEN NILAI KRITIS';

        return $return_array;
    }

    function get_stat_nilai_kritis($cust,$branch) {
        // $cust = Customer::where('id', $request->cust_id)->first();
        // $branch = CustomerBranch::where('id', $request->cust_branch)->first();
        $datas = DashTotalKritis::where('cust_name',$cust->customer_name)->where('cust_branch',$branch->branch_name)->orderBy('type', 'ASC')->get();
        // dd($datas);
        $return_array = array();

        $total = 0;
        foreach($datas as $data) {
            // $return_array['data'] = [$data->lab_no];
            $return_array['data'][] = $data->total;
            $total += $data->total;
        }

        $return_array['labels'] = ['BELUM LAPOR', 'SUDAH LAPOR'];
        $return_array['total'] = $total;
        $return_array['title'] = 'STATISTIK NILAI KRITIS';

        return $return_array;
    }

    function get_stat_asal_pasien($cust,$branch) {
        // $cust = Customer::where('id', $request->cust_id)->first();
        // $branch = CustomerBranch::where('id', $request->cust_branch)->first();
        $datas = DashVisitClasification::where('cust_name',$cust->customer_name)->where('cust_branch',$branch->branch_name)->orderBy('patient_type','DESC')->get();
        $labels = DashVisitClasification::where('cust_name',$cust->customer_name)->where('cust_branch',$branch->branch_name)->distinct()->orderBy('patient_type','DESC')->pluck('patient_type')->toArray();
        $return_array = array();

        $total = 0;
        foreach($datas as $data) {
            // $return_array['data'] = [$data->lab_no];
            $return_array['data'][] = $data->total;
            $total += $data->total;
        }

        $return_array['labels'] = $labels;
        $return_array['total'] = $total;
        $return_array['title'] = 'STATISTIK ASAL PASIEN';

        return $return_array;
    }

    function get_kunj_perjam($cust,$branch) {
        // $cust = Customer::where('id', $request->cust_id)->first();
        // $branch = CustomerBranch::where('id', $request->cust_branch)->first();
        $labels = DashVisitHour::where('cust_name',$cust->customer_name)->where('cust_branch',$branch->branch_name)->orderBy('hours','ASC')->pluck('hours')->toArray();
        $datas = DashVisitHour::where('cust_name',$cust->customer_name)->where('cust_branch',$branch->branch_name)->orderBy('hours','ASC')->pluck('total')->toArray();
        $return_array = array();

        $return_data = [
            [
                'name' => 'Total Pasien',
                'data' => $datas
            ]
        ];

        $return_array['data'] = $return_data;
        $return_array['labels'] = $labels;
        $return_array['title'] = 'STATISTIK KUNJUNGAN PERJAM';

        return $return_array;

    }

    function get_monitoring_tat($cust,$branch) {
        // $cust = Customer::where('id', $request->cust_id)->first();
        // $branch = CustomerBranch::where('id', $request->cust_branch)->first();
        $labels = DashTat::where('cust_name',$cust->customer_name)->where('cust_branch',$branch->branch_name)->distinct()->orderBy('tat_type', 'DESC')->pluck('tat_type')->toArray();
        $arr_selesai = DashTat::where('cust_name',$cust->customer_name)->where('cust_branch',$branch->branch_name)->orderBy('tat_type', 'DESC')->pluck('total')->toArray();
        $arr_belum_selesai = DashTat::where('cust_name',$cust->customer_name)->where('cust_branch',$branch->branch_name)->orderBy('tat_type', 'DESC')->pluck('total_not_finish')->toArray();
        $return_array = array();

        $return_data = [
            [
                'name' => 'BELUM SELESAI',
                'data' => $arr_belum_selesai
            ],
            [
                'name' => 'SELESAI',
                'data' => $arr_selesai
            ]
        ];

        $return_array['labels'] = $labels;
        $return_array['data'] = $return_data;
        $return_array['title'] = 'MONITORING TAT';

        return $return_array;
    }

    function get_nilai_ktitis($cust,$branch) {
        // $cust = Customer::where('id', $request->cust_id)->first();
        // $branch = CustomerBranch::where('id', $request->cust_branch)->first();
        $datas = DashNilaiKritis::where('cust_name',$cust->customer_name)->where('cust_branch',$branch->branch_name)->get();
        $return_array = array();
        foreach($datas as $data) {
            $return_array['data'][] = [
                'lab_no'        => $data->lab_no,
                'patient_name'  => $data->patient_name,
                'test_name'     => $data->test_name
            ];
        }

        $return_array['title'] = 'PASIEN NILAI KRITIS';

        return $return_array;
    }

    function get_statbox($cust,$branch) {
        // $cust = Customer::where('id', $request->cust_id)->first();
        // $branch = CustomerBranch::where('id', $request->cust_branch)->first();
        $data = DashVisitation::where('cust_name',$cust->customer_name)->where('cust_branch',$branch->branch_name)->first();
        $labels = ['KUNJUNGAN', 'SAMPEL BELUM AMBIL', 'SAMPEL TERIMA', 'PEMERIKSAAN BELUM SELESAI', 'PEMERIKSAAN SELESAI', 'TOTAL PEMERIKSAAN'];
        $return_array = array();
        foreach($labels as $label) {
            $total = 0;
            switch ($label) {
                case 'KUNJUNGAN':
                    $total = $data->visitation;
                    break;

                case 'SAMPEL BELUM AMBIL':
                    $total = $data->spec_not_draw;
                    break;

                case 'SAMPEL TERIMA':
                    $total = $data->spec_received;
                    break;

                case 'PEMERIKSAAN BELUM SELESAI':
                    $total = $data->test_not_finish;
                    break;

                case 'PEMERIKSAAN SELESAI':
                    $total = $data->test_finish;
                    break;

                default:
                    $total = $data->test_not_finish + $data->test_finish;
                    break;
            }
            $return_array['data'][] = [
                'label' => $label,
                'value' => $total
            ];
        }

        $return_array['labels'] = $labels;

        return $return_array;
    }

    public function get_permissions()
    {
        $array_permission = array();
        foreach (Auth::user()->getAllPermissions() as $key => $permission) {
            array_push($array_permission, $permission->name);
        }

        if(count($array_permission)) {
            $return['status']   = 200;
            $return['data']     = $array_permission;
        } else {
            $return['status']   = 500;
            $array_permission = [];
        }

        return response()->json(
            $array_permission
        );
    }
}
