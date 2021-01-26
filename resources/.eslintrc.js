module.exports = {
  'extends': 'airbnb-base',
  'plugins': ['import', 'mocha'],
  rules: {
    'comma-dangle': 0,
    'func-names': 0,
    'import/newline-after-import': 0,
    'import/no-extraneous-dependencies': [
      'error',
      { 'devDependencies': true }
    ],
    'max-len': 0,
    'new-cap': 0,
    'no-plusplus': 0,
    'no-prototype-builtins': 0,
    'no-restricted-syntax': 0,
    'object-curly-newline': [
      'error',
      {
        'ImportDeclaration': 'never'
      }
    ],
    'prefer-arrow-callback': 0,
    'prefer-destructuring': 0
  },
  'env': {
    'browser': true,
    'es6': true,
    'jquery': true,
    'mocha': true,
    'node': true
  },
  'globals': {
    'document': false,
    'window': false,
    'assert': true
  }
};
