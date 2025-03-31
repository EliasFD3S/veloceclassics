document.addEventListener('DOMContentLoaded', () => {
    // Injecter d'abord le modal
    const modalHTML = `
        <div class="modal fade" id="vehicleModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title">Gérer un véhicule</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="vehicleForm">
                            <input type="hidden" id="vehicleId" name="id">
                            
                            <div class="mb-3">
                                <label for="brand" class="form-label">Marque</label>
                                <select class="form-select" id="brand" name="brand" required>
                                    <option value="">Sélectionner une marque</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="modele" class="form-label">Modèle</label>
                                <input type="text" class="form-control" id="modele" name="modele" required>
                            </div>

                            <div class="mb-3">
                                <label for="annee" class="form-label">Année</label>
                                <input type="number" class="form-control" id="annee" name="annee" required>
                            </div>

                            <div class="mb-3">
                                <label for="cout_fixe" class="form-label">Coût fixe</label>
                                <input type="number" class="form-control" id="cout_fixe" name="cout_fixe" step="0.01" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="vehicleImages" class="form-label">Images</label>
                                <input type="file" class="form-control" id="vehicleImages" name="images[]" multiple>
                                <div id="currentImages" class="mt-2">
                                    <!-- Les images existantes seront affichées ici -->
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // S'assurer que le conteneur du modal existe
    const modalContainer = document.getElementById('modalContainer');
    if (!modalContainer) {
        const div = document.createElement('div');
        div.id = 'modalContainer';
        document.body.appendChild(div);
    }
    
    document.getElementById('modalContainer').innerHTML = modalHTML;

    // Gestionnaire pour le bouton d'ajout
    document.querySelector('.add-vehicle-btn').addEventListener('click', () => {
        const form = document.getElementById('vehicleForm');
        form.reset();
        document.getElementById('vehicleId').value = '';
        document.getElementById('currentImages').innerHTML = '';
        
        // Charger les marques
        const brandSelect = document.getElementById('brand');
        loadBrands(brandSelect);
        
        // Afficher le modal
        const modal = new bootstrap.Modal(document.getElementById('vehicleModal'));
        modal.show();
    });

    // Charger les véhicules
    loadVehicles();

    // Ajouter le gestionnaire de soumission du formulaire
    const vehicleForm = document.getElementById('vehicleForm');
    if (vehicleForm) {
        vehicleForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            
            try {
                const formData = new FormData(vehicleForm);
                const vehicleId = formData.get('id');
                
                // Gérer les fichiers d'images
                const imageFiles = document.getElementById('vehicleImages').files;
                if (imageFiles.length > 0) {
                    // Supprimer l'ancien tableau d'images s'il existe
                    formData.delete('images[]');
                    // Ajouter chaque fichier individuellement
                    for (let i = 0; i < imageFiles.length; i++) {
                        formData.append('images[]', imageFiles[i]);
                    }
                }

                const url = vehicleId 
                    ? '/admin/api/update-vehicle.php'
                    : '/admin/api/create-vehicle.php';

                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,  // Ne pas définir Content-Type pour FormData
                    credentials: 'include'
                });

                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status}`);
                }

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.error || 'Erreur lors de l\'enregistrement');
                }

                // Fermer le modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('vehicleModal'));
                if (modal) {
                    modal.hide();
                }

                // Recharger la liste des véhicules
                await loadVehicles();

                // Message de succès
                alert(vehicleId ? 'Véhicule mis à jour avec succès' : 'Véhicule créé avec succès');

            } catch (error) {
                console.error('Erreur lors de l\'enregistrement:', error);
                alert(`Erreur: ${error.message}`);
            }
        });
    }
});

// Fonction pour charger les marques
async function loadBrands(selectElement, selectedValue = null) {
    try {
        if (!selectElement) {
            throw new Error('L\'élément select est null');
        }

        const response = await fetch('/admin/api/get-brands.php', {
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }

        const data = await response.json();
        console.log('Données reçues pour le select:', data);
        
        // Réinitialiser le select
        selectElement.innerHTML = '<option value="">Sélectionner une marque</option>';
        
        // Vérification explicite des données
        if (!data.success || !Array.isArray(data.brands)) {
            throw new Error('Format de données invalide');
        }

        // Ajouter les options
        data.brands.forEach(brand => {
            if (!brand || !brand.id || !brand.name) {
                console.warn('Marque invalide:', brand);
                return;
            }

            const option = document.createElement('option');
            option.value = brand.id;
            option.textContent = brand.name;
            
            if (selectedValue && parseInt(selectedValue) === parseInt(brand.id)) {
                option.selected = true;
            }
            
            selectElement.appendChild(option);
        });

        // Log de vérification
        console.log('Options ajoutées au select:', selectElement.options.length - 1);

    } catch (error) {
        console.error('Erreur lors du chargement des marques dans le select:', error);
        selectElement.innerHTML = '<option value="">Erreur de chargement</option>';
    }
}

// Assurez-vous que cette fonction est appelée au bon moment
document.addEventListener('DOMContentLoaded', () => {
    const brandSelect = document.getElementById('brand');
    if (brandSelect) {
        loadBrands(brandSelect);
    }
});

// Fonction pour charger les véhicules
async function loadVehicles() {
    try {
        const response = await fetch('/admin/api/get-vehicles.php', {
            credentials: 'include'
        });

        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }

        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Erreur lors du chargement des véhicules');
        }

        const tbody = document.querySelector('.vehicles-table tbody');
        tbody.innerHTML = data.vehicles.map(vehicle => `
            <tr>
                <td>
                    ${vehicle.images && vehicle.images.length > 0 
                        ? `<img src="${vehicle.images[0].image_url}" alt="${vehicle.modele}" style="max-width: 100px;">` 
                        : 'Pas d\'image'}
                </td>
                <td>${vehicle.marque || ''}</td>
                <td>${vehicle.modele || ''}</td>
                <td>${vehicle.annee || ''}</td>
                <td>${vehicle.cout_fixe ? vehicle.cout_fixe + ' €' : ''}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="editVehicle(${vehicle.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteVehicle(${vehicle.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');

    } catch (error) {
        console.error('Erreur lors du chargement des véhicules:', error);
        alert('Erreur lors du chargement des véhicules');
    }
}

// Fonction pour supprimer un véhicule
async function deleteVehicle(vehicleId) {
    if (!confirm('Voulez-vous vraiment supprimer ce véhicule ?')) {
        return;
    }

    try {
        const response = await fetch('/admin/api/delete-vehicle.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: vehicleId }),
            credentials: 'include'
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Erreur lors de la suppression');
        }

        // Recharger la liste des véhicules
        loadVehicles();

    } catch (error) {
        console.error('Erreur lors de la suppression:', error);
        alert('Erreur lors de la suppression du véhicule');
    }
}

// Fonction pour éditer un véhicule
async function editVehicle(vehicleId) {
    try {
        const response = await fetch(`/admin/api/get-vehicles.php?id=${vehicleId}`, {
            credentials: 'include'
        });

        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }

        const data = await response.json();
        console.log('Données reçues:', data);

        if (!data.success) {
            throw new Error(data.error || 'Erreur lors du chargement du véhicule');
        }

        const vehicle = data.vehicles[0];
        if (!vehicle) {
            throw new Error('Véhicule non trouvé');
        }

        // S'assurer que le modal est injecté
        if (!document.getElementById('vehicleModal')) {
            injectVehicleModal();
        }

        // Remplir le formulaire
        document.getElementById('vehicleId').value = vehicle.id;
        document.getElementById('modele').value = vehicle.modele || '';
        document.getElementById('annee').value = vehicle.annee || '';
        document.getElementById('cout_fixe').value = vehicle.cout_fixe || '';
        document.getElementById('description').value = vehicle.description || '';

        // Charger et sélectionner la marque
        const brandSelect = document.getElementById('brand');
        if (brandSelect) {
            console.log('Chargement des marques pour le véhicule:', vehicle);
            console.log('ID de la marque à sélectionner:', vehicle.brand);
            await loadBrands(brandSelect, vehicle.brand);
        } else {
            console.error('Select des marques non trouvé');
        }

        // Afficher les images existantes
        const imagesContainer = document.getElementById('currentImages');
        if (imagesContainer && vehicle.images) {
            let images = vehicle.images;
            if (typeof images === 'string') {
                try {
                    images = JSON.parse(images);
                } catch (e) {
                    console.warn('Erreur lors du parsing des images:', e);
                    images = [];
                }
            }
            
            imagesContainer.innerHTML = images.map(image => `
                <div class="current-image-container">
                    <img src="${image.image_url}" class="current-image-preview" alt="Image du véhicule">
                    <button type="button" class="btn btn-danger btn-sm mt-2" onclick="deleteVehicleImage(${image.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `).join('');
        }

        // Afficher le modal
        const modal = new bootstrap.Modal(document.getElementById('vehicleModal'));
        modal.show();

    } catch (error) {
        console.error('Erreur détaillée:', error);
        console.error('Stack trace:', error.stack);
        alert(`Erreur lors du chargement du véhicule: ${error.message}`);
    }
}

// Fonction pour supprimer une image d'un véhicule
async function deleteVehicleImage(imageId) {
    if (!confirm('Voulez-vous vraiment supprimer cette image ?')) {
        return;
    }

    try {
        const response = await fetch('/admin/api/delete-vehicle-image.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ image_id: imageId }),
            credentials: 'include'
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Erreur lors de la suppression de l\'image');
        }

        // Recharger les images du véhicule
        const vehicleId = document.getElementById('vehicleId').value;
        if (vehicleId) {
            editVehicle(vehicleId);
        }

    } catch (error) {
        console.error('Erreur lors de la suppression de l\'image:', error);
        alert('Erreur lors de la suppression de l\'image');
    }
}
