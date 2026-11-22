#!/usr/bin/env bash
set -euo pipefail
php -m | grep -qi '^pdo_mysql$'
mkdir -p storage
test ! -e storage/database.php
php -S 127.0.0.1:8765 -t public public/index.php >/tmp/kodcommerce-php.log 2>&1 &
pid=$!
trap 'kill "$pid" 2>/dev/null || true; rm -f storage/database.php' EXIT
for i in $(seq 1 40); do
  if curl -fsS -c /tmp/kodcommerce-cookie http://127.0.0.1:8765/install -o /tmp/kodcommerce-install.html; then break; fi
  sleep 0.25
done
grep -q 'Initial setup' /tmp/kodcommerce-install.html
csrf=$(sed -n 's/.*name="csrf" value="\([^"]*\)".*/\1/p' /tmp/kodcommerce-install.html | head -1)
test -n "$csrf"
curl -fsS -b /tmp/kodcommerce-cookie -c /tmp/kodcommerce-cookie -d "csrf=$csrf" -d 'db_host=127.0.0.1' -d 'db_port=3306' -d 'db_name=kodcommerce_test' -d 'db_user=kodcommerce' -d 'db_password=testing123' --data-urlencode 'email=admin@example.test' --data-urlencode 'password=correct-horse-battery-staple' http://127.0.0.1:8765/install | grep -q '"ok":true'
curl -fsS -b /tmp/kodcommerce-cookie http://127.0.0.1:8765/ | grep -q 'Homepage content can be published'
curl -fsS -b /tmp/kodcommerce-cookie http://127.0.0.1:8765/ | grep -q 'accountDrawer'
curl -fsS -L -b /tmp/kodcommerce-cookie http://127.0.0.1:8765/install | grep -q 'Homepage content can be published'
csrf=$(curl -fsS -b /tmp/kodcommerce-cookie http://127.0.0.1:8765/ | sed -n 's/.*name="csrf-token" content="\([^"]*\)".*/\1/p' | head -1)
test -n "$csrf"
curl -fsS -b /tmp/kodcommerce-cookie -c /tmp/kodcommerce-cookie -d "csrf=$csrf" --data-urlencode 'email=admin@example.test' --data-urlencode 'password=correct-horse-battery-staple' http://127.0.0.1:8765/api/login | grep -q '"ok":true'
curl -fsS -b /tmp/kodcommerce-cookie http://127.0.0.1:8765/admin/dashboard/overview | grep -q 'adminContent'
# Save a homepage as the authenticated administrator and verify rendered output.
csrf=$(curl -fsS -b /tmp/kodcommerce-cookie http://127.0.0.1:8765/ | sed -n 's/.*name="csrf-token" content="\([^"]*\)".*/\1/p' | head -1)
test -n "$csrf"
curl -fsS -b /tmp/kodcommerce-cookie -d "csrf=$csrf" --data-urlencode 'title=Homepage' --data-urlencode 'layout=[{"width":"contained","columns":[{"span":12,"widgets":[{"type":"heading","text":"CMS MySQL smoke test"}]}]}]' http://127.0.0.1:8765/api/pages/home | grep -q '"ok":true'
curl -fsS -b /tmp/kodcommerce-cookie http://127.0.0.1:8765/ | grep -q 'CMS MySQL smoke test'
echo 'Installer, storefront, login, admin and CMS MySQL smoke tests passed'
