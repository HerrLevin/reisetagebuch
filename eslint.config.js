import js from '@eslint/js';
import prettierConfig from '@vue/eslint-config-prettier';
import {
    defineConfigWithVueTs,
    vueTsConfigs,
} from '@vue/eslint-config-typescript';
import pluginVue from 'eslint-plugin-vue';

export default defineConfigWithVueTs(
    {
        name: 'app/files-to-lint',
        files: ['**/*.{js,ts,vue}'],
    },
    js.configs.recommended,
    ...pluginVue.configs['flat/recommended'],
    prettierConfig,
    vueTsConfigs.recommended,
    {
        ignores: [
            'vendor',
            'node_modules',
            'public',
            'bootstrap/ssr',
            'tailwind.config.js',
            'resources/js/components/ui/*',
            'docs/.vitepress/cache/**',
            'docs/.vitepress/dist/**',
        ],
    },
    {
        rules: {
            'vue/multi-word-component-names': 'off',
            'no-undef': 'off',
        },
    },
);
