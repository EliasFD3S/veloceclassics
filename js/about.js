document.addEventListener('DOMContentLoaded', function() {
    loadAboutSection();
});

async function loadAboutSection() {
    try {
        const response = await fetch('api/about/get.php');
        const data = await response.json();

        if (data.success) {
            const about = data.data;

            // Mise à jour du titre principal
            document.querySelector('#about h2').textContent = about.title;

            // Mise à jour du texte principal
            const aboutText = document.querySelector('#about-text');
            if (aboutText) {
                const paragraphs = about.main_text.split('\n\n');
                aboutText.innerHTML = paragraphs
                    .map(p => `<p class="lead">${p}</p>`)
                    .join('');
            }

            // Mise à jour des features avec leurs IDs spécifiques
            const features = [
                {
                    id: 'feature-1',
                    title: about.feature_1_title,
                    description: about.feature_1_description,
                    icon: about.feature_1_icon
                },
                {
                    id: 'feature-2',
                    title: about.feature_2_title,
                    description: about.feature_2_description,
                    icon: about.feature_2_icon
                },
                {
                    id: 'feature-3',
                    title: about.feature_3_title,
                    description: about.feature_3_description,
                    icon: about.feature_3_icon
                }
            ];

            // Mettre à jour chaque feature individuellement
            features.forEach(feature => {
                const featureElement = document.getElementById(feature.id);
                if (featureElement) {
                    const iconElement = featureElement.querySelector('.feature-icon i');
                    const titleElement = featureElement.querySelector('h4');
                    const descriptionElement = featureElement.querySelector('p');

                    if (iconElement) iconElement.className = `fas ${feature.icon}`;
                    if (titleElement) titleElement.textContent = feature.title;
                    if (descriptionElement) descriptionElement.textContent = feature.description;
                }
            });

        } else {
            console.error('Erreur lors du chargement des données:', data.message);
        }
    } catch (error) {
        console.error('Erreur lors du chargement de la section À propos:', error);
    }
} 