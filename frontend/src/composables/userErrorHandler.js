import { ref } from 'vue';

export function useErrorHandler() {
    const errors = ref({});
    const errorMessage = ref('');

    /**
     * Handle API errors from Axios responses
     * @param {Error} error - The error object from the API call
     */
    const handleError = (error) => {
        clearErrors();

        if (error.response) {
            const { status, data } = error.response;

            if (status === 422 && data.errors) {
                errors.value = data.errors;
            }
            else if (data.message) {
                errorMessage.value = data.message;
            }
            else {
                switch (status) {
                    case 400:
                        errorMessage.value = 'Bad request. Please check your input.';
                        break;
                    case 401:
                        errorMessage.value = 'Unauthorized. Please log in.';
                        break;
                    case 403:
                        errorMessage.value = 'Access forbidden.';
                        break;
                    case 404:
                        errorMessage.value = 'Resource not found.';
                        break;
                    case 500:
                        errorMessage.value = 'Server error. Please try again later.';
                        break;
                    default:
                        errorMessage.value = 'An unexpected error occurred.';
                }
            }
        } else if (error.request) {
            errorMessage.value = 'Network error. Please check your connection.';
        } else {
            errorMessage.value = error.message || 'An unexpected error occurred.';
        }

        console.error('Error:', error);
    };
    
    const clearErrors = () => {
        errors.value = {};
        errorMessage.value = '';
    };

    /**
     * Get error message for a specific field
     * @param {string} field - The field name
     * @returns {string} - The error message
     */
    const getFieldError = (field) => {
        if (errors.value[field]) {
            return Array.isArray(errors.value[field])
                ? errors.value[field][0]
                : errors.value[field];
        }
        return '';
    };

    /**
     * Check if there are any errors
     * @returns {boolean}
     */
    const hasErrors = () => {
        return Object.keys(errors.value).length > 0 || errorMessage.value !== '';
    };

    return {
        errors,
        errorMessage,
        handleError,
        clearErrors,
        getFieldError,
        hasErrors,
    };
}