$(() => {
  $('b[role="presentation"]').removeAttr('role');
  const switcherContainer = $('#select2-service-switcher-container');
  const title = switcherContainer.attr('title') || 'Select a service';
  switcherContainer.parent()
                   .attr('aria-label', title)
                   .attr('aria-controls', 'select2-service-switcher-container')
                   .removeAttr('aria-labelledby');
  switcherContainer.attr('aria-label', title)
                   .removeAttr('title');
  switcherContainer.on('change', function () {
    const value = $(this).attr('title') || 'Select a service';
    $(this).attr('aria-label', value)
           .removeAttr('title')
           .parent()
           .attr('aria-label', value)
           .attr('aria-controls', 'select2-service-switcher-container')
           .removeAttr('aria-labelledby');
  });

  $('form[name="service_switcher"]').append('<button type="submit" hidden>Submit</button>').append('<label for="service-switcher" hidden>Switch service</label>');
});
