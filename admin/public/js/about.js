function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} show`;
    toast.textContent = message;
    
    toastContainer.appendChild(toast);
    
    // Supprimer le toast après 3 secondes
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.style.position = 'fixed';
    container.style.top = '20px';
    container.style.right = '20px';
    container.style.zIndex = '1000';
    document.body.appendChild(container);
    return container;
}

document.addEventListener('DOMContentLoaded', function() {
    loadAboutData();

    document.getElementById('aboutForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveAboutData();
    });
});

function loadAboutData() {
    fetch('/admin/api/about/get.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            console.log('Réponse brute:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Erreur de parsing JSON:', text);
                throw new Error('Invalid JSON response');
            }
        })
        .then(data => {
            console.log('Données reçues:', data);
            if (data.success && data.data) {
                const about = data.data;
                const elements = {
                    'main_text': about.main_text,
                    'feature_1_title': about.feature_1_title,
                    'feature_1_description': about.feature_1_description,
                    'feature_1_icon': about.feature_1_icon,
                    'feature_2_title': about.feature_2_title,
                    'feature_2_description': about.feature_2_description,
                    'feature_2_icon': about.feature_2_icon,
                    'feature_3_title': about.feature_3_title,
                    'feature_3_description': about.feature_3_description,
                    'feature_3_icon': about.feature_3_icon
                };

                for (const [id, value] of Object.entries(elements)) {
                    const element = document.getElementById(id);
                    if (element) {
                        element.value = value || '';
                        console.log(`Remplissage de ${id} avec:`, value);
                    } else {
                        console.warn(`Élément ${id} non trouvé dans le DOM`);
                    }
                }
            } else {
                console.error('Données invalides reçues:', data);
            }
        })
        .catch(error => {
            console.error('Erreur complète:', error);
            if (typeof showToast === 'function') {
                showToast('Erreur lors du chargement des données', 'error');
            } else {
                console.error('showToast n\'est pas défini');
            }
        });
}

function saveAboutData() {
    const formData = new FormData(document.getElementById('aboutForm'));
    
    fetch('/admin/api/about/update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(text => {
        console.log('Réponse brute:', text);
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Erreur de parsing JSON:', text);
            throw new Error('Invalid JSON response');
        }
    })
    .then(data => {
        if (data.success) {
            if (typeof showToast === 'function') {
                showToast('Les informations ont été mises à jour avec succès', 'success');
            } else {
                console.log('Mise à jour réussie');
            }
        } else {
            if (typeof showToast === 'function') {
                showToast(data.message || 'Une erreur est survenue lors de la mise à jour', 'error');
            } else {
                console.error(data.message || 'Erreur de mise à jour');
            }
        }
    })
    .catch(error => {
        console.error('Erreur complète:', error);
        if (typeof showToast === 'function') {
            showToast('Une erreur est survenue lors de la mise à jour', 'error');
        } else {
            console.error('showToast n\'est pas défini');
        }
    });
} 