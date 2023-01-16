# Wait untill the spdashboard is available
while [[ "$(curl -s -o /dev/null -L -w ''%{http_code}'' https://spdashboard.vm.openconext.org/health)" != "200" ]] ; do sleep 5; done
