const htmlvalidate = require('cypress-html-validate/dist/plugin');

module.exports = (on, config) => {
    htmlvalidate.install(on, {
        "rules": {
            "prefer-native-element": [ "error", {
                "exclude": [ "textbox" ],
            }],
            "require-sri": [ "error", {
                "target": "crossorigin",
            }],
            // Disable meta-rule: the Symfony profiler injects html-validate-disable
            // comments into the DOM that are not under our control.
            "no-unused-disable": "off",
            // webpack encore and Symfony's debug toolbar inject <script type="text/javascript">
            // which html-validate v10 now flags via the script-type rule. Since we don't
            // control these generated attributes, disable the rule.
            "script-type": "off",
        },
    });

    on('task', {
        log(message) {
            console.log(message);
            return null;
        },
        table(message) {
            console.table(message);
            return null;
        },
    });

    return config;
};
