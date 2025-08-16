// Legacy ESLint config - keeping for compatibility
module.exports = {
  env: {
    browser: true,
    es2021: true,
    node: true,
  },
  extends: [
    'eslint:recommended',
    'plugin:vue/vue3-essential',
    '@vue/prettier'
  ],
  parserOptions: {
    ecmaVersion: 12,
    sourceType: 'module',
  },
  plugins: [
    'vue',
  ],
  rules: {
    'vue/multi-word-component-names': 'off',
    'vue/no-unused-vars': 'error',
    'no-unused-vars': 'warn',
    'no-console': 'warn',
    'prefer-const': 'error',
  },
  ignorePatterns: [
    'public/**',
    'vendor/**',
    'node_modules/**',
    'storage/**',
    'bootstrap/cache/**'
  ]
}