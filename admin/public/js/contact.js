document.addEventListener('DOMContentLoaded', function() {
    // Charger les informations de contact au chargement de la page
    loadContactInfo();

    // Gérer la soumission du formulaire
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        updateContactInfo();
    });
});

function loadContactInfo() {
    fetch('/admin/api/contact/get_contact.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text(); // D'abord récupérer le texte brut
        })
        .then(text => {
            if (!text) {
                throw new Error('Réponse vide du serveur');
            }
            try {
                return JSON.parse(text); // Ensuite parser le JSON
            } catch (e) {
                console.error('Texte reçu:', text);
                throw new Error('Erreur de parsing JSON: ' + e.message);
            }
        })
        .then(data => {
            if (data.success) {
                // Remplir le formulaire avec les données existantes
                document.getElementById('phone').value = data.data.telephone || '';
                document.getElementById('email').value = data.data.email || '';
                document.getElementById('linkedin').value = data.data.linkedin_url || '';
                document.getElementById('tiktok').value = data.data.tiktok_url || '';
                document.getElementById('instagram').value = data.data.instagram_url || '';
            } else {
                throw new Error(data.message || 'Erreur lors du chargement des données');
            }
        })
        .catch(error => {
            console.error('Erreur détaillée:', error);
            alert('Erreur lors du chargement des informations de contact: ' + error.message);
        });
}

function updateContactInfo() {
    const formData = {
        phone: document.getElementById('phone').value,
        email: document.getElementById('email').value,
        linkedin: document.getElementById('linkedin').value,
        tiktok: document.getElementById('tiktok').value,
        instagram: document.getElementById('instagram').value
    };

    fetch('/admin/api/contact/update_contact.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Informations de contact mises à jour avec succès');
        } else {
            throw new Error(data.message || 'Erreur lors de la mise à jour');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la mise à jour des informations de contact: ' + error.message);
    });
} 