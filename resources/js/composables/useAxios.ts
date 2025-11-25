import axios, { type AxiosInstance, type AxiosRequestConfig } from 'axios';

let axiosInstance: AxiosInstance | null = null;

export const useAxios = () => {
    if (!axiosInstance) {
        axiosInstance = axios.create({
            baseURL: '/',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        // Add CSRF token to all requests
        axiosInstance.interceptors.request.use((config) => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                config.headers['X-CSRF-TOKEN'] = csrfToken;
            }
            return config;
        });

        // Handle errors
        axiosInstance.interceptors.response.use(
            (response) => response,
            (error) => {
                console.error('Axios error:', error);
                return Promise.reject(error);
            }
        );
    }

    return axiosInstance;
};

