'use strict';


function togglePrivacyQuestionsAnsweredStatusField(){
    let privacyQuestionsStatusContainer = $('.privacy-questions-container');
    let privacyQuestionsEnabledCheckbox = $(".privacy-questions-toggle");
    if (privacyQuestionsEnabledCheckbox.is(':checked')) {
        privacyQuestionsStatusContainer.parent().show(200);
    } else {
        privacyQuestionsStatusContainer.parent().hide();
    }
}

$(document).ready(function(){
    $(".privacy-questions-toggle").on('change', togglePrivacyQuestionsAnsweredStatusField);
    togglePrivacyQuestionsAnsweredStatusField();
});
