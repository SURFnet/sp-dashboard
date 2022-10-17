# This will import the certificate offered by manage.vm.openconext.org into the local certificate store.

until $(curl -k --output /dev/null --silent --head --fail https://manage.vm.openconext.org); do
    printf '.'
    sleep 1
done

echo QUIT | openssl s_client -connect manage.vm.openconext.org:443  | sed -ne '/BEGIN CERT/,/END CERT/p' > /usr/share/ca-certificates/openconext.pem
echo openconext.pem >> /etc/ca-certificates.conf
update-ca-certificates
