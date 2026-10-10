import { defineConfig } from 'vitepress';

export default defineConfig({
    title: 'Reisetagebuch',
    description: 'Dokumentation',
    base: '/reisetagebuch/',
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
            {
                text: 'Federation',
                items: [
                    {
                        text: 'ActivityPub extensions',
                        link: '/activitypub-extensions',
                    },
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
