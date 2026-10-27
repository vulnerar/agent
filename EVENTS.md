# List of Events the Agent collects

## app.info
| Field  | Example  | Description  |
|:---|:---|:---|
| `app_url`  | `https://example.com`  | Application URL  |
| `laravel_version`  | `13.1.0`  | Laravel framework version  |
| `php_version`  | `8.3.0`  | PHP runtime version  |
| `environment`  | `production`  | Application environment  |
| `debug`  | `false`  | Whether debug mode is enabled  |
| `os.name`  | `Ubuntu 26.04 LTS`  | Operating system name  |
| `os.user.uid`  | `33`  | Operating system user uid  |
| `os.user.gid`  | `33`  | Operating system user gid  |
| `os.user.user`  | `www-data`  | Operating system user  |
| `os.user.group`  | `www-data`  | Operating system user  |
| `ip_address`  | `203.0.113.10`  | Application IP address  |

## package.composer
| Field  | Example  | Description  |
|:---|:---|:---|
| `manifest`  | `eyJuYW1lIjoi...`  | Base64-encoded `composer.json`  |
| `lock`  | `eyJfcmVhZG1lIjpb...`  | Base64-encoded `composer.lock`  |

## auth.login
| Field  | Example  | Description  |
|:---|:---|:---|
| `login`  | `john@example.com`  | Login (`email`, `username` or `login`)  |
| `password_sha1`  | `5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8`  | SHA-1 hash of the password  |
| `user.id`  | `1`  | User primary key  |
| `user.type`  | `App\Models\User`  | User model class  |
| `user.name`  | `John Doe`  | User name  |
| `user.login`  | `john@example.com`  | User login (`login`, `username` or `email`)  |
| `ip_address`  | `203.0.113.10`  | Client IP address  |

## auth.failed
| Field  | Example  | Description  |
|:---|:---|:---|
| `login`  | `john@example.com`  | Login (`email`, `username` or `login` credential)  |
| `password`  | `pass**rd**3**`  | Masked password: first 4, next 2 and next 1 characters kept, padded to 14 characters  |
| `user.id`  | `1`  | User primary key  |
| `user.type`  | `App\Models\User`  | User model class  |
| `user.name`  | `John Doe`  | User name  |
| `user.login`  | `john@example.com`  | User login (`login`, `username` or `email`)  |
| `ip_address`  | `203.0.113.10`  | Client IP address  |

## auth.logout
| Field  | Example  | Description  |
|:---|:---|:---|
| `user.id`  | `1`  | User primary key  |
| `user.type`  | `App\Models\User`  | User model class  |
| `user.name`  | `John Doe`  | User name  |
| `user.login`  | `john@example.com`  | User login (`login`, `username` or `email`)  |
| `ip_address`  | `203.0.113.10`  | Client IP address  |

## auth.verified
| Field  | Example  | Description  |
|:---|:---|:---|
| `user.id`  | `1`  | User primary key  |
| `user.type`  | `App\Models\User`  | User model class  |
| `user.name`  | `John Doe`  | User name  |
| `user.login`  | `john@example.com`  | User login (`login`, `username` or `email`)  |
| `ip_address`  | `203.0.113.10`  | Client IP address  |

## http.request
| Field  | Example  | Description  |
|:---|:---|:---|
| `route.name`  | `users.show`  | Route name  |
| `route.path`  | `/users/{user}`  | Route URI pattern  |
| `request.method`  | `POST`  | HTTP method  |
| `request.url`  | `https://example.com/users/1?x=1`  | Full request URL  |
| `request.size`  | `128`  | Request body size in bytes  |
| `request.headers`  | `{"cookie": ["a=[12 bytes redacted]"]}`  | Request headers; `Authorization`, `Proxy-Authorization`, `Cookie` and `X-XSRF-TOKEN` values are redacted  |
| `request.files[].original_name`  | `avatar.png`  | Uploaded file name  |
| `request.files[].extension`  | `png`  | Uploaded file extension  |
| `request.files[].mime_type`  | `image/png`  | Uploaded file MIME type  |
| `request.files[].size`  | `2048`  | Uploaded file size in bytes  |
| `request.files[].sha1`  | `da39a3ee5e6b4b0d3255bfef95601890afd80709`  | SHA-1 of the uploaded file  |
| `request.files[].md5`  | `d41d8cd98f00b204e9800998ecf8427e`  | MD5 of the uploaded file  |
| `response.status`  | `200`  | HTTP status code  |
| `response.size`  | `1024`  | Response size in bytes  |
| `user.id`  | `1`  | User primary key  |
| `user.type`  | `App\Models\User`  | User model class  |
| `user.name`  | `John Doe`  | User name  |
| `user.login`  | `john@example.com`  | User login (`login`, `username` or `email`)  |
| `ip_address`  | `203.0.113.10`  | Client IP address  |