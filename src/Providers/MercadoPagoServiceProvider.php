<?php

namespace rodggall\MercadoPago\Providers;

use Illuminate\Support\ServiceProvider;
use rodggall\MercadoPago\MP;

class MercadoPagoServiceProvider extends ServiceProvider 
{

	protected $mp_app_id;
	protected $mp_app_secret;
	protected $mp_access_token;

	public function boot()
	{
		
		$this->publishes([__DIR__.'/../config/mercadopago.php' => config_path('mercadopago.php')]);

		$this->mp_app_id     = config('mercadopago.app_id');
		$this->mp_app_secret = config('mercadopago.app_secret');
		$this->mp_access_token=config('mercadopago.app_access_token');
	}

	public function register()
	{
		$this->app->singleton('MP', function(){
			return new MP( $this->mp_access_token);
		});
	}
}