# wahelp/newsletter-service

## Installation

- `composer install` (only for autoload)
- `cat config.example.php >> config.php`
- Paste your database config to `config.php`
- `php db-console.php` and select [0] value

## Docker

```
docker-compose up -d
```

## Routes

- /?module=users&action=import POST. Form data: `users` csv file
- /?module=users&action=list GET. Query params: per_page, page
- /?module=newsletter&action=send POST. JSON body `{name:"", text:""}"`
