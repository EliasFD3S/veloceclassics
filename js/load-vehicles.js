document.addEventListener('DOMContentLoaded', function() {
    const vehiculesContainer = document.getElementById('vehicules-container');

    fetch('/api/get-vehicles.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(vehicles => {
            vehiculesContainer.innerHTML = '';
            
            if (!Array.isArray(vehicles)) {
                throw new Error('Les données reçues ne sont pas un tableau valide');
            }

            vehicles.forEach(vehicle => {
                const imageUrl = vehicle.image_url ? `${vehicle.image_url}` : '/assets/images/default-car.jpg';
                const logoUrl = vehicle.logo_url ? `${vehicle.logo_url}` : '/assets/images/default-logo.png';
                
                console.log('URL de l\'image:', imageUrl);
                console.log('URL du logo:', logoUrl);
                console.log('Données du véhicule:', vehicle);
                
                const vehicleCard = `
                    <div class="col-md-6 col-lg-4">
                        <div class="vehicle-card">
                            <div class="vehicle-image">
                                <img src="${imageUrl}" alt="${vehicle.modele}" class="img-fluid" 
                                    onerror="this.src='/assets/images/default-car.jpg'">
                            </div>
                            <div class="vehicle-info">
                                <div class="brand-logo">
                                    <img src="${logoUrl}" alt="${vehicle.brand_name}" class="brand-img"
                                        onerror="this.src='/assets/images/default-logo.png'">
                                </div>
                                <div class="vehicle-details">
                                    <h3 class="vehicle-name">${vehicle.modele}</h3>
                                    <p class="vehicle-year">${vehicle.annee}</p>
                                    <p class="vehicle-description">${vehicle.description || ''}</p>
                                </div>
                                <button class="btn btn-photos" 
                                        onclick="showVehiclePhotos(${vehicle.id})">
                                    Voir les photos
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                vehiculesContainer.innerHTML += vehicleCard;
            });
        })
        .catch(error => {
            console.error('Erreur lors du chargement des véhicules:', error);
            vehiculesContainer.innerHTML = `
                <div class="col-12 text-center">
                    <p class="text-danger">Une erreur est survenue lors du chargement des véhicules.</p>
                </div>
            `;
        });
});

// Fonction pour afficher la galerie photos
function showVehiclePhotos(vehicleId) {
    const modal = new bootstrap.Modal(document.getElementById('galleryModal'));
    const galleryInner = document.getElementById('gallery-inner');
    const galleryIndicators = document.getElementById('gallery-indicators');
    
    galleryInner.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
    galleryIndicators.innerHTML = '';
    
    modal.show();

    fetch(`/api/get-vehicle-images.php?id=${vehicleId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Vérifier si nous avons une erreur
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Vérifier si nous avons un tableau
            if (!Array.isArray(data)) {
                throw new Error('Format de données invalide');
            }

            // Si le tableau est vide
            if (data.length === 0) {
                galleryInner.innerHTML = `
                    <div class="alert alert-info m-3">
                        Aucune image disponible pour ce véhicule.
                    </div>
                `;
                return;
            }

            // Afficher les images
            galleryInner.innerHTML = '';
            data.forEach((image, index) => {
                const activeClass = index === 0 ? 'active' : '';
                galleryInner.innerHTML += `
                    <div class="carousel-item ${activeClass}">
                        <img src="${image.image_url}" class="d-block w-100" alt="Photo ${index + 1}">
                    </div>
                `;
                
                galleryIndicators.innerHTML += `
                    <button type="button" 
                            data-bs-target="#carouselGallery" 
                            data-bs-slide-to="${index}" 
                            class="${activeClass}" 
                            aria-label="Slide ${index + 1}">
                    </button>
                `;
            });
        })
        .catch(error => {
            console.error('Erreur:', error);
            galleryInner.innerHTML = `
                <div class="alert alert-danger m-3">
                    ${error.message}
                </div>
            `;
        });
} 