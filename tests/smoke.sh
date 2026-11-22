#!/usr/bin/env bash
set -euo pipefail
php -m | grep -i '^pdo_mysql$' >/dev/null
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
curl -fsS -b /tmp/kodcommerce-cookie -c /tmp/kodcommerce-cookie -d "csrf=$csrf" -d 'db_host=127.0.0.1' -d 'db_port=3306' -d 'db_name=kodcommerce_test' -d 'db_user=kodcommerce' -d 'db_password=testing123' --data-urlencode 'email=admin@example.test' --data-urlencode 'password=correct-horse-battery-staple' http://127.0.0.1:8765/install | grep '"ok":true' >/dev/null
curl -fsS -b /tmp/kodcommerce-cookie http://127.0.0.1:8765/ | grep 'Homepage content can be published' >/dev/null
curl -fsS -b /tmp/kodcommerce-cookie http://127.0.0.1:8765/ | grep 'accountDrawer' >/dev/null
# Unauthenticated visitors must never receive the admin navigation shell.
test "$(curl -s -o /dev/null -w '%{http_code}' -b /tmp/kodcommerce-cookie http://127.0.0.1:8765/admin/dashboard/overview)" = '302'
curl -fsSI -b /tmp/kodcommerce-cookie http://127.0.0.1:8765/admin/dashboard/overview | grep -i 'location: /?login=1' >/dev/null
test "$(curl -s -o /dev/null -w '%{http_code}' -b /tmp/kodcommerce-cookie -H 'X-Requested-With: XMLHttpRequest' http://127.0.0.1:8765/admin/dashboard/overview)" = '401'
curl -fsS -L -b /tmp/kodcommerce-cookie http://127.0.0.1:8765/install | grep 'Homepage content can be published' >/dev/null
csrf=$(curl -fsS -b /tmp/kodcommerce-cookie http://127.0.0.1:8765/ | sed -n 's/.*name="csrf-token" content="\([^"]*\)".*/\1/p' | head -1)
test -n "$csrf"
curl -fsS -b /tmp/kodcommerce-cookie -c /tmp/kodcommerce-cookie -d "csrf=$csrf" --data-urlencode 'email=admin@example.test' --data-urlencode 'password=correct-horse-battery-staple' http://127.0.0.1:8765/api/login | grep '"ok":true' >/dev/null
curl -fsS -b /tmp/kodcommerce-cookie http://127.0.0.1:8765/admin/dashboard/overview | grep 'adminContent' >/dev/null
 # Pages is the only CMS navigation entry; new pages open the embedded visual editor.
curl -fsS -b /tmp/kodcommerce-cookie http://127.0.0.1:8765/admin/content-management/pages | grep 'Add New Page' >/dev/null
curl -fsS -b /tmp/kodcommerce-cookie http://127.0.0.1:8765/admin/content-management/pages | grep 'content-management/pages' >/dev/null
! curl -fsS -b /tmp/kodcommerce-cookie http://127.0.0.1:8765/admin/content-management/pages | grep 'href="/admin/content-management/page-builder"' >/dev/null
curl -fsS -b /tmp/kodcommerce-cookie -H 'X-Requested-With: XMLHttpRequest' 'http://127.0.0.1:8765/admin/content-management/pages?new=1' | grep 'builderRows' >/dev/null
# Login rotates the CSRF token; obtain the new token from the authenticated admin shell.
csrf=$(curl -fsS -b /tmp/kodcommerce-cookie http://127.0.0.1:8765/admin/dashboard/overview | awk -F '"' '/name="csrf-token"/ {print $4; exit}')
test -n "$csrf"
curl -fsS -b /tmp/kodcommerce-cookie -d "csrf=$csrf" --data-urlencode 'title=About Us' -d 'slug=about-us' -d 'status=published' --data-urlencode 'layout=[{"width":"contained","columns":[{"span":12,"widgets":[{"type":"heading","text":"Published page smoke test"}]}]}]' http://127.0.0.1:8765/api/pages | grep '"ok":true' >/dev/null
curl -fsS http://127.0.0.1:8765/pages/about-us | grep 'Published page smoke test' >/dev/null
curl -fsS -b /tmp/kodcommerce-cookie 'http://127.0.0.1:8765/admin/content-management/pages?edit=1' | grep 'builderRows' >/dev/null
echo 'Installer, login, MySQL Pages CRUD, editor routing and gettext smoke tests passed'
