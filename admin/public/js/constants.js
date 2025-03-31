$(document).ready(function() {
    // Charger les constantes au chargement de la page
    loadConstants();

    // Gérer la soumission du formulaire
    $('#constantsForm').on('submit', function(e) {
        e.preventDefault();
        saveConstants();
    });

    // Ajouter des infobulles pour montrer les valeurs par défaut
    $('input[type="number"]').each(function() {
        const input = $(this);
        input.on('focus', function() {
            const tooltip = input.next('.default-value-tooltip');
            if (tooltip) {
                tooltip.show();
            }
        });
        input.on('blur', function() {
            const tooltip = input.next('.default-value-tooltip');
            if (tooltip) {
                tooltip.hide();
            }
        });
    });
});

function loadConstants() {
    $.ajax({
        url: '/admin/api/constants/get.php',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                response.data.forEach(function(constant) {
                    const input = $(`input[name="${constant.name}"]`);
                    input.val(constant.value);
                    
                    // Ajouter la description et la valeur par défaut sous le champ
                    const helpText = $('<small>')
                        .addClass('form-text text-muted')
                        .text(constant.description);
                    
                    const defaultValue = $('<small>')
                        .addClass('form-text text-muted default-value-tooltip')
                        .html(`<i class="fas fa-info-circle"></i> Valeur par défaut : ${constant.value}`);
                    
                    input.closest('.mb-3').append(helpText, defaultValue);
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur lors du chargement des constantes:', error);
            alert('Erreur lors du chargement des constantes');
        }
    });
}

function saveConstants() {
    const formData = {};
    $('#constantsForm input').each(function() {
        formData[$(this).attr('name')] = $(this).val();
    });

    $.ajax({
        url: '/admin/api/constants/update.php',
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                // Afficher une notification de succès
                const alert = $('<div>')
                    .addClass('alert alert-success alert-dismissible fade show')
                    .html(`
                        <strong>Succès!</strong> Variables mises à jour avec succès.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `);
                $('#constantsForm').prepend(alert);
                
                // Faire disparaître l'alerte après 3 secondes
                setTimeout(() => {
                    alert.alert('close');
                }, 3000);
            } else {
                alert('Erreur lors de la mise à jour des variables');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur lors de la sauvegarde des constantes:', error);
            alert('Erreur lors de la sauvegarde des constantes');
        }
    });
} 