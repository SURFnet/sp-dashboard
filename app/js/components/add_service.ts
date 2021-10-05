import * as $ from 'jquery';

$(() => {
  const teamName = document.querySelector('.teamName') as HTMLInputElement;

  if (!!teamName) {
    const name = document.getElementById('dashboard_bundle_service_type_general_name') as HTMLInputElement;
    const organizationEn = document.getElementById('dashboard_bundle_service_type_general_organizationNameEn') as HTMLInputElement;
    const setTeamName = () => {
      const serviceName = name.value.replace(' ', '_');
      const organizationName = organizationEn.value.replace(' ', '_');
      teamName.value = `spd_${organizationName}_${serviceName}`;
    };

    if (!!name && !!organizationEn) {
      name.addEventListener('input', setTeamName);
      organizationEn.addEventListener('input', setTeamName);
    }
  }
});
