document.addEventListener('DOMContentLoaded', function() {
    // Charger les données du carrousel
    fetch('api/carousel.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.slides) {
                const indicators = document.getElementById('carousel-indicators');
                const carouselInner = document.getElementById('carousel-inner');
                
                // Vider les conteneurs
                indicators.innerHTML = '';
                carouselInner.innerHTML = '';
                
                // Générer les slides et indicateurs
                data.slides.forEach((slide, index) => {
                    // Créer l'indicateur
                    const indicator = document.createElement('button');
                    indicator.setAttribute('type', 'button');
                    indicator.setAttribute('data-bs-target', '#accueil');
                    indicator.setAttribute('data-bs-slide-to', index.toString());
                    if (index === 0) {
                        indicator.classList.add('active');
                        indicator.setAttribute('aria-current', 'true');
                    }
                    indicator.setAttribute('aria-label', `Slide ${index + 1}`);
                    indicators.appendChild(indicator);
                    
                    // Créer le slide
                    const slideDiv = document.createElement('div');
                    slideDiv.className = `carousel-item ${index === 0 ? 'active' : ''}`;
                    slideDiv.innerHTML = `
                        <img src="${slide.image_url}" class="d-block w-100" alt="${slide.title}">
                        <div class="carousel-caption">
                            <h1>${slide.title}</h1>
                            <p class="lead mb-4">${slide.description}</p>
                            <a href="${slide.button_url}" class="btn btn-primary">${slide.button_text}</a>
                        </div>
                    `;
                    carouselInner.appendChild(slideDiv);
                });

                // Initialiser le carrousel
                new bootstrap.Carousel(document.querySelector('#accueil'), {
                    interval: 5000,
                    touch: true,
                    ride: 'carousel'
                });
            }
        })
        .catch(error => console.error('Erreur lors du chargement du carrousel:', error));
}); 