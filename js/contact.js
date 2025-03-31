document.addEventListener('DOMContentLoaded', function() {
    console.log('Chargement du script contact.js'); // Debug initial

    // Éléments DOM
    const phoneElement = document.getElementById('contact-phone');
    const emailElement = document.getElementById('contact-email');
    const instagramLink = document.getElementById('instagram-link');
    const tiktokLink = document.getElementById('tiktok-link');
    const linkedinLink = document.getElementById('linkedin-link');

    // Vérifier que tous les éléments sont trouvés
    console.log('Éléments DOM:', {
        phone: phoneElement,
        email: emailElement,
        instagram: instagramLink,
        tiktok: tiktokLink,
        linkedin: linkedinLink
    });

    // Charger les informations de contact
    console.log('Début de la requête fetch');
    fetch('api/get-contact.php')
        .then(response => {
            console.log('Réponse reçue:', response.status); // Log du status HTTP
            return response.text().then(text => {
                try {
                    console.log('Texte brut reçu:', text); // Log du texte brut
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Erreur parsing JSON:', e);
                    throw new Error('Invalid JSON response');
                }
            });
        })
        .then(response => {
            console.log('Données parsées:', response);

            if (response.error) {
                console.error('Erreur serveur:', response.error);
                throw new Error(response.error);
            }

            if (!response.data) {
                console.error('Pas de données dans la réponse');
                throw new Error('Données manquantes');
            }

            const data = response.data;
            console.log('Données de contact:', data);

            // Mettre à jour les informations de contact
            if (data.telephone) {
                console.log('Mise à jour téléphone:', data.telephone);
                phoneElement.textContent = data.telephone;
            }
            if (data.email) {
                console.log('Mise à jour email:', data.email);
                emailElement.textContent = data.email;
            }
            
            // Mettre à jour les liens sociaux
            if (data.instagram_url) {
                instagramLink.href = data.instagram_url;
                instagramLink.style.display = 'inline-block';
                console.log('Instagram URL mise à jour');
            } else {
                instagramLink.style.display = 'none';
            }
            
            if (data.tiktok_url) {
                tiktokLink.href = data.tiktok_url;
                tiktokLink.style.display = 'inline-block';
                console.log('TikTok URL mise à jour');
            } else {
                tiktokLink.style.display = 'none';
            }
            
            if (data.linkedin_url) {
                linkedinLink.href = data.linkedin_url;
                linkedinLink.style.display = 'inline-block';
                console.log('LinkedIn URL mise à jour');
            } else {
                linkedinLink.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Erreur complète:', error);
            console.error('Stack trace:', error.stack);
            
            // Afficher un message d'erreur à l'utilisateur
            phoneElement.textContent = "Erreur de chargement";
            emailElement.textContent = "Erreur de chargement";
            
            // Cacher les liens sociaux en cas d'erreur
            instagramLink.style.display = 'none';
            tiktokLink.style.display = 'none';
            linkedinLink.style.display = 'none';
        });

    // Gestion du formulaire de contact
    const contactForm = document.querySelector('#contact form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Récupérer les données du formulaire
            const formData = {
                name: this.querySelector('#name').value,
                email: this.querySelector('#email').value,
                subject: this.querySelector('#subject').value,
                message: this.querySelector('#message').value
            };

            // Désactiver le bouton d'envoi
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = 'Envoi en cours...';

            // Envoyer les données
            fetch('api/send-mail.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                
                // Réinitialiser le formulaire
                contactForm.reset();
                
                // Afficher un message de succès
                alert('Message envoyé avec succès !');
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'envoi du message. Veuillez réessayer.');
            })
            .finally(() => {
                // Réactiver le bouton d'envoi
                submitButton.disabled = false;
                submitButton.innerHTML = 'Envoyer le message';
            });
        });
    }
}); 