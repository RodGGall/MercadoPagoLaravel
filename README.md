# MercadoPagoLaravel
SDK MercadoPago para Laravel

[Instalación](#install)
[Configuración](#config)
[Como Usar](#ejemplo)

<a name="install"></a>
### Instalación

`composer require rodggall/mercadopago @dev`
´
Agregar el Provider `MercadoPagoServiceProvider` en el archivo `config/app.php`

```php
'providers' => [
  // Otros Providers...
  MercadoPagoLaravel\Providers\MercadoPagoServiceProvider::class,
  /*
   * Application Service Providers...
   */
],
```

Agregar el Alias `MP` en el archivo `config/app.php`

```php
'aliases' => [
  // Otros Aliases
  'MP' => MercadoPagoLaravel\Facades\MP::class,
],
```
Ejecutar el comando `php artisan vendor:publish` elegir la opcion `0` correspondiente a todos los providers

<a name="config"></a>
### Configuración

Dentro del archivo `.env` configurar las credenciales a ocupar para mercado pago,
# Para BasicCheckout
Añadir los campos `MP_APP_SECRET` y `MP_APP_ID`

```php
  MP_APP_ID = 'TU CLIENT_ID'
  MP_APP_SECRET = 'TU CLIENT_SECRET'
```

# Para Checkout Personalizado
Añadir el campo `MP_APP_ACCESS_TOKEN`

```php
  MP_APP_ACCESS_TOKEN = 'TU ACCESS_TOKEN'
```
<a name="ejemplo"></a>
### Como Usar
Se puede hacer llamados a la api de mercado pago mediante los cuatro metodos http `POST`, `GET`, `PUT`, `DELETE`. Según la accion que se requiera. Checar documentacion https://www.mercadopago.com.mx/developers. 
Aqui muestro un par de ejemplos:

# Ejemplo de creacion de pago usando el facade `MP`en modo Checkout Personalizado

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use MP;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class MercadoPagoController extends Controller
{
  public function createPayment()
  {
    $payment_data = array(
                      "transaction_amount" => 'Monto a pagar',
                      "description" => 'Descripcion para el pago',
                      "installments" => 'Cantidad de entregas, debe ser entero',
                      "payment_method_id" => 'Metodo elegido de pago',
                      "payer" => array(
                                    "email" => 'Correo del cliente'
                       ),
                       "statement_descriptor" => "Nombre de quien recibe el pago"
                    );
    $payment = MP::post("/v1/payments",$payment_data);
    return dd($payment);
  }
}
```
# Ejemplo de creacion de preferencia de pago usando el facade `MP`en modo Checkout Basico

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use MP;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class MercadoPagoController extends Controller
{
  public function createPreferencePayment()
  {
    $preference_data = [
  		"items" => [
  			[
  				"id" => 'Id del articulo',
          "title" => 'Titulo del articulo',
          "description" => 'Descripcion del articulo',
          "picture_url" => 'Imagen del articulo',
          "quantity" => 'Cantidad de articulos',
          "currency_id" => 'Id de moneda',
          "unit_price" => 'precio por unidad'
  			]
  		],
      "payer" => [
        "email" => 'correo del cliente'
      ]
  	];
    $preference = MP::post("/checkout/preferences",$preference_data);
    return dd($preference);
  }
}
```
