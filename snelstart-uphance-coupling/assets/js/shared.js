async function show_error_from_api(error) {
    if (error instanceof Response) {
        try {
            let data = await error.json();
            if ("error_message" in data) {
                tata.error('', data.error_message);
                return;
            }
        } catch (error) {}
        try {
            if (data.status && data.statusText) {
                tata.error('', `An error occurred! ${data.statusText} (status code ${data.status}).`);
                return;
            }
        } catch (error) {}
    }
    tata.error('', 'An unknown error occurred.');
}