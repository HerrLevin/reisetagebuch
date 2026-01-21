import { defineConfig } from 'vitepress';

export default defineConfig({
    title: 'Reisetagebuch',
    description: 'Dokumentation',
    themeConfig: {
        nav: [
            { text: 'Home', link: '/' },
            { text: 'Getting Started', link: '/getting-started' },
        ],

        sidebar: [
            {
                text: 'Guide',
                items: [
                    { text: 'Getting Started', link: '/getting-started' },
                    { text: 'Installation', link: '/installation' },
                    { text: 'Dev-Setup', link: '/dev-setup' },
                    { text: 'Configuration', link: '/configuration' },
                ],
            },
        ],

        socialLinks: [
            {
                icon: 'github',
                link: 'https://github.com/herrlevin/reisetagebuch',
            },
        ],
    },
});
