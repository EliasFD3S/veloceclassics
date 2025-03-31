async function loadBrandsTable() {
    try {
        console.log('Début du chargement des marques pour le tableau');
        
        const response = await fetch('/admin/api/get-brands.php', {
            credentials: 'same-origin'
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Ne mettre à jour que le tableau des marques
            const brandsTable = document.querySelector('.brands-table tbody');
            if (brandsTable) {
                displayBrands(data.brands);
            }
        }
    } catch (error) {
        console.error('Erreur lors du chargement du tableau des marques:', error);
    }
}

function displayBrands(brands) {
    console.log('Début de displayBrands avec:', brands);
    
    const tbody = document.querySelector('.brands-table tbody');
    if (!tbody) {
        console.error('Container .brands-table tbody non trouvé');
        return;
    }
    console.log('Container tbody trouvé');

    if (!brands || brands.length === 0) {
        console.log('Aucune marque à afficher');
        tbody.innerHTML = '<tr><td colspan="3" class="text-center">Aucune marque trouvée</td></tr>';
        return;
    }

    console.log(`Affichage de ${brands.length} marques`);
    tbody.innerHTML = brands.map(brand => {
        console.log('Traitement de la marque:', brand);
        return `
            <tr>
                <td>
                    <img src="${brand.logo_url}" alt="${brand.name}" class="brand-logo" style="max-height: 50px;">
                </td>
                <td>${brand.name}</td>
                <td>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-danger delete-btn" onclick="deleteBrand(${brand.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
    console.log('Affichage terminé');
}

async function handleBrandSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const logoInput = form.querySelector('#brandLogo');
    const brandName = form.querySelector('#brandName').value;
    const modal = document.getElementById('brandModal');
    
    try {
        // Upload du logo
        const logoFile = logoInput.files[0];
        const logoFormData = new FormData();
        logoFormData.append('logo', logoFile);
        
        console.log('Début de l\'upload', {
            file: logoFile,
            formData: Object.fromEntries(logoFormData),
            sessionCookie: document.cookie
        });

        const uploadResponse = await fetch('/admin/api/upload-brand-logo.php', {
            method: 'POST',
            body: logoFormData,
            credentials: 'include',  // Assurez-vous d'envoyer les cookies
            headers: {
                // Ne pas définir Content-Type ici, il sera automatiquement défini avec le boundary
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const responseText = await uploadResponse.text();
        console.log('Réponse du serveur:', {
            status: uploadResponse.status,
            statusText: uploadResponse.statusText,
            headers: Object.fromEntries(uploadResponse.headers),
            body: responseText
        });

        let uploadResult;
        try {
            uploadResult = JSON.parse(responseText);
        } catch (e) {
            console.error('Erreur de parsing JSON:', e);
            throw new Error(`Réponse invalide du serveur: ${responseText}`);
        }

        if (!uploadResult.success) {
            console.error('Échec de l\'upload:', uploadResult);
            throw new Error(uploadResult.error || 'Erreur lors de l\'upload du logo');
        }
        
        // Création de la marque
        const brandData = {
            name: brandName,
            logo_url: uploadResult.logo_url
        };
        
        const response = await fetch('/admin/api/create-brand.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify(brandData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Fermeture forcée du modal
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
                // Nettoyage manuel du modal
                document.body.classList.remove('modal-open');
                const modalBackdrop = document.querySelector('.modal-backdrop');
                if (modalBackdrop) {
                    modalBackdrop.remove();
                }
            }
            
            // Réinitialisation du formulaire
            form.reset();
            const logoPreview = document.getElementById('logoPreview');
            if (logoPreview) {
                logoPreview.style.display = 'none';
            }
            
            // Rechargement des marques
            await loadBrandsTable();
            
        } else {
            throw new Error(result.error || 'Erreur lors de la création de la marque');
        }
        
    } catch (error) {
        console.error('Erreur détaillée:', {
            message: error.message,
            stack: error.stack,
            sessionInfo: document.cookie
        });
        alert(`Une erreur est survenue: ${error.message}`);
    }
}

function deleteBrand(brandId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette marque ?')) {
        const formData = new FormData();
        formData.append('id', brandId);

        fetch('/admin/api/delete-brand.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Rafraîchir la liste des marques ou supprimer la ligne du tableau
                location.reload(); // ou mettre à jour le DOM de manière plus élégante
            } else {
                alert(data.error || 'Erreur lors de la suppression de la marque');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la suppression de la marque');
        });
    }
}

// Ajout du style CSS pour la grille
const style = document.createElement('style');
style.textContent = `
    .brands-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 2rem;
        padding: 2rem;
    }

    .brand-card {
        background: #fff;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }

    .brand-logo-container {
        width: 150px;
        height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .brand-logo {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .brand-name {
        font-size: 1.2rem;
        text-align: center;
        margin: 0;
    }

    .brand-actions {
        display: flex;
        gap: 0.5rem;
    }

    .no-brands {
        grid-column: 1 / -1;
        text-align: center;
        padding: 2rem;
        color: #666;
    }
`;
document.head.appendChild(style);

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    loadBrandsTable();
    
    // Restaurer la section active
    const activeSectionId = localStorage.getItem('activeSectionId');
    if (activeSectionId) {
        const activeSection = document.getElementById(activeSectionId);
        if (activeSection) {
            activeSection.classList.add('active');
        }
    }

    // Rafraîchissement périodique
    setInterval(loadBrandsTable, 30000); // Rafraîchit toutes les 30 secondes
    
    // Initialisation du formulaire
    const brandForm = document.getElementById('brandForm');
    if (brandForm) {
        console.log('Formulaire trouvé, ajout du gestionnaire');
        brandForm.addEventListener('submit', handleBrandSubmit);
    } else {
        console.error('Formulaire non trouvé');
    }
    
    // Initialisation du bouton d'ajout
    const addBrandBtn = document.querySelector('.add-brand-btn');
    if (addBrandBtn) {
        console.log('Bouton d\'ajout trouvé, ajout du gestionnaire');
        addBrandBtn.addEventListener('click', () => {
            // Supprimer tout backdrop existant
            $('.modal-backdrop').remove();
            
            // Réinitialiser le modal
            $('#brandModal').modal('dispose');
            
            // Ouvrir le modal
            $('#brandModal').modal('show');
        });
    } else {
        console.error('Bouton d\'ajout non trouvé');
    }
    
    // Prévisualisation du logo
    const logoInput = document.getElementById('brandLogo');
    const logoPreview = document.getElementById('logoPreview');
    if (logoInput && logoPreview) {
        logoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    logoPreview.src = e.target.result;
                    logoPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Enregistrer la section active
    document.querySelectorAll('.section').forEach(section => {
        section.addEventListener('click', () => {
            localStorage.setItem('activeSectionId', section.id);
        });
    });
});

// Lors de la fermeture du modal
$('#brandModal').on('hide.bs.modal', function () {
    // Supprimer le backdrop avant la fermeture
    $('.modal-backdrop').remove();
});

// Ajouter cette fonction
async function getBrandNameById(brandId) {
    try {
        console.log('Recherche de la marque avec ID:', brandId);
        const response = await fetch('/admin/api/get-brands.php', {
            credentials: 'same-origin'
        });
        
        const data = await response.json();
        
        if (!data.success || !data.brands) {
            throw new Error('Impossible de récupérer les marques');
        }

        const brand = data.brands.find(b => b.id === parseInt(brandId));
        if (!brand) {
            throw new Error(`Aucune marque trouvée avec l'ID ${brandId}`);
        }

        return brand.name;
    } catch (error) {
        console.error('Erreur lors de la récupération du nom de la marque:', error);
        return null;
    }
}

// Rendre la fonction disponible globalement
window.getBrandNameById = getBrandNameById;
