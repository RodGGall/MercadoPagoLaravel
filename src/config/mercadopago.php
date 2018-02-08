<?php

if(isset(env('MP_APP_ACCESS_TOKEN'))){
	return[
		'app_access_token'=> env('MP_APP_ACCESS_TOKEN')
	];
}
elseif(isset(env('MP_APP_ID')) && isset(env('MP_APP_SECRET'))){
	return [
		'app_id'     => env('MP_APP_ID'),
		'app_secret' => env('MP_APP_SECRET')
	];
}
else{
	'app_access_token'=> env('MP_APP_ACCESS_TOKEN'),
	'app_id'     => env('MP_APP_ID'),
	'app_secret' => env('MP_APP_SECRET')
}
