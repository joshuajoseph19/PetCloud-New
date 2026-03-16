import { Platform } from 'react-native';

// UPDATE THIS IP with your laptop's current IPv4 address from 'ipconfig'
const DEV_IP = '192.168.1.8';

export const API_BASE_URL = Platform.select({
    web: 'http://localhost/PetCloud',
    android: `http://${DEV_IP}/PetCloud`,
    ios: `http://${DEV_IP}/PetCloud`,
    default: `http://${DEV_IP}/PetCloud`,
});

export const API_URL = `${API_BASE_URL}/mobile_api`;

export const getImageUrl = (path) => {
    if (!path) return 'https://ui-avatars.com/api/?name=Pet+Cloud&background=3b82f6&color=fff';
    if (path.startsWith('http')) return path;
    return `${API_BASE_URL}/${path.replace(/^\//, '')}`;
};

export const fetchWithTimeout = async (url, options = {}, timeout = 10000) => {
    const controller = new AbortController();
    const id = setTimeout(() => controller.abort(), timeout);

    try {
        const response = await fetch(url, {
            ...options,
            signal: controller.signal,
        });
        clearTimeout(id);
        return response;
    } catch (error) {
        clearTimeout(id);
        if (error.name === 'AbortError') {
            throw new Error('Connection timed out. Please check if your server is running and reachable.');
        }
        throw error;
    }
};
