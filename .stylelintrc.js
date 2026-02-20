module.exports = {
  extends: 'stylelint-config-recommended',
  customSyntax: 'postcss-scss',
  rules: {
    'at-rule-no-unknown': [true, {
      ignoreAtRules: ['mixin', 'include', 'function', 'return', 'use']
    }],
    
    // SCSS !default annotation is valid syntax for default variable values
    // Example: $fa-font-path: "../fonts" !default;
    'annotation-no-unknown': [true, {
      ignoreAnnotations: ['default']
    }],
    
    // SCSS functions that stylelint v17 doesn't recognize as valid
    // These are either SCSS built-ins (lighten, darken, rgba) or custom functions (calculateRem, color-mix)
    'function-no-unknown': [true, {
      ignoreFunctions: ['lighten', 'darken', 'color-mix', 'calculateRem', 'rgba']
    }],
    
    // SCSS allows @import rules anywhere, not just at the top
    // This is valid in SCSS but not in standard CSS
    'no-invalid-position-at-import-rule': null,
    
    // stylelint v17 cannot parse SCSS variables in property values
    // Example: border: 1px solid $lighter-grey; (valid SCSS, compiles to actual color)
    // This is a parser limitation, not a code issue
    'declaration-property-value-no-unknown': null,
    
    // stylelint v17 cannot parse SCSS interpolation in media queries
    // Example: @media (max-width: #{$maxSmallPhone}) (valid SCSS, compiles to actual pixels)
    // This is a parser limitation, not a code issue
    'media-query-no-invalid': null,
    
    // Mixins that use & (parent selector) are meant to be included inside other selectors
    // Example: @mixin no-text-decoration { &:hover { ... } } - this is valid SCSS mixin pattern
    // stylelint flags these as "missing scoping root" but they're intentionally unscoped
    'nesting-selector-no-missing-scoping-root': null
  }
};
