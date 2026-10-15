#!/bin/sh
set -e

# Clear and cache configurations
su -s /bin/sh -c "php artisan optimize --no-interaction" www-data

# Ensure storage link exists
su -s /bin/sh -c "php artisan storage:link --no-interaction" www-data

# Run the default command
exec "$@"
