import js from '@eslint/js';
import vue from 'eslint-plugin-vue';
import tseslint from 'typescript-eslint';
import globals from 'globals';

export default tseslint.config(
    js.configs.recommended,
    ...tseslint.configs.recommended,
    ...vue.configs['flat/recommended'],
    {
        files: ['*.vue', '**/*.vue'],
        languageOptions: {
            parserOptions: {
                parser: '@typescript-eslint/parser',
            },
        },
    },
    {
        languageOptions: {
            globals: {
                ...globals.browser,
                ...globals.node,
                ...globals.builtin,
                ...globals.es2021,
            },
        },
    },
    {
        rules: {
            'vue/multi-word-component-names': 'off',
            '@typescript-eslint/no-explicit-any': 'warn',
            '@typescript-eslint/no-unused-vars': ['warn', {
                argsIgnorePattern: '^_',
                varsIgnorePattern: '^_',
                caughtErrorsIgnorePattern: '^_',
            }],
            '@typescript-eslint/ban-ts-comment': 'off',
            'vue/no-v-text-v-html-on-component': 'off',
            'vue/max-attributes-per-line': 'off',
            'vue/html-indent': 'off',
            'vue/singleline-html-element-content-newline': 'off',
            'vue/html-self-closing': 'off',
            'vue/attributes-order': 'off',
            'no-unused-vars': 'off', // handled by @typescript-eslint/no-unused-vars
        },
    },
    {
        // Vitest specs lean on loose doubles/mocks; keep production `src/` any-free.
        files: ['tests/**/*.{ts,tsx,vue,js}', '**/*.{spec,test}.{ts,tsx,vue,js}'],
        rules: {
            '@typescript-eslint/no-explicit-any': 'off',
        },
    },
);
