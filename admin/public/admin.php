<?php
require_once '../api/protect.php';
require_once 'version_manager.php';
$version = getVersion();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veloce Classics - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/admin/public/styles/admin.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/admin/public/js/admin.js?v=<?php echo $version; ?>"></script>
    <script src="/admin/public/js/vehicles.js?v=<?php echo $version; ?>"></script>
    <script src="/admin/public/js/brands.js?v=<?php echo $version; ?>"></script>
    <script src="/admin/public/js/contact.js?v=<?php echo $version; ?>"></script>
    <script src="/admin/public/js/constants.js?v=<?php echo $version; ?>"></script>
    <script src="/admin/public/js/about.js?v=<?php echo $version; ?>"></script>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo-container">
                <img src="/assets/logo/whitelogo.png" alt="Logo" class="admin-logo">
            </div>
            <nav class="nav-menu">
                <div class="nav-item menu-item active" data-section="vehicules">
                    <i class="fas fa-car"></i>
                    <span>Véhicules</span>
                </div>
                <div class="nav-item menu-item" data-section="marques">
                    <i class="fas fa-trademark"></i>
                    <span>Marques</span>
                </div>
                <div class="nav-item menu-item" data-section="contact">
                    <i class="fas fa-envelope"></i>
                    <span>Contact</span>
                </div>
                <div class="nav-item menu-item" data-section="variables">
                    <i class="fas fa-sliders-h"></i>
                    <span>Variables</span>
                </div>
                <div class="nav-item menu-item" data-section="about">
                    <i class="fas fa-info-circle"></i>
                    <span>À Propos</span>
                </div>
                <div class="nav-item menu-item" onclick="refreshCache()">
                    <i class="fas fa-sync"></i>
                    <span>Rafraîchir le cache JS</span>
                </div>
                <div class="nav-item menu-item logout" onclick="window.location.href='/admin/api/logout.php'">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </div>
            </nav>
        </div>

        <!-- Contenu principal -->
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <!-- Section Véhicules (active par défaut) -->
                        <section class="section-vehicules active-section">
                            <div class="content-header">
                                <h1>Gestion des Véhicules</h1>
                                <button class="btn btn-primary add-vehicle-btn">
                                    <i class="fas fa-plus"></i> Ajouter un véhicule
                                </button>
                            </div>

                            <div class="vehicles-table">
                                <table class="table vehicles-table">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Marque</th>
                                            <th>Modèle</th>
                                            <th>Année</th>
                                            <th>Coût fixe</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Les véhicules seront chargés ici -->
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Section Marques -->
                        <section class="section-marques" style="display: none;">
                            <div class="content-header">
                                <h1>Gestion des Marques</h1>
                                <button class="btn btn-primary add-brand-btn">
                                    <i class="fas fa-plus"></i> Ajouter une marque
                                </button>
                            </div>

                            <div class="brands-table p-4">
                                <table class="table brands-table">
                                    <thead>
                                        <tr>
                                            <th>Logo</th>
                                            <th>Nom</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Les marques seront chargées ici -->
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Ajouter la nouvelle section après la section marques -->
                        <section class="section-contact" style="display: none;">
                            <div class="content-header">
                                <h1>Gestion des Informations de Contact</h1>
                            </div>
                            
                            <div class="contact-form p-4">
                                <form id="contactForm">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Téléphone</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                            <input type="tel" class="form-control" id="phone" name="phone">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control" id="email" name="email">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="linkedin" class="form-label">LinkedIn</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fab fa-linkedin"></i></span>
                                            <input type="url" class="form-control" id="linkedin" name="linkedin" placeholder="https://linkedin.com/...">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="tiktok" class="form-label">TikTok</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fab fa-tiktok"></i></span>
                                            <input type="url" class="form-control" id="tiktok" name="tiktok" placeholder="https://tiktok.com/@...">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="instagram" class="form-label">Instagram</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fab fa-instagram"></i></span>
                                            <input type="url" class="form-control" id="instagram" name="instagram" placeholder="https://instagram.com/...">
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Enregistrer
                                    </button>
                                </form>
                            </div>
                        </section>

                        <!-- Ajouter la nouvelle section avant la fin de la div.col-12 -->
                        <section class="section-variables" style="display: none;">
                            <div class="content-header">
                                <h1>Gestion des Variables</h1>
                            </div>
                            
                            <div class="constants-form p-4">
                                <form id="constantsForm">
                                    <div class="mb-3">
                                        <label for="prix_km" class="form-label">Prix par kilomètre (€)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-euro-sign"></i></span>
                                            <input type="number" step="0.01" class="form-control" id="prix_km" name="PRIX_KM">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="cout_carburant" class="form-label">Coût du carburant par litre (€)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-gas-pump"></i></span>
                                            <input type="number" step="0.01" class="form-control" id="cout_carburant" name="COUT_CARBURANT">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="consommation_moyenne" class="form-label">Consommation moyenne (L/100km)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-tachometer-alt"></i></span>
                                            <input type="number" step="0.01" class="form-control" id="consommation_moyenne" name="CONSOMMATION_MOYENNE">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="frais_usure" class="form-label">Frais fixes d'usure (€)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-tools"></i></span>
                                            <input type="number" step="0.01" class="form-control" id="frais_usure" name="FRAIS_USURE">
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Enregistrer
                                    </button>
                                </form>
                            </div>
                        </section>

                        <!-- Ajouter la nouvelle section avant la fin de la div.col-12 -->
                        <section class="section-about" style="display: none;">
                            <div class="content-header">
                                <h1>Gestion de la Section À Propos</h1>
                            </div>
                            
                            <div class="about-form p-4">
                                <form id="aboutForm">                                    
                                    <div class="mb-3">
                                        <label for="main_text" class="form-label">Texte principal</label>
                                        <textarea class="form-control" id="main_text" name="main_text" rows="6"></textarea>
                                    </div>

                                    <!-- Feature 1 -->
                                    <div class="feature-section mb-4">
                                        <h3>Caractéristique 1</h3>
                                        <div class="mb-3">
                                            <label for="feature_1_title" class="form-label">Titre</label>
                                            <input type="text" class="form-control" id="feature_1_title" name="feature_1_title">
                                        </div>
                                        <div class="mb-3">
                                            <label for="feature_1_description" class="form-label">Description</label>
                                            <textarea class="form-control" id="feature_1_description" name="feature_1_description" rows="3"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="feature_1_icon" class="form-label">Icône (classe Font Awesome)</label>
                                            <input type="text" class="form-control" id="feature_1_icon" name="feature_1_icon" placeholder="fa-car-side">
                                        </div>
                                    </div>

                                    <!-- Feature 2 -->
                                    <div class="feature-section mb-4">
                                        <h3>Caractéristique 2</h3>
                                        <div class="mb-3">
                                            <label for="feature_2_title" class="form-label">Titre</label>
                                            <input type="text" class="form-control" id="feature_2_title" name="feature_2_title">
                                        </div>
                                        <div class="mb-3">
                                            <label for="feature_2_description" class="form-label">Description</label>
                                            <textarea class="form-control" id="feature_2_description" name="feature_2_description" rows="3"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="feature_2_icon" class="form-label">Icône (classe Font Awesome)</label>
                                            <input type="text" class="form-control" id="feature_2_icon" name="feature_2_icon" placeholder="fa-tools">
                                        </div>
                                    </div>

                                    <!-- Feature 3 -->
                                    <div class="feature-section mb-4">
                                        <h3>Caractéristique 3</h3>
                                        <div class="mb-3">
                                            <label for="feature_3_title" class="form-label">Titre</label>
                                            <input type="text" class="form-control" id="feature_3_title" name="feature_3_title">
                                        </div>
                                        <div class="mb-3">
                                            <label for="feature_3_description" class="form-label">Description</label>
                                            <textarea class="form-control" id="feature_3_description" name="feature_3_description" rows="3"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="feature_3_icon" class="form-label">Icône (classe Font Awesome)</label>
                                            <input type="text" class="form-control" id="feature_3_icon" name="feature_3_icon" placeholder="fa-shield-alt">
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Enregistrer
                                    </button>
                                </form>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conteneur pour le modal des véhicules -->
        <div id="modalContainer"></div>

        <!-- Modal pour l'ajout/modification de marque -->
        <div class="modal fade" id="brandModal" tabindex="-1" aria-labelledby="brandModalLabel">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="brandModalLabel">Ajouter une marque</h2>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <form id="brandForm" class="brand-form" enctype="multipart/form-data">
                            <div class="form-group mb-3">
                                <label for="brandName">Nom de la marque</label>
                                <input type="text" id="brandName" name="brandName" class="form-control" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="brandLogo">Logo (PNG 600x600)</label>
                                <input type="file" id="brandLogo" name="brandLogo" class="form-control" accept="image/png" required>
                                <small class="form-text text-muted">Le logo doit être au format PNG et de dimensions 600x600 pixels</small>
                                <img id="logoPreview" class="logo-preview mt-2" style="display: none; max-width: 200px;" alt="Aperçu du logo">
                            </div>
                            <div class="modal-footer px-0 pb-0">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Styles CSS -->
</body>
</html> 
