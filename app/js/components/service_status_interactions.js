'use strict';


function togglePrivacyQuestionsAnsweredStatusField(){
    let privacyQuestionsStatusContainer = $('.privacy-questions-container');
    let privacyQuestionsEnabledCheckbox = $(".privacy-questions-toggle");
    if (privacyQuestionsEnabledCheckbox.is(':checked')) {
        privacyQuestionsStatusContainer.find('input[type="radio"]').prop('disabled', false);
    } else {
        privacyQuestionsStatusContainer.find('input[type="radio"]').prop('disabled', true);
    }
}

$(document).ready(function(){
    $(".privacy-questions-toggle").on('change', togglePrivacyQuestionsAnsweredStatusField);
    togglePrivacyQuestionsAnsweredStatusField();
});
