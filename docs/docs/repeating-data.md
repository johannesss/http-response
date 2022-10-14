# Repeating data (JSON)

**This functionality is only available for JSON responses**

http-response makes it possible to repeat list items in JSON responses.
This is done by including the special property ```__repeat``` on the list items you'd like to repeat.

Let me show you by using an example payload:

```
{
    "body": {
        "users": [
            {
                "name": "{{ name() }}",
                "age": "{{ numberBetween(18, 85) }}",
                "uuid": "{{ uuid() }}",
                "uploads": [
                    {
                        "filename": "{{ word() }}.png",
                        "url": "{{ imageUrl(640, 480, 'animals', true) }}",
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

[Test it in the browser](https://www.http-response.com/json?body=%7B%22users%22%3A%5B%7B%22name%22%3A%22%7B%7Bname%28%29%7D%7D%22%2C%22age%22%3A%22%7B%7BnumberBetween%2818%2C85%29%7D%7D%22%2C%22uuid%22%3A%22%7B%7Buuid%28%29%7D%7D%22%2C%22uploads%22%3A%5B%7B%22filename%22%3A%22%7B%7Bword%28%29%7D%7D.png%22%2C%22url%22%3A%22%7B%7BimageUrl%28640%2C480%2C%27animals%27%2Ctrue%29%7D%7D%22%2C%22__repeat%22%3A5%7D%5D%2C%22__repeat%22%3A5%7D%5D%7D)