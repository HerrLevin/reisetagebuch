export function useTitle(title: string) {
    const appName = import.meta.env.VITE_APP_NAME || 'Reisetagebuch';
    document.title = `${title} - ${appName}`;
}
