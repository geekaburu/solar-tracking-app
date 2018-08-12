<?php

namespace App\Http\Traits;
use Illuminate\Http\Request;

use DB;
use Carbon\Carbon;
use App\User;
use App\CarbonPrice;
use App\PanelData;
use App\CarbonTransaction;

trait HelperTrait
{
	public function generateChartData($data, $filter)
	{
		// Define parameters
	    $parameters = [
	    	DB::raw('round(avg(humidity),2) as humidity'), 
	    	DB::raw('round(avg(temperature),2) as temperature'), 
	    	DB::raw('round(sum(energy),2) as energy'), 
	    	DB::raw('round(avg(intensity),2) as intensity'), 
	    ];

	    // Create filters for the query
	    if($filter == 'today'){
	    	$chartData = $data->whereDate('panel_data.created_at', Carbon::today());
	    	array_push($parameters, DB::raw("DATE_FORMAT(panel_data.created_at,'%r') as label"));
	    } 
	    elseif($filter == 'week'){
	    	$chartData = $data->whereBetween('panel_data.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
	    	array_push($parameters, DB::raw("DATE_FORMAT(panel_data.created_at,'%a') as label"));
	    } 
	    else if($filter == 'month'){
	    	$chartData = $data->whereYear('panel_data.created_at', date('Y'))->whereMonth('panel_data.created_at', date('m'));
	    	array_push($parameters, DB::raw("DATE_FORMAT(panel_data.created_at,'%D %M') as label"));
	    } 
	    else if($filter == '3month'){
	    	$chartData = $data->whereYear('panel_data.created_at', date('Y'))->whereMonth('panel_data.created_at', '>=', Carbon::now()->subMonth(3)->month);
	    	array_push($parameters, DB::raw("DATE_FORMAT(panel_data.created_at,'%D %M') as label"));
	    } 
	    else if($filter == 'year'){
	    	$chartData = $data->whereYear('panel_data.created_at', date('Y'));
	    	array_push($parameters, DB::raw("DATE_FORMAT(panel_data.created_at,'%M') as label"));
	    } 
	    else if(is_numeric($filter)){
	    	$chartData = $data->whereYear('panel_data.created_at', date('Y'))->whereMonth('panel_data.created_at', '>=', Carbon::now()->subMonth($filter)->month);
	    	array_push($parameters, DB::raw("DATE_FORMAT(panel_data.created_at,'%M') as label"));
	    }
	    return $chartData->select($parameters)->groupBy('label')->get();
	}

	public function getTransactionData($data, $admin = false)
	{
		// Get the current rates
        $record = CarbonPrice::where('active', 1)->orderBy('created_at', 'desc')->first();
        $price = $record->value;
        $rate = $record->credit_rate;

        // Populate empty options
        $transactions = [];
        foreach ($data as $element) {
            if(Carbon::createFromFormat('Y-m-d H:i:s', $element->date)->year  == date('Y')){
                $element->credits = number_format((float) $element->energy/ $rate,5,'.','');
                $element->amount = number_format((float) $element->energy / $rate * $price,2,'.','');
                $element->price = $price;
                $element->rate = $rate;
                $element->status = 'Unavailable';
            } else{
                $element->credits = number_format((float) $element->energy/ $element->rate,5,'.','');
                $element->amount = number_format((float) $element->energy / $element->rate * $element->price,2,'.','');
                if($element->receipt_date && $element->sale_date) $element->status = 'Received'; 
                if(!$element->sale_date) $element->status = 'Processing'; 
            }

            // Add sale and dispatch status
            if($admin){
            	$element->sale_status = $element->sale_date ? 'Sold' : null; 
            	$element->dispatch_status = $element->receipt_date ? 'Dispatched' : null; 
            	$element->data = User::ofType('customer')->withCount([
					'panelData as energy' => function($query) use ($element) {
		                $query->select(DB::raw('sum(energy) as energy'))
		                    ->whereYear('panel_data.created_at', $element->year);
		            }, 
		            'panelData as credits' => function($query) use ($rate, $element) {
		                $query->select(DB::raw('round(sum(energy)/'.$rate.', 2) as credits'))
		                    ->whereYear('panel_data.created_at', $element->year);
		            },
		            'panelData as amount' => function($query) use ($rate, $price, $element) {
		                $query->select(DB::raw('round(sum(energy)/'.$rate.'*'.$price.', 2) as amount'))
		                    ->whereYear('panel_data.created_at', $element->year);
		            },
            	])->withCount([
            		'location as county' => function($query){
            			$query->select(DB::raw('(select name from counties where locations.county_id = counties.id) as county'));
            		},
            	])->whereHas('panelData')->get();
            }
            $transactions[] = $element;
        }
        return $transactions;
	}
}