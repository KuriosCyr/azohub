import './bootstrap';

import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';

window.Alpine = Alpine;

Alpine.plugin(intersect);

// Compteur animé : x-data="counter(1234)" x-intersect.once="start()" x-text="display"
Alpine.data('counter', (target = 0, duration = 1200) => ({
    display: '0',
    start() {
        const startTime = performance.now();
        const end = Number(target) || 0;
        const step = (now) => {
            const progress = Math.min((now - startTime) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            this.display = Math.floor(eased * end).toLocaleString('fr-FR');
            if (progress < 1) requestAnimationFrame(step);
            else this.display = end.toLocaleString('fr-FR');
        };
        requestAnimationFrame(step);
    },
}));

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