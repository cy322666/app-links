#!/bin/sh

echo "Running the queue..."

php app/artisan queue:work --tries=3
#queue:work --queue=bizon_export