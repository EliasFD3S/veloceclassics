// Fonction pour charger l'API Google Maps de manière sécurisée
function loadGoogleMapsAPI() {
    fetch('/api/get-google-key.php')
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(`Erreur serveur: ${JSON.stringify(err)}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (!data.apiKey) {
                throw new Error('Clé API non trouvée');
            }
            console.log('Clé API reçue avec succès');
            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${data.apiKey}&libraries=places&callback=initGoogleServices&language=fr&region=FR`;
            script.async = true;
            script.onerror = (error) => {
                console.error('Erreur de chargement de l\'API Google Maps:', error);
            };
            document.head.appendChild(script);
        })
        .catch(error => {
            console.error('Erreur détaillée:', error);
            alert('Impossible de charger la carte Google Maps. Veuillez réessayer plus tard.');
        });
}

// S'assurer que la fonction initGoogleServices existe
window.initGoogleServices = function() {
    console.log('Services Google initialisés avec succès');
    // Votre code d'initialisation ici
};

document.addEventListener('DOMContentLoaded', loadGoogleMapsAPI); 