# Preparing for production
## Install Docker
Ensure Docker and Docker Compose are installed and running.

## Configure environment variables
Copy the example environment file and update values as needed.
```shell
cp .env.example .env
```
Edit `.env` to match your environment (app URL, etc).

### Generate `APP_KEY` (only if blank)
If `APP_KEY` is currently empty, generate it and paste the output into `.env`.
```shell
docker compose -f compose.prod.yml exec app su -s /bin/sh -c "php artisan key:generate --show" www-data
```

## Deploy Docker application
If the application is already deployed, gracefully stop Horizon workers before deploying.
```shell
docker compose -f compose.prod.yml exec app su -s /bin/sh -c "php artisan horizon:terminate --no-interaction" www-data
```
Run the following commands.
```shell
docker compose -f compose.prod.yml up -d --build
```
If this is the first time running the application, run the following commands.
```shell
docker compose -f compose.prod.yml exec app su -s /bin/sh -c "php artisan permission:create-role administrator web --no-interaction" www-data
```
```shell
docker compose -f compose.prod.yml exec app su -s /bin/sh -c "php artisan app:configure-telegram-bot-webhook --no-interaction" www-data
```
___

# Domain change instructions
If the application domain or Telegram Secret Token changed, reset the Telegram webhook by running the following command inside the `app` container.
```shell
docker compose -f compose.prod.yml exec app su -s /bin/sh -c "php artisan app:configure-telegram-bot-webhook --no-interaction" www-data
```
