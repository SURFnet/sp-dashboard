# Wait untill the spdashboard is available
while [[ "$(curl -s -o /dev/null -L -w ''%{http_code}'' https://spdashboard.dev.openconext.local/health)" != "200" ]] ; do sleep 5; done
