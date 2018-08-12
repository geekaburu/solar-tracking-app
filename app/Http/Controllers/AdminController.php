<?php

namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\User;
use App\County;
use App\PanelData;
use App\CarbonPrice;

class AdminController extends Controller
{
    /**
     * Get dashboard data for a certain customer.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function getDashboardData(Request $request)
    {
        // Get the current carbon cost
        $record = CarbonPrice::where('active', 1)->orderBy('created_at', 'desc')->first();
        $creditRate = $record->credit_rate;
        $carbonPrice = $record->value;

        // Get card data
        $cardData = PanelData::whereYear('panel_data.created_at', date('Y'))
            ->orderBy('panel_data.created_at', 'asc')
            ->select(
                DB::raw('(select count(users.id) as customers from users) as customers'), 
                DB::raw('round(sum(energy), 2) as energy'), 
                DB::raw('round(sum(energy)/'.$creditRate.', 2) as credits'),
                DB::raw('round(sum(energy)/'.$creditRate.'*'.$carbonPrice.',2) as amount'),
                DB::raw('DATE_FORMAT(panel_data.created_at,"%Y") as year') 
            )->groupBy('year')->first();

        // Get chart data
       $chartData = PanelData::orderBy('panel_data.created_at', 'asc');

       // Get county data
       $countyData = County::withCount([
            'panelData as energy' =>function($query){
                $query->select(DB::raw('round(sum(energy),2) as energy'))
                    ->whereYear('panel_data.created_at', date('Y'));
            },
            'panelData as amount' => function($query) use ($creditRate, $carbonPrice) {
                $query->select(DB::raw('round(sum(energy)/'.$creditRate.'*'.$carbonPrice.', 2) as amount'))
                    ->whereYear('panel_data.created_at', date('Y'));
            }
        ])->orderBy('energy', 'desc')->get(['id','name','energy']);

       $customerData = User::ofType('customer')->withCount([
            'panelData as energy' =>function($query){
                $query->select(DB::raw('round(sum(energy),2) as energy'))
                    ->whereYear('panel_data.created_at', date('Y'));
            },
        ])->orderBy('energy', 'desc')->get(['id','name','energy']);

       $monthData = PanelData::whereYear('created_at', date('Y'))->select(
                DB::raw('round(sum(energy), 2) as energy'), 
                DB::raw('DATE_FORMAT(panel_data.created_at,"%M") as month') 
            )->groupBy('month')->orderBy('energy', 'desc')->first();

       // Return response 
        return response()->json([
            'highestCards' => [
                'county' => $countyData[0],
                'customer' => $customerData[0],
                'month' => $monthData,
            ],
            'cards' => $cardData,
            'chart' => $this->generateChartData($chartData, 'month'),
            'lastDate' => Carbon::now()->endOfYear()->format('d/m/Y H:i:s'),
            'rates' => $record,
            'counties' => $countyData,
        ]);
    }
    /**
     * Get customer data.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function getCustomerData(Request $request)
    {
        // Get the current carbon cost
        $record = CarbonPrice::where('active', 1)->orderBy('created_at', 'desc')->first();
        $creditRate = $record->credit_rate;
        $carbonPrice = $record->value;

        // Customer Data
    	$customerData =  User::ofType('customer')->withCount([
			'panelData as energy' => function($query){
	    		$query->select(DB::raw('round(sum(energy),2) as energy'))->whereYear('panel_data.created_at', date('Y'));
	    	}, 
			'panels as panels', 
	    ])->with('location')->get();

        // Energy Data
        $panelData = User::ofType('customer')->withCount([
            'panelData as energy' => function($query) {
                $query->select(DB::raw('round(sum(energy),2) as energy'))
                    ->whereYear('panel_data.created_at', date('Y'))
                    ->whereMonth('panel_data.created_at', date('m'));
            }, 
            'panelData as credits' => function($query) use ($creditRate) {
                $query->select(DB::raw('round(sum(energy)/'.$creditRate.', 2) as credits'))
                    ->whereYear('panel_data.created_at', date('Y'))
                    ->whereMonth('panel_data.created_at', date('m'));
            },
            'panelData as amount' => function($query) use ($creditRate, $carbonPrice) {
                $query->select(DB::raw('round(sum(energy)/'.$creditRate.'*'.$carbonPrice.', 2) as amount'))
                    ->whereYear('panel_data.created_at', date('Y'))
                    ->whereMonth('panel_data.created_at', date('m'));
            }
        ])->whereHas('panelData')->get(); 

	    return response([
			'customerData' => $customerData,
            'chart' => [
                'data' =>  $this->generateChartData(PanelData::orderBy('created_at', 'asc'), $request->chart_filter)
            ],
            'panels' => [
                'data' => $panelData,
            ],
	    ],200);
    }

    /**
     * Get customer analysis data.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function customerAnalysis(Request $request)
    {
        // Get the data requested for
        $data = (is_numeric($request->customer) ? PanelData::where('id', $request->customer) : PanelData::where('id', '>', 0));

        // Filter panel data by duration
        if($request->chart_filter == 'today') $data =  $data->whereDate('panel_data.created_at', Carbon::today());
        if($request->chart_filter == 'week')  $data =  $data->whereBetween('panel_data.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        if($request->chart_filter == 'month') $data =  $data->whereMonth('panel_data.created_at', date('m'));
        if($request->chart_filter == '3month')$data =  $data->whereMonth('panel_data.created_at', '>=', Carbon::now()->subMonth(3)->month);
        if($request->chart_filter == 'year')  $data =  $data->whereYear('panel_data.created_at', date('Y'));
        if($request->chart_filter == 'today') $data =  $data->whereYear('panel_data.created_at', date('Y')); 

        // Get the current carbon cost
        $record = CarbonPrice::where('active', 1)->orderBy('created_at', 'desc')->first();
        $carbonPrice = $record->value;
        $energy = $data->sum('energy');
        $credits = $energy/($record->credit_rate);

        $stats = [
            'temperature' => number_format((float) $data->avg('temperature'),2,'.',''),
            'humidity' => number_format((float) $data->avg('humidity'),2,'.',''),
            'intensity' => number_format((float) $data->avg('intensity'),2,'.',''),
            'energy' => number_format((float) $energy,2,'.',''),
            'credits' => number_format((float) $credits,2,'.',''),
            'amount' => number_format((float) $credits * $carbonPrice,2,'.',''),
        ];

        // Return response data
        return response()->json([
            'customers'=> User::ofType('customer')->get(['id','name']),
            'chart'=>[
                'data'=> $this->generateChartData($data->orderBy('panel_data.created_at', 'asc'), $request->chart_filter),
            ],
            'activeCustomer' => is_numeric($request->customer) ? User::with('location')->findOrFail($request->customer): $request->customer,
            'stats' => $stats,
        ]);
    }

    /**
     * Get transaction data.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function carbonTransactions(Request $request)
    {
        // Get transactions from previous years
        $data = PanelData::select(
            DB::raw('panel_data.created_at as date'),
            DB::raw('"" as control'),
            DB::raw('round(sum(energy),2) as energy'),
            DB::raw('DATE_FORMAT(panel_data.created_at,"%Y") as year'),
            DB::raw('(select rate from carbon_transactions where DATE_FORMAT(panel_data.created_at,"%Y") = DATE_FORMAT(carbon_transactions.sold_on,"%Y")) as rate'),
            DB::raw('(select price from carbon_transactions where DATE_FORMAT(panel_data.created_at,"%Y") = DATE_FORMAT(carbon_transactions.sold_on,"%Y")) as price'),
            DB::raw('(select dispatched_on from carbon_transactions where DATE_FORMAT(panel_data.created_at,"%Y") = DATE_FORMAT(carbon_transactions.sold_on,"%Y")) as receipt_date'),
            DB::raw('(select sold_on from carbon_transactions where DATE_FORMAT(panel_data.created_at,"%Y") = DATE_FORMAT(carbon_transactions.sold_on,"%Y")) as sale_date')
        )->groupBy('year')->get();

        return response([
            'transactions' => $this->getTransactionData($data, true),
        ], 200);
    }

    /**
     * Get energy reports.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function energyReports(Request $request)
    {

        $startDate = $request->start_date ? Carbon::createFromFormat('d/m/Y', $request->start_date)->format('Y-m-d') : '';
        $endDate = $request->end_date ? Carbon::createFromFormat('d/m/Y', $request->end_date) : Carbon::now();
        $customers = User::ofType('customers')->get(['id','name']);

        // Get panel data between 2 time durations
        $data = PanelData::whereBetween('panel_data.created_at', [$startDate, $endDate->addDays(1)->format('Y-m-d')]);

        if(is_numeric($request->county_id) && is_numeric($request->customer_id)) {
            // Get panel data for a user in a certain county
            $data = $data->whereHas('panel.user.location.county', function($query) use ($request){
                    $query->where('id', $request->county_id);
                })->whereHas('panel.user', function($query) use ($request){
                    $query->where('id', $request->customer_id);
                });
            $customers = County::findOrFail($request->county_id)->users()->get(['users.id','users.name']);
        } elseif (is_numeric($request->county_id)){
             // Get panel data for a certain county
            $data = $data->whereHas('panel.user.location.county', function($query) use ($request){
                    $query->where('id', $request->county_id);
                });
            $customers = County::findOrFail($request->county_id)->users()->get(['users.id','users.name']);
        } elseif (is_numeric($request->customer_id)) {
            // Get panel data for a certain county
            $data = $data->whereHas('panel.user', function($query) use ($request){
                    $query->where('id', $request->customer_id);
                });
        }

         // Select the relevant data
        $data = $data->select([
            DB::raw('panel_data.id as id'),
            DB::raw('panel_data.created_at as date'),
            DB::raw('energy as energy'),
            DB::raw('(select rate from carbon_transactions where DATE_FORMAT(panel_data.created_at,"%Y") = DATE_FORMAT(carbon_transactions.sold_on,"%Y")) as rate'),
            DB::raw('(select price from carbon_transactions where DATE_FORMAT(panel_data.created_at,"%Y") = DATE_FORMAT(carbon_transactions.sold_on,"%Y")) as price'),
            DB::raw('(select sold_on from carbon_transactions where DATE_FORMAT(panel_data.created_at,"%Y") = DATE_FORMAT(carbon_transactions.sold_on,"%Y")) as sale_date'),
            DB::raw('(select dispatched_on from carbon_transactions where DATE_FORMAT(panel_data.created_at,"%Y") = DATE_FORMAT(carbon_transactions.sold_on,"%Y")) as receipt_date')
        ])->get();

        foreach ($data as $item) {
            $item->county = PanelData::findOrFail($item->id)->panel->user->location->county;
            $item->customer = PanelData::findOrFail($item->id)->panel->user->name;
        }

        return response([
            'transactions' =>  $this->getTransactionData($data),
            'customers' =>  $customers,
            'counties' => County::all(['id','name']),
        ], 200);
    }
}