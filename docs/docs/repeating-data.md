# Repeating data (JSON)

**This functionality is only available for JSON responses**

http-response makes it possible to repeat list items in JSON responses.
This is done by including the special property ```__repeat``` on the list items you'd like to repeat.

Let me show you by using an example payload:

```json
{
    "body": {
        "users": [
            {
                "name": "{{ name() }}",
                "age": "{{ numberBetween(18, 85) }}",
                "uuid": "{{ uuid() }}",
                "uploads": [
                    {
                        "filename": "{{ word() }}.{{ fileExtension() }}",
                        "mime_type": "{{ mimeType() }}",
                        "__repeat": 5
                    }
                ],
                "__repeat": 5
            }
        ]
    }
}
```

This payload will generate a list of 5 users with 5 uploads each.  
Each with its own fake data generated.

[Test it in the browser](https://www.http-response.com/json?body=%7B%0A%20%20%20%20%22body%22%3A%20%7B%0A%20%20%20%20%20%20%20%20%22users%22%3A%20%5B%0A%20%20%20%20%20%20%20%20%20%20%20%20%7B%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22name%22%3A%20%22%7B%7B%20name%28%29%20%7D%7D%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22age%22%3A%20%22%7B%7B%20numberBetween%2818%2C%2085%29%20%7D%7D%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22uuid%22%3A%20%22%7B%7B%20uuid%28%29%20%7D%7D%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22uploads%22%3A%20%5B%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%7B%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22filename%22%3A%20%22%7B%7B%20word%28%29%20%7D%7D.%7B%7B%20fileExtension%28%29%20%7D%7D%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22mime_type%22%3A%20%22%7B%7B%20mimeType%28%29%20%7D%7D%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22__repeat%22%3A%205%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%7D%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%5D%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22__repeat%22%3A%205%0A%20%20%20%20%20%20%20%20%20%20%20%20%7D%0A%20%20%20%20%20%20%20%20%5D%0A%20%20%20%20%7D%0A%7D%0A)