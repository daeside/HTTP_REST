# HTTP_REST
 Biblioteca para conexiones REST y SOAP.

 ### Example

``` php
 $settings = [
    'Uri' => 'https://pokeapi.co/api/v2/pokemon/ditto',
    'Format' => 'JSON',
    'Errors' => true
];

echo HTTP::GET($settings);
```
