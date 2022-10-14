# Getting started

## Base URL
https://www.http-response.com

## Parameters

| Name                 | Data type   | Default        |  Description                                     |
| ---------------------| ------------|----------------|------------------------------------------------- |
| **status_code**      | integer     | 200            |  Sets the status code of the response            |
| **headers**          | array       | []             |  Sets the response headers                       |
| **body**             | string      | ""             |  Sets the response body                          |

## Simple example

In this example we will generate the following HTTP response using GET and POST requests.

```
HTTP/1.1 200 OK
Content-Type: application/json

{
    "hello": "world"
}
```

### Using a GET request
    https://www.http-response.com/?status_code=200&headers[content-type]=application/json&body={"hello":"world"}

[Open as link](https://www.http-response.com/?status_code=200&headers[content-type]=application/json&body={"hello":"world"})

### Using a POST request

To create the response using a POST request, the JSON payload looks like this.

```
{
    "status_code": 200,
    "headers": {
        "content-type": "application/json"
    },
    "body": {
        "hello": "world"
    }
}
```

### Endpoint for JSON responses

As JSON responses are such a common usecase, there's an endpoint that takes care of setting the
status code to 200 (overridable) and content-type header to application/json.

**GET**
```
https://www.http-response.com/json?body={"hello":"world"}
```

**POST**
```
{
    "body": {
        "hello": "world"
    }
}
```