jQuery(function($) {

    // Drag & Drop Banks
    $(".chosen_api .radio-list").sortable({
        stop: function() {
            $('.ordered-checkbox').each(function(index) {
                $(this).attr('data-order', index + 1);
            });
        }
    });
    $(".chosen_api .radio-list").disableSelection();

});






/* BANKS TESTING APIS */


// Pixabay
document.addEventListener("DOMContentLoaded", function() {
    const btnPixabay    = document.getElementById("btnPixabay");
    const resultPixabay = document.getElementById("resultPixabay");
    const apiKeyInput   = document.querySelector('input[name="ASI_plugin_banks_settings[pixabay][apikey]"]');
    const imagePixabay  = document.querySelector("#resultPixabay img");

    if (!btnPixabay || !resultPixabay || !apiKeyInput || !imagePixabay) {
        return;
    }

    btnPixabay.addEventListener("click", function() {

        imagePixabay.classList.remove("hidden");

        fetch(apisTestingAjax.ajaxurl, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "action=test_apis&apibank=pixabay&nonce=" + apisTestingAjax.nonce + "&apikey=" + apiKeyInput.value
        })
        .then(response => response.json())
        .then(data => {
            const parsedData = JSON.parse(data.data);

            if (data.success && parsedData.hits && parsedData.hits.length > 0) {
                resultPixabay.innerHTML = '<span class="text-success">' + apisTestingAjax.successful_testing + '</span>';
            } else {
                resultPixabay.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_key + '</span>';
            }
        })
        .catch(error => {
            resultPixabay.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_key + ' or ' + apisTestingAjax.error_testing + '</span>';
        });
    });
});





// Dall-e
document.addEventListener("DOMContentLoaded", function() {
    const btnDalle          = document.getElementById("btnDalle");
    const resultDalle       = document.getElementById("resultDalle");
    const apiKeyInputDalle  = document.querySelector('input[name="ASI_plugin_banks_settings[dallev1][apikey]"]');
    const imageDalle        = document.querySelector("#resultDalle img");

    if (!btnDalle || !resultDalle || !apiKeyInputDalle || !imageDalle) {
        return;
    }

    btnDalle.addEventListener("click", function() {

        imageDalle.classList.remove("hidden");

        fetch(apisTestingAjax.ajaxurl, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "action=test_apis&apibank=dalle&nonce=" + apisTestingAjax.nonce + "&apikey=" + apiKeyInputDalle.value
        })
        .then(response => response.json())
        .then(data => {
            const parsedData = JSON.parse(data.data);
            if (data.success && parsedData.data && parsedData.data.length > 0) {
                resultDalle.innerHTML = '<span class="text-success">' + apisTestingAjax.successful_testing + '</span>';
            } else {
                resultDalle.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_key + '</span>';
            }
        })
        .catch(error => {
            resultDalle.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_testing + '</span>';
        });
    });
});





// Stability AI
document.addEventListener("DOMContentLoaded", function() {
    const btnStability          = document.getElementById("btnStability");
    const resultStability       = document.getElementById("resultStability");
    const apiKeyInputStability  = document.querySelector('input[name="ASI_plugin_banks_settings[stability][apikey]"]');
    const imageStability        = document.querySelector("#resultStability img");

    if (!btnStability || !resultStability || !apiKeyInputStability || !imageStability) {
        return;
    }

    btnStability.addEventListener("click", function() {

        imageStability.classList.remove("hidden");

        //  Sending the request to check the API Stability AI key
        fetch("https://api.stability.ai/v1/user/account", {
            method: "GET",
            headers: {
                "Authorization": "Bearer " + apiKeyInputStability.value
            }
        })
        .then(response => {
            if (response.status === 200) {
                resultStability.innerHTML = '<span class="text-success">' + apisTestingAjax.successful_testing + '</span>';
            } else if (response.status === 401) {
                resultStability.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_key + '</span>';
            } else {
                resultStability.innerHTML = '<span class="text-warning">Erreur inattendue : ' + response.status + '</span>';
            }
            return response.json();
        })
        .catch(error => {
            resultStability.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_testing + '</span>';
        });
    });
});




// Gemini
document.addEventListener("DOMContentLoaded", function() {
    const btnGemini          = document.getElementById("btnGemini");
    const resultGemini       = document.getElementById("resultGemini");
    const apiKeyInputGemini  = document.querySelector('input[name="ASI_plugin_banks_settings[gemini][apikey]"]');
    const imageGemini        = document.querySelector("#resultGemini img");

    if (!btnGemini || !resultGemini || !apiKeyInputGemini || !imageGemini) {
        return;
    }

    btnGemini.addEventListener("click", function() {

        imageGemini.classList.remove("hidden");

        fetch(apisTestingAjax.ajaxurl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                action:  "asi_test_apis",
                apibank: "gemini",
                nonce:   apisTestingAjax.nonce,
                apikey:  apiKeyInputGemini.value
            })
        })
        .then(response => response.json())
        .then(data => {
            try {
                const parsed = JSON.parse(data.data);
                if (data.success && parsed.models && parsed.models.length > 0) {
                    resultGemini.innerHTML = '<span class="text-success">' + apisTestingAjax.successful_testing + '</span>';
                } else {
                    resultGemini.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_key + '</span>';
                }
            } catch (e) {
                resultGemini.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_testing + '</span>';
            }
        })
        .catch(() => {
            resultGemini.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_testing + '</span>';
        });
    });
});


// Cloudflare Workers AI
document.addEventListener("DOMContentLoaded", function() {
    const btnWorkersAI      = document.getElementById("btnWorkersAI");
    const resultWorkersAI   = document.getElementById("resultWorkersAI");
    const tokenInputWorkers = document.querySelector('input[name="ASI_plugin_banks_settings[workers_ai][api_token]"]');
    const accountInput      = document.querySelector('input[name="ASI_plugin_banks_settings[workers_ai][account_id]"]');
    const imageWorkersAI    = document.querySelector("#resultWorkersAI img");

    if (!btnWorkersAI || !resultWorkersAI || !tokenInputWorkers || !accountInput || !imageWorkersAI) {
        return;
    }

    btnWorkersAI.addEventListener("click", function() {

        imageWorkersAI.classList.remove("hidden");

        fetch(apisTestingAjax.ajaxurl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                action:     "asi_test_apis",
                apibank:    "workers_ai",
                nonce:      apisTestingAjax.nonce,
                apikey:     tokenInputWorkers.value,
                account_id: accountInput.value
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultWorkersAI.innerHTML = '<span class="text-success">' + apisTestingAjax.successful_testing + '</span>';
            } else {
                const errorMessage = data.data ? data.data : apisTestingAjax.error_key;
                resultWorkersAI.innerHTML = '<span class="text-warning">' + errorMessage + '</span>';
            }
        })
        .catch(() => {
            resultWorkersAI.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_testing + '</span>';
        });
    });
});



// Replicate
document.addEventListener("DOMContentLoaded", function() {
    const btnReplicate    = document.getElementById("btnReplicate");
    const resultReplicate = document.getElementById("resultReplicate");
    const apiKeyInput     = document.querySelector('input[name="ASI_plugin_banks_settings[replicate][apitoken]"]');
    const imageReplicate  = document.querySelector("#resultReplicate img");

    if (!btnReplicate || !resultReplicate || !apiKeyInput || !imageReplicate) {
        return;
    }

    btnReplicate.addEventListener("click", function() {
        // show spinner while testing
        imageReplicate.classList.remove("hidden");

        // send AJAX request to test Replicate API key
        fetch(apisTestingAjax.ajaxurl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                action:  "asi_test_apis",
                apibank: "replicate",
                nonce:   apisTestingAjax.nonce,
                apikey:  apiKeyInput.value
            })
        })
        .then(response => response.json())
        .then(data => {
            // parse the JSON string we got back
            const parsed = JSON.parse(data.data);

            // check for a non-empty 'results' array
            if ( data.success && parsed.results && parsed.results.length > 0 ) {
                resultReplicate.innerHTML = '<span class="text-success">' + apisTestingAjax.successful_testing + '</span>';
            } else {
                resultReplicate.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_key + '</span>';
            }
        })
        .catch(error => {
            // network or unexpected error
            resultReplicate.innerHTML = '<span class="text-warning">' 
                + apisTestingAjax.error_key + ' or ' 
                + apisTestingAjax.error_testing + '</span>';
        });
    });
});






// Youtube
document.addEventListener("DOMContentLoaded", function() {
    const btnYoutube    = document.getElementById("btnYouTube");
    const resultYoutube = document.getElementById("resultYoutube");
    const apiKeyInput   = document.querySelector('input[name="ASI_plugin_banks_settings[youtube][apikey]"]');
    const imageYt       = document.querySelector("#resultYoutube img");

    if (!btnYoutube || !resultYoutube || !apiKeyInput || !imageYt) {
        return;
    }

    btnYoutube.addEventListener("click", function() {

        imageYt.classList.remove("hidden");

        fetch(apisTestingAjax.ajaxurl, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "action=test_apis&apibank=youtube&nonce=" + apisTestingAjax.nonce + "&apikey=" + apiKeyInput.value
        })
        .then(response => response.json())
        .then(data => {
            const parsedData = JSON.parse(data.data);
            if (data.success && parsedData.items && parsedData.items.length > 0) {
                resultYoutube.innerHTML = '<span class="text-success">' + apisTestingAjax.successful_testing + '</span>';
            } else {
                resultYoutube.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_key + '</span>';
            }
        })
        .catch(error => {
            resultYoutube.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_testing + '</span>';
        });
    });
});





// Unsplash
document.addEventListener("DOMContentLoaded", function() {
    const btnUnsplash       = document.getElementById("btnUnsplash");
    const resultUnsplash    = document.getElementById("resultUnsplash");
    const apiKeyInput       = document.querySelector('input[name="ASI_plugin_banks_settings[unsplash][apikey]"]');
    const imageUnsplash     = document.querySelector("#resultUnsplash img");

    if (!btnUnsplash || !resultUnsplash || !apiKeyInput || !imageUnsplash) {
        return;
    }

    btnUnsplash.addEventListener("click", function() {

        imageUnsplash.classList.remove("hidden");

        fetch(apisTestingAjax.ajaxurl, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "action=test_apis&apibank=unsplash&nonce=" + apisTestingAjax.nonce + "&apikey=" + apiKeyInput.value
        })
        .then(response => response.json())
        .then(data => {
            const parsedData = JSON.parse(data.data);
            if (data.success && parsedData.results && parsedData.results.length > 0) {
                resultUnsplash.innerHTML = '<span class="text-success">' + apisTestingAjax.successful_testing + '</span>';
            } else {
                resultUnsplash.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_key + '</span>';
            }
        })
        .catch(error => {
            resultUnsplash.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_testing + '</span>';
        });
    });
});





// Pexels
document.addEventListener("DOMContentLoaded", function() {
    const btnPexels         = document.getElementById("btnPexels");
    const resultPexels      = document.getElementById("resultPexels");
    const apiKeyInput       = document.querySelector('input[name="ASI_plugin_banks_settings[pexels][apikey]"]');
    const imagePexels       = document.querySelector("#resultPexels img");

    if (!btnPexels || !resultPexels || !apiKeyInput || !imagePexels) {
        return;
    }

    btnPexels.addEventListener("click", function() {

        imagePexels.classList.remove("hidden");

        fetch(apisTestingAjax.ajaxurl, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "action=test_apis&apibank=pexels&nonce=" + apisTestingAjax.nonce + "&apikey=" + apiKeyInput.value
        })
        .then(response => response.json())
        .then(data => {
            const parsedData = JSON.parse(data.data);
            if (data.success && parsedData.photos && parsedData.photos.length > 0) {
                resultPexels.innerHTML = '<span class="text-success">' + apisTestingAjax.successful_testing + '</span>';
            } else {
                resultPexels.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_key + '</span>';
            }
        })
        .catch(error => {
            resultPexels.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_testing + '</span>';
        });
    });
});





// Envato Elements - DISABLED (no longer working)
/*
document.addEventListener("DOMContentLoaded", function() {
    const btnEnvato         = document.getElementById("btnEnvato");
    const resultEnvato      = document.getElementById("resultEnvato");
    const envatoTokenInput  = document.querySelector('input[name="ASI_plugin_banks_settings[envato][envato_token]"]');
    const imageEnvato       = document.querySelector("#resultEnvato img");

    btnEnvato.addEventListener("click", function() {

        imageEnvato.classList.remove("hidden");
        
        fetch(apisTestingAjax.ajaxurl, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "action=test_apis&apibank=envato&nonce=" + apisTestingAjax.nonce + "&apikey=" + envatoTokenInput.value
        })
        .then(response => response.json())
        .then(data => {
            const parsedData = JSON.parse(data.data);

            if (data.success && ( 'paid' === parsedData.subscription_status )  ) {
                resultEnvato.innerHTML = '<span class="text-success">' + apisTestingAjax.successful_testing + '</span>';
            } else {
                resultEnvato.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_key + '</span>';
            }
        })        
        .catch(error => {
            resultEnvato.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_testing + '</span>';
        });
    });
});
*/


// Google Image API
document.addEventListener("DOMContentLoaded", function() {
    const btnGoogleImage = document.getElementById("btnGoogleImage");
    const resultGoogleImage = document.getElementById("resultGoogleImage");
    const apiKeyInput = document.querySelector('input[name="ASI_plugin_banks_settings[googleimage][apikey]"]');
    const cxIdInput = document.querySelector('input[name="ASI_plugin_banks_settings[googleimage][cxid]"]');
    const imageGoogleImage = document.querySelector("#resultGoogleImage img");

    if (!btnGoogleImage || !resultGoogleImage || !apiKeyInput || !cxIdInput || !imageGoogleImage) {
        return;
    }

    btnGoogleImage.addEventListener("click", function() {
            imageGoogleImage.classList.remove("hidden");

            fetch(apisTestingAjax.ajaxurl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "action=asi_test_apis&apibank=google_image&nonce=" + apisTestingAjax.nonce + "&apikey=" + apiKeyInput.value + "&cxid=" + cxIdInput.value
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const parsedData = JSON.parse(data.data);
                    if (parsedData.items && parsedData.items.length > 0) {
                        resultGoogleImage.innerHTML = '<span class="text-success">' + apisTestingAjax.successful_testing + '</span>';
                    } else {
                        resultGoogleImage.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_key + '</span>';
                    }
                } else {
                    resultGoogleImage.innerHTML = '<span class="text-warning">' + (data.data || apisTestingAjax.error_key) + '</span>';
                }
            })
            .catch(error => {
                resultGoogleImage.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_testing + '</span>';
            });
        });
});
