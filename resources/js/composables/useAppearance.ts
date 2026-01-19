import { onMounted, ref, computed } from 'vue';

type Theme = 'light' | 'dark';

export function updateTheme(value: Theme) {
    if (typeof window === 'undefined') {
        return;
    }

    if (value === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
}

export function initializeTheme() {
    if (typeof window === 'undefined') {
        return;
    }

    const savedTheme = localStorage.getItem('theme') as Theme | null;
    const theme = savedTheme || 'light';
    
    updateTheme(theme);
}

const theme = ref<Theme>('light');

export function useAppearance() {
    onMounted(() => {
        const savedTheme = localStorage.getItem('theme') as Theme | null;
        
        if (savedTheme) {
            theme.value = savedTheme;
            updateTheme(savedTheme);
        } else {
            updateTheme('light');
        }
    });

    function toggleTheme() {
        const newTheme: Theme = theme.value === 'dark' ? 'light' : 'dark';
        theme.value = newTheme;
        localStorage.setItem('theme', newTheme);
        updateTheme(newTheme);
    }

    function setTheme(value: Theme) {
        theme.value = value;
        localStorage.setItem('theme', value);
        updateTheme(value);
    }

    const isDark = computed(() => theme.value === 'dark');

    return {
        theme,
        isDark,
        toggleTheme,
        setTheme,
    };
}
