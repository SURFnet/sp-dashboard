# Check whether we ran before
if [ -f /etc/first_run_done ]
    then exit
fi

# We wait until mysql runs
while ! mysqladmin ping -h localhost --silent; do
    sleep 1
done

# First we create the spdashboard database and user
mysql -e "create database if not exists spdashboard"
mysql -e "grant all on spdashboard.* to spdrw identified by 'secret'"

# We wait until manage becomes available
while [[ "$(curl -s -o /dev/null -L -w ''%{http_code}'' https://manage.vm.openconext.org/manage/api/health)" != "200" ]] ; do
      sleep 5
done

# Add the SPdashboard SAML client to Manage and push 
pass=$(cat /opt/manage/manage-api-users.yml | grep -A1 sysadmin | grep password | cut -d '"' -f2)
curl  -H 'Content-Type: application/json' -u sysadmin:$pass -d@/usr/local/etc/saml20_sp.json -XPOST https://manage.vm.openconext.org/manage/api/internal/metadata
curl  -u sysadmin:$pass -XGET https://manage.vm.openconext.org/manage/api/internal/push

# Set the Mujina Guest qualifier to "none"
mujina_entity_id=$(curl -s -H  'Content-Type: application/json' -u sysadmin:$pass  -X POST -d '{"ALL_ATTRIBUTES":true, "entityid":"http://mock-idp"}' 'https://manage.vm.openconext.org/manage/api/internal/search/saml20_idp'| python2 -c 'import sys, json; print json.load(sys.stdin)[0]["_id"]')
curl -s -H 'Content-Type: application/json' -u sysadmin:$pass -X PUT -d '{"id": "'$mujina_entity_id'", "type": "saml20_idp", "pathUpdates": { "metaDataFields.coin:guest_qualifier": "None" }}' https://manage.vm.openconext.org/manage/api/internal/merge
curl  -u sysadmin:$pass -XGET https://manage.vm.openconext.org/manage/api/internal/push

# Add the sp-dashboard user to the manage api user list
echo '    - {' >> /opt/manage/manage-api-users.yml
echo '      name: "sp-dashboard",' >> /opt/manage/manage-api-users.yml
echo '      password: "secret",' >> /opt/manage/manage-api-users.yml
echo '      scopes: [ READ, WRITE, PUSH, SYSTEM ]' >> /opt/manage/manage-api-users.yml
echo '      }' >> /opt/manage/manage-api-users.yml
systemctl restart manage
# Add spdashboard to the loadbalancer. We reuse the welcome backend for it 
echo "  backend spdashboard_be" >> /etc/haproxy/haproxy_backend.cfg
echo "  server spd spdashboard_web:80" >> /etc/haproxy/haproxy_backend.cfg
echo "spdashboard.vm.openconext.org spdashboard_be"  >> /etc/haproxy/maps/backends.map
echo "spdashboard.vm.openconext.org" >> /etc/haproxy/acls/validvhostsunrestricted.acl
systemctl reload haproxy

# Finished, make sure we don't run again
touch /etc/first_run_done
