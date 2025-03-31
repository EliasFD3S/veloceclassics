let PRIX_KM, COUT_CARBURANT, CONSOMMATION_MOYENNE, FRAIS_USURE;
let services = [];
let directionsService;

document.addEventListener('DOMContentLoaded', function() {
    // Récupérer les constantes depuis l'API
    fetch('/api/get-constants.php')
        .then(response => response.json())
        .then(constants => {
            PRIX_KM = parseFloat(constants['PRIX_KM']);
            COUT_CARBURANT = parseFloat(constants['COUT_CARBURANT']);
            CONSOMMATION_MOYENNE = parseFloat(constants['CONSOMMATION_MOYENNE']);
            FRAIS_USURE = parseFloat(constants['FRAIS_USURE']);
        })
        .catch(error => console.error('Erreur lors du chargement des constantes:', error));

    const vehicleSelect = document.querySelector('#vehicle-select');
    const vehiclePriceElement = document.querySelector('.vehicle-price');
    const totalPriceElement = document.querySelector('.total-price');
    
    // Charger les véhicules dans le select
    fetch('/api/get-vehicles-select.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(vehicles => {
            vehicleSelect.innerHTML = '<option value="">Choisissez votre véhicule de collection</option>';
            
            vehicles.forEach(vehicle => {
                const option = document.createElement('option');
                option.value = vehicle.id;
                option.textContent = vehicle.name;
                option.dataset.price = vehicle.price;
                vehicleSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Erreur lors du chargement des véhicules:', error);
            vehicleSelect.innerHTML = '<option value="">Erreur de chargement des véhicules</option>';
        });

    // Mettre à jour le prix quand un véhicule est sélectionné
    vehicleSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const price = selectedOption.dataset.price || '--';
        vehiclePriceElement.textContent = price !== '--' ? `${price} €` : '-- €';
        
        updateTotal();
    });

    // Récupérer la clé API et initialiser l'autocomplétion
    fetch('/api/config.php')
        .then(response => response.json())
        .then(config => {
            if (!config.apiKey) {
                throw new Error('Clé API non trouvée');
            }
            
            // Charger le script Google Maps de manière plus robuste
            loadGoogleMapsScript(config.apiKey);
        })
        .catch(error => {
            console.error('Erreur:', error);
            afficherErreurGoogleMaps();
        });

    // Charger les services depuis l'API
    fetch('/api/get-services.php')
        .then(async response => {
            if (!response.ok) {
                const text = await response.text();
                throw new Error(`Erreur HTTP: ${response.status} - ${text}`);
            }
            
            const text = await response.text();
            
            // Vérifier si la réponse est vide
            if (!text || !text.trim()) {
                throw new Error('La réponse du serveur est vide');
            }

            try {
                const data = JSON.parse(text);
                console.log('Données reçues:', text); // Debug
                return data;
            } catch (e) {
                console.error('Réponse brute du serveur:', text);
                throw new Error(`Erreur de parsing JSON: ${e.message}`);
            }
        })
        .then(data => {
            if (!Array.isArray(data)) {
                throw new Error('Format de données invalide: tableau attendu');
            }
            console.log('Services reçus:', data);
            services = data;
            afficherServices();
        })
        .catch(error => {
            console.error('Erreur détaillée lors du chargement des services:', error);
            const servicesContainer = document.querySelector('.services-optionnels');
            if (servicesContainer) {
                servicesContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Erreur lors du chargement des services</strong><br>
                        Détails: ${error.message}
                    </div>`;
            }
        });
});

// Nouvelle fonction pour charger le script Google Maps
function loadGoogleMapsScript(apiKey) {
    console.log('Tentative de chargement de Google Maps...');
    
    if (window.google) {
        console.log('Google Maps déjà chargé');
        initGoogleServices();
        return;
    }

    window.initGoogleServices = initGoogleServices;
    
    const script = document.createElement('script');
    const url = `https://maps.googleapis.com/maps/api/js`;
    
    // Ajout des paramètres séparément pour une meilleure lisibilité
    const params = new URLSearchParams({
        key: apiKey,
        libraries: 'places',
        callback: 'initGoogleServices',
        language: 'fr',
        region: 'FR',
        loading: 'async' // Ajout du paramètre de chargement asynchrone
    });

    script.src = `${url}?${params.toString()}`;
    script.async = true;
    script.defer = true;
    
    script.onerror = (error) => {
        console.error('Erreur de chargement Google Maps:', error);
        afficherErreurGoogleMaps('Erreur lors du chargement de Google Maps. Vérifiez la console pour plus de détails.');
    };
    
    document.head.appendChild(script);
}

// Fonction pour afficher l'erreur
function afficherErreurGoogleMaps(message) {
    const errorMessage = document.createElement('div');
    errorMessage.className = 'alert alert-danger mt-3';
    errorMessage.textContent = message;
    
    // Supprimer les messages d'erreur précédents
    const existingErrors = document.querySelectorAll('.alert-danger');
    existingErrors.forEach(error => error.remove());
    
    document.querySelector('#devis form').prepend(errorMessage);
}

// Nouvelle fonction pour initialiser tous les services Google
function initGoogleServices() {
    try {
        console.log('Initialisation des services Google...');
        
        // Vérifier que l'API Google Maps est bien chargée
        if (!window.google || !window.google.maps) {
            throw new Error('Google Maps n\'est pas encore chargé');
        }

        // Initialiser le service de directions
        directionsService = new google.maps.DirectionsService();
        
        // Initialiser l'autocomplétion
        initializeAutocomplete();
        
        // Ajouter les écouteurs d'événements pour le calcul de distance
        const departInput = document.querySelector('input[placeholder="Adresse de départ"]');
        const arriveeInput = document.querySelector('input[placeholder="Adresse d\'arrivée"]');

        if (!departInput || !arriveeInput) {
            throw new Error('Les champs d\'adresse n\'ont pas été trouvés');
        }

        // Calculer la distance quand les adresses changent
        [departInput, arriveeInput].forEach(input => {
            input.addEventListener('change', () => {
                if (departInput.value && arriveeInput.value) {
                    calculateDistance(departInput.value, arriveeInput.value);
                }
            });
        });

        console.log('Services Google initialisés avec succès');
    } catch (error) {
        console.error('Erreur lors de l\'initialisation des services Google:', error);
        afficherErreurGoogleMaps(error.message);
    }
}

// Fonction pour calculer la distance
function calculateDistance(origin, destination) {
    if (!origin || !destination) {
        console.error('Les adresses de départ et d\'arrivée sont requises');
        return;
    }

    const request = {
        origin: origin,
        destination: destination,
        travelMode: google.maps.TravelMode.DRIVING,
        region: 'fr',
        // Suppression des options qui pourraient affecter le calcul
        provideRouteAlternatives: false,
        optimizeWaypoints: false
    };

    directionsService.route(request, (result, status) => {
        if (status === 'OK') {
            // Récupérer la distance en mètres et la convertir en kilomètres
            // Utilisation de Math.round au lieu de Math.ceil pour un arrondi plus précis
            const distanceKm = Math.round(result.routes[0].legs[0].distance.value / 1000);
            
            // Mise à jour de l'affichage de la distance
            document.querySelector('.distance').textContent = `${distanceKm} km`;

            // Calcul des frais avec la nouvelle distance
            const fraisKm = distanceKm * PRIX_KM;
            const fraisCarburant = (distanceKm * CONSOMMATION_MOYENNE / 100) * COUT_CARBURANT;
            const fraisUsure = FRAIS_USURE;

            // Mise à jour des frais avec 2 décimales
            document.querySelector('.distance-fee').textContent = `${fraisKm.toFixed(2)} €`;
            document.querySelector('.fuel-fee').textContent = `${fraisCarburant.toFixed(2)} €`;
            document.querySelector('.wear-fee').textContent = `${fraisUsure.toFixed(2)} €`;

            updateTotal();
        } else {
            console.error('Erreur de calcul d\'itinéraire:', status);
            document.querySelector('.distance').textContent = '-- km';
            document.querySelector('.distance-fee').textContent = '-- €';
            document.querySelector('.fuel-fee').textContent = '-- €';
            document.querySelector('.wear-fee').textContent = '-- €';
            updateTotal();
        }
    });
}

// Fonction pour mettre à jour le total
function updateTotal() {
    const vehiclePrice = parseFloat(document.querySelector('.vehicle-price').textContent) || 0;
    const distanceFee = parseFloat(document.querySelector('.distance-fee').textContent) || 0;
    const fuelFee = parseFloat(document.querySelector('.fuel-fee').textContent) || 0;
    const wearFee = parseFloat(document.querySelector('.wear-fee').textContent) || 0;

    // Récupérer et afficher les services sélectionnés
    const selectedServices = document.querySelectorAll('.service-checkbox:checked');
    const servicesDetailsContainer = document.querySelector('.services-details');
    const selectedServicesList = document.getElementById('selected-services-list');
    
    // Réinitialiser la liste des services
    selectedServicesList.innerHTML = '';
    let servicesTotal = 0;

    if (selectedServices.length > 0) {
        servicesDetailsContainer.style.display = 'block';
        
        selectedServices.forEach(checkbox => {
            const servicePrice = parseFloat(checkbox.dataset.price) || 0;
            servicesTotal += servicePrice;
            
            // Créer un élément pour chaque service
            const serviceDetail = document.createElement('div');
            serviceDetail.className = 'detail-item';
            serviceDetail.innerHTML = `
                <span class="detail-label">${checkbox.nextElementSibling.textContent.split('(')[0].trim()}</span>
                <span class="detail-value">${servicePrice.toFixed(2)} €</span>
            `;
            selectedServicesList.appendChild(serviceDetail);
        });
    } else {
        servicesDetailsContainer.style.display = 'none';
    }

    const total = vehiclePrice + distanceFee + fuelFee + wearFee + servicesTotal;
    document.querySelector('.total-price').textContent = `${total.toFixed(2)} €`;
}

// Fonction d'initialisation de l'autocomplétion
function initializeAutocomplete() {
    console.log('Initialisation de l\'autocomplétion...');
    
    const options = {
        componentRestrictions: { country: 'fr' },
        fields: ['address_components', 'formatted_address', 'geometry', 'name'],
        types: ['address'],
        strictBounds: false
    };

    const departInput = document.querySelector('input[placeholder="Adresse de départ"]');
    const arriveeInput = document.querySelector('input[placeholder="Adresse d\'arrivée"]');

    if (!departInput || !arriveeInput) {
        throw new Error('Les champs d\'adresse n\'ont pas été trouvés');
    }

    try {
        const autocompleteDepart = new google.maps.places.Autocomplete(departInput, options);
        const autocompleteArrivee = new google.maps.places.Autocomplete(arriveeInput, options);

        console.log('Autocomplétion initialisée');

        autocompleteDepart.addListener('place_changed', () => {
            const place = autocompleteDepart.getPlace();
            if (place && place.formatted_address) {
                departInput.value = place.formatted_address;
                if (arriveeInput.value) {
                    calculateDistance(departInput.value, arriveeInput.value);
                }
            }
        });

        autocompleteArrivee.addListener('place_changed', () => {
            const place = autocompleteArrivee.getPlace();
            if (place && place.formatted_address) {
                arriveeInput.value = place.formatted_address;
                if (departInput.value) {
                    calculateDistance(departInput.value, arriveeInput.value);
                }
            }
        });

        [departInput, arriveeInput].forEach(input => {
            input.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });

            input.classList.add('autocomplete-input');
        });

    } catch (error) {
        console.error('Erreur lors de l\'initialisation de l\'autocomplétion:', error);
        throw error;
    }
}

// Ajouter des styles CSS pour améliorer l'apparence de l'autocomplétion
const autocompleteStyles = document.createElement('style');
autocompleteStyles.textContent = `
    .autocomplete-input {
        width: 100%;
    }
    
    .pac-container {
        z-index: 1051 !important;
        font-family: 'Rubik', sans-serif;
        border-radius: 0 0 4px 4px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    }

    .pac-item {
        padding: 8px 12px;
        cursor: pointer;
    }

    .pac-item:hover {
        background-color: #f8f9fa;
    }

    .pac-icon {
        margin-right: 10px;
    }
`;
document.head.appendChild(autocompleteStyles);

// Fonction pour afficher les services
function afficherServices() {
    const servicesContainer = document.createElement('div');
    servicesContainer.className = 'services-optionnels mt-4';
    
    services.forEach(service => {
        if (service.name.toLowerCase() !== 'frais d\'usure') {
            const serviceDiv = document.createElement('div');
            serviceDiv.className = 'form-check mb-3';

            const checkboxWrapper = document.createElement('div');
            checkboxWrapper.className = 'd-flex align-items-start';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'form-check-input service-checkbox mt-2';
            checkbox.id = `service-${service.id}`;
            checkbox.dataset.price = service.price;

            const labelWrapper = document.createElement('div');
            labelWrapper.className = 'ms-2';

            const label = document.createElement('label');
            label.className = 'form-check-label d-block';
            label.htmlFor = `service-${service.id}`;
            
            // Création d'un élément temporaire pour décoder les caractères HTML
            const decoder = document.createElement('div');
            decoder.innerHTML = service.name;
            const decodedName = decoder.textContent;
            
            label.textContent = `${decodedName} (${service.price} €)`;

            // Décodage de la description également
            const description = document.createElement('small');
            description.className = 'text-muted d-block';
            description.style.fontSize = '0.85rem';
            description.style.marginTop = '0.2rem';
            decoder.innerHTML = service.description;
            description.textContent = decoder.textContent;

            labelWrapper.appendChild(label);
            labelWrapper.appendChild(description);
            
            checkboxWrapper.appendChild(checkbox);
            checkboxWrapper.appendChild(labelWrapper);
            
            serviceDiv.appendChild(checkboxWrapper);
            servicesContainer.appendChild(serviceDiv);

            checkbox.addEventListener('change', updateTotal);
        }
    });

    const detailsTrajet = document.querySelector('.details-trajet');
    detailsTrajet.appendChild(servicesContainer);
}

// Ajouter des styles CSS pour les détails des services
const style = document.createElement('style');
style.textContent = `
    .services-details {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(0,0,0,0.1);
    }
    
    #selected-services-list .detail-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    
    #selected-services-list .detail-value {
        font-weight: 500;
        color: var(--black);
    }
`;
document.head.appendChild(style); 