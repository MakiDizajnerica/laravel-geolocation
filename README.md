<p align="center"><img src="/art/logo.png" alt="Laravel GeoLocation Logo"></p>

# Laravel GeoLocation

Laravel package to get the details about region, city, country, timezone, currency, etc. for a given IP address.

## Installation

```bash
composer require makidizajnerica/laravel-geolocation
```

As for registering Service Provider, it is not necessary,
Laravel will auto load provider using Package Discovery.

## Usage

### Using Facade

Return value of the `MakiDizajnerica\GeoLocation\Facades\GeoLocation::lookup()` is the instance of `Illuminate\Support\Collection`.
To find out more about Laravel Collections head to [Collections - Laravel](https://laravel.com/docs/8.x/collections).

```php
use MakiDizajnerica\GeoLocation\Facades\GeoLocation;

$collection = GeoLocation::lookup('8.8.4.4');

echo $collection->get('success');
// true

echo $collection->get('ip');
// 8.8.4.4

echo $collection->get('continent');
// North America

echo $collection->get('continentCode');
// NA

echo $collection->get('country');
// United States

echo $collection->get('countryCode');
// US

echo $collection->get('countryFlag');
// https://cdn.ipwhois.io/flags/us.svg

echo $collection->get('countryCapital');
// Washington

echo $collection->get('region');
// California

echo $collection->get('city');
// Mountain View

echo $collection->get('timezone');
// America/Los_Angeles

echo $collection->get('timezoneName');
// Pacific Standard Time

echo $collection->get('currency');
// US Dollar

echo $collection->get('currencyCode');
// USD

echo $collection->get('currencySymbol');
// $

echo $collection->get('latitude');
// 37.3860517

echo $collection->get('longitude');
// -122.0838511

var_dump($collection->toArray());
// Array
// (
//     [success] => 1
//     [ip] => 8.8.4.4
//     [continent] => North America
//     [continentCode] => NA
//     [country] => United States
//     [countryCode] => US
//     [countryFlag] => https://cdn.ipwhois.io/flags/us.svg
//     [countryCapital] => Washington
//     [region] => California
//     [city] => Mountain View
//     [timezone] => America/Los_Angeles
//     [timezoneName] => Pacific Standard Time
//     [currency] => US Dollar
//     [currencyCode] => USD
//     [currencySymbol] => $
//     [latitude] => 37.3860517
//     [longitude] => -122.0838511
// )
```

You can also switch driver on runtime using:

```php
use MakiDizajnerica\GeoLocation\Facades\GeoLocation;

$collection = GeoLocation::driver('geoplugin')->lookup('8.8.4.4');
```

Package comes with several predefined drivers,
some of them require `API_KEY` to work.
You can register those keys inside `.env` file:

```env
ABSTRACTAPI_KEY=*****
IPDATA_KEY=*****
```

Predefined drivers:

| Driver                | URL                           |
|-----------------------|:-----------------------------:|
| ipwhois **[default]** | https://ipwhois.io/           |
| geoplugin             | https://www.geoplugin.com/    |
| abstractapi           | https://www.abstractapi.com/  |
| ipdata                | https://ipdata.co/            |

_Depending on the driver that is used it is possible to get different results._

### Request Macro

Inside your controller `Illuminate\Http\Request $request` will have `geolocation()` method available:

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {

    dd($request->geolocation()->toArray());

    return view('welcome');
});

// Array
// (
//     [success] => 1
//     [ip] => 8.8.4.4
//     [continent] => North America
//     [continentCode] => NA
//     [country] => United States
//     [countryCode] => US
//     [countryFlag] => https://cdn.ipwhois.io/flags/us.svg
//     [countryCapital] => Washington
//     [region] => California
//     [city] => Mountain View
//     [timezone] => America/Los_Angeles
//     [timezoneName] => Pacific Standard Time
//     [currency] => US Dollar
//     [currencyCode] => USD
//     [currencySymbol] => $
//     [latitude] => 37.3860517
//     [longitude] => -122.0838511
// )
```

## Publishing Config File

To publish `geolocation.php` config file use command:

```bash
php artisan vendor:publish --tag=geolocation-config
```

## Creating Custom Driver

When creating custom driver, be sure to extend `MakiDizajnerica\GeoLocation\GeoLocationDriver` class.
Then define two methods `lookup()` and `format()`:

```php
use MakiDizajnerica\GeoLocation\GeoLocationDriver;

class CustomDriver extends GeoLocationDriver
{
    public function lookup($ipAddress): array
    {
        //
    }

    public function format(array $data): array
    {
        return [
            //
        ];
    }
}
```

Method `lookup($ipAddress)` accepts one parametar of type `string` that represents IP address. 
Inside this method you write request logic (sending request, checking response etc.),
after that you just need to return response data as array.
If request is not successful for some reason,
you may throw `MakiDizajnerica\GeoLocation\Exceptions\GeoLocationDriverException` that will be picked up and reported inside log files.

Method `format($data)` accepts one parametar of type `array` that represents your response data returned from `lookup()` method,
here you can format data as array with your own set of key/value pairs.

Inside `CustomDriver` class you will have two methods available, `options()` and `apiEndpoint()`.

Method `options($key)` accepts one parametar `$key` of type `string` and returns driver option based on provided `$key`.

Method `apiEndpoint($ipAddress)` accepts one parametar `$ipAddress` of type `string` and returns formatted api endpoint url with passed `$ipAddress`.

If you plan to use Laravel's built in `Illuminate\Support\Facades\Http` Http Client,
you can use `MakiDizajnerica\GeoLocation\Support\HandlingHttpResponse` trait inside your `CustomDriver` class:

```php
use Illuminate\Support\Facades\Http;
use MakiDizajnerica\GeoLocation\GeoLocationDriver;
use MakiDizajnerica\GeoLocation\Support\HandlingHttpResponse;

class CustomDriver extends GeoLocationDriver
{
    use HandlingHttpResponse;

    public function lookup($ipAddress): array
    {
        $response = Http::get($this->apiEndpoint($ipAddress));

        $data = $this->decodeResponseData(
            $this->checkResponse($response)
        );

        return $data;
    }

    public function format(array $data): array
    {
        return [
            'ip' => $data['ip'],
            'continent' => $data['continent'],
            'country' => $data['country'],
            'region' => $data['region'],
            'city' => $data['city'],
            'timezone' => $data['timezone'],
            'currency' => $data['currency'],
        ];
    }
}
```

Method `checkResponse(Response $response)` accepts one parameter of type `Illuminate\Http\Client\Response`,
then returns that response if it was successful,
or otherwise throws `MakiDizajnerica\GeoLocation\Exceptions\GeoLocationDriverException`.

Method `decodeResponseData(Response $response)` accepts one parameter of type `Illuminate\Http\Client\Response`,
then tries to json decode that response and return array containing response data,
or empty array on fail.

Then when you wrote lookup logic and formatted data,
you need to register your driver inside `config/geolocation.php` config file:

```php
'drivers' => [

    'customdriver' => [
        'driver' => \CustomDriver::class,
        'api_endpoint' => 'custom-endpoint.com',
        'query_params' => [

            'ip' => '{ip}'

        ],
    ],

]
```

`{ip}` is placeholder where actual IP address will be placed on `apiEndpoint()` call.
You can put placeholder either in `api_endpoint`:

```php
'api_endpoint' => 'custom-endpoint.com/{ip}'
```

or inside `query_params` like shown above:

```php
'query_params' => [

    'ip' => '{ip}'

]
```

You can also define any other placeholder wraped inside curly brackets like so:

```php
'query_params' => [

    'ip' => '{ip}'
    'lang' => '{lang}'

]
```

Then on runtime pass array containing that placeholder as key as second parameter to `driver()` method:

```php
use MakiDizajnerica\GeoLocation\Facades\GeoLocation;

$collection = GeoLocation::driver('customdriver', ['lang' => 'en'])->lookup('8.8.4.4');
```

So when you call `apiEndpoint()` method inside your driver lookup method,
the return value should look something like this:

```php
echo $this->apiEndpoint('8.8.4.4');
// custom-endpoint.com?ip=8.8.4.4&lang=en
```

## Author

**Nemanja Marijanovic** (<n.marijanovic@hotmail.com>) 

## Licence

Copyright © 2021, Nemanja Marijanovic <n.marijanovic@hotmail.com>

All rights reserved.

For the full copyright and license information, please view the LICENSE 
file that was distributed within the source root of this package.