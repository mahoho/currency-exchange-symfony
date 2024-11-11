# Currency Exchange Demo App
Written using Symfony 7. Multiple data sources could be used with default and fallback to backup sources. Cross source conversion is also supported. 

## Setup
- Start database server with `docker-compose up -d`
- Create empty database with `symphony console doctrine:database:create`
- Create database structure with `symphony console doctrine:migrations:migrate`
- Populate rate sources with `symphony console doctrine:fixtures:load --group=rates_sources --append`

## Test
Make sure to set up test env database before running tests:
- `php bin/console doctrine:database:create --env=test`
- `php bin/console doctrine:migrations:migrate --env=test`
- `php bin/console doctrine:fixtures:load --env=test`
- Run tests: `php bin/phpunit`


## Usage
Params:
`amount` - numeric > 0 
`from` - from currency code (ISO 4217)
`to` - to currency code (ISO 4217)

Both GET and POST are supported.

Sample: `/api/exchange/convert?amount=100&from=USD&to=JPY`
will return JSON with following structure:
```
{
  "amount": 100,
  "from": "USD",
  "to": "JPY",
  "convertedAmount": 15241.36651
}
```
