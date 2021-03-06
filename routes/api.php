<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Routes for JWT Auth
Route::prefix('auth')->group(function(){
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::post('payload', 'AuthController@payload');
});

// Customer routes
Route::prefix('customers')->middleware('jwt.auth')->group(function(){
	Route::post('/dashboard-data', 'CustomerController@getDashboardData')->name('customers.dashboard.data');	
	Route::post('/panel-data', 'CustomerController@getPanelData')->name('customers.panel.data');	
	Route::post('/update-controls', 'CustomerController@updateControls')->name('customers.update.controls');	
	Route::post('/panel-analysis', 'CustomerController@panelAnalysis')->name('customers.panel.analysis');	
	Route::post('/carbon-transactions', 'CustomerController@carbonTransactions')->name('customers.carbon.transactions');	
	Route::post('/energy-reports', 'CustomerController@energyReports')->name('customers.energy.reports');	
	Route::post('/update-profile', 'CustomerController@updateUserProfile')->name('customers.update.profile');	
});

// Admin Routes
Route::prefix('admin')->middleware('jwt.auth')->group(function(){
	Route::post('/dashboard-data', 'AdminController@getDashboardData')->name('admin.dashboard.data');	
	Route::post('/customer-data', 'AdminController@getCustomerData')->name('admin.customer.data');	
	Route::post('/customer-analysis', 'AdminController@customerAnalysis')->name('admin.customer.analysis');	
	Route::post('/carbon-transactions', 'AdminController@carbonTransactions')->name('admin.carbon.transactions');	
	Route::post('/energy-reports', 'AdminController@energyReports')->name('admin.energy.reports');	
	Route::post('/finance-report', 'AdminController@financeReport')->name('admin.finance.reports');	
	Route::post('/register-customer', 'AdminController@registerCustomer')->name('admin.register.customer');	
});

// Application Routes
Route::prefix('application')->middleware('jwt.auth')->group(function(){
	Route::post('/session-data', 'AppController@getSessionData')->name('app.session.data');	
	Route::post('/pricing', 'AppController@getPricingData')->name('app.pricing.data');	
});

// Panel Routes
Route::prefix('panel')->group(function(){
	Route::post('/receive-panel-data', 'PanelController@receivePanelData')->name('panel.receive.data');	
	Route::post('/market-pricing', 'PanelController@marketPricing')->name('panel.market.pricing');	
	Route::post('/create-pricing', 'PanelController@createMarketPrice')->name('panel.create.pricing');	
});

// Test Routes
Route::prefix('test')->group(function(){
	Route::get('/populate/{frequency}', function($frequency){
		for ($i=0; $i < $frequency ; $i++) { 
			App\PanelData::create([
				'panel_id' => 1,
				'angle' => rand(0,100),
				'voltage' => rand(1,5),
				'power' => rand(0,100)/100,
				'energy' => rand(10,20),
				'runtime' => rand(1,15),
			]);
			sleep(10);
		}
		return 'Done...';
	});	
});