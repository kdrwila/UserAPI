# UserAPI

# Requirements:
- php >= 7.1.3 w/ xml, dom, zip, mbstring, memcached, sqlite, curl extensions
- memcached server ( not really required, API will work without it, but results won't be cached )
- composer

# Instalation:
- clone repository or download zip archive and extract it.
- run `composer install` or if you have composer installed locally `php composer.phar install` to install all dependencies.
- run `php bin/console doctrine:migrations:migrate` to create empty database.

# Runing the server:
- run `php bin/console server:run`
- by default api should be available at http://127.0.0.1:8000, if port 8000 is taken, it will try bind nextones i.e. http://127.0.0.1:8001, http://127.0.0.1:8002

# Endpoints

| Endpoint | Methods | Fields* | Return | Description |
| --- | --- | --- | --- | --- |
| `/api/sign-up` | POST | `email(string)`<br/> `password(string)`<br/> `name(string)` | `message(string)`<br/> `id(int)`<br/> `apiToken(string)`  | Used to register new user, in return user gets unique API token and own ID | 
| `/api/sign-in` | POST | `email(string)`<br/> `password(string)` | `message(string)`<br/> `id(int)`<br/> `apiToken(string)`  | Used to sign in, in return user gets new unique API token and own ID |
| `/api/sign-out` | GET | *none* | `message(string)`  | Used to sign out, redirects to `/signed-out` |
| `/api/me` | GET | *none* | `id(int)`<br/> `name(string)`<br/> `email(string)`  | Informations about current authenticated user |
| `/api/users` | GET | *none* | `users(array)` {<br/> `id(int)`<br/> `name(string)`<br/> `email(string)`<br/> } | List of all users |
| `/api/users/[var/value]*` | GET | *none* | `users(array)` {<br/> `id(int)`<br/> `name(string)`<br/> `email(string)`<br/> } | Searching users, available query keys: email, name. Order is not important i.e. `/api/users/name/john/`, `/api/users/email/gmail/name/john/` |
| `/api/users/[id]` | GET | *none* | `id(int)`<br/> `name(string)`<br/> `email(string)` | Information about user with provided ID |
| `/api/users/[id]` | POST, PUT | `password(string)`<br/> `name(string)` | `message(string)` | Used to update user password and name, user can only update own data |
| `/api/users/[id]` | DELETE | *none* | `message(string)` | Used to delete users, user can delete all accounts beside own |

* All requests fields should be sent in JSON

# Authentication

There are two types of authentication:
- Default Symfony Guard one, session is created at endpoints `/api/sign-up` and `/api/sign-in` and closed at `/api/sign-out`
- API token, after login or register you will get api token, add it to your request headers like this `X-AUTH-TOKEN: YOUR_API_TOKEN` and you good to go.

# Return Types

- JSON - default return type, use default endpoints or add `/json` at end i.e. `/api/users`, `/api/users/json`
- XML - add `/xml` at end of each endpoint i.e. `/api/sign-up/xml`, `/api/users/xml/`

# Functional Tests

To run functional tests run `./bin/phpunit` command.
