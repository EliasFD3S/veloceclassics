document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour changer de section
    function switchSection(sectionName) {
        console.log('Changement de section vers:', sectionName);
        
        // Masquer toutes les sections
        document.querySelectorAll('section[class^="section-"]').forEach(section => {
            section.style.display = 'none';
        });

        // Afficher la section sélectionnée
        const targetSection = document.querySelector(`.section-${sectionName}`);
        if (targetSection) {
            targetSection.style.display = 'block';
        }

        // Mettre à jour le menu
        document.querySelectorAll('.nav-item.menu-item').forEach(item => {
            item.classList.remove('active');
        });

        const activeMenuItem = document.querySelector(`.menu-item[data-section="${sectionName}"]`);
        if (activeMenuItem) {
            activeMenuItem.classList.add('active');
        }

        // Recharger les données
        if (sectionName === 'vehicules' && typeof loadVehicles === 'function') {
            loadVehicles();
        } else if (sectionName === 'marques' && typeof loadBrands === 'function') {
            loadBrands();
        }
    }

    // Gestionnaire de clic pour les éléments du menu
    document.querySelectorAll('.nav-item.menu-item').forEach(item => {
        item.addEventListener('click', function(e) {
            // Si c'est le bouton de déconnexion, ne pas empêcher le comportement par défaut
            if (this.classList.contains('logout')) {
                return true;
            }
            
            // Pour les autres éléments du menu
            e.preventDefault();
            const section = this.getAttribute('data-section');
            if (section) {
                switchSection(section);
            }
        });
    });

    // Gestionnaire pour le bouton d'ajout de marque
    const addBrandBtn = document.querySelector('.add-brand-btn');
    if (addBrandBtn) {
        addBrandBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const modal = new bootstrap.Modal(document.getElementById('brandModal'));
            modal.show();
        });
    }

    // Afficher la section véhicules par défaut
    switchSection('vehicules');
});

async function refreshCache() {
    try {
        const response = await fetch('/admin/public/refresh_cache.php');
        const data = await response.json();
        if (data.success) {
            alert('Cache rafraîchi avec succès. La page va être rechargée.');
            window.location.reload();
        }
    } catch (error) {
        console.error('Erreur lors du rafraîchissement du cache:', error);
        alert('Erreur lors du rafraîchissement du cache');
    }
}