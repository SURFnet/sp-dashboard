$(() => {
  $('b[role="presentation"]').removeAttr('role');
  const switcherContainer = $('#select2-service-switcher-container');
  switcherContainer.attr('aria-label', switcherContainer.attr('title') || '');
  switcherContainer.on('change', function () {
    const value = $(this).attr('title') || 'Select a service';
    $(this).attr('aria-label', value);
  });
  $('form[name="service_switcher"]').append('<button type="submit" hidden>Submit</button>').append('<label for="service-switcher" hidden>Switch service</label>');
});
