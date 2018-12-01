# UserAPI

# Requirements:
- composer
- php >= 7.1.3

# Instalation:
- clone repository or download zip archive and extract it.
- run `composer install` or if you have composer installed locally `php composer.phar install` to install all dependencies.
- run `php bin/console doctrine:migrations:migrate` to create empty database.

# Runing the server:
- run `php bin/console server:run`
- by default api sould be available at http://127.0.0.1:8000

# Endpoints

| Endpoint | Methods | Fields | Return | Description |
| --- | --- | --- | --- | --- |
| `/api/sign-up` | POST | email (string) password (string) name(string) | message (string) id (int) apiToken (string)  | Used to register new user, in return user gets unique API token and own ID | 
| `/api/sign-in` | POST | email (string) password (string) | message (string) id (int) apiToken (string)  | Used to sign in, in return user gets new unique API token and own ID |
| `/api/sign-out` | GET | *none* | message (string) id (int) apiToken (string)  | Used to sign out, redirects to `/` |
| `/api/me` | GET | *none* | id (int) name (string) email (string)  | Informations about current authenticated user |
| `/api/users` | GET | *none* | users (array) { id (int) name (string) email (string) } | List of all users |
| `/api/users/[var/value]*` | GET | *none* | users (array) { id (int) name (string) email (string) } | Searching users, available query keys: email, name. Order is not important i.e. `/api/users/name/john/`, `/api/users/email/gmail/name/john/` |
| `/api/users/[id]` | GET | *none* | id (int) name (string) email (string) | Information about user with provided ID |
| `/api/users/[id]` | POST, PUT | password (string) name(string) | message (string) | Used to update user password and name, user can only update own data |
| `/api/users/[id]` | DELETE | *none* | message (string) | Used to delete users, user can delete all accounts beside own |

# Return Types

- JSON - default return type, use default endpoints or add `/json` at end i.e. `/api/users`, `/api/users/json`
- XML - add `/xml` at end of each endpoint i.e. `/api/sign-up/xml`, `/api/users/xml/`
