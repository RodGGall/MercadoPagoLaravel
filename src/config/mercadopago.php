<?php

$acces_token=env('MP_APP_ACCESS_TOKEN');
$secret=env('MP_APP_SECRET');
$id=env('MP_APP_ID');
if(isset($acces_token)){
	return[
		'app_access_token'=> env('MP_APP_ACCESS_TOKEN')
	];
}
elseif(isset($id) && isset($secret)){
	return [
		'app_id'     => env('MP_APP_ID'),
		'app_secret' => env('MP_APP_SECRET')
	];
}
else{
    return [
	   'app_access_token' => env('MP_APP_ACCESS_TOKEN'),
	   'app_id'     => env('MP_APP_ID'),
	   'app_secret' => env('MP_APP_SECRET')
    ];
}
