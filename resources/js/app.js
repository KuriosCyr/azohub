import './bootstrap';

// Alpine et Livewire partagent la même instance (bundle officiel Livewire), au
// lieu d'un Alpine.start() séparé qui entrait en conflit avec celui que
// @livewireScripts démarre de son côté : les x-data des composants Livewire
// (ex. la cloche de notifications) perdaient leur état (ex. dropdown qui se
// referme tout seul) après un aller-retour serveur, car deux instances Alpine
// coexistaient et Livewire ne savait préserver l'état que de la sienne.
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import intersect from '@alpinejs/intersect';
import Swal from 'sweetalert2';

window.Alpine = Alpine;
window.Livewire = Livewire;
window.Swal = Swal;

// Boîte de dialogue de confirmation stylée (remplace window.confirm()).
// Utilisation : confirmAction('Message...', { danger: true, confirmText: 'Supprimer' }).then(ok => ok && ...)
window.confirmAction = function (message, options = {}) {
    return Swal.fire({
        title: options.title ?? 'Confirmer',
        text: message,
        icon: options.icon ?? 'warning',
        showCancelButton: true,
        confirmButtonText: options.confirmText ?? 'Confirmer',
        cancelButtonText: options.cancelText ?? 'Annuler',
        confirmButtonColor: options.danger ? '#dc2626' : '#2563EB',
        cancelButtonColor: '#94A3B8',
        reverseButtons: true,
        focusCancel: true,
    }).then((result) => result.isConfirmed);
};

// Notification simple (succès/erreur/info), remplace les alert() natifs.
window.notifyAction = function (message, options = {}) {
    return Swal.fire({
        title: options.title,
        text: message,
        icon: options.icon ?? 'success',
        confirmButtonText: options.confirmText ?? 'OK',
        confirmButtonColor: '#2563EB',
    });
};

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

Livewire.start();

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