#!/usr/bin/bash

rm -rf /var/www/html/api/scripts/venv

cd /var/www/html/api/scripts/
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
deactivate
