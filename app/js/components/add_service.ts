import * as $ from 'jquery';

$(() => {
  const teamName = document.querySelector('.teamName') as HTMLInputElement;

  if (!!teamName) {
    const name = document.getElementById('dashboard_bundle_service_type_general_name') as HTMLInputElement;
    const organizationEn = document.getElementById('dashboard_bundle_service_type_general_organizationNameEn') as HTMLInputElement;
    const setTeamName = () => {
      const normalizeName = (nameToNormalize: string) => {
        /**
         * The below was modified from: https://stackoverflow.com/a/37511463/11339541
         * We allow only word characters (=digits, letters &, underscores), spaces and minus signs
         * We do not allow diacritics (letters with a special modifier, like é, è or ô for example)
         */
        // @ts-ignore
        return nameToNormalize.normalize('NFD').replace(/\p{Diacritic}/gu, '').replace(/[^\W\u00a0\u0020\-]/gu, '').replace(/[\u00a0\u0020]/gu, '-');
      };
      const serviceName = normalizeName(name.value);
      const organizationName = normalizeName(organizationEn.value);
      teamName.value = `spd_${organizationName}_${serviceName}`.toLowerCase();
    };

    if (!!name && !!organizationEn) {
      name.addEventListener('input', setTeamName);
      organizationEn.addEventListener('input', setTeamName);
    }
  }
});
