/**
 * theme-switcher.js
 * Maneja el cambio entre modo claro y oscuro
 */

(function() {
    'use strict';

    const THEME_KEY = 'app-theme-preference';
    const DARK_MODE_CLASS = 'dark-mode';

    /**
     * Obtiene la preferencia de tema guardada o la detecta del sistema
     */
    function getThemePreference() {
        // Primero, verifica si hay una preferencia guardada
        const savedTheme = localStorage.getItem(THEME_KEY);
        if (savedTheme) {
            return savedTheme;
        }

        // Si no, detecta la preferencia del sistema
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }

        return 'light';
    }

    /**
     * Aplica el tema al documento
     */
    function applyTheme(theme) {
        if (theme === 'dark') {
            document.documentElement.classList.add(DARK_MODE_CLASS);
            document.body.classList.add(DARK_MODE_CLASS);
        } else {
            document.documentElement.classList.remove(DARK_MODE_CLASS);
            document.body.classList.remove(DARK_MODE_CLASS);
        }
        
        // Guarda la preferencia
        localStorage.setItem(THEME_KEY, theme);
        
        // Dispara un evento personalizado
        window.dispatchEvent(new CustomEvent('themeChange', { detail: { theme } }));
    }

    /**
     * Cambia el tema
     */
    window.switchTheme = function(theme) {
        if (theme === 'light' || theme === 'dark') {
            applyTheme(theme);
        }
    };

    /**
     * Alterna entre claro y oscuro
     */
    window.toggleTheme = function() {
        const currentTheme = document.body.classList.contains(DARK_MODE_CLASS) ? 'dark' : 'light';
        applyTheme(currentTheme === 'dark' ? 'light' : 'dark');
    };

    /**
     * Obtiene el tema actual
     */
    window.getCurrentTheme = function() {
        return document.body.classList.contains(DARK_MODE_CLASS) ? 'dark' : 'light';
    };

    /**
     * Inicializa el tema cuando el DOM está listo
     */
    function initTheme() {
        const preference = getThemePreference();
        applyTheme(preference);

        // Escucha cambios en la preferencia del sistema
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                // Solo aplica si el usuario no ha guardado una preferencia manual
                if (!localStorage.getItem(THEME_KEY)) {
                    applyTheme(e.matches ? 'dark' : 'light');
                }
            });
        }
    }

    // Inicializa cuando el DOM está listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTheme);
    } else {
        initTheme();
    }
})();
