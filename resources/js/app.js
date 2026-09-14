import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Import Choices.js
import Choices from 'choices.js';
import 'choices.js/public/assets/styles/choices.min.css';

// Initialiser les selects recherchables
window.initSearchableSelects = function() {
    document.querySelectorAll('.searchable-select').forEach(select => {
        // Détruire l'instance existante si elle existe
        if (select.choicesInstance) {
            select.choicesInstance.destroy();
        }
        
        // Créer une nouvelle instance
        const choices = new Choices(select, {
            searchEnabled: true,
            searchPlaceholderValue: 'Rechercher...',
            noResultsText: 'Aucun résultat',
            noChoicesText: 'Aucune option disponible',
            itemSelectText: 'Cliquer pour sélectionner',
            shouldSort: false,
            position: 'bottom',
            searchFields: ['label', 'value'],
            fuseOptions: {
                threshold: 0.3,
            }
        });
        
        // Sauvegarder l'instance
        select.choicesInstance = choices;
        
        // Écouter les changements pour mettre à jour Livewire
        select.addEventListener('change', function(e) {
            // Déclencher l'événement Livewire
            this.dispatchEvent(new Event('input', { bubbles: true }));
        });
    });
};

// Initialiser au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    window.initSearchableSelects();
});

// Réinitialiser après chaque mise à jour Livewire
document.addEventListener('livewire:navigated', () => {
    window.initSearchableSelects();
});

// Pour Livewire v3
Livewire.hook('morph.updated', () => {
    setTimeout(() => {
        window.initSearchableSelects();
    }, 100);
});