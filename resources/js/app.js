import './bootstrap';

// Livewire Navigation Smooth Transitions
if (typeof window !== 'undefined' && window.Livewire) {
    window.Livewire.hook('navigate.start', () => {
        // Show loading bar
        const loader = document.createElement('div');
        loader.id = 'livewire-loader';
        loader.setAttribute('data-loading', '');
        document.body.appendChild(loader);
    });

    window.Livewire.hook('navigate.end', () => {
        // Hide loading bar
        const loader = document.getElementById('livewire-loader');
        if (loader) {
            setTimeout(() => {
                loader.style.opacity = '0';
                loader.style.transition = 'opacity 0.3s ease-out';
                setTimeout(() => loader.remove(), 300);
            }, 300);
        }
    });
}
