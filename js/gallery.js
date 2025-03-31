document.addEventListener('DOMContentLoaded', function() {
    // Récupérer tous les boutons "Voir les photos"
    const photoButtons = document.querySelectorAll('.btn-photos');
    const galleryModal = new bootstrap.Modal(document.getElementById('galleryModal'));
    
    photoButtons.forEach(button => {
        button.addEventListener('click', function() {
            const vehicleId = this.getAttribute('data-vehicle-id');
            loadGallery(vehicleId);
        });
    });

    function loadGallery(vehicleId) {
        fetch(`api/gallery.php?vehicle_id=${vehicleId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.images) {
                    updateGalleryModal(data.images);
                    galleryModal.show();
                }
            })
            .catch(error => console.error('Erreur lors du chargement de la galerie:', error));
    }

    function updateGalleryModal(images) {
        const indicators = document.getElementById('gallery-indicators');
        const galleryInner = document.getElementById('gallery-inner');
        
        // Vider les conteneurs
        indicators.innerHTML = '';
        galleryInner.innerHTML = '';
        
        // Générer les slides et indicateurs
        images.forEach((image, index) => {
            // Créer l'indicateur
            const indicator = document.createElement('button');
            indicator.setAttribute('type', 'button');
            indicator.setAttribute('data-bs-target', '#carouselGallery');
            indicator.setAttribute('data-bs-slide-to', index.toString());
            if (index === 0) indicator.classList.add('active');
            indicator.setAttribute('aria-label', `Image ${index + 1}`);
            indicators.appendChild(indicator);
            
            // Créer le slide
            const slideDiv = document.createElement('div');
            slideDiv.className = `carousel-item ${index === 0 ? 'active' : ''}`;
            slideDiv.innerHTML = `
                <img src="${image.image_url}" class="img-fluid" alt="Photo ${index + 1}">
            `;
            galleryInner.appendChild(slideDiv);
        });
    }
}); 