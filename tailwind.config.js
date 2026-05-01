import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Colores UTS Bucaramanga - Verde profesional
                uts: {
                    50: "#f0f8f1",
                    100: "#d4f0db",
                    200: "#a8e0b7",
                    300: "#7cd193",
                    400: "#51c16f",
                    500: "#2E7D32", // Verde primario UTS
                    600: "#1B5E20", // Verde oscuro UTS
                    700: "#145a1a",
                    800: "#0d3816",
                    900: "#071c0f",
                },
            },
            boxShadow: {
                soft: "0 2px 8px rgba(0, 0, 0, 0.08)",
                subtle: "0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24)",
                elevated: "0 4px 12px rgba(0, 0, 0, 0.15)",
            },
            backdropBlur: {
                xs: "2px",
            },
        },
    },

    plugins: [forms],
};
