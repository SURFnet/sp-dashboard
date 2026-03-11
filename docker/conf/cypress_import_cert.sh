# This will import the certificate offered by manage.dev.openconext.local into the local certificate store.

until wget -q --spider --no-check-certificate https://manage.dev.openconext.local/internal/health 2>/dev/null; do
	printf '.'
	sleep 1
done

echo QUIT | openssl s_client -connect manage.dev.openconext.local:443 | sed -ne '/BEGIN CERT/,/END CERT/p' >/usr/share/ca-certificates/openconext.pem
echo openconext.pem >>/etc/ca-certificates.conf
update-ca-certificates
