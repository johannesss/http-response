const payload = {
    body: {
        users: [
            {
                name: '{{ name() }}',
                email: '{{ email() }}',
                uuid: '{{ uuid() }}',
                __repeat: 25
            }
        ]
    }
};

const response = await window.fetch(
    'https://www.http-response.com/json',
    {
        method: 'POST',
        headers: {
            'content-type': 'application/json'
        },
        body: JSON.stringify(payload)
    }
);

const data = await response.json();

/*
{
    "users": [
        {
            "name": "Dr. Kyler Crooks",
            "email": "fbaumbach@braun.org",
            "uuid": "c882cd11-a1ed-3f03-8e72-a1983daabdc2"
        },
        ... 25 more users
    ]
*/