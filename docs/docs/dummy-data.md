# Dummy data

When creating a mock API response, being able to generate dummy data is very useful.

This project uses [FakerPHP/Faker](https://fakerphp.github.io/) under the hood and makes it available
to use in the response body.

## Generating values

Faker uses what it calls "formatters" to generate values, it is basically a function you call to
generate a fake value. 

Some example formatters: ``name``, ``address``, ``email`` etc.

You can see all available formatters that faker provides here:  
[https://fakerphp.github.io/formatters/](https://fakerphp.github.io/formatters/) (**Make sure to expand "Formatters" to show all**)

To generate a fake value, use the following syntax in the response body:  

```
{{ insertFakerFormatter('arg1', 'arg2') }}
```

where ``insertFakerFormatter`` would be replaced by the
by the faker formatter you would like to use. 

Some of the formatters accepts arguments, which can be provided as shown above.

## Examples

Here's an example GET request generating a JSON response with some fake data:

```
https://www.http-response.com/json?body={"name":"{{ name('female') }}","address":"{{ address() }}","favorite_color":"{{ randomElement(['green', 'red', 'black', 'blue']) }}"}
```

Here's an example GET request generating an HTML response with some fake data:
```
https://www.http-response.com/?headers[content-type]=text/html&body=<h1>Hello, my name is {{ name() }}</h1><p>I live on {{ streetName() }} in {{ city() }}
```

## Locale

Faker makes it possible to change the locale for the generated data.

You can set the locale by providing the parameter ``fake_data_locale`` and setting the value to the
locale you'd like to use.

Example GET request generating a Swedish address:
```
https://www.http-response.com/json?body={"address":"{{ address() }}"}&fake_data_locale=sv_SE
```
 
Example POST payload generating a Swedish address:
```
{
    "body": {
        "address": "{{ address() }}"
    },
    "fake_data_locale": "sv_SE"
}
```

Read more:
[https://fakerphp.github.io/#localization](https://fakerphp.github.io/#localization)

## Persistent data

Maybe you've noticed that the generated data changes on every request?
This is the default when generating data.

If you'd like to get the same data generated on subsequent requests, you can provide the parameter
``fake_data_persist`` and set the value to true.

Example GET request generating the same address on every request:
```
https://www.http-response.com/json?body={"address":"{{ address() }}"}&fake_data_persist=true
```

Example POST payload generating the same address on every request:
```
{
    "body": {
        "address": "{{ address() }}"
    },
    "fake_data_persist": true
}
```